<?php

return [
    'accepted_mimes' => ['pdf', 'doc', 'docx'],
    'max_size_kb' => 5120, // 5 MB

    // Private disk. Swap to 's3' later without touching controllers.
    'disk' => 'local',
];
