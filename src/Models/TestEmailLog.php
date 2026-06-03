<?php

namespace Hyderkamran\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class TestEmailLog extends Model
{
    protected $table = 'test_email_logs';

    protected $fillable = [
        'form_id', 'recipients', 'status', 'response', 'attempts',
    ];

    protected $casts = [
        'recipients' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
