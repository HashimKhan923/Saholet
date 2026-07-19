<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormReceived;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $contactMessage = ContactMessage::create($data);

        try {
            Mail::to(config('mail.contact_to'))->send(new ContactFormReceived($contactMessage));
            $contactMessage->update(['mail_sent' => true]);
        } catch (\Throwable $e) {
            Log::error('Failed to send contact form email: ' . $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', __('messages.contact.success'));
    }
}
