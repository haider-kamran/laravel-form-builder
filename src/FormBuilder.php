<?php

namespace Hyderkamran\FormBuilder;

use Hyderkamran\FormBuilder\Models\Form;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class FormBuilder
{
    /**
     * Render a form by slug or numeric ID.
     */
    public function render(string|int $formIdentifier): string
    {
        $ttl  = (int) config('form-builder.cache_ttl', 3600);
        $key  = "form-builder:schema:{$formIdentifier}";

        $form = $ttl > 0
            ? Cache::remember($key, $ttl, fn () => $this->resolveForm($formIdentifier))
            : $this->resolveForm($formIdentifier);

        if (!$form || !$form->is_active) {
            return '';
        }

        // Ensure $errors exists for rendering tests outside web middleware
        $errors = \Illuminate\Support\Facades\View::shared('errors', new \Illuminate\Support\ViewErrorBag);

        return View::make('form-builder::form', compact('form', 'errors'))->render();
    }

    /**
     * Programmatically create a form.
     */
    public function make(array $attributes): Form
    {
        return Form::create($attributes);
    }

    /**
     * Flush the cached schema for a given form.
     */
    public function flushCache(string|int $formIdentifier): void
    {
        Cache::forget("form-builder:schema:{$formIdentifier}");
    }

    // ─────────────────────────────────────────────────────────────
    // Internal
    // ─────────────────────────────────────────────────────────────

    protected function resolveForm(string|int $identifier): ?Form
    {
        return is_numeric($identifier)
            ? Form::with('fields')->find($identifier)
            : Form::with('fields')->where('slug', $identifier)->first();
    }
}
