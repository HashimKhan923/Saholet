<?php

return [
    'slot_start_hour' => 9,        // first slot at 09:00
    'slot_end_hour' => 18,         // last slot starts before 18:00
    'slot_interval_minutes' => 60, // hourly slots
    'advance_days' => 7,           // how many days ahead are bookable (incl. today)
    'min_lead_hours' => 2,         // earliest bookable slot from "now"
];