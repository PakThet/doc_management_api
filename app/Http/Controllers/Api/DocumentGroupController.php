<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class DocumentGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        $query = QueryBuilder::for(DocumentGroup::class)
            ->withCount('documents')
            ->allowedFilters([
                'name',
                AllowedFilter::exact('branch_id')
            ])
            ->allowedSorts(['name', 'created_at'])
            ->allowedIncludes(['documents']);

        if (!$user->hasRole('super-admin')) {
            $query->where('branch_id', $user->branch_id);
        }

        $groups = $query->paginate(request()->integer('per_page', 15))
            ->appends(request()->query());

        return response()->json($groups);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['branch_id'] = Auth::user()->branch_id;
        $validated['created_by'] = Auth::id();

        $group = DocumentGroup::create($validated);
        return response()->json($group->load('documents'), 201);
    }

    public function show(DocumentGroup $documentGroup): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user->hasRole('super-admin') && $documentGroup->branch_id !== Auth::user()->branch_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $group = QueryBuilder::for(DocumentGroup::class)
            ->allowedIncludes(['documents'])
            ->findOrFail($documentGroup->id);
        return response()->json($group);
    }

    public function update(Request $request, DocumentGroup $documentGroup): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user->hasRole('super-admin') && $documentGroup->branch_id !== Auth::user()->branch_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['updated_by'] = Auth::id();
        $documentGroup->update($validated);

        return response()->json($documentGroup->load('documents'));
    }

    public function destroy(DocumentGroup $documentGroup): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user->hasRole('super-admin') && $documentGroup->branch_id !== Auth::user()->branch_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $documentGroup->delete();
        return response()->json(['message' => 'Group deleted successfully']);
    }

    public function restore($id): JsonResponse
    {
        $group = DocumentGroup::withTrashed()->findOrFail($id);

        /** @var User|null $user */
        $user = Auth::user();
        if (!$user->hasRole('super-admin') && $group->branch_id !== Auth::user()->branch_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $group->restore();
        return response()->json(['message' => 'Group restored successfully', 'group' => $group->load('documents')]);
    }
}
