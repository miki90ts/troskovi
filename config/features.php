<?php

return [
    'pwa_install' => env('FEATURE_PWA_INSTALL', false),
    'receipt_qr_scan' => env('FEATURE_RECEIPT_QR_SCAN', false),
    'receipt_verification_hosts' => array_values(array_filter(array_map(
        static fn (string $host): string => strtolower(trim($host)),
        explode(',', (string) env('FISCAL_VERIFICATION_HOSTS', 'suf.purs.gov.rs')),
    ))),
];
