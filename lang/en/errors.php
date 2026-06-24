<?php

return [

    // Shared calls-to-action (used by layouts/error.blade.php).
    'cta_home' => 'Back to Home',
    'cta_contact' => 'Contact Us',

    '404' => [
        'eyebrow' => 'Error 404',
        'title' => 'Page not found',
        'message' => 'The page you are looking for does not exist or may have been moved.',
    ],

    '403' => [
        'eyebrow' => 'Error 403',
        'title' => 'Access denied',
        'message' => 'You do not have permission to view this page.',
    ],

    '419' => [
        'eyebrow' => 'Error 419',
        'title' => 'Your session expired',
        'message' => 'Your session timed out for security reasons. Please refresh the page and try again.',
    ],

    '429' => [
        'eyebrow' => 'Error 429',
        'title' => 'Too many requests',
        'message' => 'You have made too many requests in a short time. Please wait a moment and try again.',
    ],

    '500' => [
        'eyebrow' => 'Error 500',
        'title' => 'Something went wrong',
        'message' => 'An unexpected error occurred on our end. Our team has been notified — please try again shortly.',
    ],

    '503' => [
        'eyebrow' => 'Maintenance',
        'title' => 'We will be back shortly',
        'message' => 'The site is undergoing brief scheduled maintenance. Thank you for your patience.',
    ],

];
