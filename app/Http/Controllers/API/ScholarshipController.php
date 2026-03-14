<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    public function index()
    {
        $scholarships = Scholarship::all();

        return response()->json([
            'message'      => 'Scholarships retrieved successfully.',
            'scholarships' => $scholarships,
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admins only.'], 403);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'required|string',
            'amount'      => 'required|numeric|min:0',
            'slots'       => 'required|integer|min:1',
            'min_gpa'     => 'required|numeric|min:1.00|max:4.00',
            'deadline'    => 'required|date',
            'status'      => 'sometimes|in:open,closed',
        ]);

        $scholarship = Scholarship::create($validated);

        return response()->json([
            'message'     => 'Scholarship created successfully.',
            'scholarship' => $scholarship,
        ], 201);
    }

    public function show($id)
    {
        $scholarship = Scholarship::find($id);

        if (!$scholarship) {
            return response()->json(['message' => 'Scholarship not found.'], 404);
        }

        return response()->json([
            'message'     => 'Scholarship retrieved successfully.',
            'scholarship' => $scholarship,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admins only.'], 403);
        }

        $scholarship = Scholarship::find($id);

        if (!$scholarship) {
            return response()->json(['message' => 'Scholarship not found.'], 404);
        }

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:200',
            'description' => 'sometimes|string',
            'amount'      => 'sometimes|numeric|min:0',
            'slots'       => 'sometimes|integer|min:1',
            'min_gpa'     => 'sometimes|numeric|min:1.00|max:4.00',
            'deadline'    => 'sometimes|date',
            'status'      => 'sometimes|in:open,closed',
        ]);

        $scholarship->update($validated);

        return response()->json([
            'message'     => 'Scholarship updated successfully.',
            'scholarship' => $scholarship,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admins only.'], 403);
        }

        $scholarship = Scholarship::find($id);

        if (!$scholarship) {
            return response()->json(['message' => 'Scholarship not found.'], 404);
        }

        $scholarship->delete();

        return response()->json([
            'message' => 'Scholarship deleted successfully.',
        ]);
    }
}