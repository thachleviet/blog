<?php
return [
    'scope' => [
        'read_products',
        'write_products',
        'read_script_tags',
        'write_script_tags',
        'read_themes',
        'write_content',
        'write_themes',
    ],
    'redirect_before_install' => env('APP_URL').'/auth'
];