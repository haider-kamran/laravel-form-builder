<?php

namespace Hyderkamran\FormBuilder\Traits;

use Hyderkamran\FormBuilder\Models\Form;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasForms
{
    /**
     * Optional relation if you want to attach forms polymorphically.
     */
    public function forms(): MorphToMany
    {
        return $this->morphToMany(Form::class, 'model', 'model_has_forms');
    }
}
