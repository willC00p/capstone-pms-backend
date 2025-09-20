<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ParkingLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ParkingLayoutController extends Controller
{
    protected function getDatabaseErrorMessage(\Illuminate\Database\QueryException $e) {
        $code = $e->getCode();
        $message = $e->getMessage();
        
        // Common MySQL/PostgreSQL error codes
        switch ($code) {
            case '23000': // Integrity constraint violation
                return 'Data integrity error: Duplicate entry or invalid reference';
            case '42S22': // Column not found
                return 'Database schema error: Missing column';
            case '42S02': // Table not found
                return 'Database schema error: Missing table';
            default:
                // Extract useful info from the generic message
                if (strpos($message, "Column") !== false && strpos($message, "doesn't exist") !== false) {
                    return 'Database schema error: Missing column';
                }
                return 'Database error: ' . $message;
        }
    }

    public function index()
    {
        try {
            $layouts = ParkingLayout::with('parkingSlots')->get();
            return response()->json([
                'message' => 'Layouts retrieved successfully',
                'data' => $layouts
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving layouts: ' . $e->getMessage());
            $response = [
                'message' => 'Error retrieving layouts',
                'error' => $e->getMessage(),
            ];
            if (config('app.debug')) {
                $response['exception'] = get_class($e);
                $response['trace'] = $e->getTrace();
            }
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            Log::info('Showing layout with ID: ' . $id);
            
            $layout = ParkingLayout::with('parkingSlots')->findOrFail($id);
            
            Log::info('Found layout:', ['layout' => $layout->toArray()]);
            
            // Parse layout_data from JSON if it's a string
            $layoutData = is_string($layout->layout_data) ? 
                json_decode($layout->layout_data, true) : 
                $layout->layout_data;

            Log::info('Parsed layout data:', ['layoutData' => $layoutData]);

            // Create the full response data structure
            $responseData = [
                'id' => $layout->id,
                'name' => $layout->name,
                'background_image' => $layout->background_image,
                'parking_slots' => $layout->parkingSlots,  // Keep this for backwards compatibility
                'layout_data' => $layoutData ?: [
                    'parking_slots' => [],
                    'lines' => [],
                    'texts' => []
                ]
            ];

            Log::info('Sending response:', ['responseData' => $responseData]);

            return response()->json([
                'message' => 'Layout retrieved successfully',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving layout: ' . $e->getMessage());
            $response = [
                'message' => 'Error retrieving layout',
                'error' => $e->getMessage(),
            ];
            if (config('app.debug')) {
                $response['exception'] = get_class($e);
                $response['trace'] = $e->getTrace();
            }
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'layout_data' => 'required',
            ]);

            if ($request->hasFile('background_image')) {
                $path = $request->file('background_image')->store('parking-layouts', 'public');
                $path = asset('storage/' . $path);
            }

            // Handle layout data
            if (is_string($request->layout_data)) {
                $layoutData = json_decode($request->layout_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid layout data format: ' . json_last_error_msg());
                }
            } else {
                $layoutData = $request->layout_data;
            }

            // Ensure the layout data has the required structure
            if (!is_array($layoutData)) {
                throw new \Exception('Layout data must be an array');
            }

            // Store the complete layout data including slots
            $layout = ParkingLayout::create([
                'name' => $request->name,
                'background_image' => $path ?? null,
                'layout_data' => [
                    'parking_slots' => $layoutData['parking_slots'] ?? [],
                    'lines' => $layoutData['lines'] ?? [],
                    'texts' => $layoutData['texts'] ?? []
                ]
            ]);

            // Also create the slots in the parking_slots table for tracking status
            if (!empty($layoutData['parking_slots']) && is_array($layoutData['parking_slots'])) {
                foreach ($layoutData['parking_slots'] as $slotData) {
                    // Create the parking slot with all necessary data
                    $slot = $layout->parkingSlots()->create([
                        'space_number' => $slotData['space_number'] ?? ('Space ' . rand(1000, 9999)),
                        'space_type' => $slotData['space_type'] ?? 'standard',
                        'space_status' => $slotData['space_status'] ?? 'available',
                        'position_x' => $slotData['x_coordinate'] ?? $slotData['position_x'] ?? 0,
                        'position_y' => $slotData['y_coordinate'] ?? $slotData['position_y'] ?? 0,
                        'width' => $slotData['width'] ?? 60,
                        'height' => $slotData['height'] ?? 120,
                        'rotation' => $slotData['rotation'] ?? 0,
                        'metadata' => $slotData['metadata'] ?? [
                            'fill' => $slotData['fill'] ?? null,
                            'type' => $slotData['type'] ?? 'standard'
                        ]
                    ]);

                    Log::info('Created parking slot:', [
                        'layout_id' => $layout->id,
                        'slot_id' => $slot->id,
                        'data' => $slot->toArray()
                    ]);
                }
            }

            DB::commit();

            // Return the layout with its slots
            return response()->json([
                'message' => 'Layout created successfully',
                'data' => $layout->fresh('parkingSlots')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating parking layout: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error creating parking layout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $parking_layout = ParkingLayout::findOrFail($id);
        \Log::info('ParkingLayoutController@update: Found layout by id', ['layout_id' => $id]);
        \Log::info('ParkingLayoutController@update: Request data', ['request_data' => $request->all()]);

        // Defensive: check if layout exists and id is set
        if (!$parking_layout || !$parking_layout->id) {
            \Log::error('ParkingLayoutController@update: Layout does not exist or id is null', ['layout' => $parking_layout]);
            return response()->json(['message' => 'Layout does not exist'], 404);
        }
        \Log::info('ParkingLayoutController@update: Layout id', ['id' => $parking_layout->id]);
        try {
            DB::beginTransaction();

            Log::info('Starting layout update', [
                'layout_id' => $parking_layout->id,
                'request_data' => $request->all()
            ]);

            Log::info('Current layout state:', [
                'layout' => $parking_layout->toArray()
            ]);

            $updatedSlotIds = [];

            if ($request->has('layout_data')) {
                $layoutData = $request->input('layout_data');
                if (is_string($layoutData)) {
                    $decodedData = json_decode($layoutData, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception('Invalid JSON in layout_data: ' . json_last_error_msg());
                    }
                    $layoutData = $decodedData;
                }
                if (!is_array($layoutData)) {
                    throw new \Exception('layout_data must be an array');
                }

                // Ensure the parking_layout exists and has an ID
                if (!$parking_layout->exists || !$parking_layout->id) {
                    throw new \Exception('Layout does not exist');
                }

                // First update the layout data itself
                $parking_layout->layout_data = $layoutData;
                $parking_layout->save(); // Save first to ensure we have an ID
                
                Log::info('Updating layout data:', [
                    'layout_id' => $parking_layout->id,
                    'new_data' => $parking_layout->layout_data
                ]);

                // --- SYNC PARKING SLOTS TABLE ---
                $slotDataArr = $layoutData['parking_slots'] ?? [];
                $existingSlots = $parking_layout->parkingSlots()->get()->keyBy('id');
                foreach ($slotDataArr as $slotData) {
                    // Try to match by id, else create new
                    $slotId = isset($slotData['id']) ? $slotData['id'] : null;
                    
                    // Prepare metadata
                    $metadata = array_merge($slotData['metadata'] ?? [], [
                        'rotation' => $slotData['rotation'] ?? 0,
                        'fill' => $slotData['fill'] ?? 'rgba(0, 255, 0, 0.3)',
                        'type' => $slotData['space_type'] ?? 'standard',
                        'name' => $slotData['space_number'] ?? ('Space ' . rand(1000, 9999))
                    ]);

                    // Defensive: always set layout_id to current layout
                    $slotArr = [
                        'layout_id' => $parking_layout->id,
                        'space_number' => $slotData['space_number'] ?? ('Space ' . rand(1000, 9999)),
                        'space_type' => $slotData['space_type'] ?? 'standard',
                        'space_status' => $slotData['space_status'] ?? 'available',
                        'position_x' => $slotData['position_x'] ?? 0,
                        'position_y' => $slotData['position_y'] ?? 0,
                        'width' => $slotData['width'] ?? 60,
                        'height' => $slotData['height'] ?? 120,
                        'rotation' => $slotData['rotation'] ?? 0,
                        'metadata' => $metadata
                    ];
                    Log::info('Saving parking slot:', ['slotArr' => $slotArr, 'slotId' => $slotId]);
                    if ($slotId && $existingSlots->has($slotId)) {
                        $slot = $existingSlots[$slotId];
                        $slot->update($slotArr);
                        $updatedSlotIds[] = $slot->id;
                    } else {
                        $slot = $parking_layout->parkingSlots()->create($slotArr);
                        $updatedSlotIds[] = $slot->id;
                    }
                }
                // Delete slots that are no longer present
                $allSlotIds = $existingSlots->keys()->toArray();
                $toDelete = array_diff($allSlotIds, $updatedSlotIds);
                if (!empty($toDelete)) {
                    $parking_layout->parkingSlots()->whereIn('id', $toDelete)->delete();
                }
                // --- END SYNC ---
            }

            if ($request->has('name')) {
                $parking_layout->name = $request->input('name');
            }

            if ($request->hasFile('background_image')) {
                if ($parking_layout->background_image) {
                    Storage::disk('public')->delete($parking_layout->background_image);
                }
                $path = $request->file('background_image')->store('parking-layouts', 'public');
                $parking_layout->background_image = asset('storage/' . $path);
            }

            $parking_layout->save();

            DB::commit();

            return response()->json([
                'message' => 'Layout updated successfully',
                'data' => $parking_layout->fresh('parkingSlots')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating layout:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'layout_id' => $parking_layout->id,
                'request_data' => $request->all()
            ]);
            return response()->json([
                'message' => 'Error updating layout',
                'error' => $e->getMessage(),
                'debug' => config('app.debug') ? [
                    'trace' => $e->getTraceAsString(),
                    'layout_id' => $parking_layout->id
                ] : null
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $layout = ParkingLayout::findOrFail($id);
            
            // Log more details about the deletion attempt
            Log::info('Attempting to delete layout:', [
                'layout_id' => $layout->id,
                'layout_exists' => $layout->exists,
                'layout_data' => $layout->toArray()
            ]);

            // Verify the layout exists in the database
            if (!$layout->exists) {
                throw new \Exception('Layout does not exist in database');
            }

            // Double check that we can find it in the database
            $verifyLayout = ParkingLayout::find($layout->id);
            if (!$verifyLayout) {
                throw new \Exception('Layout not found in database during verification');
            }

            // First, delete all associated parking slots using raw SQL to ensure it executes
            $slotsDeleted = DB::table('parking_slots')
                ->where('layout_id', $layout->id)
                ->delete();
            Log::info('Deleted associated parking slots:', ['count' => $slotsDeleted]);

            // Delete the background image if it exists
            if ($layout->background_image) {
                // Extract the file path from the full URL
                $path = str_replace(asset('storage/'), '', $layout->background_image);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                    Log::info('Deleted background image:', ['path' => $path]);
                }
            }
            
            // Delete the layout using raw SQL to ensure it executes
            $deleted = DB::table('parking_layouts')
                ->where('id', $layout->id)
                ->delete();
            
            if (!$deleted) {
                throw new \Exception('Failed to delete layout from database');
            }

            Log::info('Successfully deleted layout:', [
                'layout_id' => $layout->id,
                'rows_deleted' => $deleted
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Layout and all associated data deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting layout:', [
                'layout_id' => $layout->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error deleting layout',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

