<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Temporary File Uploads
    |--------------------------------------------------------------------------
    | These rules are applied to Livewire’s temporary uploads (wire:model on
    | <input type="file">). Added Excel/CSV types so .xlsx/.xls/.csv work.
    */
    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_DISK', 'local'),
        'directory' => 'livewire-tmp',

        // Allow images, docs, zip, and EXCEL/CSV; 20 MB per file
        'rules' => 'file|mimes:pdf,doc,docx,png,jpg,jpeg,zip,xlsx,xls,csv|max:20480',

        // Leave this null unless you need to wrap uploads in custom middleware
        'middleware' => null,
    ],

    // Make sure Livewire uses the "web" middleware group
    'middleware_group' => 'web',
];
