<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use App\Models\CurriculumIndicator;
use App\Models\CurriculumStrand;
use App\Models\CurriculumSubStrand;
use App\Models\Level;
use App\Models\LevelGroup;
use App\Models\Subject;
use App\Models\Textbook;
use App\Models\TextbookChapter;
use App\Models\TextbookSection;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $admin = User::where('is_admin', true)->first() ?? User::first();
            $adminId = $admin?->id ?? 1;

            $jhsGroup = LevelGroup::where('slug', 'jhs')->first();
            $jhs1Level = Level::where('slug', 'like', '%jhs-1%')->orWhere('title', 'like', '%JHS 1%')->first();

            // Subjects to seed with official GES JHS standards
            $subjectsData = [
                // 1. Core Mathematics
                [
                    'subject_name' => 'Core Mathematics',
                    'title' => 'National Standards Curriculum: Mathematics (JHS 1)',
                    'textbook_title' => 'Core Mathematics for Junior High Schools',
                    'strands' => [
                        [
                            'title' => 'Number and Numeration Systems',
                            'description' => 'Number sense, place values, integers, set concepts, and fractions.',
                            'sub_strands' => [
                                [
                                    'title' => 'Sets and Numeration',
                                    'content_standard' => 'Demonstrate an understanding of sets, place value up to 1,000,000,000, and standard form.',
                                    'indicators' => [
                                        [
                                            'title' => 'Concept of Sets and Venn Diagrams',
                                            'description' => 'Finite, infinite, unit, empty, and universal sets. Union, intersection, and complement of sets with two-set Venn diagrams.',
                                            'exemplars' => "1. Identify sets of natural numbers, even numbers, and prime numbers.\n2. Draw Venn diagrams showing intersection A ∩ B and union A ∪ B.\n3. Solve practical two-set problem scenarios.",
                                            'lesson' => '<h3>Understanding Sets</h3><p>A <strong>set</strong> is a well-defined collection of distinct objects or elements denoted by curly braces $\{ \}$.</p><h4>Key Operations:</h4><ul><li><strong>Union ($A \cup B$):</strong> Elements belonging to $A$, $B$, or both.</li><li><strong>Intersection ($A \cap B$):</strong> Elements common to both $A$ and $B$.</li><li><strong>Complement ($A^\prime$):</strong> Elements in the universal set $\mathcal{U}$ not in $A$.</li></ul>',
                                        ],
                                        [
                                            'title' => 'Place Value & Scientific Notation',
                                            'description' => 'Read, write, and express large numbers in words, standard figures, and standard form ($A \times 10^n$).',
                                            'exemplars' => "1. Express national population data in standard form.\n2. Convert 450,000,000 to standard form as 4.5 × 10^8.",
                                            'lesson' => '<h3>Scientific Notation (Standard Form)</h3><p>Standard form expresses numbers as $A \times 10^n$ where $1 \le A < 10$ and $n$ is an integer.</p><p>Example: $64,000,000 = 6.4 \times 10^7$</p>',
                                        ],
                                    ],
                                ],
                                [
                                    'title' => 'Operations with Integers & BODMAS',
                                    'content_standard' => 'Apply mental mathematics, order of operations (BODMAS), and algorithms on positive and negative integers.',
                                    'indicators' => [
                                        [
                                            'title' => 'Addition and Subtraction of Directed Numbers',
                                            'description' => 'Use number lines and sign rules to add and subtract signed integers in contextual scenarios.',
                                            'exemplars' => "1. Model temperature changes using directed numbers.\n2. Solve -15 + (-7) and 24 - (-12).",
                                            'lesson' => '<h3>Directed Numbers</h3><p>Sign rules: $a + (-b) = a - b$ and $a - (-b) = a + b$.</p>',
                                        ],
                                        [
                                            'title' => 'Order of Operations (BODMAS)',
                                            'description' => 'Evaluate complex numerical expressions involving Brackets, Orders, Division, Multiplication, Addition, and Subtraction.',
                                            'exemplars' => "1. Evaluate 24 ÷ 6 × (3 + 2) - 4^2.",
                                            'lesson' => '<h3>BODMAS Hierarchy</h3><p>Calculate in order: Brackets, Orders, Division & Multiplication, Addition & Subtraction.</p>',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'title' => 'Algebra and Relations',
                            'description' => 'Patterns, algebraic expressions, linear equations, and functional mappings.',
                            'sub_strands' => [
                                [
                                    'title' => 'Linear Patterns & Equations',
                                    'content_standard' => 'Derive algebraic rules for linear patterns and solve linear equations in one variable.',
                                    'indicators' => [
                                        [
                                            'title' => 'Linear Sequences & The nth Term',
                                            'description' => 'Identify common differences in linear sequences and write algebraic formula Tn = a + (n - 1)d.',
                                            'exemplars' => "1. Find the 20th term of sequence: 4, 7, 10, 13, ...",
                                            'lesson' => '<h3>Arithmetic Sequences</h3><p>Formula for the $n^{\text{th}}$ term: $$T_n = a + (n - 1)d$$</p>',
                                        ],
                                        [
                                            'title' => 'Solving Linear Equations in One Variable',
                                            'description' => 'Solve equations of the form ax + b = c using inverse operations to isolate the unknown.',
                                            'exemplars' => "1. Solve 4x - 7 = 21.\n2. Solve (x + 3) / 4 = 5.",
                                            'lesson' => '<h3>Linear Equations</h3><p>Apply inverse operations to both sides to solve for the unknown variable.</p>',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'title' => 'Geometry and Measurement',
                            'description' => 'Geometric constructions, angles, perimeter, and area of plane figures.',
                            'sub_strands' => [
                                [
                                    'title' => 'Lines, Angles & Measurement',
                                    'content_standard' => 'Construct angles and calculate perimeter and area of plane figures.',
                                    'indicators' => [
                                        [
                                            'title' => 'Geometric Constructions of Angles',
                                            'description' => 'Construct 60°, 90°, and 45° angles and angle bisectors using compass and ruler only.',
                                            'exemplars' => "1. Bisect an angle of 60° to obtain 30°.\n2. Construct perpendicular bisector of a 8cm line segment.",
                                            'lesson' => '<h3>Constructions with Ruler & Compasses</h3><p>Construct accurate angles without a protractor using step-by-step geometric arcs.</p>',
                                        ],
                                        [
                                            'title' => 'Perimeter and Area of 2D Shapes',
                                            'description' => 'Calculate perimeter and area of rectangles, triangles, and parallelograms.',
                                            'exemplars' => "1. Calculate area of compound classroom floors.\n2. Calculate perimeter of fenced school compounds.",
                                            'lesson' => '<h3>Area & Perimeter</h3><p>Rectangle Area $= l \times w$, Triangle Area $= \frac{1}{2}bh$, Parallelogram Area $= bh$.</p>',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],

                // 2. English Language
                [
                    'subject_name' => 'English Language',
                    'title' => 'National Standards Curriculum: English Language (JHS 1)',
                    'textbook_title' => 'Comprehensive English Language for JHS 1',
                    'strands' => [
                        [
                            'title' => 'Grammar and Usage',
                            'description' => 'Parts of speech, sentence structures, punctuation, and tenses.',
                            'sub_strands' => [
                                [
                                    'title' => 'Nouns and Pronouns',
                                    'content_standard' => 'Demonstrate mastery of abstract, collective, and countable nouns and pronoun antecedent agreement.',
                                    'indicators' => [
                                        [
                                            'title' => 'Noun Classes and Collective Nouns',
                                            'description' => 'Identify and apply proper, common, abstract, and collective nouns in creative sentences.',
                                            'exemplars' => "1. Distinguish between concrete and abstract nouns.\n2. Correct pluralization of irregular nouns.",
                                            'lesson' => '<h3>Noun Types</h3><p>Nouns name people, places, things, and concepts. Abstract nouns denote emotions, states, and qualities.</p>',
                                        ],
                                        [
                                            'title' => 'Subject-Verb Agreement (Concord)',
                                            'description' => 'Apply principles of grammatical concord in complex and compound sentences.',
                                            'exemplars' => "1. Rules for 'either...or' and 'neither...nor'.\n2. Collective nouns with singular vs plural verbs.",
                                            'lesson' => '<h3>Subject-Verb Concord</h3><p>Singular subjects take singular verbs; plural subjects take plural verbs.</p>',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'title' => 'Reading Comprehension and Summary',
                            'description' => 'Skimming, scanning, contextual vocabulary, and summary writing.',
                            'sub_strands' => [
                                [
                                    'title' => 'Summary Skills',
                                    'content_standard' => 'Extract main points and paraphrase core ideas concisely.',
                                    'indicators' => [
                                        [
                                            'title' => 'Identifying Main Ideas in Paragraphs',
                                            'description' => 'Distinguish topic sentences from supporting details and illustrations.',
                                            'exemplars' => "1. Underline topic sentences in narrative and expository passages.",
                                            'lesson' => '<h3>Finding the Central Theme</h3><p>The topic sentence expresses the main argument of a paragraph.</p>',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],

                // 3. Integrated Science
                [
                    'subject_name' => 'Integrated Science',
                    'title' => 'National Standards Curriculum: Integrated Science (JHS 1)',
                    'textbook_title' => 'Integrated Science for Junior High Schools',
                    'strands' => [
                        [
                            'title' => 'Diversity of Matter',
                            'description' => 'Living and non-living matter, cells, states of matter, and atomic structure.',
                            'sub_strands' => [
                                [
                                    'title' => 'Cells and Living Organisms',
                                    'content_standard' => 'Demonstrate an understanding of plant and animal cell structures.',
                                    'indicators' => [
                                        [
                                            'title' => 'Plant and Animal Cell Structures',
                                            'description' => 'Identify the functions of nucleus, cytoplasm, cell wall, and cell membrane under a microscope.',
                                            'exemplars' => "1. Prepare onion skin wet mounts for light microscopy.\n2. Compare plant and animal cell organelles.",
                                            'lesson' => '<h3>Cellular Biology</h3><p>Cells are the basic structural and functional units of all living organisms.</p>',
                                        ],
                                        [
                                            'title' => 'States and Properties of Matter',
                                            'description' => 'Explain changes of state using the kinetic particle theory.',
                                            'exemplars' => "1. Investigate melting, boiling, condensation, and sublimation.",
                                            'lesson' => '<h3>Kinetic Particle Theory</h3><p>Matter exists in solid, liquid, and gas states based on kinetic energy and intermolecular forces.</p>',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'title' => 'Energy and Systems',
                            'description' => 'Forms of energy, energy conversion, and solar system.',
                            'sub_strands' => [
                                [
                                    'title' => 'Energy Transformations',
                                    'content_standard' => 'Demonstrate law of conservation of energy and everyday conversions.',
                                    'indicators' => [
                                        [
                                            'title' => 'Forms of Energy and Law of Conservation',
                                            'description' => 'Explain potential, kinetic, chemical, and electrical energy and trace conversion paths in everyday appliances.',
                                            'exemplars' => "1. Trace energy transformation in hydro-electric dams (Akosombo).\n2. Calculate simple potential and kinetic energy.",
                                            'lesson' => '<h3>Energy Conservation</h3><p>Energy cannot be created nor destroyed, but changes from one form to another.</p>',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],

                // 4. Information & Communication Technology (ICT)
                [
                    'subject_name' => 'Information & Communication Technology (ICT)',
                    'title' => 'National Standards Curriculum: Computing (JHS 1)',
                    'textbook_title' => 'Modern Computing and ICT for JHS 1',
                    'strands' => [
                        [
                            'title' => 'Computer Hardware and Systems',
                            'description' => 'Input, output, storage devices, and system components.',
                            'sub_strands' => [
                                [
                                    'title' => 'Components of a Computer',
                                    'content_standard' => 'Identify and describe input, processing, output, and storage devices.',
                                    'indicators' => [
                                        [
                                            'title' => 'Input and Output Devices',
                                            'description' => 'Classify peripheral devices and explain their roles in data processing.',
                                            'exemplars' => "1. Compare optical vs magnetic storage.\n2. Identify primary vs secondary memory.",
                                            'lesson' => '<h3>Computer Hardware</h3><p>Hardware comprises the physical components of a computer system: input, CPU, memory, and output.</p>',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'title' => 'Digital Literacy and Internet Safety',
                            'description' => 'Search engines, web browsing, cybersecurity, and netiquette.',
                            'sub_strands' => [
                                [
                                    'title' => 'Internet and Cyber Hygiene',
                                    'content_standard' => 'Practice safe web navigation and protect personal digital identity.',
                                    'indicators' => [
                                        [
                                            'title' => 'Internet Safety and Cyber Etiquette',
                                            'description' => 'Recognize phishing, malware, strong passwords, and online communication etiquette.',
                                            'exemplars' => "1. Evaluate secure HTTPS URLs and certificate warnings.\n2. Create strong 12+ character passphrases.",
                                            'lesson' => '<h3>Cyber Safety</h3><p>Protecting privacy, credentials, and digital footprint across online platforms.</p>',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            foreach ($subjectsData as $sData) {
                $subject = Subject::where('name', $sData['subject_name'])->first();
                if (!$subject) {
                    $subject = Subject::create([
                        'name' => $sData['subject_name'],
                        'description' => 'National curriculum standards for ' . $sData['subject_name'],
                    ]);
                }

                // Create or update curriculum
                $curriculum = Curriculum::updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'education_body' => 'GES (Ghana Education Service)',
                    ],
                    [
                        'title' => $sData['title'],
                        'academic_year' => '2024/2025',
                        'level_id' => $jhs1Level?->id,
                        'level_group_id' => $jhsGroup?->id,
                        'file_path' => 'curricula/ges_jhs1_' . strtolower(str_replace([' ', '&', '(', ')'], '_', $sData['subject_name'])) . '.pdf',
                        'extraction_status' => 'extracted',
                        'is_approved' => true,
                        'approved_by' => $adminId,
                        'approved_at' => now(),
                        'uploaded_by' => $adminId,
                        'notes' => 'Official GES / NaCCA Standards-Based Curriculum for JHS 1.',
                    ]
                );

                // Clean existing strands for idempotent run
                CurriculumStrand::where('curriculum_id', $curriculum->id)->delete();

                // Create textbook
                $textbook = Textbook::updateOrCreate(
                    [
                        'title' => $sData['textbook_title'],
                        'subject_id' => $subject->id,
                    ],
                    [
                        'author' => 'National Curriculum Panel',
                        'publisher' => 'Ghana Education Service',
                        'edition' => 'Standards-Based Edition',
                        'level_id' => $jhs1Level?->id,
                        'level_group_id' => $jhsGroup?->id,
                        'curriculum_id' => $curriculum->id,
                        'file_path' => 'textbooks/jhs1_' . strtolower(str_replace([' ', '&', '(', ')'], '_', $sData['subject_name'])) . '.pdf',
                        'toc_extraction_status' => 'extracted',
                        'content_extraction_status' => 'extracted',
                        'is_toc_approved' => true,
                        'is_content_approved' => true,
                        'uploaded_by' => $adminId,
                    ]
                );

                TextbookChapter::where('textbook_id', $textbook->id)->delete();

                $strandOrder = 1;
                foreach ($sData['strands'] as $strandData) {
                    $strand = CurriculumStrand::create([
                        'curriculum_id' => $curriculum->id,
                        'title' => $strandData['title'],
                        'description' => $strandData['description'] ?? null,
                        'grade_label' => 'JHS 1',
                        'sort_order' => $strandOrder,
                        'level_id' => $jhs1Level?->id,
                    ]);

                    $chapter = TextbookChapter::create([
                        'textbook_id' => $textbook->id,
                        'title' => $strandData['title'],
                        'sort_order' => $strandOrder,
                        'curriculum_strand_id' => $strand->id,
                    ]);

                    $subOrder = 1;
                    foreach ($strandData['sub_strands'] as $subStrandData) {
                        $subStrand = CurriculumSubStrand::create([
                            'strand_id' => $strand->id,
                            'title' => $subStrandData['title'],
                            'content_standard' => $subStrandData['content_standard'] ?? null,
                            'sort_order' => $subOrder,
                        ]);

                        $indOrder = 1;
                        foreach ($subStrandData['indicators'] as $indData) {
                            $indicator = CurriculumIndicator::create([
                                'sub_strand_id' => $subStrand->id,
                                'indicator_code' => null, // Omit indicator number for pure clean titles
                                'title' => $indData['title'],
                                'description' => $indData['description'],
                                'exemplars' => $indData['exemplars'],
                                'sort_order' => $indOrder,
                            ]);

                            TextbookSection::create([
                                'chapter_id' => $chapter->id,
                                'title' => $indData['title'],
                                'content_html' => $indData['lesson'],
                                'content_markdown' => strip_tags($indData['lesson']),
                                'sort_order' => $indOrder,
                                'curriculum_indicator_id' => $indicator->id,
                            ]);

                            $indOrder++;
                        }
                        $subOrder++;
                    }
                    $strandOrder++;
                }
            }
        });
    }
}
