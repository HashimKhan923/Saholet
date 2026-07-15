<?php

return [
    // key => inner SVG markup (rendered inside a <svg viewBox="0 0 24 24">)
    'icons' => [
        'ac' => '<g stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><line x1="12" y1="3" x2="12" y2="21"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="5.5" y1="5.5" x2="18.5" y2="18.5"/><line x1="18.5" y1="5.5" x2="5.5" y2="18.5"/></g>',
        'plumbing' => '<path d="M12 3s6 6.5 6 10.5a6 6 0 1 1-12 0C6 9.5 12 3 12 3z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
        'electrical' => '<path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12l1-8.5z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
        'cleaning' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
        'painting' => '<g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="6" rx="1.5"/><path d="M12 9v3"/><path d="M9.5 12h5l-.6 6a2 2 0 0 1-2 1.8 2 2 0 0 1-1.8-1.8L9.5 12z"/></g>',
        'carpentry' => '<path d="M21 4a5 5 0 0 1-6.6 6.6L6 19a2 2 0 0 1-3-3l8.4-8.4A5 5 0 0 1 18 1l-3 3 2 2 3-3z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
        'appliance' => '<g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"/><line x1="4" y1="9" x2="20" y2="9" stroke-linecap="round"/><circle cx="8" cy="6" r="0.6" fill="currentColor"/></g>',
        'pest' => '<g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="13" rx="4" ry="5"/><path d="M12 8V5M9 6 7 4M15 6l2-2M8 13H4M20 13h-4M8 17l-3 2M16 17l3 2"/></g>',
        'default' => '<g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M9 12h6M12 9v6" stroke-linecap="round"/></g>',
    ],
];