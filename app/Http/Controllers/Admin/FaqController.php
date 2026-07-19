<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::ordered()->get();

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create(): View
    {
        return view('admin.faqs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');

        Faq::create($data);
        Cache::forget('landing.faqs');

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'FAQ created.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');

        $faq->update($data);
        Cache::forget('landing.faqs');

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'FAQ updated.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();
        Cache::forget('landing.faqs');

        return redirect()
            ->route('admin.faqs.index')
            ->with('success', 'FAQ deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'question_en' => ['required', 'string', 'max:255'],
            'answer_en' => ['required', 'string', 'max:2000'],
            'question_ur' => ['nullable', 'string', 'max:255'],
            'answer_ur' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
