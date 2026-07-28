<?php

// Source of truth for what a staff account can be granted access to: which
// admin pages ("modules") exist, and which of the 4 permission actions each
// one actually uses. Keys must match the `permission:` middleware argument
// used on the matching admin route group in routes/web.php.
return [
    'requests' => ['label' => 'Requests inbox', 'actions' => ['view']],
    'categories' => ['label' => 'Categories', 'actions' => ['view', 'create', 'edit', 'delete']],
    'services' => ['label' => 'Services', 'actions' => ['view', 'create', 'edit', 'delete']],
    'service-areas' => ['label' => 'Service areas', 'actions' => ['view', 'create', 'edit', 'delete']],
    'faqs' => ['label' => 'FAQs', 'actions' => ['view', 'create', 'edit', 'delete']],
    'bookings' => ['label' => 'Bookings', 'actions' => ['view', 'create']],
    'emergencies' => ['label' => 'Emergencies', 'actions' => ['view', 'edit']],
    'invoices' => ['label' => 'Invoices', 'actions' => ['view', 'create', 'edit', 'delete']],
    'contracts' => ['label' => 'Contracts', 'actions' => ['view', 'edit']],
    'subscriptions' => ['label' => 'Subscriptions & plans', 'actions' => ['view', 'create', 'edit', 'delete']],
    'corporate-accounts' => ['label' => 'Corporate accounts', 'actions' => ['view']],
    'providers' => ['label' => 'Providers', 'actions' => ['view', 'edit']],
    'careers' => ['label' => 'Careers & applications', 'actions' => ['view', 'create', 'edit', 'delete']],
    'talent' => ['label' => 'Talent search', 'actions' => ['view']],
    'disputes' => ['label' => 'Disputes', 'actions' => ['view', 'edit']],
    'withdrawals' => ['label' => 'Withdrawals', 'actions' => ['view', 'edit']],
    'fraud' => ['label' => 'Fraud signals', 'actions' => ['view']],
    'users' => ['label' => 'Users', 'actions' => ['view', 'edit']],
    'analytics' => ['label' => 'Analytics', 'actions' => ['view']],
    'settings' => ['label' => 'Settings', 'actions' => ['edit']],
];
