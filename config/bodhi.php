<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Registration Rules
    |--------------------------------------------------------------------------
    |
    | To restrict registration to specific email domains, set domain_resticted
    | to true.  The domains variable must be listed in the format for a regex
    | string (the periods must be escaped with a \).  To include multiple
    | domains, separate them with a pipe, for example: 'gmail\.com|yahoo\.com'.
    |
    */

    'registration' => [
        'domain_restricted' => false,
        'domains' => 'pierce.ctc\.edu|gmail.com|yahoo.com|hotmail.com|live.com|outlook.com',
        'domain_error' => 'Registration is currently restricted to @pierce.ctc.edu email addresses.',
        'email_error' => 'Registration is currently restricted to @pierce.ctc.edu email addresses.'
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Seed Settings
    |--------------------------------------------------------------------------
    |
    | Credentials for administrator on database seed.
    |
    */

    'development' => [
        'admin_user_firstname' => Env::get('DEV_ADMIN_FIRSTNAME'),
        'admin_user_firstname' => env('DEV_ADMIN_FIRSTNAME'),
        'admin_user_lastname' => env('DEV_ADMIN_LASTNAME'),
        'admin_user_email' => env('DEV_ADMIN_EMAIL'),
        'admin_pwd' => env('DEV_ADMIN_PWD'),
    ],

];
