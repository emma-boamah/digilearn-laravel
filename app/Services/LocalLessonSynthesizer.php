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

        // 5. Generate Checkpoint Quiz (3 questions for student verification)
        $quiz = $this->synthesizeQuiz($indicator, $cleanTitle, $subjectName, $takeaways);

        // 6. Generate Brilliant-Style Interactive Segments (Interleaved Concept + Challenge)
        $segments = $this->synthesizeInteractiveSegments($indicator, $cleanTitle, $subjectName, $intro, $rules, $workedExamples, $takeaways);

        return [
            'markdown' => $markdownText,
            'html' => $htmlText,
            'source' => 'local',
            'key_takeaways' => $takeaways,
            'quiz' => $quiz,
            'interactive_segments' => $segments,
        ];
    }

    /**
     * Build student-oriented introduction tailored to the subject.
     */
    protected function generateIntro(CurriculumIndicator $indicator, string $subject, string $grade): string
    {
        $rawDesc = $indicator->description ?: $indicator->title;
        // Strip bureaucratic syllabus jargon: "Demonstrate understanding and use of", "Demonstrate understanding of"
        $cleanDesc = preg_replace('/^(Demonstrate\s+(an\s+)?understanding\s+(and\s+use\s+)?of\s+)/i', '', $rawDesc);
        $cleanDesc = preg_replace('/^(Demonstrate\s+)/i', '', $cleanDesc);
        $cleanDesc = lcfirst(trim($cleanDesc));

        $topicName = $indicator->student_title;
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
            $contextText = strtolower($strandClean . ' ' . $subClean . ' ' . $cleanDesc);
            if (str_contains($contextText, 'geometry') || str_contains($contextText, 'angle') || str_contains($contextText, 'shape') || str_contains($contextText, 'space')) {
                $outcome = "Mastering this topic gives you the geometric foundation and spatial reasoning skills to analyze angles, geometric properties, shapes, and structural measurements.";
            } elseif (str_contains($contextText, 'algebra') || str_contains($contextText, 'equation') || str_contains($contextText, 'expression') || str_contains($contextText, 'relation')) {
                $outcome = "Understanding this topic strengthens your algebraic thinking, pattern recognition, and ability to model relationships using equations and formulas.";
            } elseif (str_contains($contextText, 'data') || str_contains($contextText, 'probability') || str_contains($contextText, 'chance') || str_contains($contextText, 'statistic')) {
                $outcome = "This lesson builds your statistical literacy to collect, organize, analyze real-world data, and make evidence-based predictions.";
            } else {
                $outcome = "Understanding this concept gives you the mathematical foundation to solve real-world problems involving quantities, measurements, patterns, and numerical relationships.";
            }
        } else {
            $outcome = "Understanding this concept builds foundational knowledge and practical skills that prepare you for future academic and real-world success.";
        }

        return "In this lesson on **{$topicName}** ({$subClean} &bull; {$strandClean}), you will learn how to {$cleanDesc}. {$outcome}";
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

        // Mathematics: Geometry & Angles rules
        if (str_contains($text, 'alternate') || str_contains($text, 'corresponding') || str_contains($text, 'transversal') || (str_contains($text, 'parallel') && str_contains($text, 'angle'))) {
            return '1. **Identify the Transversal & Parallel Lines**: Spot the line intersecting two parallel lines ($L_1 \parallel L_2).' . "\n" .
                   '2. **Alternate Angles (Z-Shape)**: Lie on opposite sides of the transversal between parallel lines; they are always **equal** (e.g., $\angle a = \angle b$).' . "\n" .
                   '3. **Corresponding Angles (F-Shape)**: Occupy matching relative positions at each intersection; they are always **equal** (e.g., $\angle c = \angle d$).' . "\n" .
                   '4. **Vertically Opposite Angles (X-Shape)**: Oppose each other at any line intersection and are always **equal**.' . "\n" .
                   '5. **Co-Interior Angles (C-Shape)**: Lie on the same side inside parallel lines and are **supplementary** (they sum to $180^\circ$).';
        }

        if (str_contains($text, 'triangle') && (str_contains($text, 'angle') || str_contains($text, 'interior'))) {
            return '1. **Angle Sum of a Triangle**: The interior angles of any triangle always sum to **$180^\circ$** ($\angle A + \angle B + \angle C = 180^\circ$).' . "\n" .
                   '2. **Exterior Angle Theorem**: The exterior angle of a triangle equals the sum of the two opposite interior angles.' . "\n" .
                   '3. **Isosceles Triangle Rule**: The angles opposite the equal sides are equal in measure.' . "\n" .
                   '4. **Equilateral Triangle Rule**: All three sides are equal, and each interior angle measures exactly $60^\circ$ ($180^\circ \div 3 = 60^\circ$).';
        }

        if (str_contains($text, 'classify angle') || str_contains($text, 'acute') || str_contains($text, 'obtuse') || str_contains($text, 'reflex') || str_contains($text, 'complementary') || str_contains($text, 'supplementary')) {
            return '1. **Angle Classifications by Size**:' . "\n" .
                   '   - **Acute Angle**: Greater than $0^\circ$ and less than $90^\circ$ ($0^\circ < \theta < 90^\circ$).' . "\n" .
                   '   - **Right Angle**: Exactly $90^\circ$ ($\theta = 90^\circ$, formed by perpendicular lines).' . "\n" .
                   '   - **Obtuse Angle**: Greater than $90^\circ$ and less than $180^\circ$ ($90^\circ < \theta < 180^\circ$).' . "\n" .
                   '   - **Straight Angle**: Exactly $180^\circ$ ($\theta = 180^\circ$, forming a straight line).' . "\n" .
                   '   - **Reflex Angle**: Greater than $180^\circ$ and less than $360^\circ$ ($180^\circ < \theta < 360^\circ$).' . "\n" .
                   '2. **Complementary Angles**: Two angles whose measures sum to **$90^\circ$** ($\angle a + \angle b = 90^\circ$).' . "\n" .
                   '3. **Supplementary Angles**: Two angles whose measures sum to **$180^\circ$** ($\angle a + \angle b = 180^\circ$).' . "\n" .
                   '4. **Angles at a Point**: Angles around a complete circle always sum to **$360^\circ$**.';
        }

        if (str_contains($text, 'pythagoras') || str_contains($text, 'hypotenuse') || str_contains($text, 'trigonometr')) {
            return '1. **Pythagoras\' Theorem**: In any right-angled triangle, the square of the hypotenuse ($c$) equals the sum of the squares of the other two sides ($a$ and $b$):' . "\n" .
                   '   $$a^2 + b^2 = c^2$$' . "\n" .
                   '2. **Identify the Hypotenuse**: The hypotenuse is always the longest side, located directly opposite the $90^\circ$ right angle.' . "\n" .
                   '3. **Finding a Shorter Side**: If $c$ and $a$ are known, rearrange: $b = \sqrt{c^2 - a^2}.' . "\n" .
                   '4. **Common Pythagorean Triples**: Familiarize yourself with standard integer triples: $(3, 4, 5)$, $(5, 12, 13)$, and $(8, 15, 17)$.';
        }

        if (str_contains($text, 'vector') || str_contains($text, 'bearing')) {
            return '1. **Vector Quantities**: A vector possesses both magnitude (size) and direction (e.g., velocity, displacement, force).' . "\n" .
                   '2. **Column Vector Notation**: Written as $\binom{x}{y}$, where $x$ represents horizontal movement and $y$ represents vertical movement.' . "\n" .
                   '3. **Vector Addition & Subtraction**: Combine corresponding components: $\binom{x_1}{y_1} + \binom{x_2}{y_2} = \binom{x_1 + x_2}{y_1 + y_2}.' . "\n" .
                   '4. **Three-Figure Bearings**: Measured in degrees clockwise from True North ($000^\circ$ to $360^\circ$). Back bearing = $\text{Bearing} \pm 180^\circ$.';
        }

        // Mathematics: Algebra rules
        if (str_contains($text, 'gradient') || str_contains($text, 'y = mx + c') || str_contains($text, 'equation of a line') || str_contains($text, 'slope')) {
            return '1. **Slope-Intercept Form**: The equation of any straight line is written as:' . "\n" .
                   '   $$y = mx + c$$' . "\n" .
                   '   - $m$ is the **gradient** (steepness or slope).' . "\n" .
                   '   - $c$ is the **$y$-intercept** (the value of $y$ where the line crosses the vertical axis).' . "\n" .
                   '2. **Calculating Gradient**: Given two points $(x_1, y_1)$ and $(x_2, y_2):' . "\n" .
                   '   $$m = \frac{y_2 - y_1}{x_2 - x_1} = \frac{\text{Change in } y}{\text{Change in } x}$$' . "\n" .
                   '3. **Parallel & Perpendicular Lines**: Parallel lines have equal gradients ($m_1 = m_2$). Perpendicular lines satisfy $m_1 \times m_2 = -1.' . "\n" .
                   '4. **Finding the Equation**: Substitute $m$ and one known point $(x, y)$ into $y = mx + c$ to solve for $c$.';
        }

        if (str_contains($text, 'equation') || str_contains($text, 'inequalit') || str_contains($text, 'variable') || str_contains($text, 'change of subject')) {
            return '1. **Maintain Equation Balance**: Whatever mathematical operation you perform on one side, you must perform identically on the other side.' . "\n" .
                   '2. **Apply Inverse Operations**: Reverse operations to isolate the unknown variable:' . "\n" .
                   '   - Addition $\leftrightarrow$ Subtraction | Multiplication $\leftrightarrow$ Division' . "\n" .
                   '3. **Collecting Like Terms**: Group all terms containing the variable on one side and constants on the other side.' . "\n" .
                   '4. **Inequality Rule**: When multiplying or dividing both sides of an inequality by a negative number, **flip the inequality sign** ($<$ becomes $>$, $\le$ becomes $\ge$).';
        }

        if (str_contains($text, 'factoris') || str_contains($text, 'factoriz') || str_contains($text, 'binomial') || str_contains($text, 'algebraic expression')) {
            return '1. **Distributive Law**: Multiply the term outside the brackets by every term inside: $a(b + c) = ab + ac.' . "\n" .
                   '2. **Finding the Highest Common Factor (HCF)**: Identify the greatest common numerical factor and common variable powers in all terms.' . "\n" .
                   '3. **Factorisation**: Extract the HCF outside parentheses: $6x + 15 = 3(2x + 5).' . "\n" .
                   '4. **Expanding Binomials (FOIL)**: $(a + b)(c + d) = ac + ad + bc + bd$ (First, Outer, Inner, Last).';
        }

        if (str_contains($text, 'pattern') || str_contains($text, 'sequence') || str_contains($text, 'nth term')) {
            return '1. **Find the Common Difference ($d$)**: Calculate the difference between consecutive terms: $d = T_2 - T_1.' . "\n" .
                   '2. **Linear Sequence Formula**: The $n^{\text{th}}$ term is given by:' . "\n" .
                   '   $$T_n = a + (n - 1)d$$' . "\n" .
                   '   where $a$ is the first term and $d$ is the common difference.' . "\n" .
                   '3. **Predicting Terms**: Substitute the desired position $n$ (e.g. $n = 20$) into the formula to find the value directly.' . "\n" .
                   '4. **Checking Rules**: Verify the formula against known terms in the sequence.';
        }

        // Mathematics: Statistics & Probability rules
        if (str_contains($text, 'mean') || str_contains($text, 'median') || str_contains($text, 'mode') || str_contains($text, 'data') || str_contains($text, 'frequency')) {
            return '1. **Mean (Average)**: Sum of all values divided by the total number of values: $\text{Mean} = \frac{\sum x}{n}.' . "\n" .
                   '2. **Median (Middle Value)**: Arrange data in ascending order; the median is the central value (or the average of the two middle values if $n$ is even).' . "\n" .
                   '3. **Mode (Most Frequent)**: The value that appears most often in the data set.' . "\n" .
                   '4. **Range (Spread)**: The difference between the highest and lowest values: $\text{Range} = \text{Maximum} - \text{Minimum}$.';
        }

        if (str_contains($text, 'probability') || str_contains($text, 'chance') || str_contains($text, 'sample space')) {
            return '1. **Theoretical Probability Formula**:' . "\n" .
                   '   $$P(\text{Event}) = \frac{\text{Number of Favourable Outcomes}}{\text{Total Number of Possible Outcomes}}$$' . "\n" .
                   '2. **Probability Range**: Probability is always a value between 0 and 1 inclusive ($0 \le P \le 1$):' . "\n" .
                   '   - $P = 0$: Impossible event | $P = 1$: Certain event.' . "\n" .
                   '3. **Complementary Events**: The probability of an event NOT happening is $P(\text{Not } E) = 1 - P(E).' . "\n" .
                   '4. **Sample Space ($S$)**: List all possible outcomes systematically (e.g., flipping a coin has $S = \{H, T\}$, rolling a die has $S = \{1, 2, 3, 4, 5, 6\}$).';
        }

        if (str_contains($text, 'fraction') || str_contains($text, 'ratio') || str_contains($text, 'percent') || str_contains($text, 'bodmas')) {
            return '1. **Adding & Subtracting Fractions**: Find the Least Common Denominator (LCD) before adding or subtracting numerators.' . "\n" .
                   '2. **Multiplying & Dividing Fractions**: Multiply straight across ($\frac{a}{b} \times \frac{c}{d} = \frac{ac}{bd}$). To divide, multiply by the reciprocal ($\frac{a}{b} \div \frac{c}{d} = \frac{a}{b} \times \frac{d}{c}$).' . "\n" .
                   '3. **Sharing in a Ratio ($a : b$)**: Add parts ($a + b$), divide the total by total parts to find 1 part, then multiply each share.' . "\n" .
                   '4. **BODMAS Order of Operations**: Evaluate in order: **B**rackets $\rightarrow$ **O**rders (powers/roots) $\rightarrow$ **D**ivision & **M**ultiplication $\rightarrow$ **A**ddition & **S**ubtraction.';
        }

        if (str_contains($text, 'set') || str_contains($text, 'venn')) {
            return '1. **Set Notation**: A set is a well-defined collection of distinct elements enclosed in curly braces $\{ \}.' . "\n" .
                   '2. **Intersection ($A \cap B$)**: Elements common to both set $A$ and set $B$.' . "\n" .
                   '3. **Union ($A \cup B$)**: All elements belonging to set $A$, set $B$, or both combined.' . "\n" .
                   '4. **Complement ($A\'$)**: All elements in the Universal set ($\mathcal{U}$) that do not belong to set $A$.';
        }

        // Mathematics: Numbers & Numeration rules
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

        if (str_contains($text, 'decimal') || str_contains($text, 'significant') || str_contains($text, 'standard form')) {
            return "1. **Decimal Places (d.p.)**: Count digits directly to the right of the decimal point.\n" .
                   "2. **Significant Figures (s.f.)**: Count all non-zero digits, and zeros between non-zero digits or trailing zeros after a decimal point.\n" .
                   "3. **Standard Scientific Form**: Express as $A \\times 10^n$, where $1 \\le A < 10$ and $n$ is an integer.\n" .
                   "4. **Apply the 5-or-More Rule**: Check the immediate next digit to determine whether to round up or keep the digit unchanged.";
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

    /**
     * Synthesize 3 multiple-choice comprehension questions for the topic.
     * Guaranteed deterministic, offline, and relevant to the indicator context.
     *
     * @return array<int, array{id: int, question: string, options: array<int, string>, correct_answer: int, explanation: string}>
     */
    public function synthesizeQuiz(CurriculumIndicator $indicator, string $title, string $subject, array $takeaways = []): array
    {
        $cleanTitle = trim($title);
        $domain = $this->detectDomain($indicator, $title, $subject);

        $q1 = $this->generateQuizQuestion1($domain, $cleanTitle);
        $q2 = $this->generateQuizQuestion2($domain, $cleanTitle);

        $questions = [
            array_merge(['id' => 1], $q1),
            array_merge(['id' => 2], $q2),
            [
                'id' => 3,
                'question' => "To verify that your work on \"{$cleanTitle}\" is completely correct, what should you always do?",
                'options' => [
                    "Review each step, check against the standard rules, and ensure the answer makes logical sense",
                    "Submit immediately without checking your calculations or steps",
                    "Change your answer repeatedly until it matches a friend's answer",
                    "Assume the first step is always the only one that matters",
                ],
                'correct_answer' => 0,
                'explanation' => "Verification involves reviewing your steps, ensuring standard rules were respected, and verifying that the final conclusion makes logical sense.",
            ],
        ];

        return $questions;
    }

    /**
     * Synthesize 3 Brilliant-style sequential micro-segments (Concept -> Challenge).
     *
     * @return array<int, array{id: int, title: string, subtitle: string, content_html: string, challenge: array}>
     */
    public function synthesizeInteractiveSegments(
        CurriculumIndicator $indicator,
        string $title,
        string $subject,
        string $intro,
        string $rules,
        string $workedExamples,
        array $takeaways = []
    ): array {
        $cleanTitle = trim($title);
        $domain = $this->detectDomain($indicator, $title, $subject);

        // SEGMENT 1: The Intuition & The Big Idea
        $challenge1 = $this->generateChallenge1($domain, $cleanTitle);

        $segments = [];
        $segments[] = [
            'id' => 1,
            'title' => "Part 1: The Intuition",
            'subtitle' => "Why this concept matters & how it impacts daily life",
            'content_html' => Str::markdown($intro),
            'challenge' => $challenge1,
        ];

        // SEGMENT 2: Core Rules & Practical Application
        $challenge2 = $this->generateChallenge2($domain, $cleanTitle);

        $segment2Content = "### Core Rules & Principles\n" . $rules;
        if (!empty($workedExamples)) {
            $segment2Content .= "\n\n### Worked Example\n" . $workedExamples;
        }

        $segments[] = [
            'id' => 2,
            'title' => "Part 2: Core Principles & Application",
            'subtitle' => "How to work through problems step-by-step",
            'content_html' => Str::markdown($segment2Content),
            'challenge' => $challenge2,
        ];

        // SEGMENT 3: Synthesis & Real-World Mastery
        $takeawaySummary = !empty($takeaways)
            ? implode("\n- ", array_map(fn($t) => "**{$t}**", $takeaways))
            : "Review and master the core techniques of {$cleanTitle}.";

        $challenge3 = $this->generateChallenge3($domain, $cleanTitle, $takeaways);

        $segments[] = [
            'id' => 3,
            'title' => "Part 3: Concept Mastery & Reflection",
            'subtitle' => "Essential takeaways and verification strategies",
            'content_html' => Str::markdown("### 🎯 Essential Takeaways\n- " . $takeawaySummary),
            'challenge' => $challenge3,
        ];

        return $segments;
    }

    /**
     * Classify an indicator into a granular, topic-specific domain using word-boundary matching.
     */
    protected function detectDomain(CurriculumIndicator $indicator, string $title, string $subject): string
    {
        $strandTitle = $indicator->subStrand?->strand?->title ?? '';
        $subStrandTitle = $indicator->subStrand?->title ?? '';
        $subjectName = $indicator->subStrand?->strand?->curriculum?->subject?->name ?? $subject;

        $context = strtolower(
            $title . ' ' .
            ($indicator->student_title ?? '') . ' ' .
            ($indicator->description ?? '') . ' ' .
            $subStrandTitle
        );
        $sLower = strtolower($subjectName . ' ' . $strandTitle);

        $match = function (array $patterns) use ($context): bool {
            foreach ($patterns as $pattern) {
                if (preg_match('/' . $pattern . '/i', $context)) {
                    return true;
                }
            }
            return false;
        };

        // 1. ICT / Computing
        if (str_contains($sLower, 'ict') || str_contains($sLower, 'comput') || str_contains($sLower, 'hardware') || str_contains($sLower, 'digital')) {
            if ($match(['\bcyber\b', '\binternet safety\b', '\betiquette\b', '\bphishing\b', '\bmalware\b', '\bpassword\b', '\bhygiene\b'])) {
                return 'computing_safety';
            }
            if ($match(['(input|output).*device', '\bhardware\b', '\bperipherals\b', '\bdata processing\b', '\bstorage device\b', '\bcomponents of a computer\b'])) {
                return 'computing_hardware';
            }
            return 'computing_general';
        }

        // 2. English Language & Literacy
        if (str_contains($sLower, 'english') || str_contains($sLower, 'grammar') || str_contains($sLower, 'reading') || str_contains($sLower, 'comprehension')) {
            if ($match(['\bconcord\b', '\bagreement\b', '\bsubject-verb\b'])) {
                return 'english_concord';
            }
            if ($match(['\bmain idea\b', '\btopic sentence\b', '\bparagraph\b', '\bsummary\b', '\bcomprehension\b'])) {
                return 'english_reading';
            }
            if ($match(['\bnoun\b', '\bcollective noun\b', '\bpronoun\b', '\bverb\b', '\badjective\b', '\btense\b'])) {
                return 'english_nouns';
            }
            return 'english_grammar';
        }

        // 3. Integrated Science
        if (str_contains($sLower, 'sci') || str_contains($sLower, 'phys') || str_contains($sLower, 'chem') || str_contains($sLower, 'bio') || str_contains($sLower, 'matter') || str_contains($sLower, 'energy') || str_contains($sLower, 'living')) {
            if ($match(['\bstate\b', '\bstates of matter\b', '\bparticle theory\b', '\bkinetic particle\b', '\bsolid\b', '\bliquid\b', '\bgas\b', '\bmelting\b', '\bboiling\b', '\bevaporation\b'])) {
                return 'science_matter';
            }
            if ($match(['\bcell\b', '\bcells\b', '\bnucleus\b', '\bcytoplasm\b', '\bcell wall\b', '\bcell membrane\b', '\bmicroscope\b'])) {
                return 'science_cells';
            }
            if ($match(['\benergy\b', '\bconservation of energy\b', '\bpotential energy\b', '\bkinetic energy\b', '\bforms of energy\b', '\btransformation\b'])) {
                return 'science_energy';
            }
            return 'science_general';
        }

        // 4. Mathematics - Decimals & Rounding
        if ($match(['\bround.*decimal', '\bdecimal.*round', '\bnearest tenth', '\bnearest hundredth', '\bthousandth', '\bthousandths'])) {
            return 'number_rounding_decimals';
        }
        if ($match(['\bround.*whole', '\bnearest hundred-thousand', '\bnearest ten-thousand', '\bnearest thousand', '\bnearest hundred', '\bnearest ten'])) {
            return 'number_rounding_whole';
        }
        if ($match(['\bsignificant\b', '\bdecimal place', '\bstandard form', '\bscientific notation'])) {
            return 'number_significant_figures';
        }
        if ($match(['\bpowers of 10\b', '\bpower of 10\b'])) {
            return 'number_powers_of_ten';
        }
        if ($match(['1,000,000,000', '\bbillion\b', '\bmulti-base\b', '\bisometric\b', '\bplace value period', '\bplace value\b', '\bplace\b'])) {
            return 'number_large_quantities';
        }

        // 5. Mathematics - Indices, Surds, Powers & Factors
        if ($match(['\bindices\b', '\blaws of indices\b', '\bindex law', '\bexponential\b', '\bsurd\b', '\bsurds\b', '\bsquare root', '\bnon-perfect square', '\bproduct of primes?\b', '\bproduct of powers?\b', '\bindex form\b', '\brepeated factors?\b', '\bhighest common factors?\b', '\bhcf\b', '\blcm\b', '\bpowers? of natural\b', '\bexponents?\b'])) {
            return 'number_indices_surds';
        }

        // 6. Mathematics - Operations & Arithmetic
        if ($match(['\bbodmas\b', '\bpemdas\b', '\border of operation', '\bmental math', '\bfour-digit\b', '\bmulti-digit\b', '\bassociative property', '\bfour basic operations?\b', '\boperations involving\b'])) {
            return 'number_integers_operations';
        }

        // 7. Mathematics - Fractions, Percentages, Ratios
        if ($match(['\bfraction\b', '\bfractions\b', '\bnumerator\b', '\bdenominator\b'])) {
            return 'number_fractions';
        }
        if ($match(['\bpercent\b', '\bpercentage\b', '\bsimple interest\b', '\bdiscount\b', '\bcommission\b', '\btax\b', '\bdepreciation\b'])) {
            return 'number_percentages';
        }
        if ($match(['\bratio\b', '\bratios\b', '\bproportional\b', '\bproportionality\b', '\bunit rate\b', '\btravel graph\b', '\bdistance-time\b', '\bspeed\b'])) {
            return 'number_ratios_proportions';
        }

        // 8. Mathematics - Sets
        if ($match(['\bconcept of sets\b', '\bvenn\b', '\buniversal set\b', '\bintersection.*set\b', '\bunion.*set\b', '\bsubset\b', '\bempty set\b', '\bmembers of the set\b', '\bmembers of the\b'])) {
            return 'number_sets';
        }

        // 9. Mathematics - Algebra: Linear Graphs, Relations, Gradients
        if ($match(['\bgradient\b', '\bslope\b', 'y\s*=\s*mx\s*\+\s*c', '\blinear relation', '\bordered pair', '\btable of values', '\bstraight line graph'])) {
            return 'algebra_linear_relations_graphs';
        }

        // 10. Mathematics - Algebra: Expressions, Distributive, Factorisation
        if ($match(['\bdistributive property\b', '\bdistributive law\b', '\bbinomial\b', '\bfactorise\b', '\bfactorize\b', '\bexpand brackets\b', '\bremove brackets\b', '\balgebraic expression\b', '\brational coefficient\b', '\balgebraic\b', '\bfactorising expression\b'])) {
            return 'algebra_expressions_factorisation';
        }

        // 11. Mathematics - Algebra: Linear Equations & Variables
        if ($match(['\bchange of subject\b', '\bsubstitute values\b', '\bformulae\b', '\bevaluate.*algebraic', '\blinear equation\b', '\bone variable\b', '\bvariables and equations\b'])) {
            return 'algebra_substitution_formulae';
        }

        // 12. Mathematics - Algebra: Inequalities
        if ($match(['\binequalit', '\bsolution set'])) {
            return 'algebra_inequalities';
        }

        // 13. Mathematics - Algebra: Patterns & Sequences
        if ($match(['\bpattern\b', '\bpatterns\b', '\bsequence\b', '\bsequences\b', '\bnth term\b', '\blinear sequence'])) {
            return 'algebra_patterns_sequences';
        }

        // 14. Mathematics - Geometry: Bearings & Direction
        if ($match(['\bbearing\b', '\bbearings\b', '\bback bearing\b', '\bthree-figure\b'])) {
            return 'geometry_bearings';
        }

        // 15. Mathematics - Geometry: Parallel Lines & Transversals
        if ($match(['\balternate\b', '\bcorresponding\b', '\btransversal\b', '\bco-interior\b', '\bparallel.*angle'])) {
            return 'geometry_parallel_angles';
        }

        // 16. Mathematics - Geometry: Pythagoras & Trigonometry
        if ($match(['\bpythagor', '\bhypotenuse\b', '\btrigonometr', '\bsine\b', '\bcosine\b', '\btangent\b', '\bright-angled triangle', '\bsoh cah toa\b'])) {
            return 'geometry_pythagoras_trigonometry';
        }

        // 17. Mathematics - Geometry: Angle Classification & Measurement
        if ($match(['\bacute\b', '\bobtuse\b', '\breflex\b', '\bcomplementary\b', '\bsupplementary\b', '\bclassify angle', '\bangle.*size'])) {
            return 'geometry_angles_classification';
        }

        // 18. Mathematics - Geometry: Triangles & Polygons
        if ($match(['\btriangle\b', '\btriangles\b', '\binterior angle', '\bexterior angle', '\bcongruent triangle', '\bsimilar triangle', '\bpolygon\b'])) {
            return 'geometry_triangles_polygons';
        }

        // 19. Mathematics - Geometry: Constructions, Measurement & 3-D Nets
        if ($match(['\bconstruct\b', '\bbisect\b', '\binscribed\b', '\bcircumscribed\b', '\bnet of\b', '\bcuboid\b', '\bprism\b', '\bsurface area\b', '\bdiameter\b', '\bradius\b', '\bcircumference\b', '\bperimeter\b', '\bsquare grid\b', '\brectangles and\b'])) {
            return 'geometry_constructions';
        }

        // 20. Mathematics - Geometry: Vectors
        if ($match(['\bvector\b', '\bvectors\b', '\bcolumn vector', '\bscalar multiplication', '\bposition vector', '\bdisplacement\b', '\btriangular and parallelogram\b'])) {
            return 'geometry_vectors';
        }

        // 21. Mathematics - Geometry: Transformations
        if ($match(['\brotation\b', '\benlargement\b', '\breflection\b', '\btranslation\b', '\btransformation\b'])) {
            return 'geometry_transformations';
        }

        // 22. Mathematics - Statistics: Central Tendency
        if ($match(['\bmean\b', '\bmedian\b', '\bmode\b', '\bcentral tendency\b', '\brange of data'])) {
            return 'data_central_tendency';
        }

        // 23. Mathematics - Statistics: Data Collection & Charts (Careful: avoid single word 'data' collision)
        if ($match(['\bfrequency table\b', '\bhistogram\b', '\bcategorical\b', '\bgrouped data\b', '\bungrouped\b', '\bbar graph\b', '\bpie chart\b', '\bstem-and-leaf\b', '\bcollection of data\b', '\braw data\b', '\borganise and analyse data\b', '\bcollect.*data\b'])) {
            return 'data_collection_charts';
        }

        // 23. Mathematics - Probability
        if ($match(['\bprobability\b', '\bchance\b', '\bsample space\b', '\blikelihood\b', '\bdie\b', '\bdice\b', '\bcoin\b'])) {
            return 'data_probability';
        }

        if (str_contains($sLower, 'math')) {
            return 'math_general';
        }

        return 'general';
    }

    /**
     * Generate dynamic Challenge 1 (The Intuition & The Big Idea) based on domain.
     */
    protected function generateChallenge1(string $domain, string $cleanTitle): array
    {
        switch ($domain) {
            case 'number_rounding_decimals':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "When rounding a decimal number such as 3.476 to the nearest hundredth, why do you check the thousandths digit (6)?",
                    'options' => [
                        "Because the thousandths digit reveals whether the value is closer to 3.470 or 3.480 (since 6 ≥ 5, round up to 3.48)",
                        "Because you always drop all decimal digits without looking at the next number",
                        "Because the tenths digit is the only digit that can change when rounding",
                        "Because decimals with three decimal places cannot be rounded",
                    ],
                    'correct_answer' => 0,
                    'hint' => "The immediate next digit to the right determines whether the target digit rounds up or stays unchanged.",
                    'explanation' => "When rounding to a target place value, the digit immediately to the right determines proximity. If it is 5, 6, 7, 8, or 9, the number is closer to the upper value and rounds up.",
                ];

            case 'number_rounding_whole':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why is rounding large numbers (like rounding 1,482,750 to 1,500,000) useful in practical real-world reports?",
                    'options' => [
                        "It provides a clear, manageable estimate of scale without getting distracted by insignificant low-order digits",
                        "Because numbers with more than four digits cannot be read accurately",
                        "To change whole numbers into decimal fractions",
                        "To delete money from official records",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Think of census counts, national budgets, or large distances.",
                    'explanation' => "Rounding provides meaningful approximations that make large numbers easy to comprehend and compare.",
                ];

            case 'number_significant_figures':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why are significant figures essential in scientific measurements and calculations?",
                    'options' => [
                        "They communicate the precision and certainty of a measured quantity",
                        "They remove all zeros from mathematical answers",
                        "They only apply to whole numbers, never decimals",
                        "They eliminate the need to write units of measurement",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Notice how scientific instruments have limits to their measurement precision.",
                    'explanation' => "Significant figures reflect the reliability and precision of experimental data.",
                ];

            case 'number_powers_of_ten':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What happens to the place value of each digit when a number is multiplied by 10³ (1,000)?",
                    'options' => [
                        "Every digit shifts 3 places to the left, increasing its place value a thousand-fold",
                        "Every digit shifts 3 places to the right, decreasing its value",
                        "Only the first digit changes; the rest stay unchanged",
                        "The number turns into a decimal smaller than 1",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Multiplying by powers of 10 shifts decimal digits to higher place values.",
                    'explanation' => "Multiplying by 10³ increases value by shifting each digit 3 positions higher to the left.",
                ];

            case 'number_large_quantities':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "In large base-ten numerals, why do we group digits into periods of three separated by commas (e.g. 2,450,000,000)?",
                    'options' => [
                        "To clearly distinguish place value periods (Units, Thousands, Millions, Billions) for accurate reading and computation",
                        "Because whole numbers cannot have more than three digits without commas",
                        "To turn whole numbers into decimal fractions",
                        "To separate positive numbers from negative numbers",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Commas group powers of 1,000 to assist reading large quantities.",
                    'explanation' => "Periods group place values into thousands, millions, and billions, making large quantities readable and structured.",
                ];

            case 'number_indices_surds':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why does the multiplication law of indices state that aᵐ × aⁿ = aᵐ⁺ⁿ?",
                    'options' => [
                        "Because multiplying repeated factors of the same base combines their total count into a single exponent",
                        "Because exponents must always be multiplied together",
                        "Because the base disappears when multiplying powers",
                        "It only works when the base is zero",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Think of 2² × 2³ = (2 × 2) × (2 × 2 × 2) = 2⁵.",
                    'explanation' => "When multiplying powers of the same base, you add exponents because you are combining counts of factors.",
                ];

            case 'number_integers_operations':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why is the BODMAS / PEMDAS order of operations necessary when evaluating expressions?",
                    'options' => [
                        "It provides a universally agreed sequence so that everyone arrives at the single correct mathematical result",
                        "It allows mathematicians to calculate from right to left whenever they choose",
                        "To ensure addition is always performed before brackets",
                        "It applies only to calculators, never to human calculations",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Without order of operations, 2 + 3 × 4 could produce different answers.",
                    'explanation' => "Standard order of operations guarantees consistency across mathematical communication.",
                ];

            case 'number_fractions':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why must fractions have a common denominator before they can be added or subtracted?",
                    'options' => [
                        "Because you can only combine parts that are of equal unit size",
                        "Because numerators cannot be added unless both numbers are prime",
                        "To reduce the fraction to zero",
                        "Common denominators are only used when multiplying fractions",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Think of combining slices from a pizza cut into 4 slices with one cut into 8 slices.",
                    'explanation' => "The denominator indicates part size; addition requires parts of identical unit size.",
                ];

            case 'number_percentages':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why does expressing values as a percentage (out of 100) make comparisons easier?",
                    'options' => [
                        "It standardizes different quantities onto a common base of 100 for direct comparison",
                        "Because all percentages are always greater than 100",
                        "To eliminate the need for division in business",
                        "It converts money into whole numbers without decimal points",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Percent literally means 'per hundred'.",
                    'explanation' => "Standardizing onto a base of 100 allows clear comparison between groups of different sizes.",
                ];

            case 'number_ratios_proportions':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is the fundamental difference between a ratio (a : b) and a single fraction?",
                    'options' => [
                        "A ratio compares relative parts to each other, whereas a single fraction compares a part to the entire whole",
                        "Ratios only work with negative numbers",
                        "Fractions cannot be multiplied, but ratios can",
                        "Ratios always equal zero when simplified",
                    ],
                    'correct_answer' => 0,
                    'hint' => "In a ratio of 2 : 3, total parts are 2 + 3 = 5.",
                    'explanation' => "Ratios express part-to-part relationships, whereas fractions describe part-to-whole.",
                ];

            case 'number_sets':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "In Venn diagrams and set theory, what does the intersection (A ∩ B) represent?",
                    'options' => [
                        "Elements that belong to both set A and set B simultaneously",
                        "All elements in either set combined together",
                        "Elements that belong only to the universal set",
                        "Elements that belong to neither set",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Look at the overlapping region between the two circles.",
                    'explanation' => "The intersection A ∩ B contains only the common elements shared by both sets.",
                ];

            case 'algebra_linear_relations_graphs':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "In the straight-line equation y = mx + c, what does the gradient m represent?",
                    'options' => [
                        "The rate of change or steepness (vertical change divided by horizontal change: Δy / Δx)",
                        "The point where the line crosses the horizontal x-axis",
                        "The total area enclosed under the line",
                        "The physical length of the line segment drawn on paper",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Gradient measures 'rise over run'.",
                    'explanation' => "The gradient m = (y₂ - y₁) / (x₂ - x₁) measures how steeply y changes for each unit step in x.",
                ];

            case 'algebra_expressions_factorisation':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is the mathematical relationship between expanding brackets and factorisation?",
                    'options' => [
                        "They are inverse operations: expanding multiplies out terms, while factorising extracts common factors into brackets",
                        "They both increase the power of variables without limit",
                        "Expanding works only on fractions, while factorising works only on integers",
                        "There is no mathematical connection between them",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Expanding turns a(b + c) into ab + ac; factorising turns ab + ac back into a(b + c).",
                    'explanation' => "Expanding removes brackets via the distributive law, whereas factorising reverses this by finding common factors.",
                ];

            case 'algebra_substitution_formulae':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why is changing the subject of a formula useful in science and engineering?",
                    'options' => [
                        "It isolates the desired variable so you can calculate its value directly without solving from scratch every time",
                        "It eliminates the need for mathematical equality",
                        "To change linear formulas into quadratic curves",
                        "Because formulas cannot be evaluated with numbers",
                    ],
                    'correct_answer' => 0,
                    'hint' => "If you know speed and time, rearranging distance = speed × time gives time = distance / speed.",
                    'explanation' => "Rearranging formulas allows direct calculation of unknown quantities from known data.",
                ];

            case 'algebra_inequalities':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "How does a linear inequality (such as x > 3) fundamentally differ from an equation (such as x = 3)?",
                    'options' => [
                        "An inequality defines a continuous set of possible solutions, while an equation typically defines specific discrete values",
                        "Inequalities cannot be plotted on a number line",
                        "Equations never contain variables",
                        "Inequalities can only be solved using calculators",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Think of numbers like 4, 5, 6, 7.5—all satisfy x > 3.",
                    'explanation' => "Inequalities represent ranges or solution sets bounded by a threshold.",
                ];

            case 'algebra_patterns_sequences':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why is finding the nth term formula (Tn) advantageous when analyzing numerical sequences?",
                    'options' => [
                        "It allows you to calculate any term (such as the 100th term) directly without computing all intermediate terms",
                        "It eliminates the need to know the first term of the sequence",
                        "Because sequences cannot have more than 10 terms without a formula",
                        "It turns arithmetic sequences into geometric shapes",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Imagine needing term 50 without having to add the step 49 times.",
                    'explanation' => "The general nth term rule Tn = a + (n - 1)d provides direct calculation of any term position instantaneously.",
                ];

            case 'geometry_bearings':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why are three-figure bearings (measured clockwise from true North) standard in marine navigation, aviation, and surveying?",
                    'options' => [
                        "They provide an unambiguous 360° directional reference relative to true North using 3 digits (000° to 360°)",
                        "They only apply when walking in a straight line on flat ground",
                        "Because angles can only be drawn with three pencils",
                        "Bearings are measured counter-clockwise starting from the South",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Notice how 3-digit bearings ensure no confusion (e.g. 045° vs 45°).",
                    'explanation' => "Three-figure bearings measure angles clockwise from true North, providing a standardized 360° directional coordinate.",
                ];

            case 'geometry_parallel_angles':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "When a straight line (a transversal) crosses two parallel lines, which geometric relationship is always true?",
                    'options' => [
                        "Alternate interior angles and corresponding angles are equal in measure",
                        "All angles formed must always equal 90°",
                        "The angles change randomly depending on how long the parallel lines are drawn",
                        "Alternate angles always add up to 360°",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Think of the 'Z' shape for alternate angles and 'F' shape for corresponding angles.",
                    'explanation' => "Parallel lines intersected by a transversal create pairs of equal alternate angles ('Z' shape) and equal corresponding angles ('F' shape).",
                ];

            case 'geometry_pythagoras_trigonometry':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "In a right-angled triangle, why does Pythagoras' theorem state that a² + b² = c²?",
                    'options' => [
                        "The combined geometric area of squares on the two shorter sides equals the area of the square on the hypotenuse",
                        "The perimeter of the triangle always equals the hypotenuse squared",
                        "The hypotenuse length is simply equal to the sum of a and b",
                        "It only applies when all three sides are equal",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Imagine geometric squares constructed along each of the three sides.",
                    'explanation' => "Pythagoras' theorem is an exact relationship of square areas: the squares constructed on the perpendicular legs combine to equal the area on the hypotenuse.",
                ];

            case 'geometry_angles_classification':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "How do geometric angle classifications (acute, right, obtuse, reflex) help mathematicians and engineers?",
                    'options' => [
                        "They establish exact rotational benchmarks (< 90°, 90°, between 90° and 180°, > 180°) for measurement",
                        "They measure lengths in meters rather than degrees of turn",
                        "They only apply to 3D spheres, never to 2D plane geometry",
                        "They eliminate the need to use protractors or angle measurement tools",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Angles quantify rotational turn around a vertex from a baseline ray.",
                    'explanation' => "Angle classifications provide precise standards: acute (< 90°), right (90°), obtuse (between 90° and 180°), and reflex (> 180°).",
                ];

            case 'geometry_triangles_polygons':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "In Euclidean geometry, why do the interior angles of any triangle always add up to exactly 180°?",
                    'options' => [
                        "Drawing a line through one vertex parallel to the base proves the three angles form a straight line (180°)",
                        "Because every triangle must have sides of identical length",
                        "Because each individual angle in every triangle is fixed at 60°",
                        "Because triangles are formed by dividing a circle into two equal parts",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Notice that angles along any straight line always sum to 180°.",
                    'explanation' => "Drawing a parallel line through the top vertex shows that alternate interior angles sit alongside the third angle on a straight line, totaling 180°.",
                ];

            case 'geometry_constructions':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why do classical geometric constructions rely on a straightedge and compass rather than ruler measurements?",
                    'options' => [
                        "They create mathematically exact geometric relationships that do not depend on the measurement error of a physical ruler",
                        "Because rulers were prohibited in ancient times",
                        "To prevent students from drawing straight lines",
                        "Because compasses can only draw angles of 90°",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Constructions prove properties through exact circles and intersecting arcs.",
                    'explanation' => "Compass-and-straightedge constructions guarantee exact mathematical symmetry and angle bisection independent of scale markings.",
                ];

            case 'geometry_vectors':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What makes a vector quantity fundamentally different from a scalar quantity?",
                    'options' => [
                        "A vector has both magnitude (size) and direction, whereas a scalar has only magnitude",
                        "Vectors can only represent monetary currency",
                        "A scalar has direction, but a vector has no direction",
                        "Vectors always equal zero when plotted on a grid",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Think about displacement (moving 5 km North) versus simple mass or temperature.",
                    'explanation' => "Scalars have only magnitude (like temperature or mass), while vectors describe directed quantities (like displacement, velocity, or column vectors).",
                ];

            case 'geometry_transformations':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Which property is preserved under rigid transformations (translation, reflection, and rotation)?",
                    'options' => [
                        "Length, angle size, and overall shape are perfectly preserved (congruent images)",
                        "The shape increases in area by 50%",
                        "The coordinates always sum to zero",
                        "The image turns into a circle",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Rigid transformations move or turn an object without stretching or distorting it.",
                    'explanation' => "Isometries preserve Euclidean distance and angle measures, producing congruent figures.",
                ];

            case 'data_central_tendency':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why is the median often preferred over the mean when analyzing datasets with extreme outliers?",
                    'options' => [
                        "The median identifies the middle value and is not distorted by unusually high or low outliers",
                        "The median is always twice as large as the mean",
                        "The mean cannot be calculated when values are positive",
                        "The median works only for text data and not numbers",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Consider average salaries when one person earns millions.",
                    'explanation' => "Extreme outliers pull the mean away from the center, whereas the median remains stable at the true middle position.",
                ];

            case 'data_collection_charts':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why do statisticians group raw data into frequency tables and graphical charts?",
                    'options' => [
                        "To reveal patterns, distributions, clusters, and central trends that are hidden in unsorted raw data",
                        "To make the data impossible for other people to read",
                        "Because raw data cannot contain numbers greater than 100",
                        "To eliminate the need for calculations",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Visual representations make complex data interpretable at a glance.",
                    'explanation' => "Frequency tables and charts summarize large sets of raw observations into clear distributions.",
                ];

            case 'data_probability':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What do probability values of P = 0 and P = 1 signify in mathematics?",
                    'options' => [
                        "P = 0 represents an impossible event, while P = 1 represents an absolute certainty",
                        "P = 0 means it happens 50% of the time, and P = 1 means 100 times",
                        "Probabilities can never equal 0 or 1",
                        "P = 1 means impossible and P = 0 means certain",
                    ],
                    'correct_answer' => 0,
                    'hint' => "All probabilities are bounded strictly between 0 and 1 inclusive.",
                    'explanation' => "Theoretical probability ranges from 0 (impossible) to 1 (certain).",
                ];

            case 'computing_hardware':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "How do input devices, processing units, and output devices collaborate in computer architecture?",
                    'options' => [
                        "Input devices capture user data, the CPU processes instructions, and output devices present results to humans",
                        "All devices perform the same processing tasks simultaneously",
                        "Output devices convert digital data into raw mechanical electricity",
                        "Input devices operate only when the computer is turned off",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Follow the standard IPOS model: Input → Processing → Output → Storage.",
                    'explanation' => "Hardware coordinates data flow systematically from user input through computational processing to presentation.",
                ];

            case 'computing_safety':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why is cyber etiquette and internet security essential in digital communication?",
                    'options' => [
                        "It safeguards personal privacy, prevents unauthorized access, and maintains respectful online collaboration",
                        "To prevent computers from using internet cables",
                        "Because operating systems cannot run without passwords",
                        "To eliminate the need for web browsers",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Cyber safety protects identity, confidential data, and device health.",
                    'explanation' => "Responsible digital practices protect users against identity theft, malware, and privacy breaches.",
                ];

            case 'science_energy':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is the core principle governing the Law of Conservation of Energy?",
                    'options' => [
                        "Energy cannot be created or destroyed, only transformed from one form into another",
                        "Energy disappears completely when mechanical friction occurs",
                        "Energy can be manufactured from nothing using chemical reactions",
                        "Potential energy only exists on the Moon",
                    ],
                    'correct_answer' => 0,
                    'hint' => "The total amount of energy in a closed system remains constant.",
                    'explanation' => "Energy continuously transforms (e.g. potential to kinetic, electrical to light) while the total quantity is preserved.",
                ];

            case 'science_cells':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why are cells considered the fundamental building blocks of all living organisms?",
                    'options' => [
                        "Because every living organism is made of one or more cells that carry out essential life processes",
                        "Because non-living rocks and minerals are also composed of biological cells",
                        "Because cells only exist in multicellular animals, not in plants or bacteria",
                        "Because cells operate without needing any nutrients or biological energy",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Cell theory states that the cell is the basic structural and functional unit of life.",
                    'explanation' => "Cells are the smallest units that perform all the functions of life, including metabolism, growth, and replication.",
                ];

            case 'science_matter':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "According to the kinetic particle theory, what determines whether a substance exists as a solid, liquid, or gas?",
                    'options' => [
                        "The kinetic energy of its particles and the strength of the forces holding them together",
                        "The color and physical brightness of the container it is stored in",
                        "The total number of protons in the surrounding air",
                        "Matter only changes state when mixed with chemical acids",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Think about how adding heat increases particle movement and overcomes intermolecular forces.",
                    'explanation' => "Temperature increases particle kinetic energy, allowing particles to overcome attractive forces and transition between states.",
                ];

            case 'english_nouns':
            case 'english_grammar':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why are grammatical classifications (such as nouns, verbs, and collective nouns) essential in language?",
                    'options' => [
                        "They establish structural clarity and precision so that ideas are communicated without ambiguity",
                        "To ensure all sentences have exactly five words",
                        "Because adjectives cannot be used in formal writing",
                        "To eliminate punctuation marks from paragraphs",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Grammar provides the systematic architecture for sentence construction.",
                    'explanation' => "Accurate grammatical roles ensure that readers understand who is acting, what is occurring, and how entities relate.",
                ];

            case 'english_concord':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why is subject-verb agreement (grammatical concord) critical in English sentences?",
                    'options' => [
                        "It ensures that the grammatical number (singular or plural) of the subject correctly matches its corresponding verb",
                        "It makes all verbs past tense regardless of the subject",
                        "It eliminates the need for nouns in complete sentences",
                        "It forces sentences to only use auxiliary verbs",
                    ],
                    'correct_answer' => 0,
                    'hint' => "A singular subject takes a singular verb; a plural subject takes a plural verb.",
                    'explanation' => "Subject-verb concord maintains grammatical consistency so listeners and readers immediately understand who is performing the action.",
                ];

            case 'english_reading':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why is identifying the topic sentence essential when reading an informational paragraph or passage?",
                    'options' => [
                        "The topic sentence states the central main idea that all subsequent sentences explain, support, or illustrate",
                        "The topic sentence is always the longest sentence in a paragraph",
                        "The topic sentence contains all the spelling rules of the paragraph",
                        "The topic sentence must be memorized word for word by the reader",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Look for the sentence that summarizes the main thought of the paragraph.",
                    'explanation' => "The topic sentence presents the central thesis of the paragraph, anchoring all accompanying evidence and details.",
                ];

            default:
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why is understanding the concept of \"{$cleanTitle}\" essential?",
                    'options' => [
                        "It establishes the foundational principles needed to analyze real-world observations and practical problems",
                        "It replaces factual investigation with personal assumptions",
                        "It is only required for memorization, with no real application",
                        "It contradicts standard subject methodology",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Notice how this concept sets the stage for the rest of the topic.",
                    'explanation' => "Foundational conceptual knowledge provides the framework for systematic inquiry and problem-solving.",
                ];
        }
    }

    /**
     * Generate dynamic Challenge 2 (Core Rules & Practical Application) based on domain.
     */
    protected function generateChallenge2(string $domain, string $cleanTitle): array
    {
        switch ($domain) {
            case 'number_rounding_decimals':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is 14.863 rounded to the nearest tenth?",
                    'options' => [
                        "14.9 (the hundredths digit is 6, which is 5 or more, so the tenths digit 8 rounds up to 9)",
                        "14.8 (keep the tenths digit and discard the rest)",
                        "15.0 (round the whole number directly)",
                        "14.86 (keep two decimal places)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Identify the tenths position (8) and inspect the next digit to the right (6).",
                    'explanation' => "The target digit is 8 in the tenths position. The following digit is 6. Since 6 ≥ 5, round up: 14.863 becomes 14.9.",
                ];

            case 'number_rounding_whole':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is 482,750 rounded to the nearest ten-thousand?",
                    'options' => [
                        "480,000 (the thousands digit is 2, which is less than 5, so the ten-thousands digit 8 stays unchanged)",
                        "490,000 (round up regardless of digits)",
                        "500,000 (round to the nearest hundred-thousand)",
                        "482,000 (round to the nearest thousand)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "The ten-thousands digit is 8. Inspect the thousands digit (2).",
                    'explanation' => "Since the thousands digit is 2 (< 5), round down: the 8 remains unchanged and lower digits become zeros, giving 480,000.",
                ];

            case 'number_significant_figures':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "How many significant figures are in the numeral 0.004050?",
                    'options' => [
                        "4 significant figures (4, 0, 5, and the trailing 0; leading zeros are place-holders only)",
                        "6 significant figures (counting all digits)",
                        "3 significant figures (4, 0, 5)",
                        "2 significant figures (4 and 5)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Leading zeros before the first non-zero digit are never significant; trailing zeros after a decimal point are significant.",
                    'explanation' => "Leading zeros locate the decimal point. The significant digits are 4, 0, 5, and the final 0 (4 s.f.).",
                ];

            case 'number_powers_of_ten':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is 0.045 multiplied by 10³ (1,000)?",
                    'options' => [
                        "45 (shifting the decimal point 3 positions to the right)",
                        "4.5 (shifting 2 positions)",
                        "450 (shifting 4 positions)",
                        "0.000045 (dividing instead of multiplying)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Multiplying by 10³ moves the decimal point 3 places to the right.",
                    'explanation' => "0.045 × 1,000 = 45.",
                ];

            case 'number_large_quantities':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "In the numeral 5,420,800,000, what is the place value of the digit 4?",
                    'options' => [
                        "Four hundred million (400,000,000)",
                        "Four billion (4,000,000,000)",
                        "Forty million (40,000,000)",
                        "Four million (4,000,000)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "The numeral has Billions (5), then Hundred-Millions (4).",
                    'explanation' => "In 5,420,800,000, the 5 is in Billions, and 4 is in the Hundred-Millions period (400,000,000).",
                ];

            case 'number_indices_surds':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Simplify the index expression: 2³ × 2⁴.",
                    'options' => [
                        "2⁷ = 128 (add exponents: 3 + 4 = 7)",
                        "2¹² (multiply exponents instead of adding)",
                        "4⁷ (multiply the bases)",
                        "2¹ = 2 (subtract exponents)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Apply the product law of indices: aᵐ × aⁿ = aᵐ⁺ⁿ.",
                    'explanation' => "2³ × 2⁴ = 2³⁺⁴ = 2⁷ = 128.",
                ];

            case 'number_integers_operations':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Evaluate the expression using order of operations (BODMAS): 5 + 3 × 4.",
                    'options' => [
                        "17 (multiply first: 3 × 4 = 12, then add 5: 5 + 12 = 17)",
                        "32 (adding first: 5 + 3 = 8, then multiplying 8 × 4 = 32)",
                        "20 (adding 5 + 3 + 4)",
                        "60 (multiplying 5 × 3 × 4)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Multiplication takes precedence over addition in BODMAS.",
                    'explanation' => "Multiplication is performed before addition: 3 × 4 = 12; 5 + 12 = 17.",
                ];

            case 'number_fractions':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is 1/3 + 1/6 expressed as a simplified fraction?",
                    'options' => [
                        "1/2 (convert 1/3 to 2/6, then 2/6 + 1/6 = 3/6 = 1/2)",
                        "2/9 (adding numerators and denominators directly)",
                        "2/6 (forgetting to add the second numerator)",
                        "5/6 (incorrect common denominator calculation)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Find the common denominator (6) before adding.",
                    'explanation' => "1/3 = 2/6. 2/6 + 1/6 = 3/6 = 1/2.",
                ];

            case 'number_percentages':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Calculate a 15% discount on an item originally priced at GH₵200.",
                    'options' => [
                        "GH₵30 (15/100 × 200 = 30)",
                        "GH₵15 (multiplying only by 100)",
                        "GH₵185 (the price after discount, not the discount amount)",
                        "GH₵50 (guessing without computing)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Find 10% (GH₵20) and 5% (GH₵10), then combine.",
                    'explanation' => "15% of GH₵200 = 0.15 × 200 = GH₵30.",
                ];

            case 'number_ratios_proportions':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Share GH₵120 between Kofi and Ama in the ratio 2 : 3. How much does Ama receive?",
                    'options' => [
                        "GH₵72 (Total parts = 5; 1 part = 120/5 = 24; Ama's share = 3 × 24 = 72)",
                        "GH₵48 (Kofi's share: 2 × 24 = 48)",
                        "GH₵60 (an equal split)",
                        "GH₵40 (dividing by 3 directly)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Add the ratio parts (2 + 3 = 5), find 1 part, then multiply by 3.",
                    'explanation' => "Total parts = 5. One part = 120 / 5 = 24. Ama's 3 parts = 3 × 24 = GH₵72.",
                ];

            case 'number_sets':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "If Set A = {1, 2, 3, 5} and Set B = {2, 3, 4, 6}, what is the intersection A ∩ B?",
                    'options' => [
                        "{2, 3} (the elements present in both Set A and Set B)",
                        "{1, 2, 3, 4, 5, 6} (the union A ∪ B)",
                        "{1, 5} (elements exclusive to Set A)",
                        "{} (empty set)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Identify the common numbers appearing in both lists.",
                    'explanation' => "The common numbers in both sets are 2 and 3, so A ∩ B = {2, 3}.",
                ];

            case 'algebra_linear_relations_graphs':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "A straight line passes through points (1, 3) and (4, 9). What is its gradient m?",
                    'options' => [
                        "2, because m = (9 - 3) / (4 - 1) = 6 / 3 = 2",
                        "3, because 9 - 6 = 3",
                        "1/2, because horizontal change is divided by vertical change",
                        "6, the vertical distance between the two points",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Use the formula m = (y₂ - y₁) / (x₂ - x₁).",
                    'explanation' => "m = (9 - 3) / (4 - 1) = 6 / 3 = 2.",
                ];

            case 'algebra_expressions_factorisation':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Expand the binomial expression: (x + 3)(x + 2).",
                    'options' => [
                        "x² + 5x + 6 (using FOIL: x² + 2x + 3x + 6)",
                        "x² + 6 (forgetting the middle terms)",
                        "2x + 5 (adding terms instead of multiplying)",
                        "x² + 6x + 5 (swapping coefficients)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Multiply First, Outside, Inside, and Last terms (FOIL).",
                    'explanation' => "(x + 3)(x + 2) = x² + 2x + 3x + 6 = x² + 5x + 6.",
                ];

            case 'algebra_substitution_formulae':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "In the physics equation v = u + at, make acceleration a the subject of the formula.",
                    'options' => [
                        "a = (v - u) / t (subtract u from both sides, then divide by t)",
                        "a = v - u - t (subtracting all terms)",
                        "a = (v + u) / t (adding u instead of subtracting)",
                        "a = vt - u (multiplying incorrectly)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Isolate 'at' first by subtracting u, then divide by t.",
                    'explanation' => "v - u = at, so dividing both sides by t gives a = (v - u) / t.",
                ];

            case 'algebra_inequalities':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Solve the linear inequality: 3x - 5 < 10.",
                    'options' => [
                        "x < 5 (add 5 to get 3x < 15, then divide by 3)",
                        "x > 5 (flipping the sign when dividing by positive 3)",
                        "x < 15 (forgetting to divide by 3)",
                        "x = 5 (treating it as an equation)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Add 5 to both sides, then divide by 3 (a positive number, so sign stays unchanged).",
                    'explanation' => "3x - 5 < 10 => 3x < 15 => x < 5.",
                ];

            case 'algebra_patterns_sequences':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "For the arithmetic sequence 5, 9, 13, 17, ..., what is the common difference d and the 10th term?",
                    'options' => [
                        "d = 4; 10th term = 41 (T₁₀ = 5 + 9 × 4 = 41)",
                        "d = 5; 10th term = 50",
                        "d = 4; 10th term = 45",
                        "d = 3; 10th term = 32",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Find step d = 9 - 5 = 4. Then T₁₀ = a + 9d.",
                    'explanation' => "Common difference d = 4. T₁₀ = 5 + 9(4) = 5 + 36 = 41.",
                ];

            case 'geometry_bearings':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "The bearing of Town B from Town A is 065°. What is the back bearing of Town A from Town B?",
                    'options' => [
                        "245° (Since the forward bearing is less than 180°, add 180°: 65° + 180° = 245°)",
                        "065° (Back bearing is identical to forward bearing)",
                        "115° (180° - 65°)",
                        "295° (360° - 65°)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Rule for back bearings: If bearing < 180°, add 180°. If bearing ≥ 180°, subtract 180°.",
                    'explanation' => "When forward bearing < 180°, back bearing = forward bearing + 180° = 65° + 180° = 245°.",
                ];

            case 'geometry_parallel_angles':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Two parallel lines are crossed by a transversal. If one interior angle is 75°, what is the size of its alternate interior angle?",
                    'options' => [
                        "75°, because alternate interior angles between parallel lines are equal",
                        "105°, because alternate angles are supplementary",
                        "15°, because they must sum to 90°",
                        "150°, because alternate angles double in value",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Locate the alternate interior position ('Z' rule).",
                    'explanation' => "Alternate interior angles on parallel lines are equal, so the alternate angle is also 75°.",
                ];

            case 'geometry_pythagoras_trigonometry':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "A right-angled triangle has perpendicular legs of length 6 cm and 8 cm. What is the length of the hypotenuse c?",
                    'options' => [
                        "10 cm, because c = √(6² + 8²) = √(36 + 64) = √100 = 10",
                        "14 cm, because c = 6 + 8 = 14",
                        "48 cm, because c = 6 × 8 = 48",
                        "7 cm, the average of the two legs",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Calculate a² + b², then take the square root.",
                    'explanation' => "6² + 8² = 36 + 64 = 100. √100 = 10 cm.",
                ];

            case 'geometry_angles_classification':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Two angles are complementary, and one of them measures 38°. What is the size of the other angle?",
                    'options' => [
                        "52°, because complementary angles sum to 90° (90° - 38° = 52°)",
                        "142°, because complementary angles sum to 180°",
                        "38°, because complementary angles are always identical",
                        "322°, because complementary angles sum to 360°",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Complementary angles add up to 90°, while supplementary angles add up to 180°.",
                    'explanation' => "Complementary angles sum to 90°. Therefore, 90° - 38° = 52°.",
                ];

            case 'geometry_triangles_polygons':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "A triangle has two angles measuring 55° and 65°. What is the measure of the third angle?",
                    'options' => [
                        "60°, since 180° - (55° + 65°) = 180° - 120° = 60°",
                        "120°, the sum of the two given angles",
                        "55°, because all triangles require matching angle pairs",
                        "80°, the remaining angle to reach 200°",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Subtract the sum of the two known angles from 180°.",
                    'explanation' => "The interior angles of a triangle sum to 180°. 180° - (55° + 65°) = 180° - 120° = 60°.",
                ];

            case 'geometry_constructions':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "When bisecting an angle of 120° using a compass and straightedge, what is the measure of each resulting angle?",
                    'options' => [
                        "60°, because bisecting means dividing an angle into two exactly equal halves",
                        "30°, because an angle is divided into four parts",
                        "90°, because bisections always produce right angles",
                        "120°, because the angle measure cannot change",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Bisection divides an angle exactly by 2.",
                    'explanation' => "120° ÷ 2 = 60°.",
                ];

            case 'geometry_vectors':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "If vector u = (3, 4)ᵀ and vector v = (2, -1)ᵀ, what is the resulting column vector of u + v?",
                    'options' => [
                        "(5, 3)ᵀ, by adding top components (3 + 2) and bottom components (4 + (-1))",
                        "(1, 5)ᵀ, by subtracting components",
                        "(6, -4)ᵀ, by multiplying components",
                        "8, by summing all numbers into a single scalar",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Add corresponding horizontal components together, and vertical components together.",
                    'explanation' => "Column vector addition is done component-wise: top is 3 + 2 = 5, bottom is 4 + (-1) = 3.",
                ];

            case 'geometry_transformations':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "A point P(2, 5) is translated by vector (3, -2)ᵀ. What are the coordinates of the image P'?",
                    'options' => [
                        "(5, 3), because 2 + 3 = 5 and 5 + (-2) = 3",
                        "(-1, 7), subtracting components instead",
                        "(6, -10), multiplying coordinates",
                        "(2, 5), points remain unchanged under translation",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Add the translation vector components to the original coordinates.",
                    'explanation' => "P' = (2 + 3, 5 + (-2)) = (5, 3).",
                ];

            case 'data_central_tendency':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Find the median of the following test scores: 5, 8, 3, 9, 7.",
                    'options' => [
                        "7 (order the numbers: 3, 5, 7, 8, 9; the middle number is 7)",
                        "3 (the lowest number in the set)",
                        "6.4 (the sum divided by 5)",
                        "8 (the number in the middle of the unsorted list)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Always sort numbers from least to greatest before finding the middle value.",
                    'explanation' => "Arranging in ascending order gives 3, 5, 7, 8, 9. The middle (3rd) value is 7.",
                ];

            case 'data_collection_charts':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "In a grouped frequency table with class intervals 10–19, 20–29, 30–39, what is the class width of each interval?",
                    'options' => [
                        "10 (each interval spans 10 integer values)",
                        "9 (subtracting 19 - 10 without including boundaries)",
                        "19 (the upper limit)",
                        "30 (the number of intervals multiplied by 10)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Count the integers in 10 through 19: 10, 11, ..., 19 (10 values).",
                    'explanation' => "The class width is 10 (from 9.5 to 19.5 class boundaries).",
                ];

            case 'data_probability':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "A standard 6-sided die is rolled once. What is the probability of rolling an even number (2, 4, or 6)?",
                    'options' => [
                        "1/2 or 50% (3 favourable outcomes out of 6 possible: 3/6 = 1/2)",
                        "1/6 or 16.7%",
                        "2/3 or 66.7%",
                        "3/1 or 300%",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Divide the count of even faces by the total number of faces.",
                    'explanation' => "There are 3 even numbers out of 6 possible faces: P = 3/6 = 1/2.",
                ];

            case 'computing_hardware':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Which of the following consists strictly of input devices?",
                    'options' => [
                        "Keyboard, mouse, microphone, and barcode scanner",
                        "Monitor, printer, and speakers",
                        "Hard drive, RAM, and motherboard",
                        "Projector, headphones, and CPU",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Input devices send data into the computer.",
                    'explanation' => "Keyboards, mice, microphones, and scanners all enter data into the computer system.",
                ];

            case 'computing_safety':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Which password practice provides the highest level of account protection?",
                    'options' => [
                        "Using a long passphrase combining uppercase and lowercase letters, numbers, and symbols",
                        "Using your birthday so you never forget it",
                        "Sharing your login details with friends for convenience",
                        "Using the same simple 4-digit PIN across all websites",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Complexity and length protect against brute-force attacks.",
                    'explanation' => "Complex passphrases with varied character sets resist automated cracking and dictionary attacks.",
                ];

            case 'science_energy':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "A ball is released from rest from the top of a ramp. What energy transformation occurs as it rolls down?",
                    'options' => [
                        "Gravitational potential energy transforms into kinetic energy",
                        "Kinetic energy transforms into nuclear energy",
                        "Chemical energy transforms into electrical energy",
                        "Energy is completely destroyed as speed increases",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Height gives potential energy; motion gives kinetic energy.",
                    'explanation' => "At the top, the ball has maximum potential energy, which converts to kinetic energy as it accelerates downward.",
                ];

            case 'science_cells':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Which of the following cellular structures is found in plant cells but NOT in animal cells?",
                    'options' => [
                        "A rigid cellulose cell wall and large central chloroplasts",
                        "A cell membrane and cytoplasm",
                        "A nucleus containing DNA",
                        "Mitochondria for cellular respiration",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Plant cells have rigid structures for support and green pigments for photosynthesis.",
                    'explanation' => "Plant cells possess a rigid cellulose cell wall and chloroplasts for photosynthesis, whereas animal cells have only a flexible cell membrane.",
                ];

            case 'science_matter':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What phase change takes place when liquid water is heated to its boiling point and turns into steam?",
                    'options' => [
                        "Evaporation / Vaporization (particles gain sufficient kinetic energy to overcome intermolecular attraction)",
                        "Condensation (particles lose energy and pack closer together)",
                        "Freezing / Solidification into ice",
                        "Sublimation directly from solid ice to gas",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Heating increases particle speed until liquid particles escape into the gas phase.",
                    'explanation' => "Boiling is the rapid vaporization of a liquid when heated to its boiling point, turning liquid water into steam.",
                ];

            case 'english_nouns':
            case 'english_grammar':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "In the sentence: \"A pride of lions rested under the baobab tree\", what type of noun is \"pride\"?",
                    'options' => [
                        "Collective noun (names a group of animals as a single unit)",
                        "Proper noun (names a specific person)",
                        "Abstract noun (names an intangible concept)",
                        "Action verb (describes physical movement)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "A collective noun refers to a collection or group of individuals.",
                    'explanation' => "\"Pride\" refers to a group of lions treated as a single unit, making it a collective noun.",
                ];

            case 'english_concord':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Which sentence demonstrates correct subject-verb concord (agreement)?",
                    'options' => [
                        "\"The basket of ripe mangoes is on the dining table\" (singular subject 'basket' takes singular verb 'is')",
                        "\"The basket of ripe mangoes are on the dining table\"",
                        "\"The players on the soccer team was celebrating\"",
                        "\"Neither the teacher nor the students was present\"",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Ignore prepositional phrases like 'of ripe mangoes' and focus on the main head noun 'basket'.",
                    'explanation' => "The subject is the singular noun 'basket', so it takes the singular verb 'is' despite the plural noun 'mangoes' in the prepositional phrase.",
                ];

            case 'english_reading':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "In a standard informational paragraph, where is the topic sentence (main idea) most commonly placed?",
                    'options' => [
                        "At or near the beginning of the paragraph to introduce the central idea immediately",
                        "Inside a footnote at the bottom of the page",
                        "Only at the end of the entire book",
                        "Topic sentences are never written directly in paragraphs",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Authors usually place the main idea first to orient the reader before giving evidence.",
                    'explanation' => "Writers usually present the topic sentence at the start of a paragraph so readers grasp the central premise before reading supporting arguments.",
                ];

            default:
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "When solving problems or analyzing questions regarding \"{$cleanTitle}\", which approach is recommended?",
                    'options' => [
                        "Follow step-by-step analytical procedures and verify your findings against core rules",
                        "Skip the initial conditions and guess the conclusion",
                        "Assume every case produces the same fixed result",
                        "Ignore units, definitions, and context",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Review the core rules section above.",
                    'explanation' => "Systematic step-by-step verification ensures accuracy and adherence to core subject principles.",
                ];
        }
    }

    /**
     * Generate dynamic Challenge 3 (Concept Mastery & Synthesis) based on domain.
     */
    protected function generateChallenge3(string $domain, string $cleanTitle, array $takeaways = []): array
    {
        switch ($domain) {
            case 'number_rounding_decimals':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What happens when rounding 2.96 to the nearest tenth?",
                    'options' => [
                        "It becomes 3.0 (the hundredths digit 6 rounds the 9 tenths up to 10 tenths, carrying 1 into the units place)",
                        "It becomes 2.9 (ignoring the 6)",
                        "It becomes 2.10 (writing 10 in the tenths position)",
                        "It cannot be rounded because decimals cannot end in zero",
                    ],
                    'correct_answer' => 0,
                    'hint' => "When 9 rounds up by 1, it regroups (carries) into the units position.",
                    'explanation' => "Rounding 9 tenths up gives 10 tenths (1 unit), changing 2.9 to 3.0.",
                ];

            case 'number_rounding_whole':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is 99,600 rounded to the nearest thousand?",
                    'options' => [
                        "100,000 (the hundreds digit 6 rounds the 9 thousands up, carrying into ten-thousands)",
                        "99,000 (rounding down)",
                        "90,000 (rounding to the nearest ten-thousand)",
                        "99,600 (leaving it unchanged)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Inspect the hundreds digit (6 ≥ 5). 99 thousands + 1 thousand = 100 thousands.",
                    'explanation' => "Since 6 ≥ 5, 99,600 rounds up to 100,000.",
                ];

            case 'number_significant_figures':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is the standard scientific form (A × 10ⁿ) of 3,450?",
                    'options' => [
                        "3.45 × 10³ (where 1 ≤ A < 10 and n is an integer)",
                        "34.5 × 10² (A is not between 1 and 10)",
                        "0.345 × 10⁴ (A is less than 1)",
                        "3.45 × 10⁻³ (negative power represents small decimals)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Place the decimal after the first non-zero digit: 3.45, then count 3 jumps to 3,450.",
                    'explanation' => "3,450 = 3.45 × 1,000 = 3.45 × 10³.",
                ];

            case 'geometry_bearings':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "A plane flies on a bearing of 240° from Airport P to Airport Q. What bearing must the pilot fly on the return journey from Q to P?",
                    'options' => [
                        "060° (Since 240° ≥ 180°, subtract 180°: 240° - 180° = 060°)",
                        "240° (Fly on the exact same bearing)",
                        "120° (360° - 240°)",
                        "420° (240° + 180°)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "For bearings greater than 180°, subtract 180° to find the back bearing.",
                    'explanation' => "The back bearing is 240° - 180° = 060°.",
                ];

            case 'geometry_parallel_angles':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Two parallel lines are crossed by a transversal. If one interior angle is 110°, what is the measure of the co-interior angle on the same side?",
                    'options' => [
                        "70°, because co-interior angles inside parallel lines are supplementary (180° - 110° = 70°)",
                        "110°, because co-interior angles are always equal",
                        "20°, because they must sum to 90°",
                        "250°, because they sum to 360°",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Co-interior ('C' shape) angles sum to 180°.",
                    'explanation' => "Co-interior angles are supplementary: 180° - 110° = 70°.",
                ];

            case 'algebra_linear_relations_graphs':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is the gradient m and y-intercept c for the straight line with equation y = -3x + 7?",
                    'options' => [
                        "Gradient m = -3, and y-intercept c = 7",
                        "Gradient m = 3, and y-intercept c = -7",
                        "Gradient m = 7, and y-intercept c = -3",
                        "Gradient m = 1, and y-intercept c = 4",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Compare directly with y = mx + c.",
                    'explanation' => "In y = -3x + 7, m = -3 and c = 7.",
                ];

            case 'number_fractions':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Evaluate: 2/3 + 1/4. What is the simplified result?",
                    'options' => [
                        "11/12 (Common denominator is 12: 8/12 + 3/12 = 11/12)",
                        "3/7 (Adding numerators and denominators directly)",
                        "2/12",
                        "1",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Convert both fractions to twelfths (LCM of 3 and 4).",
                    'explanation' => "2/3 = 8/12 and 1/4 = 3/12. Their sum is 8/12 + 3/12 = 11/12.",
                ];

            case 'number_percentages':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "An article priced at GH₵200 is offered at a 15% discount. What is the final sale price?",
                    'options' => [
                        "GH₵170 (Discount = 0.15 × 200 = GH₵30; Sale price = 200 - 30 = GH₵170)",
                        "GH₵185",
                        "GH₵150",
                        "GH₵30",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Calculate 15% of 200, then subtract that discount from the original price.",
                    'explanation' => "15% of GH₵200 is GH₵30. The sale price is GH₵200 - GH₵30 = GH₵170.",
                ];

            case 'number_ratios_proportions':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "If 5 exercise books cost GH₵35, how much will 8 books cost at the same unit rate?",
                    'options' => [
                        "GH₵56 (Unit rate = 35 / 5 = GH₵7 per book; 8 books = 8 × 7 = GH₵56)",
                        "GH₵40",
                        "GH₵48",
                        "GH₵70",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Find the cost of 1 book first, then multiply by 8.",
                    'explanation' => "1 book costs 35 / 5 = GH₵7. Therefore, 8 books cost 8 × 7 = GH₵56.",
                ];

            case 'science_cells':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is the primary function of the cell nucleus in plant and animal cells?",
                    'options' => [
                        "It acts as the control center of the cell, housing DNA and regulating genetic activity",
                        "It provides green coloration for photosynthesis",
                        "It acts as a tough outer wall preventing water entry",
                        "It stores cellular digestive waste permanently",
                    ],
                    'correct_answer' => 0,
                    'hint' => "The nucleus contains chromosomes that govern cellular functions and replication.",
                    'explanation' => "The nucleus is the command center of the cell containing hereditary genetic instructions.",
                ];

            case 'science_matter':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why does the temperature of a pure substance stay constant while it is melting or boiling?",
                    'options' => [
                        "The absorbed thermal energy is used to break intermolecular bonds (latent heat) rather than raising temperature",
                        "Because heat stops flowing into the substance during phase changes",
                        "Because thermometers do not function when substances change state",
                        "Because atoms lose their mass during phase changes",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Consider latent heat of fusion or vaporization.",
                    'explanation' => "During phase changes, energy breaks intermolecular bonds instead of increasing particle kinetic energy, keeping temperature steady.",
                ];

            case 'science_energy':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "When a hydroelectric dam produces electricity, what is the primary sequence of energy transformations?",
                    'options' => [
                        "Potential energy (stored water) → Kinetic energy (flowing water) → Mechanical energy (turbines) → Electrical energy (generators)",
                        "Nuclear energy → Thermal energy → Light energy",
                        "Chemical energy → Sound energy → Kinetic energy",
                        "Electrical energy → Solar energy → Potential energy",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Follow the water from elevated reservoir through falling water to spinning turbine and generator.",
                    'explanation' => "High reservoir water holds potential energy, turns to kinetic energy as it falls, turns turbines mechanically, and generates electricity.",
                ];

            case 'english_concord':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "When two singular subjects are joined by 'and' (such as 'Kwame and Akua'), which verb form must be used?",
                    'options' => [
                        "A plural verb (e.g., 'Kwame and Akua are studying in the library')",
                        "A singular verb (e.g., 'Kwame and Akua is studying in the library')",
                        "A past participle with no auxiliary verb",
                        "No verb can connect two compound subjects",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Two subjects joined by 'and' form a compound plural subject.",
                    'explanation' => "Compound subjects connected with 'and' refer to multiple entities and therefore require a plural verb.",
                ];

            case 'english_reading':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "How can a student effectively differentiate between the central idea and supporting details in a comprehension passage?",
                    'options' => [
                        "The central idea is the primary point being argued, while supporting details provide facts, quotes, examples, and proof",
                        "Supporting details are always written in bold text",
                        "The central idea always contains mathematical numbers",
                        "There is no difference between a central idea and supporting examples",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Ask yourself: 'What is the main takeaway?' vs 'What evidence is used to prove it?'",
                    'explanation' => "The central idea represents the overarching premise of the text, while details furnish evidence and clarification.",
                ];

            case 'computing_hardware':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "In computer system design, which component is considered the 'brain' that executes program instructions and performs arithmetic calculations?",
                    'options' => [
                        "The Central Processing Unit (CPU)",
                        "The computer monitor",
                        "The keyboard",
                        "The optical mouse",
                    ],
                    'correct_answer' => 0,
                    'hint' => "The CPU (Central Processing Unit) carries out all instruction decoding and calculations.",
                    'explanation' => "The Central Processing Unit (CPU) contains the ALU and control unit, executing system instructions.",
                ];

            case 'computing_safety':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is the safest action when you receive an unexpected email with a link asking you to 'verify your account password immediately'?",
                    'options' => [
                        "Do NOT click the link; report the message as a phishing scam and verify through official channels",
                        "Click the link immediately and submit your current password",
                        "Forward the email and password to all your friends",
                        "Reply to the sender with your credit card or mobile money PIN",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Urgent demands for login credentials are the hallmark of phishing scams.",
                    'explanation' => "Phishing attempts trick victims into surrendering confidential credentials using fake urgency.",
                ];

            default:
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "To achieve full mastery in \"{$cleanTitle}\", which strategy ensures reliable accuracy?",
                    'options' => [
                        "Re-check calculations or reasoning, verify against the core rules, and confirm the result makes logical sense",
                        "Submit answers immediately without re-reading the question",
                        "Assume the first computed answer is always right without verification",
                        "Memorize steps blindly without understanding their underlying reasons",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Think about how experts verify their solutions.",
                    'explanation' => "Mastery comes from double-checking logic and confirming that your solution satisfies the original question.",
                ];
        }
    }

    /**
     * Generate dynamic Quiz Question 1 based on domain.
     */
    protected function generateQuizQuestion1(string $domain, string $cleanTitle): array
    {
        switch ($domain) {
            case 'number_rounding_decimals':
                return [
                    'question' => "When rounding a decimal numeral, which digit tells you whether to round up or keep the target digit unchanged?",
                    'options' => [
                        "The digit immediately to the right of the target place value",
                        "The whole number part",
                        "The very last digit in the number regardless of place",
                        "The first digit after the decimal point",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "The digit directly to the right of the target determines whether to round up (if ≥ 5) or round down (if < 5).",
                ];

            case 'number_rounding_whole':
                return [
                    'question' => "When rounding a whole number to the nearest thousand, which place value do you inspect?",
                    'options' => [
                        "The hundreds place",
                        "The ten-thousands place",
                        "The tens place",
                        "The units place",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "To round to the nearest thousand, inspect the hundreds digit immediately to its right.",
                ];

            case 'number_significant_figures':
                return [
                    'question' => "In standard scientific form A × 10ⁿ, what mathematical condition must number A satisfy?",
                    'options' => [
                        "1 ≤ A < 10",
                        "0 < A ≤ 1",
                        "A can be any number",
                        "10 ≤ A < 100",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "In scientific notation, A must be greater than or equal to 1 and strictly less than 10.",
                ];

            case 'number_powers_of_ten':
                return [
                    'question' => "What happens to the decimal point when dividing any number by 10² (100)?",
                    'options' => [
                        "It moves 2 places to the left",
                        "It moves 2 places to the right",
                        "It moves 10 places to the left",
                        "It stays in the same place",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "Dividing by 10² decreases the magnitude by shifting the decimal 2 positions to the left.",
                ];

            case 'number_indices_surds':
                return [
                    'question' => "According to the laws of indices, what is the value of any non-zero number raised to the power of 0 (a⁰)?",
                    'options' => [
                        "1",
                        "0",
                        "a",
                        "Infinity",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "Any non-zero base raised to the zero power equals 1 (a⁰ = 1).",
                ];

            case 'number_fractions':
                return [
                    'question' => "What is the reciprocal of 3/5 that is used when dividing by 3/5?",
                    'options' => [
                        "5/3",
                        "-3/5",
                        "3/10",
                        "1",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "The reciprocal is obtained by swapping numerator and denominator: 3/5 becomes 5/3.",
                ];

            case 'number_percentages':
                return [
                    'question' => "What is 25% expressed as a simplified fraction?",
                    'options' => [
                        "1/4",
                        "1/2",
                        "2/5",
                        "1/25",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "25% = 25/100 = 1/4.",
                ];

            case 'number_ratios_proportions':
                return [
                    'question' => "In a directly proportional relationship y = kx, what does k represent?",
                    'options' => [
                        "The constant of proportionality (unit rate)",
                        "The y-intercept",
                        "The sum of x and y",
                        "The square root of x",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "In y = kx, k is the constant of proportionality representing the constant ratio y / x.",
                ];

            case 'geometry_bearings':
                return [
                    'question' => "The bearing of Town P from Town Q is 075°. What is the back bearing of Town Q from Town P?",
                    'options' => [
                        "255° (075° + 180° = 255°)",
                        "075°",
                        "105°",
                        "285°",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "Because 075° is less than 180°, add 180° to obtain the back bearing: 075° + 180° = 255°.",
                ];

            case 'geometry_parallel_angles':
                return [
                    'question' => "What is the key geometric rule regarding alternate and corresponding angles on parallel lines?",
                    'options' => [
                        "Alternate interior angles are equal, and corresponding angles are equal",
                        "Alternate angles always sum to 90°",
                        "Corresponding angles only exist on perpendicular lines",
                        "Alternate angles are always twice the size of corresponding angles",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "When parallel lines are intersected by a transversal, alternate interior angles ('Z' angles) and corresponding angles ('F' angles) are equal.",
                ];

            case 'geometry_triangles_polygons':
                return [
                    'question' => "What is the sum of the interior angles in any triangle?",
                    'options' => [
                        "180°",
                        "360°",
                        "90°",
                        "270°",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "The three interior angles of any planar triangle always sum to exactly 180°.",
                ];

            case 'geometry_angles_classification':
                return [
                    'question' => "Which of the following correctly describes an obtuse angle?",
                    'options' => [
                        "An angle greater than 90° but less than 180°",
                        "An angle exactly equal to 90°",
                        "An angle less than 90°",
                        "An angle greater than 180°",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "An obtuse angle has a measure greater than 90° and less than 180°.",
                ];

            case 'geometry_pythagoras_trigonometry':
                return [
                    'question' => "In Pythagoras' theorem (a² + b² = c²), what does c represent?",
                    'options' => [
                        "The hypotenuse (the longest side opposite the right angle)",
                        "The shortest leg of the triangle",
                        "The perimeter of the triangle",
                        "The vertical height of an equilateral triangle",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "c always represents the hypotenuse, which is the longest side directly opposite the 90° right angle.",
                ];

            case 'algebra_linear_relations_graphs':
                return [
                    'question' => "In the linear equation y = mx + c, what does c represent?",
                    'options' => [
                        "The y-intercept (the point where the line crosses the y-axis)",
                        "The gradient of the line",
                        "The x-intercept",
                        "The length of the line segment",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "c is the y-intercept, where x = 0 and the line intersects the vertical y-axis.",
                ];

            case 'algebra_inequalities':
                return [
                    'question' => "When solving an inequality, what must you do if you divide or multiply both sides by a negative number?",
                    'options' => [
                        "Reverse (flip) the direction of the inequality sign",
                        "Change the inequality sign into an equal sign",
                        "Leave the inequality sign unchanged",
                        "Multiply the opposite side by zero",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "Multiplying or dividing an inequality by a negative number reverses the order of the quantities, requiring the inequality sign to be flipped.",
                ];

            case 'data_central_tendency':
                return [
                    'question' => "Which statistical measure represents the most frequently occurring value in a dataset?",
                    'options' => [
                        "Mode",
                        "Mean",
                        "Median",
                        "Range",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "The mode is the value or item that appears most frequently in a dataset.",
                ];

            case 'data_probability':
                return [
                    'question' => "If an event is guaranteed (certain) to happen, what is its theoretical probability?",
                    'options' => [
                        "1 (or 100%)",
                        "0",
                        "0.5",
                        "Infinity",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "A completely certain event has a probability of exactly 1.",
                ];

            case 'science_cells':
                return [
                    'question' => "Which cellular organelle is responsible for cellular respiration and energy (ATP) production?",
                    'options' => [
                        "Mitochondrion (Mitochondria)",
                        "Cell wall",
                        "Chloroplast",
                        "Vacuole",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "Mitochondria are the powerhouses of eukaryotic cells, generating ATP via cellular respiration.",
                ];

            case 'science_matter':
                return [
                    'question' => "What happens to the average kinetic energy of particles in matter as temperature increases?",
                    'options' => [
                        "The average kinetic energy increases, causing particles to vibrate or move faster",
                        "The kinetic energy decreases to zero",
                        "The particles immediately stop moving",
                        "The particles lose mass and turn into electrons",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "Temperature is directly proportional to the average kinetic energy of particles in a substance.",
                ];

            case 'science_energy':
                return [
                    'question' => "Which form of mechanical energy is stored in an object due to its elevated position in a gravitational field?",
                    'options' => [
                        "Gravitational potential energy",
                        "Kinetic energy",
                        "Sound energy",
                        "Thermal radiation",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "Gravitational potential energy (PE = mgh) is energy stored by virtue of an object's position above ground.",
                ];

            case 'computing_hardware':
                return [
                    'question' => "Which of the following devices is strictly classified as an output peripheral in a computer system?",
                    'options' => [
                        "Monitor / Display screen",
                        "Keyboard",
                        "Flatbed optical scanner",
                        "Microphone",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "Monitors display processed output data visually to users.",
                ];

            case 'computing_safety':
                return [
                    'question' => "What is malicious software designed to infiltrate, damage, or steal data from computers called?",
                    'options' => [
                        "Malware",
                        "Hardware",
                        "Freeware",
                        "Firmware",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "Malware (malicious software) encompasses viruses, spyware, trojans, and ransomware.",
                ];

            case 'english_nouns':
                return [
                    'question' => "Which type of noun denotes non-physical concepts, feelings, qualities, or ideas (e.g. courage, wisdom, justice)?",
                    'options' => [
                        "Abstract noun",
                        "Proper noun",
                        "Concrete noun",
                        "Compound noun",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "Abstract nouns refer to intangible ideas, emotions, or qualities that cannot be experienced with the five senses.",
                ];

            case 'english_concord':
                return [
                    'question' => "Which sentence exhibits correct grammatical subject-verb concord?",
                    'options' => [
                        "\"Every student in the classroom has submitted the term project\"",
                        "\"Every student in the classroom have submitted the term project\"",
                        "\"The herd of cattle are grazing peacefully\"",
                        "\"Each of the five cars are blue\"",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "'Every' takes a singular verb ('has submitted'). Prepositional modifiers do not change this singular requirement.",
                ];

            case 'english_reading':
                return [
                    'question' => "What is the primary role of a topic sentence in an expository paragraph?",
                    'options' => [
                        "To clearly state the main idea that is developed and supported throughout the paragraph",
                        "To introduce unrelated interesting facts",
                        "To provide a complete bibliography of the author's sources",
                        "To define every obscure word in the paragraph",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "A topic sentence synthesizes the central point of the paragraph, guiding subsequent supporting details.",
                ];

            default:
                return [
                    'question' => "What is the primary objective when studying \"{$cleanTitle}\"?",
                    'options' => [
                        "To accurately interpret, model, and apply structured reasoning to solve problems",
                        "To memorize answers without understanding underlying concepts",
                        "To guess answers without checking calculations or rules",
                        "To skip practical examples and exercises",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "The primary goal of \"{$cleanTitle}\" is to accurately understand, model, and apply systematic reasoning to solve problems.",
                ];
        }
    }

    /**
     * Generate dynamic Quiz Question 2 based on domain.
     */
    protected function generateQuizQuestion2(string $domain, string $cleanTitle): array
    {
        $challenge2 = $this->generateChallenge2($domain, $cleanTitle);

        return [
            'question' => $challenge2['prompt'],
            'options' => $challenge2['options'],
            'correct_answer' => $challenge2['correct_answer'],
            'explanation' => $challenge2['explanation'],
        ];
    }
}


