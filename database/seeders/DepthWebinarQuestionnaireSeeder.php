<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\SurveyQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DepthWebinarQuestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $module = Module::where('slug', 'recognising-managing-depression-primary-care')->first();
            if (! $module) {
                throw new RuntimeException('The DEPTH webinar module was not found.');
            }

            $module->surveyQuestions()->update(['status' => 'inactive']);

            foreach ($this->questions() as $surveyType => $questions) {
                foreach ($questions as $index => $definition) {
                    $question = SurveyQuestion::updateOrCreate(
                        [
                            'module_id' => $module->id,
                            'survey_type' => $surveyType,
                            'question' => $definition['question'],
                        ],
                        [
                            'question_type' => $definition['type'] ?? 'single_choice',
                            'sort_order' => $index + 1,
                            'status' => 'active',
                        ]
                    );

                    $optionIds = collect($definition['options'])->map(function ($text, $optionIndex) use ($question) {
                        return $question->options()->updateOrCreate(
                            ['sort_order' => $optionIndex],
                            ['option_text' => $text]
                        )->id;
                    });
                    $question->options()->whereNotIn('id', $optionIds)->delete();

                    $rowIds = collect($definition['rows'] ?? [])->map(function ($text, $rowIndex) use ($question) {
                        return $question->rows()->updateOrCreate(
                            ['sort_order' => $rowIndex],
                            ['row_text' => $text]
                        )->id;
                    });
                    $question->rows()->when($rowIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $rowIds))->delete();
                }
            }
        });
    }

    private function questions(): array
    {
        return [
            'pre' => [
                [
                    'question' => 'How do you most commonly identify possible depression in patients with chronic medical conditions (e.g., cardiovascular disease, diabetes or gastrointestinal disorders)?',
                    'options' => [
                        'Validated screening tools (e.g., PHQ-2/PHQ-9)',
                        'Clinical interview and symptom assessment',
                        'Targeted assessment when symptoms or functional decline occur',
                        'Referral to a mental health professional',
                        'No consistent approach',
                    ],
                ],
                [
                    'question' => 'When do you most commonly screen for depression in patients with chronic medical conditions?',
                    'options' => [
                        'Routinely during chronic-disease reviews',
                        'When disease control or treatment adherence deteriorates',
                        'When symptoms or quality of life worsen',
                        'Only when depression is suspected or reported',
                        'I do not routinely screen',
                    ],
                ],
                [
                    'question' => 'Which clinical feature most often prompts you to assess depression in patients with chronic medical conditions?',
                    'options' => [
                        'Persistent low mood or loss of interest',
                        'Unexplained fatigue, sleep disturbance or physical symptoms',
                        'Poor treatment adherence or worsening disease control',
                        'Reduced functioning or repeated healthcare visits',
                        'Concern raised by family or caregivers',
                    ],
                ],
                [
                    'question' => 'Approximately what proportion of your patients with the following chronic conditions have suspected or diagnosed coexisting depression?',
                    'type' => 'matrix_single_choice',
                    'rows' => [
                        'Cardiovascular diseases',
                        'Diabetes mellitus',
                        'Gastrointestinal disorders',
                        'Other chronic medical conditions (e.g., respiratory, renal or neurological disorders)',
                    ],
                    'options' => ['<10%', '10–20%', '21–30%', '>30%', 'Not sure / Not assessed'],
                ],
                [
                    'question' => 'How do coexisting chronic medical conditions most influence your management of patients with depression?',
                    'options' => [
                        'Influence antidepressant selection',
                        'Complicate symptom assessment and diagnosis',
                        'Affect treatment adherence and disease management',
                        'Require closer monitoring and follow-up',
                        'Have limited influence on my treatment approach',
                    ],
                ],
                [
                    'question' => 'What is the most important consideration when selecting antidepressant treatment for patients with depression and coexisting chronic medical conditions?',
                    'options' => [
                        'Potential effects on the underlying medical condition',
                        'Drug interactions and concomitant medications',
                        'Overall efficacy, tolerability and safety',
                        'Patient-specific symptoms and previous treatment response',
                        'Need for specialist consultation or referral',
                    ],
                ],
            ],
            'post' => [
                [
                    'question' => 'Following the webinar, how has your understanding of identifying depression in patients with chronic medical conditions improved?',
                    'options' => [
                        'Significantly improved; I can better identify symptoms and use validated screening tools.',
                        'Improved; I can recognise common clinical indicators.',
                        'Somewhat improved; further training is needed.',
                        'No significant change.',
                    ],
                ],
                [
                    'question' => 'How likely are you to incorporate routine depression screening into the management of patients with chronic medical conditions?',
                    'options' => [
                        'Very likely, during routine chronic-disease reviews.',
                        'Likely, particularly in patients with poor disease control.',
                        'Likely, when symptoms or functional status deteriorate.',
                        'Unlikely to change my current practice.',
                    ],
                ],
                [
                    'question' => 'Which patient-related factors will you now pay greater attention to when assessing possible depression?',
                    'options' => [
                        'Persistent low mood or loss of interest.',
                        'Unexplained fatigue, sleep disturbances or physical symptoms.',
                        'Poor treatment adherence or worsening disease control.',
                        'Reduced functioning and quality of life.',
                        'A combination of physical and psychological symptoms.',
                    ],
                ],
                [
                    'question' => 'Following the webinar, how confident are you in recognising the association between depression and different chronic medical conditions?',
                    'options' => [
                        'Very confident across cardiovascular, metabolic, gastrointestinal and other chronic conditions.',
                        'Confident in most chronic medical conditions.',
                        'Moderately confident; further learning is required.',
                        'Not yet confident.',
                    ],
                ],
                [
                    'question' => 'How will the learning from this webinar influence your approach to antidepressant selection in patients with coexisting chronic medical conditions?',
                    'options' => [
                        'Greater consideration of drug interactions and concomitant medications.',
                        'Greater attention to cardiovascular and metabolic safety.',
                        'Better consideration of comorbidities, tolerability and patient-specific factors.',
                        'Greater use of specialist consultation when required.',
                        'No significant change in my current approach.',
                    ],
                ],
                [
                    'question' => 'What is the most important change you intend to implement in your clinical practice following this webinar?',
                    'options' => [
                        'Improve depression screening and early recognition.',
                        'Integrate mental health assessment into chronic-disease management.',
                        'Optimise individualised antidepressant selection and monitoring.',
                        'Improve coordination between medical and mental health specialists.',
                        'Strengthen patient education and long-term follow-up.',
                    ],
                ],
            ],
        ];
    }
}
