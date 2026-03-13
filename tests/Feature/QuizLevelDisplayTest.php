<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Level;
use App\Models\LevelGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class QuizLevelDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $group = LevelGroup::create([
            'title' => 'Grade 1-3',
            'slug' => 'grade-1-3',
            'description' => 'Test Group'
        ]);
        
        Level::create(['title' => 'Primary 1', 'slug' => 'primary-1', 'level_group_id' => $group->id, 'order' => 1]);
        Level::create(['title' => 'Primary 2', 'slug' => 'primary-2', 'level_group_id' => $group->id, 'order' => 2]);
        Level::create(['title' => 'Primary 3', 'slug' => 'primary-3', 'level_group_id' => $group->id, 'order' => 3]);
    }

    public function test_quiz_dashboard_displays_level_tabs()
    {
        $user = User::factory()->create([
            'grade' => 'Primary 1',
            'is_superuser' => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['selected_level_group' => 'grade-1-3'])
            ->get(route('quiz.index'));

        $response->assertStatus(200);
        
        // Assert that the grades are rendered
        $response->assertSee('Primary 1');
        $response->assertSee('Primary 2');
        $response->assertSee('Primary 3');

        // Assert that abbreviations work (from the fallback function or global)
        $response->assertSee('P1');
        $response->assertSee('P2');
        $response->assertSee('P3');
    }
}
