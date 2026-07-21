<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderDocumentResource;
use App\Http\Resources\ProviderProfileResource;
use App\Models\ProviderDocument;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    /** Onboarding status: profile, uploaded documents, step progress, and what's still missing. */
    public function show(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);
        $profile->load('documents');

        $documentTypes = config('kyc.documents');
        $requiredTypes = collect($documentTypes)->filter(fn (array $meta) => $meta['required'] ?? false);

        $uploadedRequired = $requiredTypes
            ->keys()
            ->filter(fn (string $key) => $profile->documentOfType($key) !== null);

        $detailsDone = filled($profile->city) && filled($profile->cnic_number);
        $documentsDone = $requiredTypes->isNotEmpty() && $uploadedRequired->count() === $requiredTypes->count();
        $submitted = $profile->isPending() || $profile->isApproved();

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

        return response()->json([
            'profile' => new ProviderProfileResource($profile),
            'documents' => ProviderDocumentResource::collection($profile->documents),
            'document_types' => $documentTypes,
            'steps' => [
                ['label' => 'Your details', 'done' => $detailsDone],
                ['label' => 'KYC documents', 'done' => $documentsDone, 'uploaded' => $uploadedRequired->count(), 'required' => $requiredTypes->count()],
                ['label' => 'Review', 'done' => $profile->isApproved(), 'submitted' => $submitted],
            ],
            'missing' => $missing,
            'can_submit' => $detailsDone && $documentsDone && ! $submitted,
        ]);
    }

    /** Body: business_name?, bio?, experience_years, city, address?, latitude?, longitude?, cnic_number. */
    public function update(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);

        if (! $profile->isEditable()) {
            return response()->json(['message' => 'Your profile cannot be edited while it is under review or approved.'], 422);
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

        return response()->json(['profile' => new ProviderProfileResource($profile->fresh())]);
    }

    /** Multipart upload. Body: type (one of config('kyc.documents') keys), file. */
    public function storeDocument(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);

        if (! $profile->isEditable()) {
            return response()->json(['message' => 'Documents cannot be changed while your application is under review or approved.'], 422);
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

        foreach ($profile->documents()->where('type', $validated['type'])->get() as $old) {
            Storage::disk($disk)->delete($old->path);
            $old->delete();
        }

        $path = $file->store("provider-documents/{$profile->id}", $disk);

        $document = $profile->documents()->create([
            'type' => $validated['type'],
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json(['document' => new ProviderDocumentResource($document)], 201);
    }

    public function destroyDocument(Request $request, ProviderDocument $document): JsonResponse
    {
        $profile = $this->profileFor($request);

        $this->authorize('delete', $document);

        if (! $profile->isEditable()) {
            return response()->json(['message' => 'Documents cannot be changed while your application is under review or approved.'], 422);
        }

        Storage::disk(config('kyc.disk'))->delete($document->path);
        $document->delete();

        return response()->json(['message' => 'Document removed.']);
    }

    /** Stream a private KYC document (the owning provider only). */
    public function showDocument(Request $request, ProviderDocument $document): Response
    {
        $this->authorize('view', $document);

        return Storage::disk(config('kyc.disk'))->response($document->path, $document->original_name);
    }

    public function submit(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);

        if ($profile->isPending()) {
            return response()->json(['message' => 'Your application is already under review.'], 422);
        }
        if ($profile->isApproved()) {
            return response()->json(['message' => 'Your account is already verified.'], 422);
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
            return response()->json(['message' => 'Please complete the following before submitting: ' . implode(', ', $missing) . '.'], 422);
        }

        $profile->update([
            'status' => ProviderProfile::STATUS_PENDING,
            'submitted_at' => now(),
            'rejection_reason' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);

        return response()->json([
            'message' => 'Application submitted for review.',
            'profile' => new ProviderProfileResource($profile->fresh()),
        ]);
    }

    private function profileFor(Request $request): ProviderProfile
    {
        return ProviderProfile::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['status' => ProviderProfile::STATUS_DRAFT]
        );
    }
}
