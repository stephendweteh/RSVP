@extends('layouts.admin')

@section('title', 'Settings — '.config('app.name'))

@section('content')
    <h1 class="h3 mb-2">Settings</h1>
    <p class="text-muted small mb-4">Email delivery, optional SMS via <a href="https://arkesel.com" target="_blank" rel="noopener">Arkesel</a>, and admin notification copies for RSVP activity.</p>

    <div class="row">
        <div class="col-lg-10 col-xl-8">
            <h2 class="h5 mb-2">Email &amp; SMTP</h2>
            <p class="text-muted small mb-3">
                Configure outgoing mail for RSVP emails. Guests receive a confirmation when they submit; they receive another message when an RSVP is approved or rejected.
                @if ($mailSmtpActive)
                    <span class="badge text-bg-success ms-1">Custom SMTP active</span>
                @else
                    <span class="badge text-bg-secondary ms-1">Using <code class="small">.env</code> / default mailer</span>
                @endif
            </p>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.settings.update') }}" method="post">
                        @csrf
                        @method('PUT')
                        <h3 class="h6 text-uppercase text-muted mb-3">SMTP server</h3>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="smtp_host" class="form-label">Host</label>
                                <input type="text" class="form-control @error('smtp_host') is-invalid @enderror" id="smtp_host" name="smtp_host" value="{{ old('smtp_host', \App\Models\Setting::get('mail_smtp_host')) }}" placeholder="smtp.example.com" autocomplete="off">
                                @error('smtp_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="smtp_port" class="form-label">Port</label>
                                <input type="number" class="form-control @error('smtp_port') is-invalid @enderror" id="smtp_port" name="smtp_port" value="{{ old('smtp_port', \App\Models\Setting::get('mail_smtp_port', '587')) }}" min="1" max="65535">
                                @error('smtp_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="smtp_username" class="form-label">Username</label>
                                <input type="text" class="form-control @error('smtp_username') is-invalid @enderror" id="smtp_username" name="smtp_username" value="{{ old('smtp_username', \App\Models\Setting::get('mail_smtp_username')) }}" autocomplete="username">
                                @error('smtp_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="smtp_password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('smtp_password') is-invalid @enderror" id="smtp_password" name="smtp_password" value="" placeholder="{{ filled(\App\Models\Setting::get('mail_smtp_password')) ? 'Leave blank to keep current password' : 'Optional' }}" autocomplete="new-password">
                                @error('smtp_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="smtp_encryption" class="form-label">Encryption</label>
                                <select class="form-select @error('smtp_encryption') is-invalid @enderror" id="smtp_encryption" name="smtp_encryption">
                                    @php $enc = old('smtp_encryption', \App\Models\Setting::get('mail_smtp_encryption', 'tls')); @endphp
                                    <option value="tls" @selected($enc === 'tls')>TLS (usually port 587)</option>
                                    <option value="ssl" @selected($enc === 'ssl')>SSL (usually port 465)</option>
                                    <option value="none" @selected($enc === 'none')>None</option>
                                </select>
                                @error('smtp_encryption')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h3 class="h6 text-uppercase text-muted mb-3">From address</h3>
                        <p class="small text-muted mb-3">Required when SMTP host is set. This address must be allowed by your mail provider.</p>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="mail_from_address" class="form-label">From email</label>
                                <input type="email" class="form-control @error('mail_from_address') is-invalid @enderror" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', \App\Models\Setting::get('mail_from_address')) }}" placeholder="noreply@example.com">
                                @error('mail_from_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="mail_from_name" class="form-label">From name</label>
                                <input type="text" class="form-control @error('mail_from_name') is-invalid @enderror" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', \App\Models\Setting::get('mail_from_name', config('app.name'))) }}">
                                @error('mail_from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h3 class="h6 text-uppercase text-muted mb-3">Admin notifications</h3>
                        <p class="small text-muted mb-3">
                            <strong>Every administrator</strong> receives these copies at their account email (see <a href="{{ route('admin.users.index') }}">Users</a>).
                            Optionally add another address below to include someone who is not an admin user.
                        </p>
                        <div class="mb-4">
                            <label for="admin_notification_email" class="form-label">Extra notification email <span class="text-muted">(optional)</span></label>
                            <input type="email" class="form-control @error('admin_notification_email') is-invalid @enderror" id="admin_notification_email" name="admin_notification_email" value="{{ old('admin_notification_email', \App\Models\Setting::get('admin_notification_email')) }}" placeholder="you@example.com">
                            @error('admin_notification_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Save settings</button>
                    </form>
                </div>
            </div>

            <h2 id="sms-arkesel" class="h5 mb-2 mt-5">SMS (Arkesel)</h2>
            <p class="text-muted small mb-3">
                Send short text messages when guests RSVP or when you approve or reject them. Uses the
                <a href="https://developers.arkesel.com/sms-api-documentation" target="_blank" rel="noopener">Arkesel SMS API</a>
                (international numbers, digits only, no <code class="small">+</code> in the payload).
                @if ($smsArkeselActive)
                    <span class="badge text-bg-success ms-1">SMS active</span>
                @else
                    <span class="badge text-bg-secondary ms-1">SMS off or incomplete</span>
                @endif
            </p>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.settings.sms.update') }}" method="post">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="sms_arkesel_enabled" value="0">
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="sms_arkesel_enabled" name="sms_arkesel_enabled" value="1" @checked(old('sms_arkesel_enabled', \App\Models\Setting::get('sms_arkesel_enabled')) === '1')>
                            <label class="form-check-label" for="sms_arkesel_enabled">Enable Arkesel SMS</label>
                        </div>
                        <p class="small text-muted mb-3">Turn off the switch and save to disable SMS without removing your API key.</p>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="sms_arkesel_sender" class="form-label">Sender ID</label>
                                <input type="text" class="form-control @error('sms_arkesel_sender') is-invalid @enderror" id="sms_arkesel_sender" name="sms_arkesel_sender" value="{{ old('sms_arkesel_sender', \App\Models\Setting::get('sms_arkesel_sender')) }}" maxlength="11" placeholder="Max 11 characters" autocomplete="off">
                                @error('sms_arkesel_sender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="sms_country_code" class="form-label">Default country code</label>
                                <input type="text" class="form-control @error('sms_country_code') is-invalid @enderror" id="sms_country_code" name="sms_country_code" value="{{ old('sms_country_code', \App\Models\Setting::get('sms_country_code', '233')) }}" maxlength="4" placeholder="233" pattern="[0-9]{1,4}" inputmode="numeric" autocomplete="off">
                                @error('sms_country_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">Used when a number starts with <code class="small">0</code> (e.g. Ghana <code class="small">233</code>).</div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="sms_arkesel_api_key" class="form-label">API key</label>
                            <input type="password" class="form-control @error('sms_arkesel_api_key') is-invalid @enderror" id="sms_arkesel_api_key" name="sms_arkesel_api_key" value="" placeholder="{{ filled(\App\Models\Setting::get('sms_arkesel_api_key')) ? 'Leave blank to keep current key' : 'From Arkesel dashboard' }}" autocomplete="new-password">
                            @error('sms_arkesel_api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <h3 class="h6 text-uppercase text-muted mb-3">Message text</h3>
                        <p class="small text-muted mb-3">
                            Each box uses the same <code class="small">@{{ placeholder }}</code> syntax as the matching email template’s <strong>plain-text</strong> body.
                            <strong>Leave a box empty</strong> to send the current email template text for that event (so SMS stays in sync with <a href="{{ route('admin.settings.edit') }}#email-templates">Email content</a> until you customize it here).
                            Optional: <code class="small">@{{ app_name }}</code> for the site name. Very long text may be cut off by the SMS provider.
                        </p>
                        @php
                            $smsBoxes = [
                                ['key' => \App\Services\RsvpSmsNotifier::SETTING_BODY_SUBMITTED_GUEST, 'slug' => \App\Models\MailTemplate::SLUG_RSVP_SUBMITTED_GUEST, 'title' => 'Guest — RSVP submitted (pending)'],
                                ['key' => \App\Services\RsvpSmsNotifier::SETTING_BODY_SUBMITTED_ADMIN, 'slug' => \App\Models\MailTemplate::SLUG_RSVP_SUBMITTED_ADMIN, 'title' => 'Admin — new RSVP (pending)'],
                                ['key' => \App\Services\RsvpSmsNotifier::SETTING_BODY_DECISION_GUEST_APPROVED, 'slug' => \App\Models\MailTemplate::SLUG_RSVP_DECISION_GUEST_APPROVED, 'title' => 'Guest — RSVP approved'],
                                ['key' => \App\Services\RsvpSmsNotifier::SETTING_BODY_DECISION_GUEST_REJECTED, 'slug' => \App\Models\MailTemplate::SLUG_RSVP_DECISION_GUEST_REJECTED, 'title' => 'Guest — RSVP not approved'],
                                ['key' => \App\Services\RsvpSmsNotifier::SETTING_BODY_DECISION_ADMIN, 'slug' => \App\Models\MailTemplate::SLUG_RSVP_DECISION_ADMIN, 'title' => 'Admin — RSVP approved or rejected'],
                            ];
                        @endphp
                        @foreach ($smsBoxes as $box)
                            @php $tplRow = $mailTemplates->firstWhere('slug', $box['slug']); @endphp
                            <div class="mb-3">
                                <label for="{{ $box['key'] }}" class="form-label">{{ $box['title'] }}</label>
                                <textarea class="form-control font-monospace small @error($box['key']) is-invalid @enderror" id="{{ $box['key'] }}" name="{{ $box['key'] }}" rows="5" maxlength="2000" placeholder="Leave empty to use the matching email template’s plain-text body.">{{ old($box['key'], \App\Models\Setting::get($box['key'])) }}</textarea>
                                @error($box['key'])<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if ($tplRow)
                                    <p class="form-text mb-1">{{ $tplRow->description }} · <a href="{{ route('admin.mail-templates.edit', $box['slug']) }}">Edit email template</a></p>
                                @endif
                            </div>
                        @endforeach

                        <h3 class="h6 text-uppercase text-muted mb-3">When to text guests</h3>
                        <div class="mb-3">
                            <input type="hidden" name="sms_guest_on_submit" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sms_guest_on_submit" name="sms_guest_on_submit" value="1" @checked(old('sms_guest_on_submit', \App\Models\Setting::get('sms_guest_on_submit')) === '1')>
                                <label class="form-check-label" for="sms_guest_on_submit">After they submit (pending approval)</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="sms_guest_on_approve" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sms_guest_on_approve" name="sms_guest_on_approve" value="1" @checked(old('sms_guest_on_approve', \App\Models\Setting::get('sms_guest_on_approve')) === '1')>
                                <label class="form-check-label" for="sms_guest_on_approve">When RSVP is approved</label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <input type="hidden" name="sms_guest_on_reject" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sms_guest_on_reject" name="sms_guest_on_reject" value="1" @checked(old('sms_guest_on_reject', \App\Models\Setting::get('sms_guest_on_reject')) === '1')>
                                <label class="form-check-label" for="sms_guest_on_reject">When RSVP is rejected</label>
                            </div>
                        </div>

                        <h3 class="h6 text-uppercase text-muted mb-3">Admin SMS</h3>
                        <p class="small text-muted mb-3">
                            SMS goes to <strong>each administrator’s phone</strong> when set on their user profile (edit user under <a href="{{ route('admin.users.index') }}">Users</a>). Same country-code rules as guest numbers.
                            Optionally add another number below (e.g. shared line).
                        </p>
                        <div class="mb-3">
                            <label for="admin_notification_phone_sms" class="form-label">Extra notification phone <span class="text-muted">(optional)</span></label>
                            <input type="text" class="form-control @error('admin_notification_phone') is-invalid @enderror" id="admin_notification_phone_sms" name="admin_notification_phone" value="{{ old('admin_notification_phone', \App\Models\Setting::get('admin_notification_phone')) }}" maxlength="30" placeholder="e.g. 0550123456" autocomplete="tel">
                            @error('admin_notification_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="sms_admin_on_submit" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sms_admin_on_submit" name="sms_admin_on_submit" value="1" @checked(old('sms_admin_on_submit', \App\Models\Setting::get('sms_admin_on_submit')) === '1')>
                                <label class="form-check-label" for="sms_admin_on_submit">New RSVP submitted (pending)</label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <input type="hidden" name="sms_admin_on_decision" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sms_admin_on_decision" name="sms_admin_on_decision" value="1" @checked(old('sms_admin_on_decision', \App\Models\Setting::get('sms_admin_on_decision')) === '1')>
                                <label class="form-check-label" for="sms_admin_on_decision">RSVP approved or rejected</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save SMS settings</button>
                    </form>
                </div>
            </div>

            <h2 id="calendar-event" class="h5 mb-2 mt-5">Calendar event (approval email)</h2>
            <p class="text-muted small mb-3">
                When enabled, the <strong>Guest — RSVP approved</strong> email can include Google, Outlook, and .ics links. Use placeholders <code class="small">@{{ calendar_links_section }}</code> (HTML) and <code class="small">@{{ calendar_links_text }}</code> in that template — reset that template if yours predates this feature.
            </p>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.settings.calendar.update') }}" method="post">
                        @csrf
                        @method('PUT')
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="calendar_event_enabled" name="calendar_event_enabled" value="1" @checked(old('calendar_event_enabled', \App\Models\Setting::get('calendar_event_enabled')) === '1')>
                            <label class="form-check-label" for="calendar_event_enabled">Include add-to-calendar links in approval emails</label>
                        </div>
                        <div class="mb-3">
                            <label for="calendar_event_title" class="form-label">Event title</label>
                            <input type="text" class="form-control @error('calendar_event_title') is-invalid @enderror" id="calendar_event_title" name="calendar_event_title" value="{{ old('calendar_event_title', \App\Models\Setting::get('calendar_event_title')) }}" maxlength="255" placeholder="Wedding celebration">
                            @error('calendar_event_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="calendar_event_start" class="form-label">Start <span class="text-muted small">({{ config('app.timezone') }})</span></label>
                                <input type="datetime-local" class="form-control @error('calendar_event_start') is-invalid @enderror" id="calendar_event_start" name="calendar_event_start" value="{{ old('calendar_event_start', \App\Models\Setting::get('calendar_event_start')) }}">
                                @error('calendar_event_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="calendar_event_end" class="form-label">End</label>
                                <input type="datetime-local" class="form-control @error('calendar_event_end') is-invalid @enderror" id="calendar_event_end" name="calendar_event_end" value="{{ old('calendar_event_end', \App\Models\Setting::get('calendar_event_end')) }}">
                                @error('calendar_event_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="calendar_event_location" class="form-label">Location <span class="text-muted">(optional)</span></label>
                            <input type="text" class="form-control @error('calendar_event_location') is-invalid @enderror" id="calendar_event_location" name="calendar_event_location" value="{{ old('calendar_event_location', \App\Models\Setting::get('calendar_event_location')) }}" maxlength="500">
                            @error('calendar_event_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="calendar_event_description" class="form-label">Description <span class="text-muted">(optional)</span></label>
                            <textarea class="form-control @error('calendar_event_description') is-invalid @enderror" id="calendar_event_description" name="calendar_event_description" rows="3" maxlength="5000">{{ old('calendar_event_description', \App\Models\Setting::get('calendar_event_description')) }}</textarea>
                            @error('calendar_event_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <p class="small text-muted mb-3">Public download: <a href="{{ route('rsvp.calendar.ics') }}" target="_blank" rel="noopener">{{ url('/rsvp/calendar/event.ics') }}</a> (404 until enabled with valid times).</p>
                        <button type="submit" class="btn btn-primary">Save calendar</button>
                    </form>
                </div>
            </div>

            <h2 id="email-templates" class="h5 mb-2 mt-5">Email content</h2>
            <p class="text-muted small mb-3">
                Customize subjects and bodies for automated RSVP emails. Use placeholders such as <code class="small">@{{ guest_name }}</code> — each row lists which names are replaced when the message is sent.
            </p>
            <div class="card border-0 shadow-sm mb-2">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Template</th>
                                <th>Subject</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mailTemplates as $tpl)
                                <tr>
                                    <td class="fw-medium">{{ $tpl->name }}</td>
                                    <td class="text-muted text-truncate" style="max-width: 14rem;" title="{{ $tpl->subject }}">{{ $tpl->subject }}</td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('admin.mail-templates.edit', $tpl) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('admin.mail-templates.reset', $tpl) }}" method="post" class="d-inline" onsubmit="return confirm('Reset “{{ $tpl->name }}” to default content?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Reset</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <h2 class="h5 mb-2 mt-5">Guest list (RSVPs)</h2>
            <p class="text-muted small mb-3">
                Permanently delete every RSVP in the database. This does not affect users, slider images, or email settings.
                <strong class="text-danger">Cannot be undone.</strong>
                Currently <strong>{{ number_format($rsvpCount) }}</strong> in the list.
            </p>
            <div class="card border-danger border-opacity-25 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.settings.rsvps.destroy-all') }}" method="post"
                          onsubmit="return confirm('Delete all {{ number_format($rsvpCount) }} RSVP(s)? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <div class="form-check mb-3">
                            <input class="form-check-input @error('confirm_clear_rsvps') is-invalid @enderror" type="checkbox" value="1" id="confirm_clear_rsvps" name="confirm_clear_rsvps" {{ old('confirm_clear_rsvps') ? 'checked' : '' }}>
                            <label class="form-check-label" for="confirm_clear_rsvps">
                                I understand all guest RSVPs will be permanently deleted
                            </label>
                            @error('confirm_clear_rsvps')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-outline-danger" @disabled($rsvpCount === 0)>
                            Clear all RSVPs
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
