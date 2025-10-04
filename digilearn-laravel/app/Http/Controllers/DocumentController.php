<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentController extends Controller
{
    // First page - Document preview/selection
    public function viewDocument($lessonId, $type)
    {
        // Check if this is an AJAX request to check document existence
        if (request()->ajax() || request()->wantsJson()) {
            // For AJAX requests, check level group selection first
            if (!session('selected_level_group')) {
                return response()->json([
                    'exists' => false,
                    'error' => 'level_required',
                    'message' => 'Please select your grade level first.'
                ]);
            }

            $document = $this->getDocumentForLesson($lessonId, $type);
            return response()->json([
                'exists' => $document !== null && !empty($document),
                'document' => $document
            ]);
        }

        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return redirect()->route('dashboard.level-selection')
                ->with('error', 'Please select your grade level first.');
        }

        $selectedLevel = session('selected_level');

        // Get lesson data
        $lessons = $this->getLessonsForLevel($selectedLevel);
        $lesson = collect($lessons)->firstWhere('id', (int)$lessonId);

        if (!$lesson) {
            return view('dashboard.document-viewer', [
                'lesson' => null,
                'document' => null,
                'selectedLevel' => $selectedLevel,
                'type' => $type,
                'error' => 'Lesson not found.'
            ]);
        }

        // Get basic document info for preview
        $document = $this->getDocumentForLesson($lessonId, $type);

        if (!$document) {
            // Pass a flag or empty document to the view
            return view('dashboard.document-viewer', [
                'lesson' => $lesson,
                'document' => null,
                'selectedLevel' => $selectedLevel,
                'type' => $type
            ]);
        }

        // Return the simple preview page
        return view('dashboard.document-viewer', compact('lesson', 'document', 'selectedLevel', 'type'));
    }

    // Second page - Document content viewer
    public function viewDocumentContent($lessonId, $type)
    {
        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return redirect()->route('dashboard.level-selection')
                ->with('error', 'Please select your grade level first.');
        }

        $selectedLevelGroup = session('selected_level_group');

        // Get lesson data
        $lessons = $this->getLessonsForLevel($selectedLevelGroup);
        $lesson = collect($lessons)->firstWhere('id', (int)$lessonId);
        
        if (!$lesson) {
            return redirect()->route('dashboard.digilearn')
                ->with('error', 'Lesson not found.');
        }

        // Get full document data with content
        $document = $this->getDocumentContentForLesson($lessonId, $type);
        
        if (!$document) {
            return redirect()->route('dashboard.lesson.document', [$lessonId, $type])
                ->with('error', 'Document content not found.');
        }

        // Return the full content viewer page
        return view('dashboard.document-content-viewer', compact('lesson', 'document', 'selectedLevelGroup', 'type'));
    }

    private function getLessonsForLevel($level)
    {
        $allLessons = [
            'primary-1-3' => [
                [
                    'id' => 1,
                    'title' => 'Basic Numbers and Counting',
                    'subject' => 'Mathematics Gr-1',
                    'duration' => '12:30',
                    'instructor' => 'Prof. Mensah',
                    'year' => '2025',
                ],
                [
                    'id' => 2,
                    'title' => 'Animals and Their Homes',
                    'subject' => 'Science Gr-2',
                    'duration' => '15:45',
                    'instructor' => 'Prof. Asante',
                    'year' => '2025',
                ]
            ],
            'primary-4-6' => [
                [
                    'id' => 3,
                    'title' => 'Fractions and Decimals',
                    'subject' => 'Mathematics Gr-4',
                    'duration' => '18:20',
                    'instructor' => 'Prof. Osei',
                    'year' => '2025',
                ]
            ],
            'jhs-7-9' => [
                [
                    'id' => 4,
                    'title' => 'Living and Non Living organism',
                    'subject' => 'Science Gr-7',
                    'duration' => '14:07',
                    'instructor' => 'Prof. Aboagye',
                    'year' => '2025',
                ],
                [
                    'id' => 5,
                    'title' => 'Algebraic Expressions',
                    'subject' => 'Mathematics Gr-8',
                    'duration' => '16:30',
                    'instructor' => 'Prof. Kwame',
                    'year' => '2025',
                ]
            ],
            'shs-1-3' => [
                [
                    'id' => 6,
                    'title' => 'Chemical Bonding',
                    'subject' => 'Chemistry SHS-1',
                    'duration' => '22:15',
                    'instructor' => 'Prof. Adjei',
                    'year' => '2025',
                ]
            ]
        ];

        return $allLessons[$level] ?? [];
    }

    // Basic document info for preview page
    private function getDocumentForLesson($lessonId, $type)
    {
        $documents = [
            1 => [
                'pdf' => [
                    [
                        'id' => 1,
                        'title' => 'How to operate on woods - Part 1',
                        'file_path' => 'documents/lessons/basic-numbers-counting-1.pdf',
                        'file_size' => '2.5 MB',
                        'pages' => 15
                    ],
                    [
                        'id' => 2,
                        'title' => 'How to operate on woods - Part 2',
                        'file_path' => 'documents/lessons/basic-numbers-counting-2.pdf',
                        'file_size' => '1.8 MB',
                        'pages' => 12
                    ]
                ],
                'ppt' => [
                    [
                        'id' => 1,
                        'title' => 'How to operate on woods - Presentation',
                        'file_path' => 'documents/lessons/basic-numbers-counting.pptx',
                        'file_size' => '4.2 MB',
                        'slides' => 20
                    ]
                ]
            ],
            2 => [
                'pdf' => [
                    'title' => 'Animals and Their Homes Guide',
                    'file_path' => 'documents/lessons/animals-homes.pdf',
                    'file_size' => '1.8 MB',
                    'pages' => 12
                ],
                [
                    'title' => 'Animals and Their Homes Presentation',
                    'file_path' => 'documents/lessons/animals-homes.pptx',
                    'file_size' => '3.5 MB',
                    'slides' => 15
                ],
                'ppt' => [
                    'title' => 'Animals and Their Homes Presentation',
                    'file_path' => 'documents/lessons/animals-homes.pptx',
                    'file_size' => '3.5 MB',
                    'slides' => 15
                ]
            ],
            3 => [
                'pdf' => [
                    'title' => 'Fractions and Decimals Workbook',
                    'file_path' => 'documents/lessons/fractions-decimals.pdf',
                    'file_size' => '2.2 MB',
                    'pages' => 18
                ],
                [
                    'title' => 'Fractions and Decimals Presentation',
                    'file_path' => 'documents/lessons/fractions-decimals.pptx',
                    'file_size' => '4.1 MB',
                    'slides' => 22
                ],
                'ppt' => [
                    'title' => 'Fractions and Decimals Slides',
                    'file_path' => 'documents/lessons/fractions-decimals.pptx',
                    'file_size' => '4.1 MB',
                    'slides' => 22
                ]
            ],
            4 => [
                'pdf' => [
                    'title' => 'How to operate on woods',
                    'file_path' => 'documents/lessons/living-non-living.pdf',
                    'file_size' => '3.1 MB',
                    'pages' => 22
                ],
                [
                    'title' => 'How to operate on woods Presentation',
                    'file_path' => 'documents/lessons/living-non-living.pptx',
                    'file_size' => '5.8 MB',
                    'slides' => 25
                ],
                'ppt' => [
                    'title' => 'How to operate on woods',
                    'file_path' => 'documents/lessons/living-non-living.pptx',
                    'file_size' => '5.8 MB',
                    'slides' => 25
                ]
            ],
            5 => [
                'pdf' => [
                    'title' => 'Algebraic Expressions Manual',
                    'file_path' => 'documents/lessons/algebra.pdf',
                    'file_size' => '2.7 MB',
                    'pages' => 20
                ],
                [
                    'title' => 'Algebraic Expressions Presentation',
                    'file_path' => 'documents/lessons/algebra.pptx',
                    'file_size' => '4.8 MB',
                    'slides' => 28
                ],
                'ppt' => [
                    'title' => 'Algebraic Expressions Presentation',
                    'file_path' => 'documents/lessons/algebra.pptx',
                    'file_size' => '4.8 MB',
                    'slides' => 28
                ]
            ],
            6 => [
                'pdf' => [
                    'title' => 'Chemical Bonding Study Guide',
                    'file_path' => 'documents/lessons/chemistry.pdf',
                    'file_size' => '3.5 MB',
                    'pages' => 25
                ],
                [
                    'title' => 'How to operate on woods Presentation',
                    'file_path' => 'documents/lessons/living-non-living.pptx',
                    'file_size' => '5.8 MB',
                    'slides' => 25
                ],
                'ppt' => [
                    'title' => 'Chemical Bonding Slides',
                    'file_path' => 'documents/lessons/chemistry.pptx',
                    'file_size' => '6.2 MB',
                    'slides' => 35
                ]
            ]
        ];

        $docs = $documents[$lessonId][$type] ?? [];
        return is_array($docs) && isset($docs[0]) ? $docs[0] : $docs;
    }

    // Full document content for content viewer page
    private function getDocumentContentForLesson($lessonId, $type)
    {
        // Check if this is a user-created PPT
        if ($type === 'ppt' && request()->has('ppt_id')) {
            $pptId = request()->get('ppt_id');
            $presentations = session('user_presentations', []);
            
            if (isset($presentations[$lessonId][$pptId])) {
                return $presentations[$lessonId][$pptId];
            }
        }

        $documents = [
            1 => [
                'pdf' => [
                    'id' => 1,
                    'title' => 'Basic Numbers and Counting Study Guide',
                    'file_path' => 'documents/lessons/basic-numbers-counting.pdf',
                    'file_size' => '2.5 MB',
                    'pages' => [
                        [
                            'number' => 1,
                            'title' => 'Page 1',
                            'content' => 'Introduction to Numbers and Counting

Numbers are fundamental building blocks of mathematics. In this lesson, we will explore basic counting principles and number recognition. Understanding numbers helps us in daily life activities such as counting objects, telling time, and measuring quantities.

Basic counting starts with understanding the sequence: 1, 2, 3, 4, 5, and so on. Each number represents a specific quantity. When we count, we assign one number to each object in a group.

Key concepts covered:
- Number recognition from 1 to 100
- Counting forward and backward
- Understanding quantity and number relationships
- Basic addition and subtraction concepts'
                        ],
                        [
                            'number' => 2,
                            'title' => 'Page 2',
                            'content' => 'Counting Exercises and Practice

Practice makes perfect when learning to count. Here are some exercises to help reinforce counting skills:

Exercise 1: Count the objects
Look around your classroom and count different items like chairs, books, pencils, and windows.

Exercise 2: Number sequence
Fill in the missing numbers: 1, 2, _, 4, 5, _, 7, 8, _, 10

Exercise 3: Counting backwards
Practice counting backwards from 10: 10, 9, 8, 7, 6, 5, 4, 3, 2, 1

These exercises help develop number sense and mathematical thinking skills that will be useful throughout your education.'
                        ],
                        [
                            'number' => 3,
                            'title' => 'Page 3',
                            'content' => 'Advanced Counting Concepts

As we progress in mathematics, counting becomes more sophisticated. We learn to count by 2s, 5s, and 10s, which helps with multiplication and division later.

Skip counting examples:
- By 2s: 2, 4, 6, 8, 10, 12, 14, 16, 18, 20
- By 5s: 5, 10, 15, 20, 25, 30, 35, 40, 45, 50
- By 10s: 10, 20, 30, 40, 50, 60, 70, 80, 90, 100

Understanding these patterns helps students recognize mathematical relationships and prepares them for more complex mathematical concepts in future grades.'
                        ]
                    ]
                ],
                'ppt' => [
                    'id' => 1,
                    'title' => 'Basic Numbers and Counting',
                    'file_path' => 'documents/lessons/basic-numbers-counting.pptx',
                    'file_size' => '4.2 MB',
                    'subject' => 'Mathematics Gr-1',
                    'slides' => [
                        [
                            'number' => 1,
                            'title' => 'Basic Numbers and Counting',
                            'subtitle' => 'Mathematics Grade 1',
                            'type' => 'title'
                        ],
                        [
                            'number' => 2,
                            'title' => 'What are Numbers?',
                            'content' => 'Numbers help us count and measure things around us.',
                            'type' => 'definition'
                        ],
                        [
                            'number' => 3,
                            'title' => 'Counting Examples',
                            'content' => [
                                'Fingers on your hand',
                                'Books on the shelf',
                                'Students in class',
                                'Days in a week'
                            ],
                            'type' => 'list'
                        ]
                    ]
                ]
            ],
            2 => [
                'pdf' => [
                    'title' => 'Animals and Their Homes Study Guide',
                    'pages' => [
                        [
                            'number' => 1,
                            'title' => 'Page 1',
                            'content' => 'Animals and Their Homes

Animals live in different types of homes depending on their needs and environment. Just like humans need shelter, animals also need safe places to live, sleep, and raise their young.

Different animals have adapted to live in various environments:
- Some animals live in water
- Some live on land
- Some live in trees
- Some live underground

Understanding where animals live helps us learn about their behavior and how they survive in nature. This knowledge is important for protecting wildlife and their habitats.'
                        ],
                        [
                            'number' => 2,
                            'title' => 'Page 2',
                            'content' => 'Types of Animal Homes

Land Animals:
- Bears live in caves or dens
- Birds build nests in trees
- Rabbits live in burrows underground
- Lions live in dens

Water Animals:
- Fish live in rivers, lakes, and oceans
- Beavers build dams in streams
- Frogs live near ponds and wetlands

Each type of home provides protection from weather, predators, and a safe place to raise babies.'
                        ]
                    ]
                ],
                'ppt' => [
                    'title' => 'Animals and Their Homes',
                    'subject' => 'Science Grade 2',
                    'slides' => [
                        [
                            'number' => 1,
                            'title' => 'Animals and Their Homes',
                            'subtitle' => 'Science Grade 2',
                            'type' => 'title'
                        ],
                        [
                            'number' => 2,
                            'title' => 'Why do animals need homes?',
                            'content' => 'Animals need homes for protection, shelter, and raising their babies.',
                            'type' => 'definition'
                        ],
                        [
                            'number' => 3,
                            'title' => 'Examples of Animal Homes',
                            'content' => [
                                'Birds - Nests',
                                'Bears - Caves',
                                'Fish - Water',
                                'Bees - Hives'
                            ],
                            'type' => 'list'
                        ]
                    ]
                ]
            ],
            4 => [
                'pdf' => [
                    'title' => 'Living and Non-Living Organisms Study Guide',
                    'pages' => [
                        [
                            'number' => 1,
                            'title' => 'Page 1',
                            'content' => 'Sustainable Number Kendall Organic Properties Regulate Yang Cells Recreational Limits Metabolism Includes Fuel Provide Protein Repair

Provide oxygen and liquids sugar, breathe clean work, membrane oxygen. Plant has produced also both hormonal plant properties are also important. Provide that may necessary information oxygen may extremely nutritious balance contain they require. Kinda health contain also require may has work energy and liquid oxygen and liquid substance that require. Provide that may necessary information oxygen may extremely nutritious balance contain they require. Kinda health contain also require may has work energy and liquid oxygen and liquid substance that require.

That can be below, advise value but information also may liquid oxygen and liquid substance that require. Provide that may necessary information oxygen may extremely nutritious balance contain they require. Kinda health contain also require may has work energy and liquid oxygen and liquid substance that require. Provide that may necessary information oxygen may extremely nutritious balance contain they require. Kinda health contain also require may has work energy and liquid oxygen and liquid substance that require.

Provide that may necessary information oxygen may extremely nutritious balance contain they require. Kinda health contain also require may has work energy and liquid oxygen and liquid substance that require. Provide that may necessary information oxygen may extremely nutritious balance contain they require. Kinda health contain also require may has work energy and liquid oxygen and liquid substance that require.'
                        ],
                        [
                            'number' => 2,
                            'title' => 'Page 2',
                            'content' => 'Sustainable Number Kendall Organic Properties Regulate Yang Cells Recreational Limits Metabolism Includes Fuel Provide Protein Repair

Provide oxygen and liquids sugar, breathe clean work, membrane oxygen. Plant has produced also both hormonal plant properties are also important. Provide that may necessary information oxygen may extremely nutritious balance contain they require. Kinda health contain also require may has work energy and liquid oxygen and liquid substance that require.

That can be below, advise value but information also may liquid oxygen and liquid substance that require. Provide that may necessary information oxygen may extremely nutritious balance contain they require. Kinda health contain also require may has work energy and liquid oxygen and liquid substance that require.'
                        ],
                        [
                            'number' => 3,
                            'title' => 'Page 3',
                            'content' => 'Advanced Topics in Living Organisms

This section covers more complex aspects of living organisms including cellular respiration, photosynthesis, and metabolic processes. Understanding these fundamental processes is crucial for comprehending how life sustains itself.

Key concepts include:
- Energy conversion in cells
- Nutrient absorption and distribution
- Waste elimination processes
- Growth and reproduction mechanisms'
                        ]
                    ]
                ],
                'ppt' => [
                    'title' => 'Living and Non-Living Things',
                    'subject' => 'Grade 3 Science',
                    'slides' => [
                        [
                            'number' => 1,
                            'title' => 'Living and non-living things',
                            'subtitle' => 'Grade 3 Science',
                            'type' => 'title',
                            'background_image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=300&fit=crop'
                        ],
                        [
                            'number' => 2,
                            'title' => 'Definition',
                            'content' => 'Living things are the things the have life and performs life activities.',
                            'type' => 'definition',
                            'background_image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=300&fit=crop'
                        ],
                        [
                            'number' => 3,
                            'title' => 'Examples',
                            'content' => [
                                'Trees',
                                'Animals', 
                                'Humans'
                            ],
                            'type' => 'list',
                            'background_image' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400&h=300&fit=crop'
                        ],
                        [
                            'number' => 4,
                            'title' => 'Non-living things',
                            'content' => 'They do not have life and performs life activities',
                            'type' => 'definition'
                        ],
                        [
                            'number' => 5,
                            'title' => 'Example of non-living things',
                            'content' => [
                                'car',
                                'stone',
                                'toy',
                                'laptop'
                            ],
                            'type' => 'list'
                        ]
                    ]
                ]
            ]
        ];

        return $documents[$lessonId][$type] ?? [];
    }

    // Create new PPT
    public function createPpt($lessonId)
    {
        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return redirect()->route('dashboard.level-selection')
                ->with('error', 'Please select your grade level first.');
        }

        $selectedLevelGroup = session('selected_level_group');

        // Get lesson data
        $lessons = $this->getLessonsForLevel($selectedLevelGroup);
        $lesson = collect($lessons)->firstWhere('id', (int)$lessonId);

        if (!$lesson) {
            return redirect()->route('dashboard.digilearn')
                ->with('error', 'Lesson not found.');
        }

        // Create a new empty PPT structure
        $newPpt = [
            'id' => uniqid(),
            'title' => 'New Presentation',
            'subject' => $lesson['subject'],
            'slides' => [
                [
                    'number' => 1,
                    'type' => 'title',
                    'title' => 'New Presentation',
                    'subtitle' => $lesson['subject'],
                    'content' => ''
                ]
            ]
        ];

        return view('dashboard.ppt-creator', compact('lesson', 'newPpt', 'selectedLevelGroup'));
    }

    // Store new PPT
    public function storePpt(Request $request, $lessonId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slides' => 'required|array|min:1',
            'slides.*.type' => 'required|in:title,definition,list',
            'slides.*.title' => 'required|string',
        ]);

        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        // In a real application, you would save this to your database
        // For now, we'll store it in session
        $presentations = session('user_presentations', []);
        $pptId = uniqid();
        
        $presentations[$lessonId][$pptId] = [
            'id' => $pptId,
            'title' => $request->title,
            'slides' => $request->slides,
            'created_at' => now(),
            'updated_at' => now()
        ];

        session(['user_presentations' => $presentations]);

        return response()->json([
            'success' => true,
            'message' => 'Presentation created successfully!',
            'ppt_id' => $pptId,
            'redirect_url' => route('dashboard.lesson.document.content', [$lessonId, 'ppt']) . '?ppt_id=' . $pptId
        ]);
    }

    // Update existing PPT
    public function updatePpt(Request $request, $lessonId, $pptId)
    {
        $request->validate([
            'slides' => 'required|array'
        ]);

        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        $presentations = session('user_presentations', []);
        
        if (isset($presentations[$lessonId][$pptId])) {
            $presentations[$lessonId][$pptId]['slides'] = $request->slides;
            $presentations[$lessonId][$pptId]['updated_at'] = now();
            
            session(['user_presentations' => $presentations]);

            return response()->json([
                'success' => true,
                'message' => 'Presentation updated successfully!'
            ]);
        }

        return response()->json(['error' => 'Presentation not found'], 404);
    }

    public function saveDocumentChanges(Request $request, $lessonId, $type)
    {
        $request->validate([
            'changes' => 'required|array'
        ]);

        // Check if user has selected a level group
        if (!session('selected_level_group')) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        // In a real application, you would save these changes to your database
        // For now, we'll just store them in the session
        $documentChanges = session('document_changes', []);
        $documentChanges[$lessonId][$type] = $request->changes;
        session(['document_changes' => $documentChanges]);

        return response()->json([
            'success' => true,
            'message' => 'Document changes saved successfully'
        ]);
    }
}