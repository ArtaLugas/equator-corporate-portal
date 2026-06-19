<?php

/**
 * Social platform metadata — single source of truth for brand colors and labels.
 *
 * Key: lowercase platform slug (must match SocialLink.platform value, case-insensitive).
 * color: official brand hex — used as hover color via inline style (bypasses Tailwind purging).
 * label: human-readable display name for aria-label / title attributes.
 *
 * Adding a new platform: add one entry here. No Blade, model, or JS changes needed.
 */
return [
    'linkedin' => ['color' => '#0A66C2', 'label' => 'LinkedIn'],
    'instagram' => ['color' => '#E1306C', 'label' => 'Instagram'],
    'facebook' => ['color' => '#1877F2', 'label' => 'Facebook'],
    'twitter' => ['color' => '#1DA1F2', 'label' => 'Twitter'],
    'x' => ['color' => '#e7e9ea', 'label' => 'X'],
    'youtube' => ['color' => '#FF0000', 'label' => 'YouTube'],
    'github' => ['color' => '#f0f6fc', 'label' => 'GitHub'],
    'tiktok' => ['color' => '#69C9D0', 'label' => 'TikTok'],
    'whatsapp' => ['color' => '#25D366', 'label' => 'WhatsApp'],
    'telegram' => ['color' => '#2AABEE', 'label' => 'Telegram'],
    'pinterest' => ['color' => '#E60023', 'label' => 'Pinterest'],
    'discord' => ['color' => '#5865F2', 'label' => 'Discord'],
    'vimeo' => ['color' => '#1AB7EA', 'label' => 'Vimeo'],
    'medium' => ['color' => '#ffffff', 'label' => 'Medium'],
    'behance' => ['color' => '#1769FF', 'label' => 'Behance'],
    'dribbble' => ['color' => '#EA4C89', 'label' => 'Dribbble'],
];
