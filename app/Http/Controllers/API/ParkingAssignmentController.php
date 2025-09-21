<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ParkingAssignment;
use App\Models\ParkingSlot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class ParkingAssignmentController extends Controller
{
    // Get all assignments for a given layout (for frontend search/filter)
    public function byLayout($layoutId)
    {
        $assignments = ParkingAssignment::with(['parkingSlot', 'user'])
            ->whereHas('parkingSlot', function($q) use ($layoutId) {
                $q->where('layout_id', $layoutId);
            })
            ->get();

        return response()->json($assignments);
    }
    public function active(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $activeAssignment = ParkingAssignment::where('user_id', $userId)
                ->where('status', 'active')
                ->with(['parkingSlot', 'user'])
                ->first();

            return response()->json([
                'message' => $activeAssignment ? 'Active assignment found' : 'No active assignment found',
                'data' => $activeAssignment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving active assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // ...existing code...

    public function index()
    {
        return response()->json(
            ParkingAssignment::with(['parkingSlot', 'user'])
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'parking_slot_id' => 'nullable|exists:parking_slots,id',
                'user_id' => 'nullable|exists:users,id',
                'guest_name' => 'nullable|string',
                'guest_contact' => 'nullable|string',
                'vehicle_plate' => [
                    'nullable',
                    'string',
                    function ($attribute, $value, $fail) {
                        if ($value) {
                            $existingAssignment = ParkingAssignment::where('vehicle_plate', $value)
                                ->whereIn('status', ['active', 'reserved'])
                                ->first();
                            if ($existingAssignment) {
                                $fail("A vehicle with plate number {$value} is already parked or has a reservation in another layout.");
                            }
                        }
                    },
                ],
                'vehicle_type' => 'nullable|string|in:car,motorcycle,bicycle',
                'vehicle_color' => 'nullable|string',
                'start_time' => 'nullable|date',
                'end_time' => 'nullable|date|after:start_time',
                'purpose' => 'nullable|string',
                'faculty_position' => 'nullable|string',
                'assignee_type' => 'nullable|string|in:guest,faculty',
                'assignment_type' => 'nullable|string|in:assign,reserve'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Check if slot is available
            $slot = ParkingSlot::findOrFail($request->parking_slot_id);
            if ($slot->space_status !== 'available') {
                throw new \Exception('Parking slot is not available');
            }

            // Validate vehicle type against space type
            if ($slot->space_type === 'compact') {
                if (!in_array($request->vehicle_type, ['motorcycle', 'bicycle'])) {
                    throw new \Exception("Cannot assign {$request->vehicle_type} to a compact space. Space #{$slot->space_number} is designed for motorcycles and bicycles only.");
                }
            }
            if ($slot->space_type === 'standard') {
                if ($request->vehicle_type !== 'car') {
                    throw new \Exception("Cannot assign {$request->vehicle_type} to a standard space. Space #{$slot->space_number} is designed for cars only.");
                }
            }

            // Check for duplicate plate numbers in active/reserved assignments
            $existingAssignment = ParkingAssignment::where('vehicle_plate', $request->vehicle_plate)
                ->whereIn('status', ['active', 'reserved'])
                ->first();
            if ($existingAssignment) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'vehicle_plate' => ['A vehicle with this plate number is already parked or reserved in another space.']
                    ]
                ], 422);
            }

            // Determine the status based on assignment type
            $status = $request->assignment_type === 'reserve' ? 'reserved' : 'active';

            // Create assignment
            $assignment = ParkingAssignment::create([
                'parking_slot_id' => $request->parking_slot_id,
                'user_id' => $request->user_id,
                'guest_name' => $request->guest_name,
                'guest_contact' => $request->guest_contact,
                'vehicle_plate' => $request->vehicle_plate,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_color' => $request->vehicle_color,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'status' => $status,
                'purpose' => $request->purpose,
                'assignee_type' => $request->assignee_type,
                'assignment_type' => $request->assignment_type,
                'faculty_position' => $request->faculty_position
            ]);

            // Update slot status based on assignment type
            $slotStatus = $request->assignment_type === 'reserve' ? 'reserved' : 'occupied';
            $slot->update(['space_status' => $slotStatus]);

            DB::commit();

            return response()->json([
                'message' => 'Parking assignment created successfully',
                'assignment' => $assignment->load(['parkingSlot', 'user'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Parking assignment creation error', [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response()->json([
                'message' => 'Error creating parking assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, ParkingAssignment $assignment)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'guest_name' => 'required_without:user_id|nullable|string',
            'guest_contact' => 'nullable|string',
            'vehicle_plate' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'vehicle_color' => 'nullable|string',
            'end_time' => 'nullable|date|after:start_time',
            'purpose' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $assignment->update($request->all());

            DB::commit();

            return response()->json([
                'message' => 'Parking assignment updated successfully',
                'assignment' => $assignment->load(['parkingSlot', 'user'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating parking assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function switchParking(Request $request, $assignmentId)
    {
        $request->validate([
            'new_slot_id' => 'required|exists:parking_slots,id',
            'target_assignment_id' => 'nullable|exists:parking_assignments,id'
        ]);

        try {
            DB::beginTransaction();

            // Find the source assignment
            $sourceAssignment = ParkingAssignment::findOrFail($assignmentId);
            $sourceSlot = $sourceAssignment->parkingSlot;

            // Find the target slot and possibly its assignment
            $targetSlot = ParkingSlot::findOrFail($request->new_slot_id);
            $targetAssignment = null;

            // Validate vehicle type against target space type
            if ($targetSlot->space_type === 'compact') {
                if (!in_array($sourceAssignment->vehicle_type, ['motorcycle', 'bicycle'])) {
                    throw new \Exception("Cannot move {$sourceAssignment->vehicle_type} to a compact space. This space (#{$targetSlot->space_number}) is designed for motorcycles and bicycles only.");
                }
            }
            if ($targetSlot->space_type === 'standard') {
                if ($sourceAssignment->vehicle_type !== 'car') {
                    throw new \Exception("Cannot move {$sourceAssignment->vehicle_type} to a standard space. This space (#{$targetSlot->space_number}) is designed for cars only.");
                }
            }


            // If the target slot is occupied/reserved, get its assignment
            if (in_array($targetSlot->space_status, ['occupied', 'reserved']) && $request->target_assignment_id) {
                $targetAssignment = ParkingAssignment::findOrFail($request->target_assignment_id);

                // Prevent switching if vehicle types are different (case-insensitive, non-empty)
                $sourceType = $sourceAssignment->vehicle_type ? strtolower($sourceAssignment->vehicle_type) : null;
                $targetType = $targetAssignment->vehicle_type ? strtolower($targetAssignment->vehicle_type) : null;
                if (!$sourceType || !$targetType || $sourceType !== $targetType) {
                    throw new \Exception('Cannot switch between vehicles of different types or if vehicle type is missing.');
                }

                // Swap the parking slots between assignments
                $targetAssignment->update([
                    'parking_slot_id' => $sourceSlot->id
                ]);
            }

            // Update the source assignment with new slot
            $sourceAssignment->update([
                'parking_slot_id' => $targetSlot->id
            ]);

            // Update slot statuses
            if ($targetAssignment) {
                // For slot swap
                $sourceSlot->update(['space_status' => $targetAssignment->assignment_type === 'reserve' ? 'reserved' : 'occupied']);
                $targetSlot->update(['space_status' => $sourceAssignment->assignment_type === 'reserve' ? 'reserved' : 'occupied']);
            } else {
                // For moving to an empty slot
                $sourceSlot->update(['space_status' => 'available']);
                $targetSlot->update(['space_status' => $sourceAssignment->assignment_type === 'reserve' ? 'reserved' : 'occupied']);
            }

            DB::commit();

            // Load both assignments for the response
            $response = [
                'message' => 'Parking slot changed successfully',
                'source_assignment' => $sourceAssignment->fresh(['parkingSlot', 'user'])
            ];

            if ($targetAssignment) {
                $response['target_assignment'] = $targetAssignment->fresh(['parkingSlot', 'user']);
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error switching parking slot',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function endAssignment(ParkingAssignment $assignment)
    {
        try {
            DB::beginTransaction();

            $assignment->update([
                'status' => 'completed',
                'end_time' => now()
            ]);

            // Update slot status
            $assignment->parkingSlot->update(['space_status' => 'available']);

            DB::commit();

            return response()->json([
                'message' => 'Parking assignment ended successfully',
                'assignment' => $assignment->load(['parkingSlot', 'user'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error ending parking assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

