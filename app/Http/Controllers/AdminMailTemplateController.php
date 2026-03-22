<?php

namespace App\Http\Controllers;

use App\Mail\MailTemplateSeed;
use App\Models\MailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMailTemplateController extends Controller
{
    public function edit(MailTemplate $mailTemplate): View
    {
        return view('admin.mail-templates.edit', compact('mailTemplate'));
    }

    public function update(Request $request, MailTemplate $mailTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'subject' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string', 'max:500000'],
            'body_text' => ['nullable', 'string', 'max:500000'],
        ]);

        $mailTemplate->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'subject' => $validated['subject'],
            'body_html' => $validated['body_html'],
            'body_text' => $validated['body_text'] ?? '',
        ]);

        return redirect()
            ->to(route('admin.settings.edit').'#email-templates')
            ->with('success', 'Email template “'.$mailTemplate->name.'” saved.');
    }

    public function reset(MailTemplate $mailTemplate): RedirectResponse
    {
        $defaults = MailTemplateSeed::forSlug($mailTemplate->slug);

        if ($defaults === null) {
            return redirect()
                ->to(route('admin.settings.edit').'#email-templates')
                ->withErrors(['mail_template' => 'No default content for this template.']);
        }

        $mailTemplate->update([
            'name' => $defaults['name'],
            'description' => $defaults['description'],
            'subject' => $defaults['subject'],
            'body_html' => $defaults['body_html'],
            'body_text' => $defaults['body_text'],
            'sort_order' => $defaults['sort_order'],
        ]);

        return redirect()
            ->route('admin.mail-templates.edit', $mailTemplate)
            ->with('success', 'Template reset to default content.');
    }
}
