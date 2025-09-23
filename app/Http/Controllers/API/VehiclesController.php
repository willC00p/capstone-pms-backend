<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VehiclesController extends BaseController
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::with('user','userDetails')->get();
        return $this->sendResponse($vehicles, 'Vehicles retrieved');
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'plate_number' => 'required|string',
            'vehicle_color' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'or_file' => 'sometimes|file|mimes:pdf|max:5120',
            'cr_file' => 'sometimes|file|mimes:pdf|max:5120',
            'or_number' => 'nullable|string|unique:vehicles,or_number',
            'cr_number' => 'nullable|string|unique:vehicles,cr_number',
        ]);

        if ($v->fails()) return $this->sendError('Validation error', $v->errors());

        // If attaching to a user, ensure they don't have more than 3 vehicles
        $userDetailsId = null;
        if ($request->filled('user_id')) {
            $user = User::find($request->user_id);
            if ($user) {
                // find or create the user_details row
                $ud = $user->userDetail()->first();
                if (!$ud) {
                    $ud = $user->userDetail()->create(['user_id' => $user->id, 'firstname' => $user->name]);
                }
                $userDetailsId = $ud->id;
                if ($ud->vehicles()->count() >= 3) {
                    return $this->sendError('User already has maximum number of vehicles (3).', [], 422);
                }
            }
        }

        $orPath = null; $crPath = null;
        if ($request->hasFile('or_file')) {
            $file = $request->file('or_file');
            $filename = 'veh_or_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $orPath = $file->storeAs('or_cr', $filename, 'public');
        }
        if ($request->hasFile('cr_file')) {
            $file = $request->file('cr_file');
            $filename = 'veh_cr_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $crPath = $file->storeAs('or_cr', $filename, 'public');
        }

        $vehicle = Vehicle::create([
            'user_id' => $request->user_id,
            'user_details_id' => $userDetailsId,
            'plate_number' => $request->plate_number,
            'vehicle_color' => $request->vehicle_color,
            'vehicle_type' => $request->vehicle_type,
            'brand' => $request->brand,
            'model' => $request->model,
            'or_path' => $orPath,
            'cr_path' => $crPath,
            'or_number' => $request->or_number ?? null,
            'cr_number' => $request->cr_number ?? null,
        ]);

        // Link vehicle to user_details plate_numbers array if we have user details
        if ($userDetailsId) {
            $ud = UserDetails::find($userDetailsId);
            if ($ud) {
                $ud->addPlateNumber($vehicle->plate_number);
            }
        }

        return $this->sendResponse($vehicle->load('user','userDetails'), 'Vehicle created');
    }

    public function show(Vehicle $vehicle)
    {
        return $this->sendResponse($vehicle->load('user','userDetails'), 'Vehicle retrieved');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $v = Validator::make($request->all(), [
            'plate_number' => 'required|string',
            'vehicle_color' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'or_file' => 'sometimes|file|mimes:pdf|max:5120',
            'cr_file' => 'sometimes|file|mimes:pdf|max:5120',
            'or_number' => ['nullable','string', Rule::unique('vehicles','or_number')->ignore($vehicle->id)],
            'cr_number' => ['nullable','string', Rule::unique('vehicles','cr_number')->ignore($vehicle->id)],
        ]);

        if ($v->fails()) return $this->sendError('Validation error', $v->errors());

        // handle files
        if ($request->hasFile('or_file')) {
            $file = $request->file('or_file');
            $filename = 'veh_or_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $vehicle->or_path = $file->storeAs('or_cr', $filename, 'public');
        }
        if ($request->hasFile('cr_file')) {
            $file = $request->file('cr_file');
            $filename = 'veh_cr_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $vehicle->cr_path = $file->storeAs('or_cr', $filename, 'public');
        }

        // optional OR/CR numbers
        $vehicle->or_number = $request->or_number ?? $vehicle->or_number;
        $vehicle->cr_number = $request->cr_number ?? $vehicle->cr_number;

        $oldPlate = $vehicle->plate_number;
        $vehicle->plate_number = $request->plate_number;
        $vehicle->vehicle_color = $request->vehicle_color;
        $vehicle->vehicle_type = $request->vehicle_type;
        $vehicle->brand = $request->brand;
        $vehicle->model = $request->model;
        $vehicle->save();

        // if linked to user_details, update plate_numbers array if plate changed
        if ($vehicle->user_details_id && $oldPlate !== $vehicle->plate_number) {
            $ud = UserDetails::find($vehicle->user_details_id);
            if ($ud) {
                // remove old plate and add new one
                $plates = $ud->plate_numbers ?? [];
                $plates = array_filter($plates, function($p) use ($oldPlate) { return $p !== $oldPlate; });
                if (!in_array($vehicle->plate_number, $plates)) $plates[] = $vehicle->plate_number;
                $ud->plate_numbers = array_values($plates);
                $ud->save();
            }
        }

        return $this->sendResponse($vehicle->load('user','userDetails'), 'Vehicle updated');
    }

    public function destroy(Vehicle $vehicle)
    {
        // If vehicle linked to user_details, remove plate from plate_numbers
        if ($vehicle->user_details_id) {
            $ud = UserDetails::find($vehicle->user_details_id);
            if ($ud) {
                $plates = $ud->plate_numbers ?? [];
                $plates = array_filter($plates, function($p) use ($vehicle) { return $p !== $vehicle->plate_number; });
                $ud->plate_numbers = array_values($plates);
                $ud->save();
            }
        }

        $vehicle->delete();
        return $this->sendResponse([], 'Vehicle deleted');
    }

    // Public check for OR/CR uniqueness (used by frontend before submit)
    public function checkUnique(Request $request)
    {
        $v = Validator::make($request->all(), [
            'or_number' => 'nullable|string',
            'cr_number' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'email' => 'nullable|email',
            'contact_number' => 'nullable|string',
        ]);
        if ($v->fails()) return $this->sendError('Validation error', $v->errors());

        $exists = [];
        if ($request->filled('or_number')) {
            $exists['or_number'] = Vehicle::where('or_number', $request->or_number)->exists();
        }
        if ($request->filled('cr_number')) {
            $exists['cr_number'] = Vehicle::where('cr_number', $request->cr_number)->exists();
        }
        if ($request->filled('plate_number')) {
            $exists['plate_number'] = Vehicle::where('plate_number', $request->plate_number)->exists();
        }
        if ($request->filled('email')) {
            // email uniqueness should consider users table
            $exists['email'] = User::where('email', $request->email)->exists();
        }
        if ($request->filled('contact_number')) {
            // contact stored in user_details
            $exists['contact_number'] = UserDetails::where('contact_number', $request->contact_number)->exists();
        }

        return $this->sendResponse(['exists' => $exists], 'Uniqueness check');
    }

    /**
     * Lookup a vehicle by plate (case-insensitive) and return vehicle with user and userDetails.
     * Used by frontend to auto-fill assignment form when typing a plate number.
     */
    public function lookupByPlate($plate)
    {
        if (!$plate) {
            return $this->sendResponse(null, 'No plate provided');
        }

        $normalized = mb_strtolower(trim($plate));
        $vehicle = Vehicle::with(['user', 'userDetails'])
            ->whereRaw('LOWER(plate_number) = ?', [$normalized])
            ->first();

        return $this->sendResponse($vehicle, $vehicle ? 'Vehicle found' : 'Vehicle not found');
    }

    /**
     * Partial search for plates. Accepts query param 'q' and returns up to 12 matches.
     */
    public function searchPlates(Request $request)
    {
        $q = $request->query('q', '');
        if (!is_string($q) || trim($q) === '') {
            return $this->sendResponse([], 'No query provided');
        }
        $term = '%' . strtolower(trim($q)) . '%';
        $matches = Vehicle::with(['user','userDetails'])
            ->whereRaw('LOWER(plate_number) LIKE ?', [$term])
            ->orderBy('plate_number')
            ->limit(12)
            ->get();

        return $this->sendResponse($matches, 'Search results');
    }
}
