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
        $subjectName = $curriculum?->subject?->name ?? 'Core Mathematics';
        $gradeLabel = $strand?->grade_label ?? 'JHS';

        $cleanTitle = $indicator->student_title;

        // 1. Generate "The Big Idea" (Student introduction)
        $intro = $this->generateIntro($indicator, $subjectName, $gradeLabel);

        // 2. Generate Core Principles & Rules
        $rules = $this->generateCoreRules($indicator, $subjectName);

        // 3. Transform Exemplars into Worked Examples
        $workedExamples = $this->transformExemplarsIntoWorkedExamples($indicator->exemplars, $subjectName);

        // 4. Generate Key Takeaways
        $takeaways = $this->generateTakeaways($indicator, $cleanTitle, $subjectName);

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
     * Build student-oriented introduction tailored to the subject.
     */
    protected function generateIntro(CurriculumIndicator $indicator, string $subject, string $grade): string
    {
        $desc = $indicator->description ?: $indicator->title;
        $strandTitle = $indicator->subStrand?->strand?->title ?? 'Core Concept';
        $subStrandTitle = $indicator->subStrand?->title ?? 'Topic';

        // Clean titles
        $strandClean = trim(preg_replace('/^STRAND\s+\d+\s*[:.\-]?\s*/i', '', $strandTitle));
        $subClean = trim(preg_replace('/^SUB[-\s]?STRAND\s+\d+\s*[:.\-]?\s*/i', '', $subStrandTitle));

        $sLower = strtolower($subject);
        if (str_contains($sLower, 'ict') || str_contains($sLower, 'comput') || str_contains($sLower, 'technol')) {
            $outcome = "Mastering this topic equips you with vital digital literacy, hardware architecture principles, and computational skills needed to understand and navigate modern technology.";
        } elseif (str_contains($sLower, 'sci') || str_contains($sLower, 'phys') || str_contains($sLower, 'chem') || str_contains($sLower, 'bio')) {
            $outcome = "Developing this understanding builds your scientific inquiry, observation, and analytical skills to understand natural phenomena, physical laws, and the living world.";
        } elseif (str_contains($sLower, 'social') || str_contains($sLower, 'hist') || str_contains($sLower, 'geog') || str_contains($sLower, 'civic') || str_contains($sLower, 'econ')) {
            $outcome = "Understanding this topic enriches your perspective on society, governance, historical developments, and community environments.";
        } elseif (str_contains($sLower, 'eng') || str_contains($sLower, 'french') || str_contains($sLower, 'literat') || str_contains($sLower, 'lang')) {
            $outcome = "Mastering this enhances your language mastery, reading comprehension, critical analysis, and clear communication.";
        } elseif (str_contains($sLower, 'math')) {
            $outcome = "Understanding this concept gives you the mathematical foundation to solve real-world problems involving quantities, measurements, patterns, and numerical relationships.";
        } else {
            $outcome = "Understanding this concept builds foundational knowledge and practical skills that prepare you for future academic and real-world success.";
        }

        return "In this lesson on **{$subClean}** ({$strandClean}), you will explore how to {$desc}. {$outcome}";
    }

    /**
     * Formulate clear educational rules based on subject and indicator context.
     */
    protected function generateCoreRules(CurriculumIndicator $indicator, string $subject): string
    {
        $text = strtolower($indicator->title . ' ' . ($indicator->description ?? ''));
        $sLower = strtolower($subject);
        $isComputing = str_contains($sLower, 'ict') || str_contains($sLower, 'comput') || str_contains($sLower, 'technol');
        $isScience = str_contains($sLower, 'sci') || str_contains($sLower, 'phys') || str_contains($sLower, 'chem') || str_contains($sLower, 'bio');

        if ($isComputing) {
            if (str_contains($text, 'generation') || str_contains($text, 'architecture') || str_contains($text, 'history')) {
                return "1. **Core Processing Milestone**: Recognize the defining hardware component of the generation:\n" .
                       "   - 1st Gen: Vacuum Tubes | 2nd Gen: Transistors | 3rd Gen: Integrated Circuits (ICs)\n" .
                       "   - **4th Gen**: Very Large Scale Integration (VLSI) & Microprocessors (Single-chip CPUs)\n" .
                       "   - 5th Gen: Artificial Intelligence (AI), Parallel Processing & Quantum Computing.\n" .
                       "2. **Architectural Innovations**: 4th generation introduced microprocessors (e.g., Intel 4004/8086), high-speed semiconductor memory (RAM/ROM), and graphical operating systems.\n" .
                       "3. **Physical & Operational Characteristics**: Vastly smaller footprint (desktop/laptop), higher processing speeds (megahertz to gigahertz), minimal power consumption, and negligible heat generation compared to previous eras.\n" .
                       "4. **Accessibility & Networking**: Enabled personal computing (PCs), local area networks (LAN), wide-scale Internet connectivity, and user-friendly software.";
            }

            if (str_contains($text, 'input') || str_contains($text, 'output') || str_contains($text, 'device') || str_contains($text, 'hardware')) {
                return "1. **Input Devices**: Feed raw data and user instructions into the system (e.g., keyboard, mouse, scanner, microphone, touchscreen).\n" .
                       "2. **Central Processing Unit (CPU)**: Interprets and executes instructions via the Control Unit (CU) and Arithmetic Logic Unit (ALU).\n" .
                       "3. **Output Devices**: Communicate processed information in human-readable or audio/visual format (e.g., monitor, printer, speakers, projectors).\n" .
                       "4. **Storage Subsystems**: Retain data temporarily during execution (Primary: RAM/Cache) or permanently for future retrieval (Secondary: SSD, HDD, Flash Drives).";
            }

            if (str_contains($text, 'network') || str_contains($text, 'internet') || str_contains($text, 'cyber') || str_contains($text, 'safety') || str_contains($text, 'security')) {
                return "1. **Authentication & Password Hygiene**: Create strong passphrases combining letters, numbers, and symbols; never reuse credentials across services.\n" .
                       "2. **Data Privacy**: Keep personal identifying information (PII), location details, and financial credentials private and secure.\n" .
                       "3. **Safe Navigation**: Avoid opening unverified attachments, clicking phishing links, or installing unapproved software.\n" .
                       "4. **Digital Citizenship & Netiquette**: Practice ethical communication online, respect intellectual property, and report malicious activity immediately.";
            }

            return "1. **Identify the Core System/Component**: Clearly define the hardware, software, or digital system under study.\n" .
                   "2. **Trace the Information Processing Cycle**: Analyze the path of data from Input $\\rightarrow$ Processing $\\rightarrow$ Storage $\\rightarrow$ Output.\n" .
                   "3. **Apply Technical Terminology**: Use standard, accurate computing and architectural terminology in your analysis.\n" .
                   "4. **Evaluate Practical Application**: Relate the technical concept to real-world tasks, efficiency, and safety considerations.";
        }

        if ($isScience) {
            return "1. **Observe and Identify Phenomena**: Note key variables, states, or biological/physical structures carefully.\n" .
                   "2. **Relate to Scientific Principles**: Connect the observed event to fundamental laws, properties of matter, or biological processes.\n" .
                   "3. **Analyze Cause and Effect**: Examine how changes in one variable (temperature, pressure, concentration, force) impact the system.\n" .
                   "4. **Formulate Conclusions**: State clear findings supported by empirical evidence and correct scientific vocabulary.";
        }

        // Mathematics rules
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
        return "1. **Identify the Core Concepts**: Read the problem or topic carefully to isolate key requirements and terms.\n" .
               "2. **Apply Analytical Steps**: Break down the topic systematically using established subject principles.\n" .
               "3. **Follow Standard Procedures**: Present each step of analysis or working clearly and concisely.\n" .
               "4. **Verify Your Solution**: Review the outcome to confirm it directly answers the objective.";
    }

    /**
     * Transform raw syllabus exemplars into step-by-step worked examples.
     */
    protected function transformExemplarsIntoWorkedExamples(?string $exemplarsText, string $subject): string
    {
        if (empty($exemplarsText)) {
            return "";
        }

        $sLower = strtolower($subject);
        $isComputing = str_contains($sLower, 'ict') || str_contains($sLower, 'comput') || str_contains($sLower, 'technol');
        $isScience = str_contains($sLower, 'sci') || str_contains($sLower, 'phys') || str_contains($sLower, 'chem') || str_contains($sLower, 'bio');

        if ($isComputing) {
            $step1 = "- Step 1: Identify the technical component, generation, or scenario in the prompt.";
            $step2 = "- Step 2: Analyze its architectural features, operation, and functional role.";
            $step3 = "- Step 3: State the practical outcome or standard technical classification.\n";
        } elseif ($isScience) {
            $step1 = "- Step 1: Identify the scientific apparatus, variables, or phenomenon.";
            $step2 = "- Step 2: Apply the governing scientific law, observation, or formula.";
            $step3 = "- Step 3: Draw a clear, evidence-based conclusion using correct scientific terms.\n";
        } else {
            $step1 = "- Step 1: Identify the given information and relationships specified in the problem.";
            $step2 = "- Step 2: Apply the standard concept rule and evaluate each part carefully.";
            $step3 = "- Step 3: State the final solution clearly with proper annotations.\n";
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
            $output[] = $step1;
            $output[] = $step2;
            $output[] = $step3;

            $count++;
            if ($count > 3) break; // Limit to top 3 focused examples
        }

        return implode("\n", $output);
    }

    /**
     * Generate 3-4 bullet point key takeaways tailored to the subject.
     */
    protected function generateTakeaways(CurriculumIndicator $indicator, string $title, string $subject): array
    {
        $code = $indicator->indicator_code ? " ({$indicator->indicator_code})" : "";
        $sLower = strtolower($subject);

        if (str_contains($sLower, 'ict') || str_contains($sLower, 'comput') || str_contains($sLower, 'technol')) {
            return [
                "Mastered the core objectives for {$title}{$code}.",
                "Understood key architectural features, components, and operations.",
                "Learned how to analyze technical principles and practical computing applications.",
                "Prepared to solve related checkpoint quizzes and exam practice questions.",
            ];
        }

        if (str_contains($sLower, 'sci') || str_contains($sLower, 'phys') || str_contains($sLower, 'chem') || str_contains($sLower, 'bio')) {
            return [
                "Mastered the core objectives for {$title}{$code}.",
                "Understood fundamental scientific concepts and physical phenomena.",
                "Learned how to apply observational and analytical methods to verify findings.",
                "Prepared to solve related checkpoint quizzes and exam practice questions.",
            ];
        }

        if (str_contains($sLower, 'math')) {
            return [
                "Mastered the core objectives for {$title}{$code}.",
                "Understood how to interpret and model numerical quantities accurately.",
                "Learned how to apply step-by-step reasoning to verify solutions.",
                "Prepared to solve related checkpoint quizzes and exam practice questions.",
            ];
        }

        return [
            "Mastered the core objectives for {$title}{$code}.",
            "Acquired in-depth conceptual and practical understanding of this topic.",
            "Learned how to apply structured reasoning to solve exercises and problems.",
            "Prepared to solve related checkpoint quizzes and exam practice questions.",
        ];
    }
}
