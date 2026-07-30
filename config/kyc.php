<?php

return [
    // Document types collected during provider verification.
    'documents' => [
        'cnic_front' => ['label' => 'CNIC — Front', 'required' => true],
        'cnic_back'  => ['label' => 'CNIC — Back', 'required' => true],
        'selfie'     => ['label' => 'Selfie holding CNIC', 'required' => true],
        'certificate' => ['label' => 'Trade certificate', 'required' => false],
    ],

    // Includes heic/heif since that's the default camera format on iPhones —
    // rejecting it outright made uploads from most iOS users fail silently.
    'accepted_mimes' => ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'pdf'],
    'max_size_kb' => 8192, // 8 MB — real phone camera photos routinely exceed the old 4 MB cap.

    // Private disk. Swap to 's3' later without touching controllers.
    'disk' => 'local',
];