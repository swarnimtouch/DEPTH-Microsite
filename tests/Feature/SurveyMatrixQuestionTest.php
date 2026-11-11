<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Module;
use App\Models\SurveyQuestion;
use App\Models\SurveySubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyMatrixQuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_submit_one_answer_for_every_matrix_row(): void
    {
        [$user, $module, $single, $matrix] = $this->records();
        $singleOption = $single->options()->create(['option_text' => 'Yes', 'sort_order' => 0]);
        $single->options()->create(['option_text' => 'No', 'sort_order' => 1]);
        $firstRow = $matrix->rows()->create(['row_text' => 'Cardiovascular diseases', 'sort_order' => 0]);
        $secondRow = $matrix->rows()->create(['row_text' => 'Diabetes mellitus', 'sort_order' => 1]);
        $low = $matrix->options()->create(['option_text' => '<10%', 'sort_order' => 0]);
        $high = $matrix->options()->create(['option_text' => '>30%', 'sort_order' => 1]);

        $this->actingAs($user, 'web')->postJson(route('modules.surveys.submit', [$module->slug, 'pre']), [
            'answers' => [
                $single->id => $singleOption->id,
                $matrix->id => [$firstRow->id => $low->id, $secondRow->id => $high->id],
            ],
        ])->assertOk();

        $submission = SurveySubmission::with('answers')->firstOrFail();
        $this->assertCount(3, $submission->answers);
        $this->assertDatabaseHas('survey_answers', [
            'survey_question_id' => $matrix->id,
            'survey_question_row_id' => $secondRow->id,
            'row_text' => 'Diabetes mellitus',
            'answer_text' => '>30%',
        ]);
    }

    public function test_every_matrix_row_is_required(): void
    {
        [$user, $module, $single, $matrix] = $this->records();
        $singleOption = $single->options()->create(['option_text' => 'Yes', 'sort_order' => 0]);
        $single->options()->create(['option_text' => 'No', 'sort_order' => 1]);
        $firstRow = $matrix->rows()->create(['row_text' => 'Cardiovascular diseases', 'sort_order' => 0]);
        $matrix->rows()->create(['row_text' => 'Diabetes mellitus', 'sort_order' => 1]);
        $low = $matrix->options()->create(['option_text' => '<10%', 'sort_order' => 0]);
        $matrix->options()->create(['option_text' => '>30%', 'sort_order' => 1]);

        $this->actingAs($user, 'web')->postJson(route('modules.surveys.submit', [$module->slug, 'pre']), [
            'answers' => [
                $single->id => $singleOption->id,
                $matrix->id => [$firstRow->id => $low->id],
            ],
        ])->assertStatus(422);

        $this->assertDatabaseCount('survey_submissions', 0);
    }

    private function records(): array
    {
        $course = Course::create(['title' => 'DEPTH', 'status' => 'active', 'microsite_access_enabled' => true]);
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Recognising & Managing Depression in Primary Care',
            'slug' => 'recognising-managing-depression-primary-care',
            'status' => 'active',
        ]);
        $user = User::factory()->create(['type' => 'doctor', 'status' => 'active']);
        $single = SurveyQuestion::create([
            'module_id' => $module->id,
            'survey_type' => 'pre',
            'question' => 'Single choice question',
            'question_type' => 'single_choice',
            'sort_order' => 1,
            'status' => 'active',
        ]);
        $matrix = SurveyQuestion::create([
            'module_id' => $module->id,
            'survey_type' => 'pre',
            'question' => 'Matrix question',
            'question_type' => 'matrix_single_choice',
            'sort_order' => 2,
            'status' => 'active',
        ]);

        return [$user, $module, $single, $matrix];
    }
}
