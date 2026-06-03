<?php

namespace Hyderkamran\FormBuilder\Services;

use Hyderkamran\FormBuilder\Models\Form;
use Hyderkamran\FormBuilder\Models\FormSubmission;
use Hyderkamran\FormBuilder\Events\FormSubmitted;
use Hyderkamran\FormBuilder\Enums\FieldType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class FormSubmissionService
{
    /**
     * Validate, store, and trigger events for a form submission.
     */
    public function handleSubmission(Form $form, Request $request): FormSubmission
    {
        $rules = $this->buildValidationRules($form);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $this->processFiles($form, $validator->validated(), $request);

        $submission = $form->submissions()->create([
            'data'       => $data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Dispatch event
        event(new FormSubmitted($form, $submission));

        // Send notifications if enabled
        $this->sendNotifications($form, $submission);

        // Fire webhooks if configured
        $this->fireWebhook($form, $submission);

        return $submission;
    }

    // ─────────────────────────────────────────────────────────────
    // Validation Rule Builder
    // ─────────────────────────────────────────────────────────────

    protected function buildValidationRules(Form $form): array
    {
        $rules = [];

        foreach ($form->fields as $field) {
            $typeValue = $field->type instanceof FieldType
                ? $field->type->value
                : (string) $field->type;

            $fieldRules = $field->is_required ? ['required'] : ['nullable'];

            // Type-specific base rules
            $fieldRules = array_merge($fieldRules, match ($typeValue) {
                'email'    => ['email:rfc,dns'],
                'url'      => ['url'],
                'number'   => ['numeric'],
                'tel'      => ['string'],
                'date'     => ['date'],
                'datetime' => ['date'],
                'time'     => ['date_format:H:i', 'date_format:H:i:s'],
                'file'     => ['file'],
                'image'    => ['image'],
                'toggle'   => ['boolean'],
                default    => ['string'],
            });

            // Merge developer-defined validation_rules (JSON)
            if (!empty($field->validation_rules)) {
                foreach ($field->validation_rules as $key => $val) {
                    if ($key === 'min')    { $fieldRules[] = 'min:' . $val; }
                    elseif ($key === 'max') { $fieldRules[] = 'max:' . $val; }
                    elseif ($key === 'regex') { $fieldRules[] = 'regex:/' . $val . '/'; }
                    elseif ($key === 'custom') { $fieldRules[] = $val; }
                    else { $fieldRules[] = $key . ':' . $val; }
                }
            }

            // Handle multi-file / checkbox arrays
            if (in_array($typeValue, ['file', 'image'])) {
                if (!empty($field->options['multiple'])) {
                    $rules[$field->name]     = ['nullable', 'array'];
                    $rules[$field->name . '.*'] = ['file'];
                    continue;
                }
            }
            if ($typeValue === 'checkbox' && !empty($field->options['choices'])) {
                $rules[$field->name]     = ['nullable', 'array'];
                $rules[$field->name . '.*'] = ['string'];
                continue;
            }

            $rules[$field->name] = $fieldRules;
        }

        return $rules;
    }

    // ─────────────────────────────────────────────────────────────
    // File Processing
    // ─────────────────────────────────────────────────────────────

    protected function processFiles(Form $form, array $data, Request $request): array
    {
        $disk = config('form-builder.storage_disk', 'public');

        foreach ($form->fields as $field) {
            $typeValue = $field->type instanceof FieldType
                ? $field->type->value
                : (string) $field->type;

            if (!in_array($typeValue, ['file', 'image'])) {
                continue;
            }

            if (!$request->hasFile($field->name)) {
                continue;
            }

            $files = $request->file($field->name);

            if (is_array($files)) {
                $paths = [];
                foreach ($files as $file) {
                    $paths[] = $file->store('form-builder', $disk);
                }
                $data[$field->name] = $paths;
            } else {
                $data[$field->name] = $files->store('form-builder', $disk);
            }
        }

        return $data;
    }

    // ─────────────────────────────────────────────────────────────
    // Email Notifications
    // ─────────────────────────────────────────────────────────────

    protected function sendNotifications(Form $form, FormSubmission $submission): void
    {
        $settings = (array) ($form->settings ?? []);

        $enabled = $settings['enable_notifications']
            ?? config('form-builder.enable_notifications', true);

        if (!$enabled) {
            return;
        }

        $recipients = $settings['notification_recipients']
            ?? config('form-builder.notification_recipients', []);

        if (empty($recipients)) {
            return;
        }

        try {
            Mail::to(array_filter((array) $recipients))
                ->send(new \Hyderkamran\FormBuilder\Mail\FormSubmittedNotification($form, $submission));
        } catch (\Throwable $e) {
            report($e); // Log but don't fail the submission
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Webhook
    // ─────────────────────────────────────────────────────────────

    protected function fireWebhook(Form $form, FormSubmission $submission): void
    {
        $settings = (array) ($form->settings ?? []);

        if (empty($settings['webhook_enabled']) || empty($settings['webhook_url'])) {
            return;
        }

        try {
            Http::timeout(5)->post($settings['webhook_url'], [
                'event'      => 'form.submitted',
                'form_id'    => $form->id,
                'form_slug'  => $form->slug,
                'submission' => $submission->toArray(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
