<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = Banner::orderBy('sort_order')->get();

        $counts = [
            'total' => Banner::count(),
            'active' => Banner::where('is_active', true)->count(),
            'hidden' => Banner::where('is_active', false)->count(),
        ];

        return view('admin.banners.index', compact('banners', 'counts'));
    }

    public function create(): View
    {
        return view('admin.banners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request, false);
        unset($data['remove_image']);

        $data['is_active'] = $request->boolean('is_active');

        $this->applyUpload($request, null, $data, 'image', 'banners');

        Banner::create($data);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner created.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $this->validateData($request, true);
        unset($data['remove_image']);

        $data['is_active'] = $request->boolean('is_active');

        $this->applyUpload($request, $banner, $data, 'image', 'banners');

        $banner->update($data);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner deleted.');
    }

    private function applyUpload(Request $request, ?Banner $banner, array &$data, string $field, string $folder): void
    {
        if ($request->hasFile($field)) {
            if ($banner?->{$field}) {
                Storage::disk('public')->delete($banner->{$field});
            }
            $data[$field] = $request->file($field)->store($folder, 'public');
        } elseif ($banner && $request->boolean('remove_' . $field)) {
            if ($banner->{$field}) {
                Storage::disk('public')->delete($banner->{$field});
            }
            $data[$field] = null;
        } else {
            unset($data[$field]);
        }
    }

    private function validateData(Request $request, bool $isUpdate): array
    {
        return $request->validate([
            'image' => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'remove_image' => ['nullable', 'boolean'],
            'link_url' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
