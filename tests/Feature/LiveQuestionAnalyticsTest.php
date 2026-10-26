<?php

namespace Tests\Feature;

use App\Models\LiveQuestion;
use App\Models\LiveQuestionResponse;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveQuestionAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_load_question_analytics_with_option_counts_and_participant_responses(): void
    {
        [$admin, $participant, $question, $correctOption] = $this->records();

        LiveQuestionResponse::create([
            'live_question_id' => $question->id,
            'live_question_option_id' => $correctOption->id,
            'user_id' => $participant->id,
            'is_correct' => true,
            'answered_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.live_questions.analytics', $question))
            ->assertOk()
            ->assertJsonPath('summary.total', 1)
            ->assertJsonPath('summary.correct', 1)
            ->assertJsonPath('summary.incorrect', 0)
            ->assertJsonPath('responses.0.user_name', $participant->name)
            ->assertJsonPath('responses.0.answer', $correctOption->option_text)
            ->assertJsonPath('responses.0.result', 'Correct')
            ->assertJsonPath('options.0.responses', 1);
    }

    public function test_analytics_returns_clean_empty_response_data(): void
    {
        [$admin, , $question] = $this->records();

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.live_questions.analytics', $question))
            ->assertOk()
            ->assertJsonPath('summary.total', 0)
            ->assertJsonCount(0, 'responses');
    }

    public function test_admin_can_download_question_specific_excel(): void
    {
        [$admin, , $question] = $this->records();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.live_questions.responses.export', $question))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    private function records(): array
    {
        $admin = User::factory()->create(['type' => 'admin', 'status' => 'active']);
        $participant = User::factory()->create(['type' => 'doctor', 'status' => 'active']);
        $module = Module::create(['title' => 'Live Session', 'slug' => 'live-session', 'status' => 'active']);
        $question = LiveQuestion::create([
            'module_id' => $module->id,
            'created_by' => $admin->id,
            'type' => 'quiz',
            'question' => 'Which option is correct?',
            'status' => 'stopped',
        ]);
        $correctOption = $question->options()->create(['option_text' => 'Correct answer', 'is_correct' => true, 'sort_order' => 0]);
        $question->options()->create(['option_text' => 'Other answer', 'is_correct' => false, 'sort_order' => 1]);

        return [$admin, $participant, $question, $correctOption];
    }
}
