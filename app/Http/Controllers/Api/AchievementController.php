<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function index()
    {
        return response()->json(
            Achievement::with('employee')->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'achievement_date' => 'required|date'
        ]);

        $achievement = Achievement::create($request->all());

        return response()->json([
            'message' => 'Achievement created successfully',
            'data' => $achievement
        ]);
    }

    public function show($id)
    {
        $achievement = Achievement::with('employee')->findOrFail($id);

        return response()->json($achievement);
    }

    public function update(Request $request, $id)
    {
        $achievement = Achievement::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'achievement_date' => 'required|date'
        ]);

        $achievement->update($request->all());

        return response()->json([
            'message' => 'Achievement updated',
            'data' => $achievement
        ]);
    }

    public function destroy($id)
    {
        Achievement::destroy($id);

        return response()->json([
            'message' => 'Achievement deleted'
        ]);
    }
}