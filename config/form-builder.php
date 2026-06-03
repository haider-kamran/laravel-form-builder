<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    | The disk used for storing uploaded files from form submissions.
    */
    'storage_disk' => env('FORM_BUILDER_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Export Formats
    |--------------------------------------------------------------------------
    | Supported export formats for form submissions.
    */
    'export_format' => ['csv', 'xlsx', 'pdf'],

    /*
    |--------------------------------------------------------------------------
    | Enable Notifications
    |--------------------------------------------------------------------------
    | Globally enable or disable email notifications on submission.
    */
    'enable_notifications' => env('FORM_BUILDER_NOTIFICATIONS', true),

    /*
    |--------------------------------------------------------------------------
    | Default Notification Recipients
    |--------------------------------------------------------------------------
    | Fallback email recipients when no per-form recipients are configured.
    */
    'notification_recipients' => array_filter(
        explode(',', env('FORM_BUILDER_RECIPIENTS', ''))
    ),

    /*
    |--------------------------------------------------------------------------
    | Admin Middleware
    |--------------------------------------------------------------------------
    | Middleware applied to all admin panel routes.
    | Add 'auth' to restrict to authenticated users.
    */
    'admin_middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (seconds)
    |--------------------------------------------------------------------------
    | How long form schemas are cached. Set to 0 to disable caching.
    */
    'cache_ttl' => env('FORM_BUILDER_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'max_size_kb'  => env('FORM_BUILDER_MAX_UPLOAD_KB', 10240), // 10 MB
        'allowed_mimes'=> ['jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'],
    ],
];
