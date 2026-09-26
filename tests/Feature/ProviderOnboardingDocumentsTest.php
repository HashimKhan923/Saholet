<?php

namespace Tests\Feature;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProviderOnboardingDocumentsTest extends TestCase
{
    use RefreshDatabase;

    private const ORDER = ['cnic_front', 'cnic_back', 'selfie', 'police_verification', 'nadra_verification'];

    private function draftProvider(): array
    {
        Storage::fake(config('kyc.disk'));

        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'status' => ProviderProfile::STATUS_DRAFT,
            'city' => 'Karachi',
            'cnic_number' => '42101-1234567-8',
        ]);

        return [$user, $profile];
    }

    private function upload(string $type): array
    {
        return ['type' => $type, 'file' => UploadedFile::fake()->image("{$type}.jpg")];
    }

    public function test_police_and_nadra_verification_are_required_documents_after_the_selfie(): void
    {
        $keys = array_keys(config('kyc.documents'));

        $this->assertSame(self::ORDER, array_slice($keys, 0, 5));
        $this->assertTrue(config('kyc.documents.police_verification.required'));
        $this->assertTrue(config('kyc.documents.nadra_verification.required'));
    }

    public function test_web_onboarding_page_locks_every_slot_below_the_first_missing_one(): void
    {
        [$user] = $this->draftProvider();

        $this->actingAs($user)->get('/provider/onboarding')
            ->assertOk()
            ->assertSee('Police verification')
            ->assertSee('NADRA verification')
            ->assertSee('Upload CNIC — Front first to unlock this.')
            ->assertSee('Locked — upload in order');
    }

    public function test_web_rejects_an_out_of_order_upload(): void
    {
        [$user, $profile] = $this->draftProvider();

        $this->actingAs($user)->post('/provider/onboarding/documents', $this->upload('nadra_verification'))
            ->assertSessionHasErrors('file');

        $this->assertSame(0, $profile->documents()->count());
    }

    public function test_web_accepts_uploads_in_order_and_unlocks_the_next_slot(): void
    {
        [$user, $profile] = $this->draftProvider();

        $this->actingAs($user)->post('/provider/onboarding/documents', $this->upload('cnic_front'))
            ->assertSessionHasNoErrors();

        // cnic_back is now open, but selfie (two steps ahead) is still locked.
        $this->actingAs($user)->post('/provider/onboarding/documents', $this->upload('selfie'))
            ->assertSessionHasErrors('file');
        $this->actingAs($user)->post('/provider/onboarding/documents', $this->upload('cnic_back'))
            ->assertSessionHasNoErrors();

        $this->assertSame(['cnic_front', 'cnic_back'], $profile->documents()->orderBy('id')->pluck('type')->all());
    }

    public function test_web_submit_needs_police_and_nadra_documents(): void
    {
        [$user, $profile] = $this->draftProvider();

        foreach (['cnic_front', 'cnic_back', 'selfie'] as $type) {
            $this->actingAs($user)->post('/provider/onboarding/documents', $this->upload($type));
        }

        $this->actingAs($user)->post('/provider/onboarding/submit')
            ->assertSessionHas('error', fn ($msg) => str_contains($msg, 'Police verification') && str_contains($msg, 'NADRA verification'));
        $this->assertTrue($profile->fresh()->isDraft());

        foreach (['police_verification', 'nadra_verification'] as $type) {
            $this->actingAs($user)->post('/provider/onboarding/documents', $this->upload($type));
        }

        $this->actingAs($user)->post('/provider/onboarding/submit')->assertRedirect(route('provider.dashboard'));
        $this->assertTrue($profile->fresh()->isPending());
    }

    public function test_api_status_reports_slot_order_and_lock_state(): void
    {
        [$user] = $this->draftProvider();
        Sanctum::actingAs($user);

        $types = $this->getJson('/api/provider/onboarding')->assertOk()->json('document_types');

        $this->assertSame(self::ORDER, array_slice(array_keys($types), 0, 5));
        $this->assertFalse($types['cnic_front']['locked']);
        $this->assertNull($types['cnic_front']['blocked_by']);
        $this->assertTrue($types['cnic_back']['locked']);
        $this->assertSame('CNIC — Front', $types['cnic_back']['blocked_by']);
        $this->assertSame(1, $types['cnic_front']['order']);
        $this->assertSame(5, $types['nadra_verification']['order']);
        $this->assertTrue($types['police_verification']['required']);
    }

    public function test_api_rejects_out_of_order_upload_and_accepts_in_order(): void
    {
        [$user] = $this->draftProvider();
        Sanctum::actingAs($user);

        $this->postJson('/api/provider/onboarding/documents', $this->upload('police_verification'))
            ->assertStatus(422)
            ->assertJsonValidationErrors('type');

        foreach (self::ORDER as $type) {
            $this->postJson('/api/provider/onboarding/documents', $this->upload($type))
                ->assertCreated()
                ->assertJsonPath('document.type', $type);
        }

        $status = $this->getJson('/api/provider/onboarding')->assertOk();
        $this->assertTrue($status->json('can_submit'));
        $this->assertFalse($status->json('document_types.certificate.locked'));
    }

    public function test_api_submit_lists_police_and_nadra_as_missing(): void
    {
        [$user] = $this->draftProvider();
        Sanctum::actingAs($user);

        foreach (['cnic_front', 'cnic_back', 'selfie'] as $type) {
            $this->postJson('/api/provider/onboarding/documents', $this->upload($type))->assertCreated();
        }

        $this->postJson('/api/provider/onboarding/submit')
            ->assertStatus(422)
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'Police verification') && str_contains($m, 'NADRA verification'));
    }
}
