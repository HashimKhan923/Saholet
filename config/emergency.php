<?php

return [
    // Match providers only within the request's city (approximates "nearest" without geo math).
    'match_city_only' => true,

    // Cap how many top-ranked providers get alerted per request.
    'max_providers_notified' => 15,
];