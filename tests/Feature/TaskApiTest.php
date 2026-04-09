<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_get_all_tasks()
    {
        Task::factory()->count(10)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data');
    }

    public function test_it_can_create_a_task()
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description'
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Test Task')
            ->assertJsonPath('data.description', 'Test Description');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description'
        ]);
    }

    public function test_it_validates_title_is_required()
    {
        $response = $this->postJson('/api/tasks', [
            'description' => 'No title here'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_it_can_get_single_task()
    {
        $task = Task::factory()->create();

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $task->id)
            ->assertJsonPath('data.title', $task->title);
    }

    public function test_it_returns_404_when_task_not_found()
    {
        $response = $this->getJson('/api/tasks/' . rand(100000, 999999));

        $response->assertStatus(404)
            ->assertJson(['message' => 'Task not found']);
    }

    public function test_it_can_update_a_task()
    {
        $task = Task::factory()->create();

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Title',
            'status' => 'completed'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title'
        ]);
    }

    public function test_it_can_delete_a_task()
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Task deleted successfully']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_it_can_filter_tasks_by_status()
    {
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'completed']);
        Task::factory()->create(['status' => 'pending']);

        $response = $this->getJson('/api/tasks?status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_it_can_search_tasks()
    {
        Task::factory()->create(['title' => 'Special Task']);
        Task::factory()->create(['title' => 'Another Task']);

        $response = $this->getJson('/api/tasks?search=Special');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Special Task');
    }
}