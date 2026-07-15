<?php

return [
    // Document types collected during provider verification.
    'documents' => [
        'cnic_front' => ['label' => 'CNIC — Front', 'required' => true],
        'cnic_back'  => ['label' => 'CNIC — Back', 'required' => true],
        'selfie'     => ['label' => 'Selfie holding CNIC', 'required' => true],
        'certificate' => ['label' => 'Trade certificate', 'required' => false],
    ],

    'accepted_mimes' => ['jpg', 'jpeg', 'png', 'pdf'],
    'max_size_kb' => 4096, // 4 MB

    // Private disk. Swap to 's3' later without touching controllers.
    'disk' => 'local',
];