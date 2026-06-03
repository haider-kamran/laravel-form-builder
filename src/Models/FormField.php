<?php

namespace Hyderkamran\FormBuilder\Models;

use Hyderkamran\FormBuilder\Enums\FieldType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    protected $fillable = [
        'form_id',
        'label',
        'name',
        'type',
        'options',
        'validation_rules',
        'is_required',
        'order',
    ];

    protected $casts = [
        'type'             => FieldType::class,
        'options'          => 'array',
        'validation_rules' => 'array',
        'is_required'      => 'boolean',
        'order'            => 'integer',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Return the raw string value of the type for blade includes.
     */
    public function getTypeValueAttribute(): string
    {
        return $this->type instanceof FieldType
            ? $this->type->value
            : (string) $this->type;
    }
}
