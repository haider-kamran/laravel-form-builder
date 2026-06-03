<?php

namespace Hyderkamran\FormBuilder\Facades;

use Illuminate\Support\Facades\Facade;

class FormBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'form-builder';
    }
}
