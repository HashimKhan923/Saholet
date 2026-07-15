<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderDocument;
use App\Models\ProviderProfile;
use App\Models\ServiceArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(Request $request): View
    {
        $profile = $this->profileFor($request);
        $profile->load('documents');

        $documentTypes = config('kyc.documents');
        $cities = ServiceArea::active()->pluck('city')->unique()->sort()->values();

        /* ── Step 1: details ── */
        $detailsDone = filled($profile->city) && filled($profile->cnic_number);

        /* ── Step 2: required documents ── */
        $requiredTypes = collect($documentTypes)->filter(fn (array $meta) => $meta['required'] ?? false);

        $uploadedRequired = $requiredTypes
            ->keys()
            ->filter(fn (string $key) => $profile->documentOfType($key) !== null);

        $documentsDone = $requiredTypes->isNotEmpty()
            && $uploadedRequired->count() === $requiredTypes->count();

        /* ── Step 3: review ── */
        $submitted = $profile->isPending() || $profile->isApproved();

        /* ── What's still outstanding (mirrors submit() validation) ── */
        $missing = [];
        if (blank($profile->city)) {
            $missing[] = 'City';
        }
        if (blank($profile->cnic_number)) {
            $missing[] = 'CNIC number';
        }
        foreach ($requiredTypes as $key => $meta) {
            if (! $profile->documentOfType($key)) {
                $missing[] = $meta['label'];
            }
        }

        $steps = [
            [
                'label' => 'Your details',
                'hint'  => 'City, CNIC and experience',
                'done'  => $detailsDone,
            ],
            [
                'label' => 'KYC documents',
                'hint'  => $uploadedRequired->count() . ' of ' . $requiredTypes->count() . ' uploaded',
                'done'  => $documentsDone,
            ],
            [
                'label' => 'Review',
                'hint'  => $profile->isApproved()
                    ? 'Verified'
                    : ($profile->isPending() ? 'Under review' : 'Submit when ready'),
                'done'  => $profile->isApproved(),
            ],
        ];

        $progress = (int) round(collect($steps)->where('done', true)->count() / count($steps) * 100);
        $canSubmit = $detailsDone && $documentsDone && ! $submitted;

        return view('provider.onboarding', compact(
            'profile', 'documentTypes', 'cities', 'steps', 'progress',
            'missing', 'canSubmit', 'requiredTypes', 'uploadedRequired'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);

        if (! $profile->isEditable()) {
            return back()->with('error', 'Your profile cannot be edited while it is under review or approved.');
        }

        $data = $request->validate([
            'business_name' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'city' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'cnic_number' => ['required', 'string', 'max:20'],
        ]);

        $profile->fill($data)->save();

        return back()->with('success', 'Profile saved.');
    }

    public function storeDocument(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);

        if (! $profile->isEditable()) {
            return back()->with('error', 'Documents cannot be changed while your application is under review or approved.');
        }

        $types = array_keys(config('kyc.documents'));
        $mimes = implode(',', config('kyc.accepted_mimes'));
        $maxKb = config('kyc.max_size_kb');

        $validated = $request->validate([
            'type' => ['required', Rule::in($types)],
            'file' => ['required', 'file', "mimes:$mimes", "max:$maxKb"],
        ]);

        $disk = config('kyc.disk');
        $file = $request->file('file');

        // Replace any existing document of the same type.
        foreach ($profile->documents()->where('type', $validated['type'])->get() as $old) {
            Storage::disk($disk)->delete($old->path);
            $old->delete();
        }

        $path = $file->store("provider-documents/{$profile->id}", $disk);

        $profile->documents()->create([
            'type' => $validated['type'],
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function destroyDocument(Request $request, ProviderDocument $document): RedirectResponse
    {
        $profile = $this->profileFor($request);

        $this->authorize('delete', $document);

        if (! $profile->isEditable()) {
            return back()->with('error', 'Documents cannot be changed while your application is under review or approved.');
        }

        Storage::disk(config('kyc.disk'))->delete($document->path);
        $document->delete();

        return back()->with('success', 'Document removed.');
    }

    public function submit(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);

        if ($profile->isPending()) {
            return back()->with('error', 'Your application is already under review.');
        }

        if ($profile->isApproved()) {
            return back()->with('error', 'Your account is already verified.');
        }

        $missing = [];

        if (blank($profile->city)) {
            $missing[] = 'City';
        }
        if (blank($profile->cnic_number)) {
            $missing[] = 'CNIC number';
        }

        foreach (config('kyc.documents') as $key => $meta) {
            if (($meta['required'] ?? false) && ! $profile->documents()->where('type', $key)->exists()) {
                $missing[] = $meta['label'];
            }
        }

        if (! empty($missing)) {
            return back()->with('error', 'Please complete the following before submitting: ' . implode(', ', $missing) . '.');
        }

        $profile->update([
            'status' => ProviderProfile::STATUS_PENDING,
            'submitted_at' => now(),
            'rejection_reason' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);

        return redirect()
            ->route('provider.dashboard')
            ->with('success', 'Application submitted for review.');
    }

    private function profileFor(Request $request): ProviderProfile
    {
        return ProviderProfile::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['status' => ProviderProfile::STATUS_DRAFT]
        );
    }
}