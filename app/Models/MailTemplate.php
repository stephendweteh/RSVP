<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'slug',
    'name',
    'description',
    'subject',
    'body_html',
    'body_text',
    'sort_order',
])]
class MailTemplate extends Model
{
    public const SLUG_RSVP_SUBMITTED_GUEST = 'rsvp_submitted_guest';

    public const SLUG_RSVP_SUBMITTED_ADMIN = 'rsvp_submitted_admin';

    public const SLUG_RSVP_DECISION_GUEST_APPROVED = 'rsvp_decision_guest_approved';

    public const SLUG_RSVP_DECISION_GUEST_REJECTED = 'rsvp_decision_guest_rejected';

    public const SLUG_RSVP_DECISION_ADMIN = 'rsvp_decision_admin';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
