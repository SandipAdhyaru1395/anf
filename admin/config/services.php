<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ERP Services
    |--------------------------------------------------------------------------
    */
    'planufac' => [
        // Credentials are stored in DB (settings) and loaded by PlanufacClient.
        // Keep only non-secret defaults here.
        'base_url' => 'https://sandbox.planufac.com',
        'timeout' => env('PLANUFAC_TIMEOUT', 20),
    ],

    'dna_payments' => [
        'currency' => env('DNA_PAYMENTS_CURRENCY', 'GBP'),
        'return_url' => env('DNA_PAYMENTS_RETURN_URL'),
        'failure_url' => env('DNA_PAYMENTS_FAILURE_URL'),
        'callback_url' => env('DNA_PAYMENTS_CALLBACK_URL'),
    ],

];
