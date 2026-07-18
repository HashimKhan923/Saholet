@extends('layouts.app')

@section('title', 'Terms & Conditions — ' . config('app.name'))

@section('content')
<section class="border-b border-slate-100 bg-gradient-to-b from-brand-50 to-slate-50 dark:border-slate-800 dark:from-slate-900 dark:to-slate-950">
    <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
        <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Terms and Conditions</h1>
        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Effective Date: 17 July 2026 &middot; Last Updated: 17 July 2026</p>
    </div>
</section>

<section class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="space-y-8 text-sm leading-relaxed text-slate-600 dark:text-slate-400">

        <p>These Terms and Conditions ("Terms") govern access to and use of the website <a href="{{ url('/') }}" class="font-medium text-brand-700 hover:underline dark:text-brand-400">https://sahoulat.com</a> and any related mobile applications, subdomains, or services (collectively, the "Platform"), operated by Sahoulat Pakistan (Private) Limited, a company incorporated in Pakistan, having its registered office at Bahria Town Karachi, Pakistan ("Sahoulat," "we," "us," or "our").</p>

        <p>By creating an account, browsing the Platform, or booking a service through it, you ("User," "you," or "your") agree to be bound by these Terms. If you do not agree, do not use the Platform.</p>

        <p><span class="font-semibold text-slate-700 dark:text-slate-300">Please also read:</span> our <a href="{{ route('legal.privacy') }}" class="font-medium text-brand-700 hover:underline dark:text-brand-400">Privacy Policy</a>, Cookie Policy, Refund and Cancellation Policy, and (if you are a service provider) our Service Provider Agreement. Together with these Terms, they form the full agreement between you and Sahoulat.</p>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">1. What Sahoulat Is</h2>
            <div class="mt-3 space-y-3">
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">1.1</span> Sahoulat is an online marketplace that connects Customers seeking home services (such as cleaning, plumbing, electrical work, appliance repair, painting, pest control, and similar categories, as listed on the Platform from time to time) with independent, third-party Service Providers who offer those services.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">1.2</span> Sahoulat is a technology platform only. We do not ourselves perform home services. Service Providers listed on the Platform are independent contractors or independent businesses; they are not employees, agents, partners, or representatives of Sahoulat. Sahoulat is not a party to the service contract formed between a Customer and a Service Provider when a booking is confirmed &mdash; that contract exists directly between the Customer and the Service Provider, subject to these Terms.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">1.3</span> We take reasonable steps to verify Service Providers (see Section 5), but we do not guarantee the quality, safety, timeliness, legality, or outcome of any service booked through the Platform.</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">2. Eligibility and Accounts</h2>
            <div class="mt-3 space-y-3">
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">2.1</span> You must be at least 18 years old and capable of entering into a binding contract under the laws of Pakistan to use the Platform.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">2.2</span> To book or provide a service, you must register an account and provide accurate, current, and complete information, including your name, phone number, email address, and (for bookings) service address. You are responsible for keeping this information up to date.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">2.3</span> You are responsible for maintaining the confidentiality of your account credentials and for all activity that occurs under your account. Notify us immediately at <a href="mailto:info@sahoulat.com" class="font-medium text-brand-700 hover:underline dark:text-brand-400">info@sahoulat.com</a> if you suspect unauthorized use of your account.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">2.4</span> We may suspend or terminate accounts that provide false information, violate these Terms, or are used fraudulently or abusively.</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">3. Booking Services (for Customers)</h2>
            <div class="mt-3 space-y-3">
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">3.1</span> When you request a service through the Platform, you are making an offer to the relevant Service Provider on the terms displayed at checkout (service description, price, and scheduled time). A booking is confirmed once the Service Provider accepts it and, where applicable, payment is authorized.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">3.2</span> Prices shown on the Platform are set by Service Providers or by Sahoulat depending on the service category and may include a Sahoulat service/platform fee, which will be disclosed before you confirm a booking.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">3.3</span> You agree to provide accurate details about the service required, provide reasonable access to the service location at the scheduled time, and behave respectfully toward Service Providers. Sahoulat may charge a reasonable fee, at rates disclosed on the Platform, for cancellations, no-shows, or wasted visits caused by inaccurate information or lack of access.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">3.4</span> Any dispute about the quality of a completed service should first be raised through the Platform's support channel within <span class="rounded bg-amber-50 px-1 py-0.5 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400">[7]</span> days of service completion so we can attempt to mediate between you and the Service Provider.</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">4. Payments</h2>
            <div class="mt-3 space-y-3">
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">4.1</span> The Platform supports in-app payment via the payment methods displayed at checkout (which may include debit/credit cards, mobile wallets such as JazzCash or Easypaisa, and other methods we enable from time to time). Payments are processed by third-party payment gateways; Sahoulat does not store your full card details.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">4.2</span> By making a payment, you authorize Sahoulat and/or its payment processor to charge the payment method you select for the total amount due, including the service price and any applicable platform fee and taxes.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">4.3</span> Funds collected from Customers may be held by Sahoulat or its payment processor and released to the Service Provider, less Sahoulat's commission, after the service is marked as completed, subject to our standard payout schedule.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">4.4</span> Refunds and cancellations are governed by our Refund and Cancellation Policy.</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">5. Service Provider Verification</h2>
            <div class="mt-3 space-y-3">
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">5.1</span> Service Providers must complete an onboarding and verification process before listing services, which may include submission of a valid CNIC or other government-issued identification, proof of address, contact details, and (for certain categories) proof of skill, licensing, or experience.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">5.2</span> Verification reduces but does not eliminate risk. Sahoulat does not guarantee that any Service Provider's identity documents, background, skills, or conduct meet any particular standard, and Customers should exercise their own judgment.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">5.3</span> Sahoulat may remove any Service Provider from the Platform at its discretion, including for failed verification, complaints, safety concerns, or breach of the Service Provider Agreement.</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">6. User Conduct</h2>
            <p class="mt-3">You agree not to: (a) use the Platform for any unlawful purpose; (b) circumvent the Platform to transact directly with a Service Provider or Customer you were introduced to through Sahoulat in order to avoid platform fees, within <span class="rounded bg-amber-50 px-1 py-0.5 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400">[90]</span> days of the introduction; (c) post false, misleading, defamatory, or abusive content (including reviews); (d) harass, threaten, or discriminate against any other user; (e) attempt to interfere with, hack, or reverse-engineer the Platform; or (f) use automated means (bots, scrapers) to access the Platform without our written permission.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">7. Reviews and Content</h2>
            <div class="mt-3 space-y-3">
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">7.1</span> The Platform may allow Customers to rate and review Service Providers after a completed booking. Reviews must be honest, based on genuine experience, and must not contain unlawful, defamatory, or abusive content.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">7.2</span> By submitting a review, photo, or other content to the Platform, you grant Sahoulat a non-exclusive, worldwide, royalty-free license to use, display, reproduce, and distribute that content in connection with operating and promoting the Platform.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">7.3</span> We may remove any content that violates these Terms or that we consider inappropriate, without prior notice.</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">8. Intellectual Property</h2>
            <p class="mt-3">The Platform, including its design, logos, text, graphics, and software, is owned by or licensed to Sahoulat and is protected by applicable intellectual property laws. You may not copy, modify, distribute, or create derivative works from the Platform without our prior written consent.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">9. Disclaimers</h2>
            <div class="mt-3 space-y-3">
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">9.1</span> The Platform is provided "as is" and "as available," without warranties of any kind, whether express or implied, including warranties of merchantability, fitness for a particular purpose, or non-infringement.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">9.2</span> Sahoulat does not warrant that the Platform will be uninterrupted, error-free, or secure, or that any Service Provider will meet your expectations.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">9.3</span> Sahoulat is not responsible for the acts, omissions, negligence, or misconduct of any Service Provider or Customer, including any damage to property, personal injury, theft, or loss occurring during or in connection with a service booked through the Platform. Any such claim must be pursued against the relevant Service Provider directly.</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">10. Limitation of Liability</h2>
            <div class="mt-3 space-y-3">
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">10.1</span> To the maximum extent permitted by law, Sahoulat's total liability to you for any claim arising out of or relating to the Platform or these Terms shall not exceed the total platform fees paid by you to Sahoulat in the three (3) months preceding the claim.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">10.2</span> Sahoulat shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including loss of profits, data, or goodwill.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">10.3</span> Nothing in these Terms limits liability that cannot be limited under applicable Pakistani law, including liability for fraud or willful misconduct.</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">11. Indemnification</h2>
            <p class="mt-3">You agree to indemnify and hold Sahoulat, its officers, directors, employees, and agents harmless from any claims, losses, liabilities, and expenses (including reasonable legal fees) arising from your breach of these Terms, your misuse of the Platform, or your violation of any law or third-party right.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">12. Suspension and Termination</h2>
            <p class="mt-3">We may suspend or terminate your access to the Platform, with or without notice, if we reasonably believe you have violated these Terms, engaged in fraudulent or unsafe conduct, or for any other reason at our discretion. You may close your account at any time by contacting <a href="mailto:info@sahoulat.com" class="font-medium text-brand-700 hover:underline dark:text-brand-400">info@sahoulat.com</a>. Sections that by their nature should survive termination (including Sections 8, 9, 10, 11, and 14) will survive.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">13. Changes to These Terms</h2>
            <p class="mt-3">We may update these Terms from time to time. If we make material changes, we will notify you via the Platform or by email before the changes take effect. Continued use of the Platform after changes take effect constitutes acceptance of the revised Terms.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">14. Governing Law and Dispute Resolution</h2>
            <div class="mt-3 space-y-3">
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">14.1</span> These Terms are governed by the laws of the Islamic Republic of Pakistan, without regard to conflict-of-law principles.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">14.2</span> Any dispute arising out of or relating to these Terms or the Platform shall first be attempted to be resolved amicably through good-faith negotiation. If unresolved within <span class="rounded bg-amber-50 px-1 py-0.5 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400">[30]</span> days, the dispute shall be subject to the exclusive jurisdiction of the courts of <span class="rounded bg-amber-50 px-1 py-0.5 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400">[City]</span>, Pakistan.</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">15. General</h2>
            <div class="mt-3 space-y-3">
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">15.1 Entire Agreement:</span> These Terms, together with the policies referenced above, constitute the entire agreement between you and Sahoulat regarding the Platform.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">15.2 Severability:</span> If any provision of these Terms is held invalid or unenforceable, the remaining provisions will remain in full force.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">15.3 No Waiver:</span> Our failure to enforce any right or provision of these Terms is not a waiver of that right or provision.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">15.4 Assignment:</span> You may not assign these Terms without our prior written consent. We may assign these Terms freely in connection with a merger, acquisition, or sale of assets.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">15.5 Contact:</span> Questions about these Terms can be sent to <a href="mailto:info@sahoulat.com" class="font-medium text-brand-700 hover:underline dark:text-brand-400">info@sahoulat.com</a> or <a href="https://wa.me/923313578446" class="font-medium text-brand-700 hover:underline dark:text-brand-400">+92 331 3578446</a>.</p>
            </div>
        </div>

    </div>
</section>
@endsection
