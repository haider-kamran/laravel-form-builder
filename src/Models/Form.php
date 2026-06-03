<?php

namespace Hyderkamran\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    protected static function booted(): void
    {
        static::created(function (self $form) {
            event(new \Hyderkamran\FormBuilder\Events\FormCreated($form));
            $form->clearCache();
        });

        static::updated(function (self $form) {
            event(new \Hyderkamran\FormBuilder\Events\FormUpdated($form));
            $form->clearCache();
        });

        static::deleted(function (self $form) {
            $form->clearCache();
        });
    }

    public function clearCache(): void
    {
        if ($this->slug) {
            cache()->forget("form:{$this->slug}");
        }

        cache()->forget("form:{$this->id}");
    }
}
