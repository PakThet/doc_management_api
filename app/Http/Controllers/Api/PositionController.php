<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class PositionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');

        $this->middleware('permission:view positions')->only(['index', 'show']);
        $this->middleware('permission:create positions')->only(['store']);
        $this->middleware('permission:edit positions')->only(['update']);
        $this->middleware('permission:delete positions')->only(['destroy']);
    }

    /**
     * List positions
     */
    public function index(): JsonResponse
    {
        $positions = QueryBuilder::for(Position::class)
            ->allowedFilters([
                AllowedFilter::partial('position_title'),
                AllowedFilter::exact('department_id'),
                AllowedFilter::exact('level'),
            ])
            ->allowedSorts([
                'position_title',
                'level',
                'created_at'
            ])
            ->allowedIncludes([
                'department',
                'employees',
                'creator'
            ])
            ->paginate(request()->integer('per_page', 15))
            ->appends(request()->query());

        return response()->json($positions);
    }

    /**
     * Create position
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'position_title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'level' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {

            $validated['created_by'] = Auth::id();

            $position = Position::create($validated);

            DB::commit();

            return response()->json([
                'message' => 'Position created successfully',
                'data' => $position
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create position',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show position
     */
    public function show(Position $position): JsonResponse
    {
        $position->load([
            'department',
            'employees',
            'creator'
        ]);

        return response()->json($position);
    }

    /**
     * Update position
     */
    public function update(Request $request, Position $position): JsonResponse
    {
        $validated = $request->validate([
            'position_title' => 'sometimes|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'level' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {

            $position->update($validated);

            DB::commit();

            return response()->json([
                'message' => 'Position updated successfully',
                'data' => $position
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update position',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete position
     */
    public function destroy(Position $position): JsonResponse
    {
        $position->delete();

        return response()->json([
            'message' => 'Position deleted successfully'
        ]);
    }
}