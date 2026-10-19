<?php

namespace App\Services;

use App\Models\TextbookSection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class PedagogicalCourseSchemeService
{
    protected string $apiKey;
    protected string $model;

    public function __construct(
        protected LocalLessonSynthesizer $localSynthesizer
    ) {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $this->model = config('services.gemini.model', 'gemini-flash-latest');
    }

    /**
     * List of available pedagogical schemes with metadata.
     */
    public const AVAILABLE_SCHEMES = [
        'math_first_principles' => [
            'name' => 'Mathematics (First-Principles & Operator Logic)',
            'description' => '1. Etymology & Origin → 2. Daily Life Usage → 3. Formula & Operator Logic (Why × and not +) → 4. Worked Scenarios & Quizzes',
            'subject_match' => ['math', 'mathematics', 'algebra', 'geometry', 'arithmetic', 'calculus'],
        ],
        'science_inquiry' => [
            'name' => 'Science (Phenomenon & Mechanism)',
            'description' => '1. Observable Phenomenon → 2. Scientific Terminology & Etymology → 3. Causal Mechanism & Law → 4. Practical Experiments & Quizzes',
            'subject_match' => ['science', 'physics', 'chemistry', 'biology', 'integrated science'],
        ],
        'social_studies' => [
            'name' => 'Social Studies (Origins, Systems & Society)',
            'description' => '1. System Origin & Meaning → 2. Modern Everyday Relevance → 3. Historical Drivers & Human Motives → 4. Case Studies & Quizzes',
            'subject_match' => ['social studies', 'history', 'geography', 'citizenship', 'government'],
        ],
        'language_arts' => [
            'name' => 'Language & Literacy (Etymology & Mechanics)',
            'description' => '1. Word Roots & Origins → 2. Real Conversation Context → 3. Grammatical Mechanics & Why Rules Exist → 4. Application Drills & Quizzes',
            'subject_match' => ['english', 'language', 'literacy', 'reading', 'french', 'ghanaian language'],
        ],
        'general_academic' => [
            'name' => 'General Academic (First-Principles Foundation)',
            'description' => '1. Core Definition & Origin → 2. Real-World Applications → 3. Mechanical Principles & Deep Dive → 4. Checkpoint Quizzes',
            'subject_match' => [],
        ],
    ];

    public static function getAvailableSchemes(): array
    {
        return self::AVAILABLE_SCHEMES;
    }

    /**
     * Resolve default scheme based on subject name.
     */
    public static function getDefaultSchemeForSubject(string $subjectName): string
    {
        $clean = strtolower(trim($subjectName));
        foreach (self::getAvailableSchemes() as $key => $data) {
            foreach ($data['subject_match'] as $keyword) {
                if (str_contains($clean, $keyword)) {
                    return $key;
                }
            }
        }
        return 'general_academic';
    }

    /**
     * Generate course reading text and checkpoint quiz according to the selected scheme.
     */
    public function generateContent(
        string $topicTitle,
        string $subjectName,
        string $gradeLevel = 'JHS',
        ?string $contextText = null,
        string $scheme = 'math_first_principles'
    ): array {
        $prompt = $this->buildPrompt($topicTitle, $subjectName, $gradeLevel, $contextText, $scheme);

        if (!empty($this->apiKey)) {
            try {
                $rawResponse = $this->queryGemini($prompt);
                if (!empty($rawResponse)) {
                    return $this->parseResponse($rawResponse, $topicTitle, $subjectName, $scheme, 'ai');
                }
            } catch (\Throwable $e) {
                Log::warning("PedagogicalCourseSchemeService AI generation failed ({$e->getMessage()}). Using deterministic synthesizer fallback.");
            }
        }

        // Fallback: Deterministic local synthesis
        return $this->synthesizeLocalFallback($topicTitle, $subjectName, $gradeLevel, $scheme);
    }

    /**
     * Build the structured pedagogical prompt based on scheme.
     */
    public function buildPrompt(
        string $topicTitle,
        string $subjectName,
        string $gradeLevel,
        ?string $contextText,
        string $scheme
    ): string {
        $contextSnippet = !empty($contextText)
            ? "\nReference context from textbook/syllabus:\n" . mb_substr($contextText, 0, 4000) . "\n"
            : '';

        $schemeInstructions = match ($scheme) {
            'math_first_principles' => <<<'SCHEME'
You MUST strictly structure this Mathematics lesson into these 4 essential pedagogical stages:

### 1. The Origin and Meaning of the Words (Etymology & Core Concept)
- Trace the literal origin/etymology of the key mathematical words in this topic (e.g., Greek/Latin or historical roots).
- Explain what the concept fundamentally is in plain, intuitive English before introducing any symbols or notation.

### 2. Daily Life Usage & Real-World Purpose
- Explain why human society developed this concept.
- Give a concrete, vivid everyday scenario showing how it is used in modern daily life (e.g., trade, counting livestock/money, sports scoring, architecture, navigation).
- Highlight why confusing related concepts causes real-world errors (e.g., why fencing a yard uses perimeter, but painting a wall uses area).

### 3. Core Mechanism, Patterns & Logic (Why This Rule or System?)
- **ADAPT TO THE NATURE OF THE TOPIC**:
  - **A. For Elementary / Conceptual Topics** (e.g. "Counting and writing numbers", "Place values", "Number bases", "Shapes", "Sets", "Tally charts", "Classifying"):
    - DO NOT force artificial algebraic formulas!
    - Teach through **concrete physical correspondence** using object countables and emojis (🍎, ⭐, 📦, 🔵, 🟡, base-10 blocks).
    - Provide a structured breakdown table mapping Quantity (with emojis) -> Numeral -> Number Name -> How to write the numeral / stroke guide.
    - Explain the systematic patterns: The 10 base digits (0 to 9), how teen numbers (11-19) represent 1 ten and n units ($10 + n$), the repeating tens pattern (20, 30, 40...), and place value columns (Thousands, Hundreds, Tens, Ones).
    - Explain punctuation and grammar rules for writing numbers in words (e.g. hyphenating two-word numbers 21-99).
  - **B. For Formula / Computational Topics** (e.g. Area, Perimeter, Linear Equations, Speed, Percentages):
    - State the formula clearly using standard symbols ($L \times W$, etc.).
    - Explain the "Operator Logic": explicitly WHY the formula uses its specific mathematical operators (e.g. why Length × Breadth for 2-dimensional surface tiling, rather than addition).
- Show visual intuition so the student genuinely understands WHY the system or calculation works.

### 4. Step-by-Step Worked Problems & Checkpoint Quiz
- Walk through 2 clear, realistic worked examples with step-by-step solutions and explanations.
- Provide 2 to 3 exam pro-tips or common misconceptions students make on this topic.
SCHEME,

            'science_inquiry' => <<<'SCHEME'
You MUST strictly structure this Science lesson into these 4 essential pedagogical stages:

### 1. The Observable Phenomenon in Daily Life
- Open with a tangible, relatable observation students see in daily life (e.g. sweating to cool down, leaves turning to light).
- Hook the student's curiosity before introducing abstract terminology.

### 2. Scientific Terminology & Word Origins
- Break down the Greek/Latin roots of the scientific terms (e.g., *Photosynthesis* = *photo* [light] + *synthesis* [putting together]).
- Define the scientific concept in clear, crystal-clear language.

### 3. The Underlying Mechanism: How & Why It Works
- Explain the causal mechanism step-by-step: what happens at the physical, chemical, or biological level.
- Explain the cause-and-effect relationship and the scientific law governing it.

### 4. Real-World Applications, Experiments & Checkpoint Quiz
- Describe a simple observation or hands-on demonstration.
- Provide step-by-step real-world examples and common misconceptions.
SCHEME,

            'social_studies' => <<<'SCHEME'
You MUST strictly structure this Social Studies lesson into these 4 essential pedagogical stages:

### 1. Concept Origin & Human Foundations
- Explain the historical origin and meaning of the term/institution (e.g., *Constitution*, *Democracy*, *Taxation*).
- What core human need or societal problem prompted people to establish this system?

### 2. Everyday Life Relevance Today
- Explain how this concept touches the student's daily life right now in Ghana / Africa / the world.
- Use a vivid, concrete modern scenario illustrating its real impact.

### 3. Underlying Drivers, Motives & Systems
- Break down the mechanical workings: how the system operates, who the key actors are, and the cause-and-effect chain.
- Analyze why decisions are made, avoiding simplistic summaries.

### 4. Case Scenarios & Critical Thinking Checkpoint
- Present 1-2 realistic dilemmas or case studies with guided analysis.
- Provide common pitfalls and exam tips.
SCHEME,

            'language_arts' => <<<'SCHEME'
You MUST strictly structure this Language lesson into these 4 essential pedagogical stages:

### 1. Linguistic Roots & Meaning
- Break down word roots, prefixes, or the linguistic foundation of the topic.
- Define what the grammatical or communicative concept actually accomplishes.

### 2. Everyday Context & Communication
- Show where students hear or see this in daily conversation, literature, speeches, or digital media.
- Contrast strong, natural usage against clumsy or broken usage.

### 3. The Rules & Why They Exist
- Explain the mechanics of the rule and WHY language developed this convention (clarity, rhythm, avoiding ambiguity).
- Walk through common grammatical errors and why they happen.

### 4. Practical Application Drills & Checkpoint Quiz
- 2-3 guided sentence/passage transformations with explanations.
- Exam tips and common traps.
SCHEME,

            default => <<<'SCHEME'
You MUST strictly structure this lesson into 4 first-principles stages:
### 1. Origin & Fundamental Meaning
### 2. Real-World Purpose & Daily Usage
### 3. Core Mechanism & Step-by-Step Logic
### 4. Practical Worked Examples & Checkpoint Quiz
SCHEME,
        };

        $rules = <<<'RULES'
### Typography & Visual Presentation Rules:
- Write plain numbers directly without dollar signs (write "100", "50", "1,000", not "$100$").
- Reserve LaTeX $...$ or $$...$$ exclusively for actual formulas and algebraic expressions.
- Format mathematical multiplication as \times or &times;, and division as \div or &divide;.
- Write in an encouraging, engaging, and deeply informative tone speaking directly to the student.

### VISUAL DIAGRAMS, GRAPHS & COMPARISON TABLES MANDATE:
Learning is dramatically enhanced by visual and tabulated representations. You MUST include in the lesson:
1. **At least ONE colored visual diagram, physical emoji representation, or graph**:
   - Write clean, self-contained, responsive inline SVG code (`<svg viewBox="0 0 450 220" class="mx-auto my-4 max-w-md w-full" xmlns="http://www.w3.org/2000/svg">...</svg>`) OR a ```mermaid code block.
   - For early math, counting, or sets: use visual emoji counting tables (e.g. 🍎, ⭐, 📦) and place value grids.
   - Use vibrant modern colors (blue `#3b82f6`, emerald `#10b981`, amber `#f59e0b`, indigo `#6366f1`, purple `#8b5cf6`, red `#ef4444`) with transparent opacity fills (`fill="rgba(59, 130, 246, 0.25)"`) and readable text labels (`fill="#1e293b"`).
   - **Topic-Specific Guidance**:
     - *Counting / Numbers / Place Value*: Provide a table of base digits with fruit/object emojis for quantity, numeral, number name, and how to write the numeral. Include place-value decomposition.
     - *Sets / Venn Diagrams*: Draw a colored Venn diagram showing the Universal Set rectangle $U$, Set $A$ circle, Set $B$ circle, overlapping intersection $A \cap B$, and labeled elements.
     - *Geometry / Measurement*: Draw dimensioned 2D/3D shapes with labeled lengths, breadths, heights, or unit grids showing surface tiling.
     - *Algebra / Functions / Coordinate Geometry*: Draw coordinate axes ($x, y$), plotted points, slope/curve lines, or graphs with clear labels.
     - *Science*: Draw process diagrams (e.g., Photosynthesis inputs/outputs, Water Cycle, Circuit with battery & bulb), ray diagrams, or anatomical/system cross-sections.
     - *Social Studies / Language*: Timeline milestones, organizational branch charts (e.g. Arms of Government), or sentence grammar structure trees.
2. **At least ONE structured Markdown comparison or summary table**:
   - Format using Markdown table syntax (`| Concept / Component | Meaning & Purpose | Key Example / Formula |`).
   - Summarize differences, comparison pairs, or operator rules for quick student reference.

At the very end of your response, you MUST provide:
1. Exactly 4 concise bullet points under the heading "### Key Takeaways".
2. A JSON codeblock containing 3 multiple-choice checkpoint questions AND 1-3 recommended educational YouTube tutorial resources:
```json
{
  "quiz": [
    {
      "id": 1,
      "question": "Question text testing the core concept or formula logic?",
      "options": ["Option A", "Option B", "Option C", "Option D"],
      "correct_answer": 0,
      "explanation": "Clear explanation of why this option is correct based on the lesson."
    }
  ],
  "recommended_videos": [
    {
      "title": "Educational title (e.g. Counting and Writing Numbers 1-50)",
      "channel": "Channel name (e.g. Teacher Ira, Khan Academy, BYJU'S)",
      "video_id": "ivbh1khoWoU"
    }
  ]
}
```
Note: correct_answer is the 0-indexed integer (0 to 3) of the correct option. Provide authentic YouTube video IDs if known, or omit video_id.
RULES;

        return "You are a master teacher and instructional designer explaining the topic \"{$topicTitle}\" to a {$gradeLevel} student in {$subjectName}.\n\n"
            . "{$schemeInstructions}\n"
            . "{$contextSnippet}\n\n"
            . $rules;
    }

    /**
     * Parse Gemini response into structured lesson bundle.
     */
    protected function parseResponse(string $rawMarkdown, string $topicTitle, string $subjectName, string $scheme, string $source): array
    {
        $quiz = [];
        $takeaways = [];
        $mediaResources = [];

        // 1. Extract JSON block (supports array format or object format with quiz & recommended_videos)
        if (preg_match('/```(?:json)?\s*(\[\s*\{.*?\}\s*\]|\{\s*".*?\}\s*)\s*```/s', $rawMarkdown, $m)) {
            $parsedJson = json_decode($m[1], true);
            if (is_array($parsedJson)) {
                // If top-level object
                if (isset($parsedJson['quiz']) && is_array($parsedJson['quiz'])) {
                    $quiz = $parsedJson['quiz'];
                    if (!empty($parsedJson['takeaways']) && is_array($parsedJson['takeaways'])) {
                        $takeaways = $parsedJson['takeaways'];
                    }
                    if (!empty($parsedJson['recommended_videos']) && is_array($parsedJson['recommended_videos'])) {
                        foreach ($parsedJson['recommended_videos'] as $idx => $vid) {
                            $vidId = $vid['video_id'] ?? null;
                            if (!empty($vidId) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $vidId)) {
                                $mediaResources[] = [
                                    'id' => 'yt_' . $vidId,
                                    'type' => 'youtube',
                                    'title' => $vid['title'] ?? ($topicTitle . ' Tutorial'),
                                    'channel' => $vid['channel'] ?? 'Educational Video',
                                    'video_id' => $vidId,
                                    'url' => 'https://www.youtube.com/watch?v=' . $vidId,
                                    'thumbnail_url' => 'https://img.youtube.com/vi/' . $vidId . '/hqdefault.jpg',
                                ];
                            }
                        }
                    }
                } elseif (isset($parsedJson[0]) && is_array($parsedJson[0]) && isset($parsedJson[0]['question'])) {
                    // Legacy array of quiz questions
                    $quiz = $parsedJson;
                }
            }
            $cleanMarkdown = trim(str_replace($m[0], '', $rawMarkdown));
        } else {
            $cleanMarkdown = $rawMarkdown;
        }

        // 2. Extract Key Takeaways if not in JSON
        if (empty($takeaways) && preg_match('/### Key Takeaways\s*(.+?)(?=(?:###|```|$))/s', $cleanMarkdown, $tm)) {
            $takeawayLines = explode("\n", trim($tm[1]));
            foreach ($takeawayLines as $line) {
                $cleanLine = trim(preg_replace('/^[-*•]\s*/', '', $line));
                if (!empty($cleanLine) && strlen($cleanLine) > 5) {
                    $takeaways[] = StudentLessonService::formatTakeaway($cleanLine);
                }
            }
        }

        if (empty($takeaways)) {
            $takeaways = [
                "Understand the fundamental definition and origin of {$topicTitle}.",
                "Recognize practical everyday applications in {$subjectName}.",
                "Master the mechanical steps and formula logic behind the calculations.",
                "Avoid common exam traps through step-by-step verification.",
            ];
        }

        // 3. Render Markdown to HTML and clean typography
        $html = $this->renderMarkdownToHtml($cleanMarkdown);
        $cleanHtml = StudentLessonService::cleanMathTypography($html);

        // 4. Ensure quiz is valid
        if (empty($quiz)) {
            $quiz = $this->localSynthesizer->synthesizeQuiz(
                null,
                $topicTitle,
                $subjectName,
                $takeaways
            );
        }

        // 5. Default media resources if none provided by AI
        if (empty($mediaResources)) {
            $mediaResources = $this->suggestEducationalVideos($topicTitle, $subjectName);
        }

        return [
            'markdown' => $cleanMarkdown,
            'html' => $cleanHtml,
            'key_takeaways' => array_slice($takeaways, 0, 4),
            'quiz' => $quiz,
            'media_resources' => $mediaResources,
            'scheme' => $scheme,
            'source' => $source,
        ];
    }

    /**
     * Deterministic local fallback synthesis when AI is unavailable.
     */
    protected function synthesizeLocalFallback(string $topicTitle, string $subjectName, string $gradeLevel, string $scheme): array
    {
        $lower = strtolower($topicTitle . ' ' . $subjectName);
        $isCountingTopic = str_contains($lower, 'count') || str_contains($lower, 'number') || str_contains($lower, 'numeral') || str_contains($lower, 'digit') || str_contains($lower, 'place value');

        if ($isCountingTopic) {
            $takeaways = [
                "All numbers in the world are built from the 10 base digits (0 to 9).",
                "Counting uses one-to-one correspondence to match physical objects to symbols and words.",
                "Place value groups numbers into units, tens, and hundreds, creating repeating patterns.",
                "Two-word numbers from 21 to 99 are hyphenated when written in words (e.g. forty-seven).",
            ];

            $md = [];
            $md[] = "# {$topicTitle}";
            $md[] = "";
            $md[] = "Counting and writing numbers is the bedrock of mathematics. It involves matching physical quantities to written symbols (numerals) and verbal names.";
            $md[] = "";
            $md[] = "### 1. The Base Digits (0 to 9) & One-to-One Correspondence";
            $md[] = "Before humans invented written numbers, keeping track of livestock or grain required carrying piles of pebbles. To simplify trade, mathematicians developed ten fundamental symbols called **digits**: `0, 1, 2, 3, 4, 5, 6, 7, 8, 9`.";
            $md[] = "";
            $md[] = "When we count, we use **one-to-one correspondence**: pointing to one physical object at a time in sequential order.";
            $md[] = "";
            $md[] = "| Quantity | Numeral | Number Name | How to Write the Numeral |";
            $md[] = "| :--- | :--- | :--- | :--- |";
            $md[] = "| *None* | **0** | zero | A smooth closed oval starting from the top. |";
            $md[] = "| 🍎 | **1** | one | A clean vertical stroke straight down. |";
            $md[] = "| 🍎🍎 | **2** | two | A curved arc to the right, diagonal down, then across. |";
            $md[] = "| 🍎🍎🍎 | **3** | three | A curve to the middle, then another curve to the bottom. |";
            $md[] = "| 🍎🍎🍎🍎 | **4** | four | Line down to the middle, across right, then straight vertical line. |";
            $md[] = "| 🍎🍎🍎🍎🍎 | **5** | five | Line across top, short line down, then round curve at the bottom. |";
            $md[] = "| 🍎🍎🍎🍎🍎🍎 | **6** | six | Curve down to the left and swing into a closed loop. |";
            $md[] = "| 🍎🍎🍎🍎🍎🍎🍎 | **7** | seven | Horizontal line across top, then diagonal line down. |";
            $md[] = "| 🍎🍎🍎🍎🍎🍎🍎🍎 | **8** | eight | A continuous 'S' loop crossing in the middle and closing top. |";
            $md[] = "| 🍎🍎🍎🍎🍎🍎🍎🍎🍎 | **9** | nine | Small circle at the top right, followed by a straight line down. |";
            $md[] = "";

            $svg = $this->generateTopicSvgDiagram($topicTitle, $subjectName);
            if (!empty($svg)) {
                $md[] = "### Visual Place Value Representation";
                $md[] = $svg;
                $md[] = "";
            }

            $md[] = "### 2. Daily Life Usage & Real-World Purpose";
            $md[] = "Every single transaction in daily life depends on accurate counting and writing of numbers:";
            $md[] = "- **Commerce & Market Trading**: Buying groceries, paying with Mobile Money (MoMo), and checking change.";
            $md[] = "- **Identity & Organization**: House numbers, phone numbers, bus routes, and clock time.";
            $md[] = "- **Precision in Records**: Ensuring a hospital administers exactly 2 tablets, not 20 tablets. Misreading a zero can be catastrophic!";
            $md[] = "";
            $md[] = "### 3. Core Mechanism: Place Value Patterns (Counting Beyond 9)";
            $md[] = "What happens when you count past 9? You have run out of single digits! Instead of inventing endless new symbols, mathematics uses **Place Value**:";
            $md[] = "1. **The Teen Numbers (11–19)**: Built by placing a '1' in the tens position (representing 1 full group of ten) and appending digits 1 through 9. For example, **14** is 1 group of ten and 4 units.";
            $md[] = "2. **The Tens Pattern**: The tens column increases by 1, while the ones column cycles through 0 to 9:";
            $md[] = "   - 20 (twenty) &rarr; 21, 22, 23... 29";
            $md[] = "   - 30 (thirty) &rarr; 31, 32, 33... 39";
            $md[] = "3. **Writing Numbers in Words Rules**:";
            $md[] = "   - Use a **hyphen** for compound two-word numbers between 21 and 99 (e.g., *forty-seven*, *eighty-two*).";
            $md[] = "   - Large numbers are spoken and written exactly as structured: `356` &rarr; *three hundred fifty-six*.";
            $md[] = "";
            $md[] = "### Place Value Breakdown Table";
            $md[] = "| Place Column | Value | Meaning | Example in 356 |";
            $md[] = "| :--- | :--- | :--- | :--- |";
            $md[] = "| **Hundreds** | 100 | Groups of 10 tens ($10 \\times 10$) | $3 \\times 100 = 300$ |";
            $md[] = "| **Tens** | 10 | Groups of ten items | $5 \\times 10 = 50$ |";
            $md[] = "| **Ones / Units** | 1 | Individual items (0 to 9) | $6 \\times 1 = 6$ |";
            $md[] = "";
            $md[] = "### 4. Step-by-Step Worked Practice";
            $md[] = "#### Worked Example 1: Writing Numbers in Words";
            $md[] = "**Question**: Write 478 in words.";
            $md[] = "- **Step 1**: Identify hundreds digit: 4 &rarr; *four hundred*.";
            $md[] = "- **Step 2**: Identify tens and ones: 78 &rarr; *seventy-eight* (remember the hyphen!).";
            $md[] = "- **Answer**: *Four hundred seventy-eight* (or *Four hundred and seventy-eight*).";
            $md[] = "";
            $md[] = "#### Worked Example 2: Place Value Extraction";
            $md[] = "**Question**: In the number 5,283, what is the place value of the digit 2?";
            $md[] = "- **Step 1**: Count columns from right to left: 3 is Ones, 8 is Tens, 2 is Hundreds.";
            $md[] = "- **Step 2**: Calculate value: $2 \\times 100 = 200$.";
            $md[] = "- **Answer**: Hundreds (Value = 200).";
            $md[] = "";
            $md[] = "### Key Takeaways";
            foreach ($takeaways as $t) {
                $md[] = "- {$t}";
            }

            $fullMarkdown = implode("\n", $md);
            $html = $this->renderMarkdownToHtml($fullMarkdown);
            $cleanHtml = StudentLessonService::cleanMathTypography($html);

            $quiz = [
                [
                    'id' => 1,
                    'question' => "What is the place value of the digit 7 in the number 4,725?",
                    'options' => ["Hundreds (700)", "Tens (70)", "Thousands (7,000)", "Units (7)"],
                    'correct_answer' => 0,
                    'explanation' => "In 4,725, counting from right to left: 5 is in the ones place, 2 is in the tens place, and 7 is in the hundreds place (value = 700).",
                ],
                [
                    'id' => 2,
                    'question' => "Which of the following correctly writes 64 in words according to standard hyphenation rules?",
                    'options' => ["Sixty-four", "Sixty four", "Six four", "Six and four"],
                    'correct_answer' => 0,
                    'explanation' => "Compound two-word numbers between 21 and 99 must be joined with a hyphen (sixty-four).",
                ],
                [
                    'id' => 3,
                    'question' => "Why do we repeat the digits 0 to 9 in different positions (place values) when writing numbers larger than 9?",
                    'options' => [
                        "Because positional place value allows us to represent infinitely large quantities with only 10 symbols.",
                        "Because ancient computers could only count to 9.",
                        "Because numbers larger than 9 do not have names.",
                        "It is an arbitrary rule with no practical purpose."
                    ],
                    'correct_answer' => 0,
                    'explanation' => "The Hindu-Arabic place value system allows us to represent every possible number using just 10 base digits by shifting places to the left.",
                ],
            ];

            $mediaResources = [
                [
                    'id' => 'yt_counting_1',
                    'type' => 'youtube',
                    'title' => 'Counting & Writing Numbers 1-50 The Easiest Way',
                    'channel' => 'Teacher Ira',
                    'video_id' => 'ivbh1khoWoU',
                    'url' => 'https://www.youtube.com/watch?v=ivbh1khoWoU',
                    'thumbnail_url' => 'https://img.youtube.com/vi/ivbh1khoWoU/hqdefault.jpg',
                ],
                [
                    'id' => 'yt_counting_2',
                    'type' => 'youtube',
                    'title' => 'The Base-10 Number System and Place Value',
                    'channel' => 'Khan Academy',
                    'video_id' => 'b4qVmYYCTE8',
                    'url' => 'https://www.youtube.com/watch?v=b4qVmYYCTE8',
                    'thumbnail_url' => 'https://img.youtube.com/vi/b4qVmYYCTE8/hqdefault.jpg',
                ],
            ];

            return [
                'markdown' => $fullMarkdown,
                'html' => $cleanHtml,
                'key_takeaways' => $takeaways,
                'quiz' => $quiz,
                'media_resources' => $mediaResources,
                'scheme' => $scheme,
                'source' => 'local',
            ];
        }

        // Generic fallback for other topics
        $takeaways = [
            "Definition and foundation of {$topicTitle} in {$subjectName}.",
            "Everyday real-world context and practical applications.",
            "Core principles, key patterns, and problem-solving steps.",
            "Best practices for self-checking answers and exam mastery.",
        ];

        $md = [];
        $md[] = "# {$topicTitle}";
        $md[] = "";
        $md[] = "### 1. Origin and Meaning of the Concept";
        $md[] = "In {$subjectName}, **{$topicTitle}** is a foundational concept created to measure, organize, and solve real-world problems. Historically, thinkers coined terms based on direct physical observations to communicate exact ideas without ambiguity.";
        $md[] = "";
        $md[] = "### 2. Daily Life Usage & Real-World Purpose";
        $md[] = "We rely on {$topicTitle} daily in commerce, technology, science, and governance. Understanding this topic allows us to make accurate decisions, avoid costly errors, and analyze systems efficiently.";
        $md[] = "";

        // Add colored responsive visual diagram
        $svgDiagram = $this->generateTopicSvgDiagram($topicTitle, $subjectName);
        if (!empty($svgDiagram)) {
            $md[] = "### Visual Diagram & Model";
            $md[] = $svgDiagram;
            $md[] = "";
        }

        $md[] = "### 3. Step-by-Step Mechanism & Core Logic";
        $md[] = "To solve problems in {$topicTitle}:";
        $md[] = "1. **Identify the Given Data**: List known values with their appropriate units or properties.";
        $md[] = "2. **Apply the Core Rule**: Select the required rule and verify why specific operations apply.";
        $md[] = "3. **Calculate and Verify**: Perform steps sequentially and verify that the final answer is logically sound.";
        $md[] = "";

        // Add summary comparison table
        $md[] = "### Concept Summary Table";
        $md[] = "| Component / Concept | Meaning & Role | Practical Example / Rule |";
        $md[] = "| :--- | :--- | :--- |";
        $md[] = "| **Core Concept** | Definition and scope of {$topicTitle} | Foundation of {$subjectName} |";
        $md[] = "| **Key Rule / Logic** | Method or rule logic | Checked step-by-step |";
        $md[] = "| **Real-World Application** | Daily life utility | Practical planning & decision-making |";
        $md[] = "| **Common Pitfall** | What to watch out for | Check assumptions and avoid mixing concepts |";
        $md[] = "";

        $md[] = "### 4. Step-by-Step Worked Practice";
        $md[] = "**Example 1**: Calculate or explain the required value for a standard problem scenario.";
        $md[] = "- *Step 1*: State given parameters clearly.";
        $md[] = "- *Step 2*: Apply the core principles step-by-step.";
        $md[] = "- *Step 3*: State the final verified conclusion.";
        $md[] = "";
        $md[] = "### Key Takeaways";
        foreach ($takeaways as $t) {
            $md[] = "- {$t}";
        }

        $fullMarkdown = implode("\n", $md);
        $html = $this->renderMarkdownToHtml($fullMarkdown);
        $cleanHtml = StudentLessonService::cleanMathTypography($html);

        $quiz = [
            [
                'id' => 1,
                'question' => "What is the primary origin and fundamental meaning of \"{$topicTitle}\"?",
                'options' => [
                    $takeaways[0] ?? "It represents a core concept developed to solve practical analytical problems.",
                    "It is an arbitrary symbol created with no connection to real-world experience.",
                    "It can only be understood through memorizing disconnected facts.",
                    "It was invented solely for examination questions.",
                ],
                'correct_answer' => 0,
                'explanation' => "The concept originates from foundational human needs to measure, organize, and reason about physical or logical quantities.",
            ],
            [
                'id' => 2,
                'question' => "Why does human society use \"{$topicTitle}\" in everyday modern life?",
                'options' => [
                    $takeaways[1] ?? "To solve real-world problems such as layout planning, commerce, or communication.",
                    "It has no practical daily usage outside of textbooks.",
                    "Only computer software is capable of applying this concept.",
                    "It is exclusively used in advanced university research.",
                ],
                'correct_answer' => 0,
                'explanation' => "Everyday applications include construction, commerce, resource budgeting, and physical analysis.",
            ],
            [
                'id' => 3,
                'question' => "When solving problems in \"{$topicTitle}\", what is the critical step to avoid errors?",
                'options' => [
                    $takeaways[2] ?? "Identify the given dimensions, understand which rule applies, and verify consistency.",
                    "Add all numbers together regardless of what the operators mean.",
                    "Ignore units and only write down the numbers.",
                    "Skip the problem statement and guess the largest value.",
                ],
                'correct_answer' => 0,
                'explanation' => "First-principles understanding requires checking the parameters, selecting the appropriate logic, and keeping reasoning consistent.",
            ],
        ];

        return [
            'markdown' => $fullMarkdown,
            'html' => $cleanHtml,
            'key_takeaways' => $takeaways,
            'quiz' => $quiz,
            'media_resources' => $this->suggestEducationalVideos($topicTitle, $subjectName),
            'scheme' => $scheme,
            'source' => 'local',
        ];
    }

    /**
     * Generate responsive colored SVG diagram for common academic topics.
     */
    protected function generateTopicSvgDiagram(string $topicTitle, string $subjectName): string
    {
        $lower = strtolower($topicTitle . ' ' . $subjectName);

        // 1. Counting, Numbers and Place Value System
        if (str_contains($lower, 'count') || str_contains($lower, 'number') || str_contains($lower, 'numeral') || str_contains($lower, 'digit') || str_contains($lower, 'place value')) {
            return <<<'SVG'
<div class="my-6 flex flex-col items-center">
    <svg viewBox="0 0 520 170" class="w-full max-w-lg rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/80" xmlns="http://www.w3.org/2000/svg">
        <!-- Hundreds Box -->
        <rect x="25" y="25" width="135" height="110" rx="10" fill="rgba(59, 130, 246, 0.15)" stroke="#2563eb" stroke-width="2"/>
        <text x="45" y="55" font-family="sans-serif" font-size="13" font-weight="bold" fill="#1e40af">HUNDREDS</text>
        <text x="65" y="85" font-family="sans-serif" font-size="22" font-weight="bold" fill="#2563eb">100</text>
        <text x="45" y="115" font-family="sans-serif" font-size="11" fill="#64748b">10 Tens grouped</text>

        <!-- Plus 1 -->
        <text x="175" y="85" font-family="sans-serif" font-size="20" font-weight="bold" fill="#94a3b8">+</text>

        <!-- Tens Box -->
        <rect x="205" y="25" width="125" height="110" rx="10" fill="rgba(16, 185, 129, 0.15)" stroke="#059669" stroke-width="2"/>
        <text x="245" y="55" font-family="sans-serif" font-size="13" font-weight="bold" fill="#065f46">TENS</text>
        <text x="250" y="85" font-family="sans-serif" font-size="22" font-weight="bold" fill="#059669">10</text>
        <text x="220" y="115" font-family="sans-serif" font-size="11" fill="#64748b">10 Units grouped</text>

        <!-- Plus 2 -->
        <text x="345" y="85" font-family="sans-serif" font-size="20" font-weight="bold" fill="#94a3b8">+</text>

        <!-- Ones / Units Box -->
        <rect x="375" y="25" width="120" height="110" rx="10" fill="rgba(245, 158, 11, 0.15)" stroke="#d97706" stroke-width="2"/>
        <text x="415" y="55" font-family="sans-serif" font-size="13" font-weight="bold" fill="#92400e">ONES</text>
        <text x="425" y="85" font-family="sans-serif" font-size="22" font-weight="bold" fill="#d97706">1</text>
        <text x="390" y="115" font-family="sans-serif" font-size="11" fill="#64748b">Single units (0-9)</text>
    </svg>
    <span class="text-xs text-slate-500 mt-2 font-medium">Figure: Base-10 Positional Place Value System (Hundreds, Tens, and Ones)</span>
</div>
SVG;
        }

        // 2. Sets and Venn Diagrams
        if (str_contains($lower, 'set') || str_contains($lower, 'venn')) {
            return <<<'SVG'
<div class="my-6 flex flex-col items-center">
    <svg viewBox="0 0 500 260" class="w-full max-w-lg rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/80" xmlns="http://www.w3.org/2000/svg">
        <!-- Universal Set Frame -->
        <rect x="20" y="20" width="460" height="220" rx="14" fill="none" stroke="#64748b" stroke-width="2" stroke-dasharray="6,4"/>
        <text x="35" y="45" font-family="sans-serif" font-size="14" font-weight="bold" fill="#64748b">Universal Set (&xi; or U)</text>

        <!-- Set A (Blue) -->
        <circle cx="200" cy="140" r="80" fill="rgba(59, 130, 246, 0.22)" stroke="#2563eb" stroke-width="2.5"/>
        <text x="155" y="100" font-family="sans-serif" font-size="16" font-weight="bold" fill="#1d4ed8">Set A</text>
        <text x="160" y="145" font-family="sans-serif" font-size="12" fill="#3b82f6">Only in A</text>

        <!-- Set B (Amber) -->
        <circle cx="300" cy="140" r="80" fill="rgba(245, 158, 11, 0.22)" stroke="#d97706" stroke-width="2.5"/>
        <text x="325" y="100" font-family="sans-serif" font-size="16" font-weight="bold" fill="#b45309">Set B</text>
        <text x="305" y="145" font-family="sans-serif" font-size="12" fill="#d97706">Only in B</text>

        <!-- Intersection Label (Green highlight) -->
        <text x="235" y="135" font-family="sans-serif" font-size="13" font-weight="bold" fill="#047857">A &cap; B</text>
        <text x="220" y="155" font-family="sans-serif" font-size="11" fill="#059669">Both Sets</text>
    </svg>
    <span class="text-xs text-slate-500 mt-2 font-medium">Figure: Overlapping Venn Diagram showing Set A, Set B, and Intersection (A &cap; B)</span>
</div>
SVG;
        }

        // 2. Area, Perimeter and Geometry
        if (str_contains($lower, 'area') || str_contains($lower, 'perimeter') || str_contains($lower, 'rectangle') || str_contains($lower, 'geometry')) {
            return <<<'SVG'
<div class="my-6 flex flex-col items-center">
    <svg viewBox="0 0 500 240" class="w-full max-w-lg rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/80" xmlns="http://www.w3.org/2000/svg">
        <!-- Tiled Grid for Surface Area -->
        <rect x="120" y="45" width="260" height="130" fill="rgba(99, 102, 241, 0.15)" stroke="#4f46e5" stroke-width="2.5" rx="4"/>
        
        <!-- Grid lines representing 1x1 unit squares -->
        <path d="M 172 45 L 172 175 M 224 45 L 224 175 M 276 45 L 276 175 M 328 45 L 328 175" stroke="#818cf8" stroke-width="1" stroke-dasharray="2,2"/>
        <path d="M 120 90 L 380 90 M 120 135 L 380 135" stroke="#818cf8" stroke-width="1" stroke-dasharray="2,2"/>

        <!-- Length dimension line (Top) -->
        <line x1="120" y1="28" x2="380" y2="28" stroke="#1e293b" stroke-width="1.5" marker-start="url(#arrow)" marker-end="url(#arrow)"/>
        <text x="225" y="22" font-family="sans-serif" font-size="13" font-weight="bold" fill="#1e293b">Length (L = 5 units)</text>

        <!-- Width dimension line (Right) -->
        <line x1="400" y1="45" x2="400" y2="175" stroke="#1e293b" stroke-width="1.5"/>
        <text x="410" y="115" font-family="sans-serif" font-size="13" font-weight="bold" fill="#1e293b">Width (W = 3)</text>

        <!-- Area formula annotation -->
        <text x="185" y="115" font-family="sans-serif" font-size="15" font-weight="bold" fill="#312e81">Area = L &times; W = 15 units&sup2;</text>
        <text x="160" y="210" font-family="sans-serif" font-size="12" fill="#475569">Perimeter = 2 &times; (5 + 3) = 16 units (Boundary line)</text>
    </svg>
    <span class="text-xs text-slate-500 mt-2 font-medium">Figure: Geometric Model showing 2D surface tiling (Area = L &times; W) versus 1D boundary (Perimeter)</span>
</div>
SVG;
        }

        // 3. Coordinate Plane, Linear Graphs and Functions
        if (str_contains($lower, 'graph') || str_contains($lower, 'coordinate') || str_contains($lower, 'function') || str_contains($lower, 'linear')) {
            return <<<'SVG'
<div class="my-6 flex flex-col items-center">
    <svg viewBox="0 0 460 260" class="w-full max-w-lg rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/80" xmlns="http://www.w3.org/2000/svg">
        <!-- Coordinate Axes -->
        <line x1="60" y1="200" x2="400" y2="200" stroke="#334155" stroke-width="2"/>
        <line x1="120" y1="30" x2="120" y2="230" stroke="#334155" stroke-width="2"/>
        <text x="390" y="220" font-family="sans-serif" font-size="14" font-weight="bold" fill="#334155">x</text>
        <text x="100" y="40" font-family="sans-serif" font-size="14" font-weight="bold" fill="#334155">y</text>

        <!-- Grid ticks -->
        <circle cx="120" cy="200" r="3" fill="#0f172a"/>
        <text x="105" y="215" font-family="sans-serif" font-size="11" fill="#64748b">(0,0)</text>

        <!-- Plotted Line y = mx + c -->
        <line x1="90" y1="180" x2="360" y2="60" stroke="#2563eb" stroke-width="3"/>

        <!-- Points on line -->
        <circle cx="180" cy="140" r="5" fill="#ef4444"/>
        <text x="190" y="145" font-family="sans-serif" font-size="11" font-weight="bold" fill="#b91c1c">(x&#8321;, y&#8321;)</text>

        <circle cx="300" cy="85" r="5" fill="#ef4444"/>
        <text x="310" y="90" font-family="sans-serif" font-size="11" font-weight="bold" fill="#b91c1c">(x&#8322;, y&#8322;)</text>

        <text x="210" y="70" font-family="sans-serif" font-size="13" font-weight="bold" fill="#1d4ed8">Line: y = mx + c</text>
    </svg>
    <span class="text-xs text-slate-500 mt-2 font-medium">Figure: Cartesian Coordinate Plane with Plotted Linear Relationship</span>
</div>
SVG;
        }

        // 4. General First-Principles Concept Flow Diagram
        return <<<'SVG'
<div class="my-6 flex flex-col items-center">
    <svg viewBox="0 0 520 160" class="w-full max-w-lg rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/80" xmlns="http://www.w3.org/2000/svg">
        <!-- Box 1: Concept Origin -->
        <rect x="20" y="40" width="130" height="70" rx="10" fill="rgba(59, 130, 246, 0.15)" stroke="#2563eb" stroke-width="2"/>
        <text x="40" y="70" font-family="sans-serif" font-size="12" font-weight="bold" fill="#1e40af">1. Origin &amp; Need</text>
        <text x="35" y="90" font-family="sans-serif" font-size="10" fill="#3b82f6">Human Foundation</text>

        <!-- Arrow 1 -->
        <path d="M 160 75 L 185 75" stroke="#94a3b8" stroke-width="2.5" marker-end="url(#arrow)"/>

        <!-- Box 2: Core Mechanism -->
        <rect x="195" y="40" width="130" height="70" rx="10" fill="rgba(16, 185, 129, 0.15)" stroke="#059669" stroke-width="2"/>
        <text x="210" y="70" font-family="sans-serif" font-size="12" font-weight="bold" fill="#065f46">2. Core Logic</text>
        <text x="210" y="90" font-family="sans-serif" font-size="10" fill="#10b981">Rules &amp; Formulas</text>

        <!-- Arrow 2 -->
        <path d="M 335 75 L 360 75" stroke="#94a3b8" stroke-width="2.5"/>

        <!-- Box 3: Real Application -->
        <rect x="370" y="40" width="130" height="70" rx="10" fill="rgba(245, 158, 11, 0.15)" stroke="#d97706" stroke-width="2"/>
        <text x="385" y="70" font-family="sans-serif" font-size="12" font-weight="bold" fill="#92400e">3. Daily Usage</text>
        <text x="390" y="90" font-family="sans-serif" font-size="10" fill="#f59e0b">Practical Solution</text>
    </svg>
    <span class="text-xs text-slate-500 mt-2 font-medium">Figure: First-Principles Flow from Real Need to Mathematical &amp; Scientific Resolution</span>
</div>
SVG;
    }

    /**
     * Query Gemini with multi-model fallback.
     */
    protected function queryGemini(string $prompt): ?string
    {
        $modelsToTry = [
            'gemini-flash-lite-latest',
            'gemini-3.5-flash-lite',
            'gemini-2.5-flash-lite',
            'gemini-3.8-flash',
            'gemini-flash-latest',
        ];

        if (!empty($this->model) && !in_array($this->model, $modelsToTry)) {
            array_unshift($modelsToTry, $this->model);
        }
        $modelsToTry = array_values(array_unique($modelsToTry));

        $lastError = null;

        foreach ($modelsToTry as $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$this->apiKey}";
            try {
                $response = Http::timeout(35)
                    ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                    ->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.4,
                            'maxOutputTokens' => 3500,
                        ]
                    ]);

                if ($response->successful()) {
                    $body = $response->json();
                    $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    if (!empty($text)) {
                        return $text;
                    }
                } else {
                    $lastError = "Gemini error (HTTP {$response->status()} on {$modelName})";
                }
            } catch (\Throwable $e) {
                $lastError = "Gemini exception on {$modelName}: " . $e->getMessage();
            }
        }

        throw new Exception($lastError ?? "Gemini API query failed on all models.");
    }

    /**
     * Convert markdown text to clean HTML while preserving SVG graphics and formatting.
     */
    public function renderMarkdownToHtml(string $markdown): string
    {
        $svgMap = [];
        $counter = 0;
        $safeMarkdown = preg_replace_callback('/<svg\b[^>]*>[\s\S]*?<\/svg>/i', function ($m) use (&$svgMap, &$counter) {
            $token = "<!--SVG_BLOCK_PLACEHOLDER_{$counter}-->";
            $counter++;
            $svgMap[$token] = $m[0];
            return "\n\n{$token}\n\n";
        }, $markdown);

        $html = Str::markdown($safeMarkdown, [
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);

        foreach ($svgMap as $token => $svg) {
            $html = str_replace("<p>{$token}</p>", $svg, $html);
            $html = str_replace($token, $svg, $html);
        }

        return $html;
    }

    /**
     * Suggest curated educational video tutorials for a topic.
     */
    public function suggestEducationalVideos(string $topicTitle, string $subjectName, string $gradeLevel = 'JHS'): array
    {
        $lower = strtolower($topicTitle . ' ' . $subjectName);

        if (str_contains($lower, 'count') || str_contains($lower, 'number') || str_contains($lower, 'numeral')) {
            return [
                [
                    'id' => 'yt_counting_1',
                    'type' => 'youtube',
                    'title' => 'Counting & Writing Numbers 1-50 The Easiest Way',
                    'channel' => 'Teacher Ira',
                    'video_id' => 'ivbh1khoWoU',
                    'url' => 'https://www.youtube.com/watch?v=ivbh1khoWoU',
                    'thumbnail_url' => 'https://img.youtube.com/vi/ivbh1khoWoU/hqdefault.jpg',
                ],
                [
                    'id' => 'yt_counting_2',
                    'type' => 'youtube',
                    'title' => 'The Base-10 Number System and Place Value',
                    'channel' => 'Khan Academy',
                    'video_id' => 'b4qVmYYCTE8',
                    'url' => 'https://www.youtube.com/watch?v=b4qVmYYCTE8',
                    'thumbnail_url' => 'https://img.youtube.com/vi/b4qVmYYCTE8/hqdefault.jpg',
                ],
                [
                    'id' => 'yt_counting_3',
                    'type' => 'youtube',
                    'title' => 'Numbers Definition & How to Write Large Numbers',
                    'channel' => "BYJU'S",
                    'video_id' => '4U608p_L5hU',
                    'url' => 'https://www.youtube.com/watch?v=4U608p_L5hU',
                    'thumbnail_url' => 'https://img.youtube.com/vi/4U608p_L5hU/hqdefault.jpg',
                ],
            ];
        }

        if (str_contains($lower, 'set') || str_contains($lower, 'venn')) {
            return [
                [
                    'id' => 'yt_sets_1',
                    'type' => 'youtube',
                    'title' => 'Introduction to Sets and Venn Diagrams',
                    'channel' => 'Khan Academy',
                    'video_id' => 'tyDKR4FG3Yw',
                    'url' => 'https://www.youtube.com/watch?v=tyDKR4FG3Yw',
                    'thumbnail_url' => 'https://img.youtube.com/vi/tyDKR4FG3Yw/hqdefault.jpg',
                ],
                [
                    'id' => 'yt_sets_2',
                    'type' => 'youtube',
                    'title' => 'Intersection, Union and Complement of Sets',
                    'channel' => 'Math Antics',
                    'video_id' => '1Bsk0B-n_g8',
                    'url' => 'https://www.youtube.com/watch?v=1Bsk0B-n_g8',
                    'thumbnail_url' => 'https://img.youtube.com/vi/1Bsk0B-n_g8/hqdefault.jpg',
                ],
            ];
        }

        // Default educational recommendation
        return [
            [
                'id' => 'yt_gen_' . substr(md5($topicTitle), 0, 8),
                'type' => 'youtube',
                'title' => "{$topicTitle} - Complete Educational Overview",
                'channel' => 'Khan Academy',
                'video_id' => 'b4qVmYYCTE8',
                'url' => 'https://www.youtube.com/watch?v=b4qVmYYCTE8',
                'thumbnail_url' => 'https://img.youtube.com/vi/b4qVmYYCTE8/hqdefault.jpg',
            ],
        ];
    }
}
