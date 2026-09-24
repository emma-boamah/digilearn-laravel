<?php

namespace App\Services;

use App\Models\CurriculumIndicator;
use Illuminate\Support\Str;

class LocalLessonSynthesizer
{
    /**
     * Synthesize a structured, student-facing lesson from curriculum context.
     *
     * @return array [
     *   'markdown' => string,
     *   'html' => string,
     *   'source' => 'local',
     *   'key_takeaways' => array,
     * ]
     */
    public function synthesize(CurriculumIndicator $indicator): array
    {
        $subStrand = $indicator->subStrand;
        $strand = $subStrand?->strand;
        $curriculum = $strand?->curriculum;
        $subjectName = $curriculum?->subject?->name ?? 'Mathematics';
        $gradeLabel = $strand?->grade_label ?? 'JHS';

        $cleanTitle = $indicator->student_title;

        // 1. Generate "The Big Idea" (Student introduction)
        $intro = $this->generateIntro($indicator, $subjectName, $gradeLabel);

        // 2. Generate Core Principles & Rules
        $rules = $this->generateCoreRules($indicator, $subjectName);

        // 3. Transform Exemplars into Worked Examples
        $workedExamples = $this->transformExemplarsIntoWorkedExamples($indicator->exemplars);

        // 4. Generate Key Takeaways
        $takeaways = $this->generateTakeaways($indicator, $cleanTitle);

        // Assemble clean Markdown
        $md = [];
        $md[] = "## Concept Overview: {$cleanTitle}";
        $md[] = $intro;
        $md[] = "";
        $md[] = "### 📌 Core Rules & Steps to Follow";
        $md[] = $rules;
        $md[] = "";

        if (!empty($workedExamples)) {
            $md[] = "### 💡 Step-by-Step Worked Examples";
            $md[] = $workedExamples;
            $md[] = "";
        }

        $md[] = "### 🎯 Key Takeaways";
        foreach ($takeaways as $t) {
            $md[] = "- **{$t}**";
        }

        $markdownText = implode("\n", $md);
        $htmlText = Str::markdown($markdownText);

        return [
            'markdown' => $markdownText,
            'html' => $htmlText,
            'source' => 'local',
            'key_takeaways' => $takeaways,
        ];
    }

    /**
     * Build student-oriented introduction.
     */
    protected function generateIntro(CurriculumIndicator $indicator, string $subject, string $grade): string
    {
        $desc = $indicator->description ?: $indicator->title;
        $strandTitle = $indicator->subStrand?->strand?->title ?? 'Core Concept';
        $subStrandTitle = $indicator->subStrand?->title ?? 'Topic';

        // Clean titles
        $strandClean = trim(preg_replace('/^STRAND\s+\d+\s*[:.\-]?\s*/i', '', $strandTitle));
        $subClean = trim(preg_replace('/^SUB[-\s]?STRAND\s+\d+\s*[:.\-]?\s*/i', '', $subStrandTitle));

        return "In this lesson on **{$subClean}** ({$strandClean}), you will explore how to {$desc}. Understanding this concept gives you the mathematical foundation to solve real-world problems involving quantities, measurements, and numerical relationships.";
    }

    /**
     * Formulate clear educational rules based on indicator context.
     */
    protected function generateCoreRules(CurriculumIndicator $indicator, string $subject): string
    {
        $text = strtolower($indicator->title . ' ' . ($indicator->description ?? ''));

        if (str_contains($text, 'compare') || str_contains($text, 'order') || str_contains($text, 'greater') || str_contains($text, 'less')) {
            return "1. **Check the Number of Digits**: A number with more digits is always greater than a number with fewer digits.\n" .
                   "2. **Compare from Left to Right**: If two numbers have the same number of digits, compare each digit starting from the highest place value (the leftmost digit).\n" .
                   "3. **Use the Correct Symbol**:\n" .
                   "   - `>` means **greater than** (e.g., $15 > 9$)\n" .
                   "   - `<` means **less than** (e.g., $4 < 12$)\n" .
                   "   - `=` means **equal to** (e.g., $100 = 100$)\n" .
                   "4. **Ordering Sequences**:\n" .
                   "   - **Ascending Order**: Arrange from smallest to largest.\n" .
                   "   - **Descending Order**: Arrange from largest to smallest.";
        }

        if (str_contains($text, 'place value') || str_contains($text, 'model number') || str_contains($text, 'billion') || str_contains($text, 'million')) {
            return "1. **Place Value Periods**: In base-ten numerals, digits are grouped into periods separated by commas: **Units**, **Thousands**, **Millions**, and **Billions**.\n" .
                   "2. **Powers of 10**: Each place value step to the left represents a quantity 10 times larger (\\(10^1 = 10\\), \\(10^2 = 100\\), \\(10^3 = 1,000\\), ..., \\(10^9 = 1,000,000,000\\)).\n" .
                   "3. **Multi-base & Currency Modeling**: Large sums can be represented using multi-base blocks (unit cubes, rods, flats, large blocks) or currency notes (GH₵50, GH₵100, GH₵200).\n" .
                   "4. **Reading Large Numbers**: Read the digits in each period as a three-digit group, followed by the period name (e.g., \\(2,450,000,000\\) is *two billion, four hundred and fifty million*).";
        }

        if (str_contains($text, 'round') || str_contains($text, 'nearest')) {
            return "1. **Locate Target Place Value**: Identify the digit at the place value you are rounding to.\n" .
                   "2. **Examine the Next Digit (to the right)**:\n" .
                   "   - If it is **5, 6, 7, 8, or 9**, round **UP** (add $1$ to target digit).\n" .
                   "   - If it is **0, 1, 2, 3, or 4**, round **DOWN** (keep target digit unchanged).\n" .
                   "3. **Replace with Zeros**: Change all digits to the right of the target place value to zeros.";
        }

        if (str_contains($text, 'decimal') || str_contains($text, 'significant')) {
            return "1. **Decimal Places (d.p.)**: Count digits directly to the right of the decimal point.\n" .
                   "2. **Significant Figures (s.f.)**: Count all non-zero digits, and zeros between non-zero digits or trailing zeros after a decimal point.\n" .
                   "3. **Apply the 5-or-More Rule**: Check the immediate next digit to determine whether to round up or keep the digit unchanged.";
        }

        // General default structured rules
        return "1. **Identify the Given Information**: Read the problem carefully to note known values and the target goal.\n" .
               "2. **Choose the Mathematical Strategy**: Apply the relevant formula, property, or step-by-step procedure.\n" .
               "3. **Compute Accurately**: Show each step of working clearly, maintaining proper unit annotations.\n" .
               "4. **Verify Your Solution**: Check if the resulting answer is reasonable in the context of the question.";
    }

    /**
     * Transform raw syllabus exemplars into step-by-step worked examples.
     */
    protected function transformExemplarsIntoWorkedExamples(?string $exemplarsText): string
    {
        if (empty($exemplarsText)) {
            return "";
        }

        // Split by E.g. or bullet
        $rawItems = preg_split('/(?=E\.g\.[,\s]*\d*[\.:]?)/i', $exemplarsText, -1, PREG_SPLIT_NO_EMPTY);
        $output = [];
        $count = 1;

        foreach ($rawItems as $item) {
            $cleaned = trim($item);
            if (strlen($cleaned) < 10) continue;

            // Remove leading "E.g. 1:" prefix
            $cleaned = preg_replace('/^E\.g\.[,\s]*\d*[\.:]?\s*/i', '', $cleaned);
            $cleaned = trim($cleaned);

            $output[] = "#### Example {$count}\n";
            $output[] = "**Problem Statement:**";
            $output[] = "{$cleaned}\n";
            $output[] = "**Step-by-Step Guided Solution:**";
            $output[] = "- Step 1: Identify the quantities and relationships specified in the problem.";
            $output[] = "- Step 2: Apply the standard mathematical rule and calculate each part carefully.";
            $output[] = "- Step 3: Conclude with the final result in proper mathematical notation.\n";

            $count++;
            if ($count > 3) break; // Limit to top 3 focused examples
        }

        return implode("\n", $output);
    }

    /**
     * Generate 3-4 bullet point key takeaways.
     */
    protected function generateTakeaways(CurriculumIndicator $indicator, string $title): array
    {
        $code = $indicator->indicator_code ? " ({$indicator->indicator_code})" : "";
        return [
            "Mastered the core objectives for {$title}{$code}.",
            "Understood how to interpret and model numerical quantities accurately.",
            "Learned how to apply step-by-step reasoning to verify solutions.",
            "Prepared to solve related checkpoint quizzes and exam practice questions.",
        ];
    }
}
