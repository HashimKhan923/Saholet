<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBrandManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin@example.com', 'phone' => '+923001112222',
            'role' => User::ROLE_ADMIN, 'password' => 'password',
        ]);
    }

    private function staff(array $permissions = []): User
    {
        static $n = 0;
        $n++;

        return User::create([
            'name' => 'Staff', 'email' => "staff{$n}@example.com", 'phone' => '+92300999' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'role' => User::ROLE_STAFF, 'password' => 'password', 'permissions' => $permissions,
        ]);
    }

    public function test_admin_can_create_a_brand_with_logo_name_and_sort_order(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/brands', [
            'name' => 'Engro',
            'logo' => UploadedFile::fake()->image('engro.png'),
            'sort_order' => 3,
            'is_active' => 1,
        ])->assertRedirect(route('admin.brands.index'));

        $brand = Brand::firstOrFail();
        $this->assertSame('Engro', $brand->name);
        $this->assertSame(3, $brand->sort_order);
        $this->assertTrue($brand->is_active);
        Storage::disk('public')->assertExists($brand->logo);
    }

    public function test_logo_is_required_on_create_and_must_be_an_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/brands', ['name' => 'No logo', 'sort_order' => 0])
            ->assertSessionHasErrors('logo');

        $this->actingAs($admin)->post('/admin/brands', [
            'name' => 'Bad file', 'sort_order' => 0,
            'logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('logo');

        $this->assertSame(0, Brand::count());
    }

    public function test_updating_without_a_new_logo_keeps_the_old_one_and_a_new_logo_replaces_it(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/brands', [
            'name' => 'Old name', 'logo' => UploadedFile::fake()->image('a.png'), 'sort_order' => 0,
        ]);
        $brand = Brand::firstOrFail();
        $oldLogo = $brand->logo;

        $this->actingAs($admin)->put("/admin/brands/{$brand->id}", ['name' => 'New name', 'sort_order' => 1, 'is_active' => 0])
            ->assertRedirect();
        $brand->refresh();
        $this->assertSame('New name', $brand->name);
        $this->assertFalse($brand->is_active);
        $this->assertSame($oldLogo, $brand->logo);
        Storage::disk('public')->assertExists($oldLogo);

        $this->actingAs($admin)->put("/admin/brands/{$brand->id}", [
            'name' => 'New name', 'sort_order' => 1, 'logo' => UploadedFile::fake()->image('b.png'),
        ])->assertRedirect();
        $brand->refresh();
        $this->assertNotSame($oldLogo, $brand->logo);
        Storage::disk('public')->assertMissing($oldLogo);
        Storage::disk('public')->assertExists($brand->logo);
    }

    public function test_deleting_a_brand_removes_its_logo_file(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/brands', [
            'name' => 'Temp', 'logo' => UploadedFile::fake()->image('t.png'), 'sort_order' => 0,
        ]);
        $brand = Brand::firstOrFail();

        $this->actingAs($admin)->delete("/admin/brands/{$brand->id}")->assertRedirect();

        $this->assertSame(0, Brand::count());
        Storage::disk('public')->assertMissing($brand->logo);
    }

    public function test_staff_needs_the_brands_permission(): void
    {
        $this->actingAs($this->staff())->get('/admin/brands')->assertForbidden();

        $staff = $this->staff(['brands' => ['view']]);
        $this->actingAs($staff)->get('/admin/brands')->assertOk()->assertSee('No brands yet');
    }

    public function test_sidebar_shows_brands_link_under_review_videos_for_admins(): void
    {
        $this->actingAs($this->admin())->get('/admin/brands')
            ->assertOk()
            ->assertSeeInOrder(['Review videos', 'Brands']);
    }
}
