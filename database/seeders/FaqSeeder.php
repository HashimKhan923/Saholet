<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        if (Faq::query()->exists()) {
            return;
        }

        $en = trans('messages.landing', [], 'en');
        $ur = trans('messages.landing', [], 'ur');

        foreach (range(1, 8) as $i) {
            Faq::create([
                'question_en' => $en['faq_q' . $i] ?? '',
                'answer_en' => $en['faq_a' . $i] ?? '',
                'question_ur' => $ur['faq_q' . $i] ?? null,
                'answer_ur' => $ur['faq_a' . $i] ?? null,
                'sort_order' => $i * 10,
                'is_active' => true,
            ]);
        }
    }
}
