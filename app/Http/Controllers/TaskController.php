<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        try {
            $limit = $request->integer('limit', 30);
            $perPage = min($limit, 50);

            $query = Task::query();

            if ($request->filled('status')) {
                $status = $request->query('status');
                if (in_array($status, Task::STATUSES)) {
                    $query->where('status', $status);
                }
            }

            if ($request->filled('search')) {
                $search = $request->query('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('sort')) {
                $allowedSortFields = ['id', 'title', 'status', 'created_at', 'updated_at'];
                $sortParam = $request->query('sort');

                if (str_contains($sortParam, ',')) {
                    [$field, $direction] = explode(',', $sortParam);
                    $direction = in_array(strtolower($direction), ['asc', 'desc'])
                        ? strtolower($direction)
                        : 'desc';
                } else {
                    $field = $sortParam;
                    $direction = 'desc';
                }

                if (in_array($field, $allowedSortFields)) {
                    $query->orderBy($field, $direction);
                }
            } else {
                $query->latest();
            }

            $tasks = $query->paginate($perPage);

            return TaskResource::collection($tasks);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch tasks: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve tasks'
            ], 500);
        }
    }

    public function store(StoreTaskRequest $request)
    {
        try {
            $task = Task::create($request->validated());

            return response()->json([
                'data' => new TaskResource($task->refresh()),
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Task creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create task'
            ], 500);
        }
    }

    public function show($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        $task->update($request->validated());

        return new TaskResource($task->fresh());
    }

    public function destroy($id)
    {
        try {
            $deleted = Task::destroy($id);

            if (!$deleted) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Task deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Task deletion failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete task'
            ], 500);
        }
    }
}