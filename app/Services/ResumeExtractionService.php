<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Best-effort, no-AI resume field extraction: pulls raw text out of the
 * uploaded PDF/DOC/DOCX, splits it into sections by common resume headings,
 * and maps each section onto a profile field. Every result is a suggestion
 * for the candidate to review — never written to the database directly.
 */
class ResumeExtractionService
{
    /** @var array<string, string[]> */
    private const SECTION_HEADINGS = [
        'summary' => ['summary', 'objective', 'profile', 'about', 'professional summary'],
        'experience' => ['experience', 'work experience', 'employment history', 'professional experience'],
        'skills' => ['skills', 'technical skills', 'core competencies', 'key skills'],
    ];

    /**
     * Common resume headings we don't map to any field, but still need to recognize —
     * otherwise a section we DO care about (e.g. skills) would keep absorbing every
     * line all the way to the end of the document whenever it's not the last section.
     */
    private const OTHER_HEADINGS = [
        'education', 'certifications', 'certification', 'projects', 'languages',
        'references', 'awards', 'achievements', 'publications', 'volunteer experience',
        'interests', 'hobbies', 'contact', 'personal information', 'training',
    ];

    /** @return array<string, mixed> Partial set of: headline, bio, current_position, experience_years, skills */
    public function extract(string $path, string $disk): array
    {
        try {
            $text = $this->extractText($path, $disk);
        } catch (\Throwable $e) {
            Log::info('Resume extraction: could not read file', ['path' => $path, 'error' => $e->getMessage()]);

            return [];
        }

        if (trim($text) === '') {
            return [];
        }

        $sections = $this->splitIntoSections($text);

        $result = [];

        if (($summary = $sections['summary'] ?? null) !== null) {
            $lines = $this->nonEmptyLines($summary);
            if ($lines !== []) {
                $bio = trim($summary);
                if ($bio !== '') {
                    $result['bio'] = mb_substr($bio, 0, 2000);
                }
                if (mb_strlen($lines[0]) <= 255) {
                    $result['headline'] = $lines[0];
                }
            }
        }

        if (($experience = $sections['experience'] ?? null) !== null) {
            $lines = $this->nonEmptyLines($experience);
            if ($lines !== [] && mb_strlen($lines[0]) <= 255) {
                $result['current_position'] = $lines[0];
            }
        }

        if (($years = $this->guessExperienceYears($text, $sections['experience'] ?? '')) !== null) {
            $result['experience_years'] = $years;
        }

        if (($skills = $sections['skills'] ?? null) !== null) {
            $parsed = $this->parseSkills($skills);
            if ($parsed !== []) {
                $result['skills'] = $parsed;
            }
        }

        return $result;
    }

    private function extractText(string $path, string $disk): string
    {
        $absolutePath = Storage::disk($disk)->path($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'pdf') {
            $parser = new PdfParser();

            return $parser->parseFile($absolutePath)->getText();
        }

        if (in_array($extension, ['doc', 'docx'], true)) {
            $document = IOFactory::load($absolutePath);
            $text = '';

            foreach ($document->getSections() as $section) {
                $text .= $this->collectElementText($section->getElements());
            }

            return $text;
        }

        return '';
    }

    /** @param array<int, mixed> $elements */
    private function collectElementText(array $elements): string
    {
        $text = '';

        foreach ($elements as $element) {
            if (method_exists($element, 'getText')) {
                $value = $element->getText();
                if (is_string($value)) {
                    $text .= $value . "\n";
                }
            } elseif (method_exists($element, 'getElements')) {
                $text .= $this->collectElementText($element->getElements()) . "\n";
            }
        }

        return $text;
    }

    /** @return array<string, string> */
    private function splitIntoSections(string $text): array
    {
        $aliasToSection = [];
        foreach (self::SECTION_HEADINGS as $section => $aliases) {
            foreach ($aliases as $alias) {
                $aliasToSection[$alias] = $section;
            }
        }

        $sections = [];
        $current = null;

        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $trimmed = trim($line);
            $normalized = strtolower(rtrim($trimmed, ':'));

            if (isset($aliasToSection[$normalized]) && mb_strlen($trimmed) <= 40) {
                $current = $aliasToSection[$normalized];
                $sections[$current] ??= '';

                continue;
            }

            if (in_array($normalized, self::OTHER_HEADINGS, true) && mb_strlen($trimmed) <= 40) {
                $current = null;

                continue;
            }

            if ($current !== null) {
                $sections[$current] .= $line . "\n";
            }
        }

        return $sections;
    }

    /** @return string[] */
    private function nonEmptyLines(string $text): array
    {
        $lines = array_map('trim', preg_split('/\r\n|\r|\n/', $text));

        return array_values(array_filter($lines, fn ($line) => $line !== ''));
    }

    private function guessExperienceYears(string $fullText, string $experienceSection): ?int
    {
        if (preg_match('/(\d{1,2})\+?\s*years?\b/i', $fullText, $matches) === 1) {
            $years = (int) $matches[1];
            if ($years >= 0 && $years <= 60) {
                return $years;
            }
        }

        preg_match_all('/\b(19|20)\d{2}\b/', $experienceSection, $matches);
        $years = array_map('intval', $matches[0] ?? []);

        if (count($years) >= 2) {
            $span = max($years) - min($years);
            if ($span >= 0 && $span <= 60) {
                return $span;
            }
        }

        return null;
    }

    /** @return string[] */
    private function parseSkills(string $skillsSection): array
    {
        $pieces = preg_split('/[,\n\r•|]+|\s-\s|\*\s/', $skillsSection);

        $skills = [];
        foreach ($pieces as $piece) {
            $skill = trim($piece, " \t\n\r\0\x0B-*•\u{2022}");
            if ($skill !== '' && mb_strlen($skill) <= 60) {
                $skills[] = $skill;
            }
        }

        return array_slice(array_values(array_unique($skills)), 0, 30);
    }
}
