<?php

namespace App\Mail;

use App\Models\MailTemplate;
use App\Models\Rsvp;
use App\Services\MailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RsvpSubmittedAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var array{subject: string, html: string, text: string}|null */
    private ?array $renderedCache = null;

    public function __construct(public Rsvp $rsvp) {}

    public function envelope(): Envelope
    {
        $rendered = $this->rendered();

        return new Envelope(
            subject: $rendered['subject'],
        );
    }

    public function content(): Content
    {
        $rendered = $this->rendered();

        return new Content(
            html: 'mail.raw-html',
            text: 'mail.raw-text',
            with: [
                'body_html' => $rendered['html'],
                'body_text' => $rendered['text'],
            ],
        );
    }

    /**
     * @return array{subject: string, html: string, text: string}
     */
    private function rendered(): array
    {
        return $this->renderedCache ??= (function (): array {
            $vars = MailTemplateRenderer::varsForSubmittedAdmin($this->rsvp);

            return MailTemplateRenderer::render(
                MailTemplate::query()->where('slug', MailTemplate::SLUG_RSVP_SUBMITTED_ADMIN)->firstOrFail(),
                $vars['html'],
                $vars['text'],
            );
        })();
    }
}
