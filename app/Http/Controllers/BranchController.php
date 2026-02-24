<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Http\Requests\BranchRequest;
use App\Http\Resources\BranchResource;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BranchController extends Controller
{
    // GET ALL
    public function index()
    {
        $branches = QueryBuilder::for(Branch::class)
            ->with('organization')
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('organization_id'),
                AllowedFilter::partial('email'),
            ])
            ->allowedSorts([
                'name',
                'created_at',
                'established_date'
            ])
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return BranchResource::collection($branches);
    }


    // STORE
    public function store(BranchRequest $request)
    {
        $branch = Branch::create($request->validated());

        return response()->json([
            'message' => 'Branch created successfully',
            'data' => new BranchResource($branch->load('organization'))
        ], 201);
    }


    // SHOW
    public function show(Branch $branch)
    {
        $branch->load('organization');

        return new BranchResource($branch);
    }


    // UPDATE
    public function update(BranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return response()->json([
            'message' => 'Branch updated successfully',
            'data' => new BranchResource($branch->load('organization'))
        ]);
    }

    // DELETE
    public function destroy(Branch $branch)
    {
        $branch->delete(); // soft delete

        return response()->json([
            'message' => 'Branch deleted successfully'
        ]);
    }

}
