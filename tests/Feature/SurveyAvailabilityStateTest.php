<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Module;
use App\Models\SurveyQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyAvailabilityStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_survey_state_reflects_admin_status_changes_without_caching(): void
    {
        $course = Course::create(['title' => 'DEPTH', 'status' => 'active', 'microsite_access_enabled' => true]);
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Depression module',
            'slug' => 'depression-module',
            'status' => 'active',
        ]);
        $user = User::factory()->create(['type' => 'doctor', 'status' => 'active']);
        $question = SurveyQuestion::create([
            'module_id' => $module->id,
            'survey_type' => 'post',
            'question' => 'Was this module useful?',
            'question_type' => 'single_choice',
            'sort_order' => 1,
            'status' => 'inactive',
        ]);
        $question->options()->create(['option_text' => 'Yes', 'sort_order' => 0]);
        $question->options()->create(['option_text' => 'No', 'sort_order' => 1]);

        $stateUrl = route('modules.surveys.state', $module->slug);

        $this->actingAs($user, 'web')
            ->getJson($stateUrl)
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->assertJsonPath('post.enabled', false);

        $question->update(['status' => 'active']);

        $this->getJson($stateUrl)
            ->assertOk()
            ->assertJsonPath('post.enabled', true)
            ->assertJsonCount(1, 'post.questions');
    }
}
