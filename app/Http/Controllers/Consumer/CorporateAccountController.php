<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\CorporateAccount;
use App\Models\User;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CorporateAccountController extends Controller
{
    public function create(Request $request): View
    {
        $this->authorize('create', CorporateAccount::class);

        return view('consumer.corporate.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CorporateAccount::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'billing_email' => ['required', 'email', 'max:255'],
            'billing_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
        ]);

        $account = CorporateAccount::create($data + ['owner_id' => $request->user()->id]);

        $request->user()->update([
            'corporate_account_id' => $account->id,
            'corporate_role' => CorporateAccount::ROLE_OWNER,
        ]);

        return redirect()
            ->route('consumer.corporate.show')
            ->with('success', 'Company account created. Invite your team to start consolidating bookings and billing.');
    }

    public function show(Request $request): View
    {
        $account = $request->user()->corporateAccount;
        abort_unless($account, 404);

        $account->load('members', 'owner');

        return view('consumer.corporate.show', [
            'account' => $account,
            'totalSpend' => $account->totalSpend(),
        ]);
    }

    public function inviteMember(Request $request): RedirectResponse
    {
        $owner = $request->user();
        $account = $owner->corporateAccount;
        abort_unless($account, 404);
        $this->authorize('manageMembers', $account);

        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $member = User::where('email', $data['email'])->first();

        if (! $member) {
            return back()->with('error', 'No Sahoulet account found with that email. They need to sign up first.');
        }

        if ($member->id === $owner->id) {
            return back()->with('error', 'You’re already the owner of this account.');
        }

        if ($member->corporate_account_id) {
            return back()->with('error', 'That user already belongs to a company account.');
        }

        if (! $member->isConsumer()) {
            return back()->with('error', 'Only consumer accounts can join a company account.');
        }

        $member->update([
            'corporate_account_id' => $owner->corporate_account_id,
            'corporate_role' => CorporateAccount::ROLE_MEMBER,
        ]);

        app(Notifier::class)->notify(
            $member,
            'corporate',
            'Added to a company account',
            "You've been added to {$owner->corporateAccount->name}'s company account on Sahoulet. Your bookings now roll up to their consolidated billing.",
            route('consumer.corporate.show')
        );

        return back()->with('success', "{$member->name} added to your team.");
    }

    public function removeMember(Request $request, User $member): RedirectResponse
    {
        $owner = $request->user();
        $account = $owner->corporateAccount;
        abort_unless($account, 404);
        $this->authorize('manageMembers', $account);
        abort_unless($member->corporate_account_id === $owner->corporate_account_id, 404);
        abort_if($member->id === $owner->id, 403);

        $member->update(['corporate_account_id' => null, 'corporate_role' => null]);

        return back()->with('success', "{$member->name} removed from your team.");
    }
}
