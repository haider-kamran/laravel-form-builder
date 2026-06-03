<?php

namespace Hyderkamran\FormBuilder\Services;

use Hyderkamran\FormBuilder\Models\Form;
use Hyderkamran\FormBuilder\Models\FormSubmission;
use Hyderkamran\FormBuilder\Mail\FormSubmittedNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function send(Form $form, FormSubmission $submission): void
    {
        // allow per-form settings to override global config
        $settings = is_array($form->settings) ? $form->settings : (array) ($form->settings ?? []);

        $enabled = $settings['enable_notifications'] ?? config('form-builder.enable_notifications', true);
        if (! $enabled) {
            return;
        }

        $recipients = collect($settings['notification_recipients'] ?? config('form-builder.notification_recipients', []))->filter()->values()->all();

        if (! empty($recipients)) {
            Mail::to($recipients)->send(new FormSubmittedNotification($form, $submission));
        }

        $webhookEnabled = $settings['webhook_enabled'] ?? config('form-builder.webhook_enabled', false);
        $webhookUrl = $settings['webhook_url'] ?? config('form-builder.webhook_url');

        if ($webhookEnabled && filled($webhookUrl)) {
            Http::timeout(5)->post($webhookUrl, [
                'form' => $form->toArray(),
                'submission' => $submission->toArray(),
            ]);
        }
    }
}
