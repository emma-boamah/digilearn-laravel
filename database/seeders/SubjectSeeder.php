<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                'name' => 'Core Mathematics',
                'description' => 'Algebra, Geometry, Trigonometry, Statistics, and Calculus foundation for secondary level students.'
            ],
            [
                'name' => 'Elective Mathematics',
                'description' => 'Advanced algebra, vectors, mechanics, probability, and advanced calculus.'
            ],
            [
                'name' => 'English Language',
                'description' => 'Grammar, comprehension, essay writing, summary, and oral English communication.'
            ],
            [
                'name' => 'Integrated Science',
                'description' => 'Fundamentals of biology, chemistry, physics, and agricultural science.'
            ],
            [
                'name' => 'Physics',
                'description' => 'Mechanics, heat, optics, electricity, magnetism, modern physics, and practical experiments.'
            ],
            [
                'name' => 'Chemistry',
                'description' => 'General, organic, physical chemistry, stoichiometry, and quantitative analysis.'
            ],
            [
                'name' => 'Biology',
                'description' => 'Cell biology, genetics, ecology, plant physiology, and human anatomy.'
            ],
            [
                'name' => 'Social Studies',
                'description' => 'Civic education, socio-economic development, culture, and governance.'
            ],
            [
                'name' => 'Information & Communication Technology (ICT)',
                'description' => 'Computer literacy, software applications, web design, networking, and basic programming.'
            ],
            [
                'name' => 'Economics',
                'description' => 'Microeconomics, macroeconomics, international trade, inflation, and development economics.'
            ],
            [
                'name' => 'Financial Accounting',
                'description' => 'Bookkeeping, final accounts, trial balance, partnership accounts, and company accounts.'
            ],
            [
                'name' => 'Cost Accounting & Business Management',
                'description' => 'Costing principles, budgeting, business organisation, and entrepreneurial management.'
            ],
            [
                'name' => 'Literature-in-English',
                'description' => 'Prose, drama, poetry analysis, literary appreciation, and set text studies.'
            ],
            [
                'name' => 'French',
                'description' => 'French vocabulary, grammar, reading comprehension, composition, and oral expression.'
            ],
            [
                'name' => 'Geography',
                'description' => 'Physical geography, map reading, human geography, and environmental studies.'
            ],
            [
                'name' => 'History',
                'description' => 'African history, modern world history, and political development.'
            ],
            [
                'name' => 'Government & Civics',
                'description' => 'Political science, constitutions, arms of government, and public administration.'
            ],
        ];

        foreach ($subjects as $subjectData) {
            Subject::firstOrCreate(
                ['name' => $subjectData['name']],
                ['description' => $subjectData['description']]
            );
        }
    }
}
