<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Todo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    public function index (Request $recuest)
    {
        $data = Todo::get();

        return $data;
    }

    public function store (Request $request)
    {
        $todo = new Todo();

        $user = Auth::user();

        $todo->create([
            'user_id' => $user->id,
            'name' => $request->name,
            'startDate' => $request->startDate,
            'deadline' => $request->deadline,
            'priority' => $request->priority,
            'status' => $request->status
        ]);
    }

    public function update (Request $request)
    {
        $user = Auth::user();
        Todo::where('name', $request->editName)->update([
            'user_id' => $user->id,
            'name' => $request->name,
            'startDate' => $request->startDate,
            'deadline' => $request->deadline,
            'priority' => $request->priority,
            'status' => $request->status
        ]);
    }

    public function delete (Request $request)
    {
        Todo::where('name', $request->name)->delete();
    }
}
