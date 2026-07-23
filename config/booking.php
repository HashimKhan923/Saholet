<?php

return [
    'slot_start_hour' => 0,        // first slot at 00:00
    'slot_end_hour' => 24,         // last slot starts before 24:00 (full day)
    'slot_interval_minutes' => 60, // hourly slots
    'advance_days' => 7,           // how many days ahead are bookable (incl. today)
    'min_lead_hours' => 2,         // earliest bookable slot from "now"
];