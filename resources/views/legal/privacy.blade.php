@extends('layouts.app')

@section('title', 'Privacy Policy — ' . config('app.name'))

@section('content')
<section class="border-b border-slate-100 bg-gradient-to-b from-brand-50 to-slate-50 dark:border-slate-800 dark:from-slate-900 dark:to-slate-950">
    <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
        <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Privacy Policy</h1>
        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Effective Date: 17 July 2026 &middot; Last Updated: 17 July 2026</p>
    </div>
</section>

<section class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="space-y-8 text-sm leading-relaxed text-slate-600 dark:text-slate-400">

        <p>Sahoulat Pakistan (Private) Limited ("Sahoulat," "we," "us," or "our") operates <a href="{{ url('/') }}" class="font-medium text-brand-700 hover:underline dark:text-brand-400">https://sahoulat.com</a> and related mobile applications (the "Platform"), a marketplace connecting Customers with independent home-service providers in Pakistan. This Privacy Policy explains what personal data we collect, why we collect it, how we use and protect it, and the choices you have.</p>

        <p>This Policy applies to Customers, Service Providers, and anyone else who visits or uses the Platform ("you"). By using the Platform, you agree to the collection and use of information as described here. If you do not agree, please do not use the Platform.</p>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">1. Information We Collect</h2>

            <h3 class="mt-4 text-sm font-semibold text-slate-800 dark:text-slate-200">1.1 Information you give us directly</h3>
            <ul class="mt-2 list-disc space-y-1.5 pl-5">
                <li><span class="font-medium text-slate-700 dark:text-slate-300">Account information:</span> name, phone number, email address, password, and profile photo (optional).</li>
                <li><span class="font-medium text-slate-700 dark:text-slate-300">Customer booking information:</span> service address, service details/requirements, and special instructions you provide when booking.</li>
                <li><span class="font-medium text-slate-700 dark:text-slate-300">Service Provider verification information:</span> CNIC number/copy or other government-issued ID, proof of address, date of birth, bank or mobile wallet account details for payouts, skill/experience/certification details, and (optionally) a police character certificate or references for certain service categories.</li>
                <li><span class="font-medium text-slate-700 dark:text-slate-300">Payment information:</span> when you pay through the Platform, our payment processor collects your card or mobile wallet details directly; we receive confirmation of payment and limited transaction metadata (amount, date, last 4 digits of card, payment method), but we do not store full card numbers or CVVs.</li>
                <li><span class="font-medium text-slate-700 dark:text-slate-300">Communications:</span> messages you send through in-app chat or support, reviews and ratings you post, and any correspondence with our support team.</li>
            </ul>

            <h3 class="mt-4 text-sm font-semibold text-slate-800 dark:text-slate-200">1.2 Information we collect automatically</h3>
            <ul class="mt-2 list-disc space-y-1.5 pl-5">
                <li><span class="font-medium text-slate-700 dark:text-slate-300">Location/GPS data:</span> with your permission, we collect your device's precise or approximate location to show nearby Service Providers, estimate arrival times, confirm a Service Provider has reached the service address, and improve service matching. Service Providers' location may be shared with the Customer while a booking is active, for safety and coordination purposes.</li>
                <li><span class="font-medium text-slate-700 dark:text-slate-300">Device and usage data:</span> IP address, device type, operating system, browser type, app version, pages viewed, features used, crash logs, and general usage patterns, collected via cookies, SDKs, and similar technologies (see our Cookie Policy).</li>
                <li><span class="font-medium text-slate-700 dark:text-slate-300">Log data:</span> access times, referring/exit pages, and diagnostic information generated when you use the Platform.</li>
            </ul>

            <h3 class="mt-4 text-sm font-semibold text-slate-800 dark:text-slate-200">1.3 Information from third parties</h3>
            <ul class="mt-2 list-disc space-y-1.5 pl-5">
                <li>If you sign up or log in using a third-party service (e.g., Google or Facebook login), we receive the profile information you authorize that service to share (typically name, email, profile photo).</li>
                <li>Payment processors and identity-verification providers may share confirmation of successful verification or transaction status with us.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">2. How We Use Your Information</h2>
            <p class="mt-3">We use personal data to:</p>
            <ul class="mt-2 list-disc space-y-1.5 pl-5">
                <li>create and manage your account and verify your identity (particularly for Service Providers);</li>
                <li>facilitate bookings &mdash; matching Customers with nearby, available, verified Service Providers;</li>
                <li>process payments and payouts through our payment gateway partners;</li>
                <li>enable communication between Customers and Service Providers regarding a booking;</li>
                <li>provide customer support and respond to inquiries or complaints;</li>
                <li>send booking confirmations, service updates, and important notices about your account;</li>
                <li>send promotional messages about offers or new services, where you have not opted out;</li>
                <li>monitor, analyze, and improve the Platform's safety, performance, and features;</li>
                <li>detect, investigate, and prevent fraud, abuse, or violations of our Terms and Conditions; and</li>
                <li>comply with legal obligations, respond to lawful requests from authorities, and enforce our agreements.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">3. How We Share Your Information</h2>
            <div class="mt-3 space-y-3">
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">3.1 Between Customers and Service Providers:</span> to complete a booking, we share the information each party needs to perform the service &mdash; for example, the Customer's name, phone number, and service address are shared with the assigned Service Provider; the Service Provider's name, photo, verification status, and rating are shared with the Customer.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">3.2 Service providers to Sahoulat (vendors):</span> we share data with third parties that help us operate the Platform, including payment gateways (e.g., card processors, JazzCash/Easypaisa or similar mobile wallet providers), cloud hosting providers, SMS/email/push notification providers, identity-verification services, analytics providers, and customer support tools. These providers are only permitted to use your data to perform services on our behalf.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">3.3 Legal and safety:</span> we may disclose information if required by law, regulation, court order, or a valid request from a Pakistani government or law-enforcement authority, or where necessary to protect the rights, property, or safety of Sahoulat, our users, or the public.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">3.4 Business transfers:</span> if Sahoulat is involved in a merger, acquisition, financing, or sale of assets, personal data may be transferred as part of that transaction, subject to this Policy or a policy at least as protective.</p>
                <p><span class="font-semibold text-slate-700 dark:text-slate-300">3.5 With your consent:</span> we may share information for any other purpose with your explicit consent.</p>
                <p>We do not sell your personal data to third parties for their own marketing purposes.</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">4. Cookies and Tracking Technologies</h2>
            <p class="mt-3">We use cookies, SDKs, and similar technologies to operate the Platform, remember your preferences, keep you logged in, and understand how the Platform is used. Details of the categories of cookies we use, their purposes, and how to manage your preferences are set out in our separate Cookie Policy.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">5. Data Retention</h2>
            <p class="mt-3">We retain personal data for as long as your account is active and as needed to provide the Platform, comply with our legal, tax, and accounting obligations, resolve disputes, and enforce our agreements. Service Provider verification records (including ID documents) are retained for the period required for safety, fraud-prevention, and applicable regulatory purposes, after which they are securely deleted or anonymized. You may request deletion of your account as described in Section 7.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">6. Data Security</h2>
            <p class="mt-3">We use administrative, technical, and physical safeguards designed to protect personal data against unauthorized access, alteration, disclosure, or destruction, including encryption of data in transit, access controls, and working only with payment processors that meet applicable industry security standards (e.g., PCI-DSS) for handling card data. No method of transmission or storage is 100% secure, and we cannot guarantee absolute security.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">7. Your Rights and Choices</h2>
            <p class="mt-3">Depending on your relationship with us, you may:</p>
            <ul class="mt-2 list-disc space-y-1.5 pl-5">
                <li>access, review, and update your profile information directly within your account settings;</li>
                <li>request a copy of the personal data we hold about you;</li>
                <li>request correction of inaccurate data;</li>
                <li>request deletion of your account and associated data, subject to our legal retention obligations (e.g., transaction records we must keep for accounting purposes);</li>
                <li>withdraw consent for location tracking at any time via your device settings, though this may limit your ability to book or provide services;</li>
                <li>opt out of promotional emails, SMS, or push notifications by using the unsubscribe link, replying "STOP," or adjusting notification settings in the app (transactional and booking-related messages cannot be opted out of while you have an active account).</li>
            </ul>
            <p class="mt-3">To exercise any of these rights, contact us at <a href="mailto:info@sahoulat.com" class="font-medium text-brand-700 hover:underline dark:text-brand-400">info@sahoulat.com</a>. We will respond within a reasonable time and in accordance with applicable law.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">8. Children's Privacy</h2>
            <p class="mt-3">The Platform is not directed to children under 18, and we do not knowingly collect personal data from anyone under 18. If we become aware that we have collected data from a minor without appropriate consent, we will delete it.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">9. International Data Transfers</h2>
            <p class="mt-3">Some of our service providers (e.g., cloud hosting, analytics, or payment infrastructure) may process data outside Pakistan. Where this occurs, we take reasonable steps to ensure such providers apply security and privacy standards consistent with this Policy.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">10. Third-Party Links and Services</h2>
            <p class="mt-3">The Platform may contain links to third-party websites or services (e.g., payment gateways, social media). We are not responsible for the privacy practices of those third parties, and we encourage you to review their privacy policies separately.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">11. Changes to This Policy</h2>
            <p class="mt-3">We may update this Privacy Policy from time to time. If we make material changes, we will notify you through the Platform, by email, or by another reasonable means before the changes take effect. The "Last Updated" date at the top of this Policy indicates when it was last revised.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">12. Governing Law</h2>
            <p class="mt-3">This Policy is governed by the laws of the Islamic Republic of Pakistan, including applicable provisions of the Electronic Transactions Ordinance 2002 and any data protection legislation in force in Pakistan from time to time.</p>
        </div>

        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">13. Contact Us</h2>
            <p class="mt-3">If you have questions, concerns, or requests regarding this Privacy Policy or your personal data, contact us at:</p>
            <div class="mt-3 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <p class="font-semibold text-slate-900 dark:text-white">Sahoulat</p>
                <p class="mt-1">Bahria Town Karachi, Pakistan</p>
                <p class="mt-1">Email: <a href="mailto:info@sahoulat.com" class="font-medium text-brand-700 hover:underline dark:text-brand-400">info@sahoulat.com</a></p>
                <p class="mt-1">Phone: <a href="tel:+923313578446" class="font-medium text-brand-700 hover:underline dark:text-brand-400">+92 331 3578446</a></p>
            </div>
        </div>

    </div>
</section>
@endsection
