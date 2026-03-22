<?php

namespace App\Services;

use App\Models\MailTemplate;
use App\Models\Rsvp;
use App\Models\Setting;

class RsvpSmsNotifier
{
    public const SETTING_BODY_SUBMITTED_GUEST = 'sms_body_rsvp_submitted_guest';

    public const SETTING_BODY_SUBMITTED_ADMIN = 'sms_body_rsvp_submitted_admin';

    public const SETTING_BODY_DECISION_GUEST_APPROVED = 'sms_body_rsvp_decision_guest_approved';

    public const SETTING_BODY_DECISION_GUEST_REJECTED = 'sms_body_rsvp_decision_guest_rejected';

    public const SETTING_BODY_DECISION_ADMIN = 'sms_body_rsvp_decision_admin';

    public static function guestSubmitted(Rsvp $rsvp): void
    {
        if (Setting::get('sms_guest_on_submit') !== '1') {
            return;
        }

        $to = ArkeselSmsService::normalizeRecipient($rsvp->phone, Setting::get('sms_country_code', '233'));
        if ($to === null) {
            return;
        }

        $msg = self::resolveMessage(
            self::SETTING_BODY_SUBMITTED_GUEST,
            MailTemplate::SLUG_RSVP_SUBMITTED_GUEST,
            MailTemplateRenderer::varsForSubmittedGuest($rsvp)['text'],
        );

        if (trim($msg) === '') {
            return;
        }

        ArkeselSmsService::send($to, $msg);
    }

    public static function guestDecision(Rsvp $rsvp, string $decision): void
    {
        if ($decision === Rsvp::STATUS_APPROVED && Setting::get('sms_guest_on_approve') !== '1') {
            return;
        }
        if ($decision === Rsvp::STATUS_REJECTED && Setting::get('sms_guest_on_reject') !== '1') {
            return;
        }

        $to = ArkeselSmsService::normalizeRecipient($rsvp->phone, Setting::get('sms_country_code', '233'));
        if ($to === null) {
            return;
        }

        if ($decision === Rsvp::STATUS_APPROVED) {
            $settingKey = self::SETTING_BODY_DECISION_GUEST_APPROVED;
            $slug = MailTemplate::SLUG_RSVP_DECISION_GUEST_APPROVED;
            $vars = MailTemplateRenderer::varsForDecisionGuestApproved($rsvp)['text'];
        } else {
            $settingKey = self::SETTING_BODY_DECISION_GUEST_REJECTED;
            $slug = MailTemplate::SLUG_RSVP_DECISION_GUEST_REJECTED;
            $vars = MailTemplateRenderer::varsForDecisionGuestRejected($rsvp)['text'];
        }

        $msg = self::resolveMessage($settingKey, $slug, $vars);

        if (trim($msg) === '') {
            return;
        }

        ArkeselSmsService::send($to, $msg);
    }

    public static function adminNewRsvp(Rsvp $rsvp): void
    {
        if (Setting::get('sms_admin_on_submit') !== '1') {
            return;
        }

        $msg = self::resolveMessage(
            self::SETTING_BODY_SUBMITTED_ADMIN,
            MailTemplate::SLUG_RSVP_SUBMITTED_ADMIN,
            MailTemplateRenderer::varsForSubmittedAdmin($rsvp)['text'],
        );

        if (trim($msg) === '') {
            return;
        }

        foreach (AdminNotificationRecipients::normalizedAdminSmsPhones() as $to) {
            ArkeselSmsService::send($to, $msg);
        }
    }

    public static function adminDecision(Rsvp $rsvp, string $decision): void
    {
        if (Setting::get('sms_admin_on_decision') !== '1') {
            return;
        }

        $msg = self::resolveMessage(
            self::SETTING_BODY_DECISION_ADMIN,
            MailTemplate::SLUG_RSVP_DECISION_ADMIN,
            MailTemplateRenderer::varsForDecisionAdmin($rsvp, $decision)['text'],
        );

        if (trim($msg) === '') {
            return;
        }

        foreach (AdminNotificationRecipients::normalizedAdminSmsPhones() as $to) {
            ArkeselSmsService::send($to, $msg);
        }
    }

    /**
     * @param  array<string, string>  $textVars
     */
    public static function resolveMessage(string $settingKey, string $mailSlug, array $textVars): string
    {
        $custom = trim(Setting::get($settingKey));
        $template = $custom !== '' ? $custom : self::defaultSmsTemplateFromMail($mailSlug);
        $vars = array_merge($textVars, ['app_name' => config('app.name')]);

        return MailTemplateRenderer::interpolate($template, $vars);
    }

    public static function defaultSmsTemplateFromMail(string $mailSlug): string
    {
        $tpl = MailTemplate::query()->where('slug', $mailSlug)->first();

        return $tpl !== null ? (string) $tpl->body_text : '';
    }
}
