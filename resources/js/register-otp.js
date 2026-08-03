import { initializeApp } from 'firebase/app';
import { getAuth, RecaptchaVerifier, signInWithPhoneNumber } from 'firebase/auth';

const RESEND_SECONDS = 60;

let auth = null;
let recaptchaVerifier = null;
let confirmationResult = null;

function ensureAuth(firebaseConfig) {
    if (!auth) {
        auth = getAuth(initializeApp(firebaseConfig));
    }
    return auth;
}

/** Accepts 03XX-XXXXXXX, 03XXXXXXXXX, 923XXXXXXXXX or +923XXXXXXXXX and normalizes to E.164. Returns null if it isn't a recognizable PK mobile number. */
function toE164Pk(rawPhone) {
    const digits = String(rawPhone || '').replace(/\D/g, '');

    if (digits.length === 10) return '+92' + digits;
    if (digits.length === 11 && digits.startsWith('0')) return '+92' + digits.slice(1);
    if (digits.length === 12 && digits.startsWith('92')) return '+' + digits;

    return null;
}

const FIREBASE_ERROR_MESSAGES = {
    'auth/invalid-phone-number': "That phone number doesn't look right. Use a Pakistani mobile number like 0300-1234567.",
    'auth/too-many-requests': 'Too many attempts from this device. Please wait a while and try again.',
    'auth/invalid-verification-code': "That code isn't right. Double check and try again.",
    'auth/code-expired': 'That code expired. Request a new one.',
    'auth/quota-exceeded': "We've hit our daily verification limit. Please try again later or contact support.",
    'auth/captcha-check-failed': 'Verification check failed. Please refresh the page and try again.',
};

function friendlyFirebaseError(err) {
    return FIREBASE_ERROR_MESSAGES[err?.code] || 'Something went wrong sending the verification code. Please try again.';
}

document.addEventListener('alpine:init', () => {
    Alpine.data('registerOtp', (cfg = {}) => ({
        step: 'form',
        checking: false,
        verifying: false,
        resending: false,
        errors: {},
        generalError: '',
        phoneDisplay: '',
        otpCode: '',
        countdown: 0,
        countdownTimer: null,
        role: cfg.initialRole || 'consumer',
        verifySubTemplate: cfg.verifySubTemplate || '',

        init() {
            ensureAuth(cfg.firebaseConfig);

            // Render the (invisible) reCAPTCHA as soon as the page loads instead of
            // waiting for the submit click — the challenge/config fetch is what was
            // making "Send verification code" feel slow; doing it here front-loads
            // that cost while the user is still filling in the form.
            recaptchaVerifier = new RecaptchaVerifier(auth, this.$refs.recaptcha, { size: 'invisible' });
            recaptchaVerifier.render().catch((err) => {
                this.generalError = friendlyFirebaseError(err);
            });
        },

        fieldError(name) {
            return this.errors?.[name]?.[0] || '';
        },

        formPayload() {
            const data = new FormData(this.$refs.form);
            return Object.fromEntries(data.entries());
        },

        async submitForm() {
            this.errors = {};
            this.generalError = '';

            const payload = this.formPayload();
            const e164 = toE164Pk(payload.phone);
            if (!e164) {
                this.errors = { phone: ['Enter a valid Pakistani mobile number.'] };
                return;
            }

            this.checking = true;
            try {
                await window.axios.post(cfg.checkUrl, payload);
            } catch (err) {
                this.checking = false;
                if (err.response?.status === 422) {
                    this.errors = err.response.data.errors || {};
                } else {
                    this.generalError = 'Could not reach the server. Check your connection and try again.';
                }
                return;
            }

            try {
                if (!recaptchaVerifier) {
                    recaptchaVerifier = new RecaptchaVerifier(auth, this.$refs.recaptcha, { size: 'invisible' });
                }
                confirmationResult = await signInWithPhoneNumber(auth, e164, recaptchaVerifier);
                this.phoneDisplay = payload.phone;
                this.otpCode = '';
                this.step = 'otp';
                this.startCountdown();
            } catch (err) {
                this.generalError = friendlyFirebaseError(err);
            } finally {
                this.checking = false;
            }
        },

        async resend() {
            if (this.countdown > 0 || this.resending) return;

            this.resending = true;
            this.generalError = '';
            try {
                const e164 = toE164Pk(this.phoneDisplay);
                recaptchaVerifier.clear();
                recaptchaVerifier = new RecaptchaVerifier(auth, this.$refs.recaptcha, { size: 'invisible' });
                confirmationResult = await signInWithPhoneNumber(auth, e164, recaptchaVerifier);
                this.otpCode = '';
                this.startCountdown();
            } catch (err) {
                this.generalError = friendlyFirebaseError(err);
            } finally {
                this.resending = false;
            }
        },

        startCountdown() {
            this.countdown = RESEND_SECONDS;
            clearInterval(this.countdownTimer);
            this.countdownTimer = setInterval(() => {
                this.countdown -= 1;
                if (this.countdown <= 0) clearInterval(this.countdownTimer);
            }, 1000);
        },

        backToForm() {
            this.step = 'form';
            this.generalError = '';
            this.errors = {};
            clearInterval(this.countdownTimer);
        },

        async verifyOtp() {
            if (!confirmationResult || this.otpCode.length !== 6) return;

            this.verifying = true;
            this.generalError = '';
            try {
                const credential = await confirmationResult.confirm(this.otpCode);
                const idToken = await credential.user.getIdToken();
                await auth.signOut();

                const payload = { ...this.formPayload(), firebase_id_token: idToken };
                const { data } = await window.axios.post(cfg.registerUrl, payload);
                window.location.href = data.redirect;
            } catch (err) {
                this.verifying = false;

                if (err?.response?.status === 422) {
                    this.errors = err.response.data.errors || {};
                    this.generalError = err.response.data.errors?.firebase_id_token?.[0] || '';
                    return;
                }

                // Wrong/expired code (or any other Firebase-side confirm() failure) —
                // clear the input so the user retypes fresh rather than editing a
                // code we already know is wrong. No refresh needed: confirmationResult
                // is still valid and confirm() can simply be called again.
                this.otpCode = '';
                this.generalError = friendlyFirebaseError(err);
            }
        },
    }));
});
