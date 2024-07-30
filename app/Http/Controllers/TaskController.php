<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        return view('tasks.index');
    }

    public function store(Request $request)
    {
        $request->validate(['task' => 'required|unique:tasks,task']);
        Task::create(['task' => $request->task]);
        return response()->json(['message' => 'Task added successfully']);
    }

    public function update(Task $task)
    {
        $task->completed = !$task->completed;
        $task->save();
        return response()->json(['message' => 'Task status updated']);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }

    public function showAll(Request $request)
    {
        $showAll = $request->input('showAll', false);

        if($showAll === 'true') {
            $tasks = Task::all();
        } else {
            $tasks = Task::where('completed', 0)->get();
        }
        return response()->json($tasks);
    }
}
