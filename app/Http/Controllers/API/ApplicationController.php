<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Scholarship;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    // Admin and Secretary can view all applications
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isSecretary()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $applications = Application::with('applicant.user', 'scholarship', 'actionedBy')->get();

        return response()->json([
            'message'      => 'Applications retrieved successfully.',
            'applications' => $applications,
        ]);
    }

    // Student: Submit a scholarship application
    public function submit(Request $request)
    {
        if (!$request->user()->isStudent()) {
            return response()->json(['message' => 'Unauthorized. Students only.'], 403);
        }

        $applicant = $request->user()->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'You need an applicant profile first.'], 422);
        }

        $validated = $request->validate([
            'scholarship_id' => 'required|exists:scholarships,id',
        ]);

        $scholarship = Scholarship::find($validated['scholarship_id']);

        if ($scholarship->status !== 'open') {
            return response()->json(['message' => 'This scholarship is currently closed.'], 422);
        }

        if ($scholarship->deadline->isPast()) {
            return response()->json(['message' => 'The deadline for this scholarship has passed.'], 422);
        }

        if ($applicant->gpa < $scholarship->min_gpa) {
            return response()->json(['message' => 'Your GPA does not meet the minimum requirement.'], 422);
        }

        if ($scholarship->getRemainingSlots() <= 0) {
            return response()->json(['message' => 'No available slots for this scholarship.'], 422);
        }

        $existing = Application::where('applicant_id', $applicant->id)
            ->where('scholarship_id', $scholarship->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'You have already applied for this scholarship.'], 422);
        }

        $application = Application::create([
            'applicant_id'   => $applicant->id,
            'scholarship_id' => $scholarship->id,
            'status'         => 'pending',
        ]);

        return response()->json([
            'message'     => 'Application submitted successfully.',
            'application' => $application->load('scholarship'),
        ], 201);
    }

    // Student: View own application status
    public function myApplications(Request $request)
    {
        if (!$request->user()->isStudent()) {
            return response()->json(['message' => 'Unauthorized. Students only.'], 403);
        }

        $applicant = $request->user()->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'No applicant profile found.'], 404);
        }

        $applications = Application::with('scholarship')
            ->where('applicant_id', $applicant->id)
            ->get();

        return response()->json([
            'message'      => 'Applications retrieved successfully.',
            'applications' => $applications,
        ]);
    }

    // Admin and Secretary: Approve an application
    public function approve(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isSecretary()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $application = Application::with('scholarship')->find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found.'], 404);
        }

        if ($application->status !== 'pending') {
            return response()->json(['message' => 'Only pending applications can be approved.'], 422);
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string',
        ]);

        $application->update([
            'status'      => 'approved',
            'remarks'     => $validated['remarks'] ?? null,
            'actioned_by' => $request->user()->id,
        ]);

        return response()->json([
            'message'     => 'Application approved successfully.',
            'application' => $application->fresh('applicant.user', 'scholarship', 'actionedBy'),
        ]);
    }

    // Admin and Secretary: Reject an application
    public function reject(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isSecretary()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $application = Application::find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found.'], 404);
        }

        if ($application->status !== 'pending') {
            return response()->json(['message' => 'Only pending applications can be rejected.'], 422);
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string',
        ]);

        $application->update([
            'status'      => 'rejected',
            'remarks'     => $validated['remarks'] ?? null,
            'actioned_by' => $request->user()->id,
        ]);

        return response()->json([
            'message'     => 'Application rejected.',
            'application' => $application->fresh('applicant.user', 'scholarship', 'actionedBy'),
        ]);
    }

    // Admin and Secretary: Update an application
    public function update(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isSecretary()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $application = Application::find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found.'], 404);
        }

        $validated = $request->validate([
            'status'  => 'sometimes|in:pending,approved,rejected',
            'remarks' => 'nullable|string',
        ]);

        $application->update(array_merge($validated, [
            'actioned_by' => $request->user()->id,
        ]));

        return response()->json([
            'message'     => 'Application updated successfully.',
            'application' => $application->fresh('applicant.user', 'scholarship', 'actionedBy'),
        ]);
    }
}