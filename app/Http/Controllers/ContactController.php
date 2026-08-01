<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormReceived;
use App\Support\PakFormat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if (! empty($data['phone'])) {
            $data['phone'] = PakFormat::phone($data['phone']);
        }

        try {
            Mail::to(config('mail.contact_to'))->send(new ContactFormReceived($data));
        } catch (\Throwable $e) {
            Log::error('Failed to send contact form email: ' . $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', __('messages.contact.success'));
    }
}
