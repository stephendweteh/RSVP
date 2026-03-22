<?php

namespace App\Mail;

/**
 * Factory defaults for mail_templates (migration seed + admin reset).
 */
final class MailTemplateSeed
{
    /**
     * @return list<array{slug: string, name: string, description: string, subject: string, body_html: string, body_text: string, sort_order: int}>
     */
    public static function all(): array
    {
        return [
            [
                'slug' => 'rsvp_submitted_guest',
                'name' => 'Guest — RSVP submitted (pending approval)',
                'description' => 'Placeholders: {{guest_name}}, {{guests_count}}, {{attendance_label}}',
                'subject' => 'We received your RSVP — pending approval',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #333;">
    <p>Hi {{guest_name}},</p>
    <p>Thank you for your RSVP. We have received it and it is <strong>awaiting approval</strong>. We will follow up if needed.</p>
    <p><strong>Summary</strong></p>
    <ul>
        <li>Guests: {{guests_count}}</li>
        <li>Attendance: {{attendance_label}}</li>
    </ul>
    <p style="color:#666;font-size:14px;">This message was sent because you provided an email with your RSVP.</p>
</body>
</html>
HTML,
                'body_text' => <<<'TEXT'
Hi {{guest_name}},

Thank you for your RSVP. We have received it and it is awaiting approval.

Summary:
- Guests: {{guests_count}}
- Attendance: {{attendance_label}}
TEXT,
                'sort_order' => 1,
            ],
            [
                'slug' => 'rsvp_submitted_admin',
                'name' => 'Admin — New RSVP (pending)',
                'description' => 'Placeholders: {{guest_name}}, {{guest_email}}, {{guest_phone}}, {{guests_count}}, {{attendance_label}}, {{guest_message_section}} (HTML block or empty), {{guest_message_text}}',
                'subject' => 'New RSVP pending: {{guest_name}}',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #333;">
    <p>A new RSVP was submitted and is <strong>pending approval</strong>.</p>
    <p><strong>Guest</strong></p>
    <ul>
        <li>Name: {{guest_name}}</li>
        <li>Email: {{guest_email}}</li>
        <li>Phone: {{guest_phone}}</li>
        <li>Attendance: {{attendance_label}}</li>
        <li>Guests: {{guests_count}}</li>
    </ul>
    {{guest_message_section}}
    <p style="color:#666;font-size:14px;">Review RSVPs in the admin dashboard.</p>
</body>
</html>
HTML,
                'body_text' => <<<'TEXT'
A new RSVP was submitted and is pending approval.

Name: {{guest_name}}
Email: {{guest_email}}
Phone: {{guest_phone}}
Attendance: {{attendance_label}}
Guests: {{guests_count}}{{guest_message_text}}

Review RSVPs in the admin dashboard.
TEXT,
                'sort_order' => 2,
            ],
            [
                'slug' => 'rsvp_decision_guest_approved',
                'name' => 'Guest — RSVP approved',
                'description' => 'Placeholders: {{guest_name}}, {{guests_count}}, {{attendance_summary}}, {{table_number_section}}, {{table_number_text}}, {{check_in_qr_section}} (HTML QR + check-in block), {{check_in_qr_text}} (plain check-in URL), {{calendar_links_section}} (HTML “Add to calendar” when configured in Settings), {{calendar_links_text}} (plain URLs)',
                'subject' => 'Your RSVP has been approved',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #333;">
    <p>Hi {{guest_name}},</p>
    <p>Your RSVP has been <strong>approved</strong>. We look forward to celebrating with you.</p>
    {{table_number_section}}
    {{check_in_qr_section}}
    {{calendar_links_section}}
    <p style="color:#666;font-size:14px;">Summary: {{attendance_summary}}</p>
</body>
</html>
HTML,
                'body_text' => <<<'TEXT'
Hi {{guest_name}},

Your RSVP has been approved. We look forward to celebrating with you.
{{table_number_text}}{{check_in_qr_text}}
{{calendar_links_text}}
Summary: {{attendance_summary}}
TEXT,
                'sort_order' => 3,
            ],
            [
                'slug' => 'rsvp_decision_guest_rejected',
                'name' => 'Guest — RSVP not approved',
                'description' => 'Placeholders: {{guest_name}}, {{guests_count}}, {{attendance_summary}}',
                'subject' => 'Update on your RSVP',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #333;">
    <p>Hi {{guest_name}},</p>
    <p>Your RSVP has been <strong>not approved</strong> at this time. If you have questions, please contact the hosts.</p>
    <p style="color:#666;font-size:14px;">Summary: {{attendance_summary}}</p>
</body>
</html>
HTML,
                'body_text' => <<<'TEXT'
Hi {{guest_name}},

Your RSVP has been not approved at this time. If you have questions, please contact the hosts.

Summary: {{attendance_summary}}
TEXT,
                'sort_order' => 4,
            ],
            [
                'slug' => 'rsvp_decision_admin',
                'name' => 'Admin — RSVP status changed',
                'description' => 'Placeholders: {{guest_name}}, {{guest_email}}, {{status_label}}, {{table_row_section}} (HTML &lt;li&gt;… or empty)',
                'subject' => 'RSVP {{status_label}}: {{guest_name}}',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #333;">
    <p>An RSVP status was updated in the dashboard.</p>
    <ul>
        <li><strong>Guest:</strong> {{guest_name}}</li>
        <li><strong>Email:</strong> {{guest_email}}</li>
        <li><strong>New status:</strong> {{status_label}}</li>
        {{table_row_section}}
    </ul>
    <p style="color:#666;font-size:14px;">The guest was sent an email with this update (if their address is on file).</p>
</body>
</html>
HTML,
                'body_text' => <<<'TEXT'
An RSVP status was updated in the dashboard.

Guest: {{guest_name}}
Email: {{guest_email}}
New status: {{status_label}}{{table_number_text}}

The guest was sent an email with this update (if their address is on file).
TEXT,
                'sort_order' => 5,
            ],
        ];
    }

    /**
     * @return array{slug: string, name: string, description: string, subject: string, body_html: string, body_text: string, sort_order: int}|null
     */
    public static function forSlug(string $slug): ?array
    {
        foreach (self::all() as $row) {
            if ($row['slug'] === $slug) {
                return $row;
            }
        }

        return null;
    }
}
