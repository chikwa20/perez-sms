<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use Illuminate\Http\Request;

class ApplicantController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admins only.'], 403);
        }

        $applicants = Applicant::with('user')->get();

        return response()->json([
            'message'    => 'Applicants retrieved successfully.',
            'applicants' => $applicants,
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admins only.'], 403);
        }

        $validated = $request->validate([
            'user_id'        => 'required|exists:users,id',
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'middle_name'    => 'nullable|string|max:100',
            'date_of_birth'  => 'required|date',
            'gender'         => 'required|in:male,female,other',
            'contact_number' => 'required|string|max:20',
            'address'        => 'required|string',
            'school'         => 'required|string|max:200',
            'course'         => 'required|string|max:100',
            'year_level'     => 'required|integer|min:1|max:6',
            'gpa'            => 'required|numeric|min:1.00|max:4.00',
        ]);

        // Check if the user_id belongs to an admin or secretary
        $userToAdd = \App\Models\User::find($validated['user_id']);

        if ($userToAdd->isAdmin() || $userToAdd->isSecretary()) {
            return response()->json([
                'message' => 'Admin and Secretary accounts cannot be added as an applicant.',
            ], 422);
        }

        $applicant = Applicant::create($validated);

        return response()->json([
            'message'   => 'Applicant added successfully.',
            'applicant' => $applicant,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $applicant = Applicant::with('user', 'applications.scholarship')->find($id);

        if (!$applicant) {
            return response()->json(['message' => 'Applicant not found.'], 404);
        }

        if ($request->user()->isStudent()) {
            if ($request->user()->applicant?->id !== $applicant->id) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
        }

        return response()->json([
            'message'   => 'Applicant retrieved successfully.',
            'applicant' => $applicant,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admins only.'], 403);
        }

        $applicant = Applicant::find($id);

        if (!$applicant) {
            return response()->json(['message' => 'Applicant not found.'], 404);
        }

        $validated = $request->validate([
            'first_name'     => 'sometimes|string|max:100',
            'last_name'      => 'sometimes|string|max:100',
            'middle_name'    => 'nullable|string|max:100',
            'date_of_birth'  => 'sometimes|date',
            'gender'         => 'sometimes|in:male,female,other',
            'contact_number' => 'sometimes|string|max:20',
            'address'        => 'sometimes|string',
            'school'         => 'sometimes|string|max:200',
            'course'         => 'sometimes|string|max:100',
            'year_level'     => 'sometimes|integer|min:1|max:6',
            'gpa'            => 'sometimes|numeric|min:1.00|max:4.00',
        ]);

        $applicant->update($validated);

        return response()->json([
            'message'   => 'Applicant updated successfully.',
            'applicant' => $applicant,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admins only.'], 403);
        }

        $applicant = Applicant::find($id);

        if (!$applicant) {
            return response()->json(['message' => 'Applicant not found.'], 404);
        }

        $applicant->delete();

        return response()->json([
            'message' => 'Applicant deleted successfully.',
        ]);
    }
}