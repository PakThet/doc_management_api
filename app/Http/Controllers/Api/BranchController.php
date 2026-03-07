<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\BranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BranchController extends BaseController
{
    public function index(Request $request)
    {
        $branches = QueryBuilder::for(Branch::class)
            ->with('organization')
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('organization_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::partial('code'),
                AllowedFilter::partial('city'),
            ])
            ->allowedSorts(['id', 'name', 'code', 'city', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->sendPaginated(
            $branches,
            BranchResource::collection($branches->items()),
            'Branches retrieved successfully'
        );
    }

    public function store(BranchRequest $request)
    {
        $branch = Branch::create($request->validated());

        return $this->sendResponse(new BranchResource($branch), 'Branch created successfully', 201);
    }

    public function show(Branch $branch)
    {
        $branch->load('organization')->loadCount(['employees', 'documents']);

        return $this->sendResponse(new BranchResource($branch), 'Branch retrieved successfully');
    }

    public function update(BranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return $this->sendResponse(new BranchResource($branch), 'Branch updated successfully');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return $this->sendResponse(null, 'Branch deleted successfully');
    }
}
