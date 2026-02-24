<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    // GET ALL
    public function index()
    {
        return response()->json(Permission::all());
    }

    // CREATE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Permission created successfully',
            'data' => $permission
        ], 201);
    }

    // SHOW
    public function show(Permission $permission)
    {
        return response()->json($permission);
    }

    // UPDATE
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Permission updated successfully',
            'data' => $permission
        ]);
    }

    // DELETE
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted successfully'
        ]);
    }
}