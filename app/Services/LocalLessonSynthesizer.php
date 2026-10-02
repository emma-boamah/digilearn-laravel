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

        $challenge3 = [
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
     * Classify an indicator into a subject/topic domain for content generation.
     */
    protected function detectDomain(CurriculumIndicator $indicator, string $title, string $subject): string
    {
        $sLower = strtolower($subject);
        $context = strtolower(
            $title . ' ' .
            ($indicator->description ?? '') . ' ' .
            ($indicator->subStrand?->title ?? '') . ' ' .
            ($indicator->subStrand?->strand?->title ?? '')
        );

        if (str_contains($sLower, 'ict') || str_contains($sLower, 'comput') || str_contains($sLower, 'technol')) {
            return 'computing_ict';
        }

        if (str_contains($sLower, 'sci') || str_contains($sLower, 'phys') || str_contains($sLower, 'chem') || str_contains($sLower, 'bio')) {
            return 'science';
        }

        if (str_contains($sLower, 'math')) {
            if (str_contains($context, 'alternate') || str_contains($context, 'corresponding') || str_contains($context, 'transversal') || str_contains($context, 'parallel line') || str_contains($context, 'co-interior')) {
                return 'geometry_parallel_angles';
            }
            if (str_contains($context, 'triangle') || str_contains($context, 'polygon') || str_contains($context, 'quadrilateral') || str_contains($context, 'interior angle') || str_contains($context, 'exterior angle')) {
                return 'geometry_triangles';
            }
            if (str_contains($context, 'pythagoras') || str_contains($context, 'hypotenuse') || str_contains($context, 'trigonometr')) {
                return 'geometry_pythagoras_trig';
            }
            if (str_contains($context, 'vector') || str_contains($context, 'bearing')) {
                return 'geometry_vectors_bearings';
            }
            if (str_contains($context, 'angle') || str_contains($context, 'acute') || str_contains($context, 'obtuse') || str_contains($context, 'reflex') || str_contains($context, 'complementary') || str_contains($context, 'supplementary') || str_contains($context, 'protractor')) {
                return 'geometry_angles_measure';
            }
            if (str_contains($context, 'gradient') || str_contains($context, 'y = mx + c') || str_contains($context, 'slope') || str_contains($context, 'straight line') || str_contains($context, 'linear graph')) {
                return 'algebra_gradients_linear';
            }
            if (str_contains($context, 'equation') || str_contains($context, 'inequalit') || str_contains($context, 'variable') || str_contains($context, 'change of subject')) {
                return 'algebra_equations';
            }
            if (str_contains($context, 'factoris') || str_contains($context, 'factoriz') || str_contains($context, 'binomial') || str_contains($context, 'algebraic expression') || str_contains($context, 'expand')) {
                return 'algebra_factorisation';
            }
            if (str_contains($context, 'pattern') || str_contains($context, 'sequence') || str_contains($context, 'nth term') || str_contains($context, 'progression')) {
                return 'algebra_patterns';
            }
            if (str_contains($context, 'mean') || str_contains($context, 'median') || str_contains($context, 'mode') || str_contains($context, 'data') || str_contains($context, 'frequency') || str_contains($context, 'chart') || str_contains($context, 'histogram')) {
                return 'data_statistics';
            }
            if (str_contains($context, 'probability') || str_contains($context, 'chance') || str_contains($context, 'sample space') || str_contains($context, 'likelihood') || str_contains($context, 'die') || str_contains($context, 'coin')) {
                return 'data_probability';
            }
            if (str_contains($context, 'fraction') || str_contains($context, 'ratio') || str_contains($context, 'percent') || str_contains($context, 'proportion') || str_contains($context, 'bodmas') || str_contains($context, 'rate')) {
                return 'number_fractions_ratios';
            }
            if (str_contains($context, 'set') || str_contains($context, 'venn') || str_contains($context, 'subset') || str_contains($context, 'intersection') || str_contains($context, 'universal set')) {
                return 'number_sets';
            }
            if (str_contains($context, 'place value') || str_contains($context, 'compare') || str_contains($context, 'order') || str_contains($context, 'billion') || str_contains($context, 'million') || str_contains($context, 'round') || str_contains($context, 'numeral')) {
                return 'number_place_value';
            }
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

            case 'geometry_triangles':
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

            case 'geometry_angles_measure':
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

            case 'geometry_pythagoras_trig':
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

            case 'geometry_vectors_bearings':
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

            case 'algebra_gradients_linear':
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

            case 'algebra_equations':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why is applying inverse operations equally to both sides essential when solving algebraic equations?",
                    'options' => [
                        "Because an equation represents equal balance; performing operations on only one side destroys equality",
                        "Because variables always turn into negative numbers",
                        "To convert an equation into an inequality",
                        "Because equations cannot be solved without turning them into fractions",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Think of an equation as a perfectly balanced scale.",
                    'explanation' => "Equality is preserved only when the identical mathematical operation is performed on both the left and right sides.",
                ];

            case 'algebra_factorisation':
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

            case 'algebra_patterns':
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

            case 'data_statistics':
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

            case 'number_fractions_ratios':
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
                    'explanation' => "The denominator indicates the size of each slice; fractions can only be directly combined when the slices are identical in size.",
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

            case 'number_place_value':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Why does our base-ten number system group digits into periods of three separated by commas?",
                    'options' => [
                        "To make large numbers easy to read and identify place values (Units, Thousands, Millions, Billions)",
                        "Because numbers cannot exceed three digits without commas",
                        "To convert whole numbers into decimal fractions",
                        "To separate odd numbers from even numbers",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Commas group powers of 1,000 to assist reading large quantities.",
                    'explanation' => "Periods group place values into thousands, millions, and billions, making large quantities structured and readable.",
                ];

            case 'computing_ict':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is the primary role of \"{$cleanTitle}\" in modern computing systems?",
                    'options' => [
                        "To coordinate digital processes, ensure efficient information processing, and provide user reliability",
                        "To bypass operating system safety checks",
                        "To eliminate the need for digital storage media",
                        "To convert electronic computers into analog mechanical calculators",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Consider how computer components work together to process and store data.",
                    'explanation' => "Understanding computer architecture and software principles allows users to operate and troubleshoot systems safely.",
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
            case 'geometry_parallel_angles':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Two parallel lines are crossed by a transversal. If one interior angle is 70°, what is the size of its alternate interior angle?",
                    'options' => [
                        "70°, because alternate interior angles between parallel lines are equal",
                        "110°, because alternate angles are supplementary",
                        "20°, because they must sum to 90°",
                        "140°, because alternate angles double in value",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Locate the alternate interior position ('Z' rule).",
                    'explanation' => "Alternate interior angles on parallel lines are equal, so the alternate angle is also 70°.",
                ];

            case 'geometry_triangles':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "A triangle has two angles measuring 65° and 50°. What is the measure of the third angle?",
                    'options' => [
                        "65°, since 180° - (65° + 50°) = 65°",
                        "115°, the sum of the two given angles",
                        "50°, because all triangles require matching angle pairs",
                        "80°, the remaining angle to reach 200°",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Subtract the sum of the two known angles from 180°.",
                    'explanation' => "The interior angles of a triangle sum to 180°. Therefore: 180° - (65° + 50°) = 180° - 115° = 65°.",
                ];

            case 'geometry_angles_measure':
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

            case 'geometry_pythagoras_trig':
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

            case 'geometry_vectors_bearings':
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

            case 'algebra_gradients_linear':
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

            case 'algebra_equations':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Solve for x in the linear equation: 3x - 5 = 16.",
                    'options' => [
                        "x = 7 (add 5 to both sides to get 3x = 21, then divide by 3)",
                        "x = 3.6 (divide 16 by 3 and subtract 5)",
                        "x = 11 (subtract 5 from 16)",
                        "x = 63 (multiply 21 by 3)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Isolate the term with x by adding 5 first, then divide by the coefficient 3.",
                    'explanation' => "3x - 5 = 16 => 3x = 21 => x = 21 / 3 = 7.",
                ];

            case 'algebra_factorisation':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "What is the completely factorised form of 8x + 12?",
                    'options' => [
                        "4(2x + 3), since 4 is the Highest Common Factor (HCF) of 8 and 12",
                        "2(4x + 6), because 2 is the only common divisor",
                        "20x, by adding the coefficients together",
                        "8(x + 12), by factoring out the first number",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Find the greatest factor that divides both 8 and 12.",
                    'explanation' => "The HCF of 8 and 12 is 4. Factoring 4 out gives 4(2x + 3).",
                ];

            case 'algebra_patterns':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "For the arithmetic sequence 4, 7, 10, 13, ..., what is the common difference d and the 10th term?",
                    'options' => [
                        "d = 3; 10th term = 31 (T₁₀ = 4 + 9 × 3 = 31)",
                        "d = 4; 10th term = 40",
                        "d = 3; 10th term = 34",
                        "d = 2; 10th term = 26",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Find the step size: 7 - 4 = 3. Then calculate 4 + (10 - 1) × 3.",
                    'explanation' => "The common difference d = 3. The 10th term is a + 9d = 4 + 9(3) = 4 + 27 = 31.",
                ];

            case 'data_statistics':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Find the median of the following set of numbers: 5, 8, 3, 9, 7.",
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

            case 'number_fractions_ratios':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Share GH₵120 between Kofi and Ama in the ratio 2 : 3. How much does Ama receive?",
                    'options' => [
                        "GH₵72 (Total parts = 5; 1 part = 120/5 = 24; Ama's share = 3 × 24 = 72)",
                        "GH₵48 (Kofi's share)",
                        "GH₵60 (Equal split)",
                        "GH₵40 (120 divided by 3)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Add the ratio parts (2 + 3 = 5), find the value of 1 part, then multiply by 3.",
                    'explanation' => "Total parts = 2 + 3 = 5. One part = 120 / 5 = 24. Ama's share = 3 × 24 = GH₵72.",
                ];

            case 'number_sets':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "If Set A = {2, 3, 5, 7} and Set B = {1, 3, 5, 9}, what is A ∩ B?",
                    'options' => [
                        "{3, 5} (the elements appearing in both Set A and Set B)",
                        "{1, 2, 3, 5, 7, 9} (the union of the sets)",
                        "{2, 7} (elements exclusive to Set A)",
                        "{} (empty set)",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Identify which numbers appear in both lists.",
                    'explanation' => "The numbers 3 and 5 are present in both sets, so A ∩ B = {3, 5}.",
                ];

            case 'number_place_value':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "Which number is greater: 4,582,100 or 4,579,990?",
                    'options' => [
                        "4,582,100, because comparing the ten-thousands digit shows 8 > 7",
                        "4,579,990, because it has three 9s which are larger digits",
                        "They are equal because both start with 4,5...",
                        "4,579,990, because 990 is greater than 100",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Compare corresponding digits starting from the highest place value on the left.",
                    'explanation' => "Comparing from left to right: millions (4) and hundred-thousands (5) are equal, but at ten-thousands 8 > 7, so 4,582,100 is greater.",
                ];

            case 'computing_ict':
                return [
                    'type' => 'multiple-choice',
                    'prompt' => "When implementing or configuring procedures related to \"{$cleanTitle}\", what is best practice?",
                    'options' => [
                        "Follow verified operational workflows and verify system feedback before proceeding",
                        "Force-quit background processes without saving changes",
                        "Ignore system compatibility requirements and error dialogs",
                        "Disable network security protocols during file transfers",
                    ],
                    'correct_answer' => 0,
                    'hint' => "Structured procedures protect system stability and data integrity.",
                    'explanation' => "Structured execution and observing system prompts prevents data loss and software malfunction.",
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
     * Generate dynamic Quiz Question 1 based on domain.
     */
    protected function generateQuizQuestion1(string $domain, string $cleanTitle): array
    {
        switch ($domain) {
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

            case 'geometry_triangles':
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

            case 'geometry_angles_measure':
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

            case 'geometry_pythagoras_trig':
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

            case 'algebra_gradients_linear':
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

            case 'algebra_equations':
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

            case 'data_statistics':
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

            case 'computing_ict':
                return [
                    'question' => "In computer technology, what is the main purpose of studying \"{$cleanTitle}\"?",
                    'options' => [
                        "To understand how hardware, software, and computational processes work together reliably",
                        "To bypass standard system security procedures",
                        "To use electronic devices without adhering to digital safety rules",
                        "To replace software applications with manual mechanical tools",
                    ],
                    'correct_answer' => 0,
                    'explanation' => "Understanding \"{$cleanTitle}\" builds foundational knowledge of digital architecture, processes, and safe technology utilization.",
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


