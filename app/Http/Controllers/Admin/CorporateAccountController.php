<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorporateAccount;
use Illuminate\View\View;

class CorporateAccountController extends Controller
{
    public function index(): View
    {
        $accounts = CorporateAccount::with('owner')
            ->withCount('members')
            ->latest()
            ->paginate(15);

        $counts = [
            'total' => CorporateAccount::count(),
            'members' => \App\Models\User::whereNotNull('corporate_account_id')->count(),
        ];

        return view('admin.corporate-accounts.index', compact('accounts', 'counts'));
    }

    public function show(CorporateAccount $corporateAccount): View
    {
        $corporateAccount->load('owner', 'members');

        return view('admin.corporate-accounts.show', [
            'account' => $corporateAccount,
            'totalSpend' => $corporateAccount->totalSpend(),
        ]);
    }
}
