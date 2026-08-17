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
        'education' => ['education', 'academic background', 'educational qualification', 'qualification', 'qualifications'],
    ];

    /**
     * Common resume headings we don't map to any field, but still need to recognize —
     * otherwise a section we DO care about (e.g. skills) would keep absorbing every
     * line all the way to the end of the document whenever it's not the last section.
     */
    private const OTHER_HEADINGS = [
        'certifications', 'certification', 'projects', 'languages',
        'references', 'awards', 'achievements', 'publications', 'volunteer experience',
        'interests', 'hobbies', 'contact', 'personal information', 'training',
    ];

    /**
     * Ordered highest → lowest, checked in this order so a resume listing multiple
     * levels (very common — "Matric... Intermediate... BSc...") maps to the highest
     * one actually held, not just whichever appears first in the text. Keys must
     * match JobSeekerProfile::QUALIFICATIONS exactly — this is what gets saved as
     * old-input for the qualification <select> on the profile form.
     *
     * @var array<string, string[]>
     */
    private const QUALIFICATION_KEYWORDS = [
        'phd' => ['phd', 'ph.d', 'doctorate', 'doctoral'],
        'mphil' => ['mphil', 'm.phil', 'm phil'],
        'masters' => ['master', "master's", 'masters', 'msc', 'm.sc', 'ms', 'm.s', 'ma', 'm.a', 'm.e', 'mtech', 'm.tech', 'mcom', 'm.com', 'mba', 'mcs', 'llm'],
        'bachelors' => ['bachelor', "bachelor's", 'bachelors', 'bsc', 'b.sc', 'bs', 'b.s', 'ba', 'b.a', 'b.e', 'btech', 'b.tech', 'bcom', 'b.com', 'bba', 'bcs', 'llb'],
        'diploma' => ['diploma', 'vocational certificate'],
        'intermediate' => ['intermediate', 'fsc', 'f.sc', 'hssc', 'a-level', 'a level'],
        'matric' => ['matric', 'matriculation', 'ssc', 'secondary school certificate', 'o-level', 'o level'],
        'middle' => ['middle school'],
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
                // Flattened, not the raw section text — a summary is normally one flowing
                // paragraph, and PDF extraction breaks lines wherever the page happened to
                // wrap, not at sentence boundaries. Collapsing that back into a paragraph
                // keeps both the bio and the headline from being cut off mid-sentence.
                $flat = trim(preg_replace('/\s+/', ' ', $summary));
                if ($flat !== '') {
                    $result['bio'] = mb_substr($flat, 0, 2000);
                    $result['headline'] = $this->firstSentence($flat, $lines[0]);
                }
            }
        }

        if (($experience = $sections['experience'] ?? null) !== null) {
            $position = $this->guessCurrentPosition($experience);
            if ($position !== null) {
                $result['current_position'] = $position;
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

        if (($qualification = $this->guessQualification($text, $sections['education'] ?? '')) !== null) {
            $result['qualification'] = $qualification;
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

    /** Up through the first sentence-ending punctuation in the flattened paragraph, capped at the field's own 255-char limit — falls back to the raw first line if there's no sentence break early enough to use. */
    private function firstSentence(string $flatText, string $firstLine): ?string
    {
        if (preg_match('/^(.{1,255}?[.!?])(\s|$)/', $flatText, $matches) === 1) {
            return $matches[1];
        }

        if (mb_strlen($flatText) <= 255) {
            return $flatText;
        }

        return mb_strlen($firstLine) <= 255 ? $firstLine : mb_substr($flatText, 0, 255);
    }

    /**
     * The first line of an experience entry isn't always the job title — many resumes
     * lead with the company/location and a right-aligned date range instead (title on
     * the line below). Skip any line that looks like that date/meta line rather than
     * blindly trusting whichever line comes first.
     */
    private function guessCurrentPosition(string $experienceSection): ?string
    {
        foreach ($this->nonEmptyLines($experienceSection) as $line) {
            if (preg_match('/\b(19|20)\d{2}\b/', $line) === 1 || stripos($line, 'present') !== false) {
                continue;
            }

            return mb_strlen($line) <= 255 ? $line : null;
        }

        return null;
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

    /** Prefers the Education section (if found) over the full text — a "Bachelor's" mentioned in a job title elsewhere shouldn't outrank a real education entry. */
    private function guessQualification(string $fullText, string $educationSection): ?string
    {
        $haystack = trim($educationSection) !== '' ? $educationSection : $fullText;
        $haystack = strtolower($haystack);

        foreach (self::QUALIFICATION_KEYWORDS as $level => $keywords) {
            foreach ($keywords as $keyword) {
                if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $haystack) === 1) {
                    return $level;
                }
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
