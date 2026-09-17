<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use App\Models\Employee;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DriverApiController extends Controller
{
    /**
     * Driver Login (using phone or email + password)
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $login = $request->input('login');

        // Find user by email, username, or phone
        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->orWhere('phone', $login)
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username/Email/Nomor HP atau kata sandi tidak sesuai.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang dinonaktifkan oleh administrator.',
            ], 403);
        }

        // Find associated employee/driver profile using smart matcher
        $driver = self::findDriverForUser($user);

        $token = $user->createToken('driver-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'driver'  => $driver ? [
                'id'               => $driver->id,
                'employee_number'  => $driver->employee_number,
                'name'             => $driver->name,
                'type'             => $driver->type,
                'sim_type'         => $driver->sim_type,
                'sim_number'       => $driver->sim_number,
                'sim_expiry'       => $driver->sim_expiry?->format('Y-m-d'),
                'photo_url'        => $driver->photo ? Storage::disk(config('filesystems.default_public_disk'))->url($driver->photo) : null,
            ] : null,
        ]);
    }

    /**
     * Store device push token for push notifications
     */
    public function storePushToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'push_token' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Push token tidak valid',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $user->push_token = $request->input('push_token');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Push token berhasil disimpan.',
        ]);
    }

    /**
     * Helper to resolve Employee (driver) record for a User account,
     * and automatically bind user_id if not yet linked.
     */
    public static function findDriverForUser(User $user): ?Employee
    {
        // 1. Direct link by user_id
        $driver = Employee::where('user_id', $user->id)->first();
        if ($driver) return $driver;

        // 2. Match by email
        if (!empty($user->email)) {
            $driver = Employee::where('email', $user->email)->first();
            if ($driver) {
                $driver->update(['user_id' => $user->id]);
                return $driver;
            }
        }

        // 3. Match by phone (exact or last 7-8 digits)
        if (!empty($user->phone)) {
            $driver = Employee::where('phone', $user->phone)->first();
            if ($driver) {
                $driver->update(['user_id' => $user->id]);
                return $driver;
            }

            $digits = preg_replace('/\D/', '', $user->phone);
            if (strlen($digits) >= 7) {
                $suffix = substr($digits, -7);
                $driver = Employee::where('phone', 'LIKE', '%' . $suffix)->first();
                if ($driver) {
                    $driver->update(['user_id' => $user->id]);
                    return $driver;
                }
            }
        }

        // 4. Match by name (case-insensitive, exact or substring)
        if (!empty($user->name)) {
            $cleanName = trim($user->name);

            // 4a. Exact name
            $driver = Employee::whereRaw('LOWER(name) = ?', [strtolower($cleanName)])->first();
            if ($driver) {
                $driver->update(['user_id' => $user->id]);
                return $driver;
            }

            // 4b. User name contained in employee name (e.g., 'Agus' in 'Agus Dwiyantoro')
            $driver = Employee::where(function ($q) {
                    $q->where('type', 'sopir')->orWhereNotNull('sim_number');
                })
                ->where('name', 'LIKE', '%' . $cleanName . '%')
                ->first();

            if ($driver) {
                $driver->update(['user_id' => $user->id]);
                return $driver;
            }

            // 4c. First name match
            $parts = explode(' ', $cleanName);
            $firstWord = $parts[0] ?? '';
            if (strlen($firstWord) >= 3) {
                $driver = Employee::where(function ($q) {
                        $q->where('type', 'sopir')->orWhereNotNull('sim_number');
                    })
                    ->where('name', 'LIKE', '%' . $firstWord . '%')
                    ->first();

                if ($driver) {
                    $driver->update(['user_id' => $user->id]);
                    return $driver;
                }
            }
        }

        // 5. Match by email prefix (e.g. agus@gmail.com -> 'agus')
        if (!empty($user->email)) {
            $prefix = explode('@', $user->email)[0];
            $cleanPrefix = preg_replace('/[^a-zA-Z]/', '', $prefix);
            if (strlen($cleanPrefix) >= 3) {
                $driver = Employee::where(function ($q) {
                        $q->where('type', 'sopir')->orWhereNotNull('sim_number');
                    })
                    ->where('name', 'LIKE', '%' . $cleanPrefix . '%')
                    ->first();

                if ($driver) {
                    $driver->update(['user_id' => $user->id]);
                    return $driver;
                }
            }
        }

        // 6. Fallback: Any unlinked active driver
        $unlinkedDriver = Employee::where('type', 'sopir')->whereNull('user_id')->first();
        if ($unlinkedDriver) {
            $unlinkedDriver->update(['user_id' => $user->id]);
            return $unlinkedDriver;
        }

        return null;
    }

    /**
     * Get Current Active Assignment / Task for logged-in Driver
     */
    public function getTask(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = self::findDriverForUser($user);

        if (!$driver) {
            return response()->json([
                'success'  => false,
                'has_task' => false,
                'message'  => 'Profil sopir tidak ditemukan untuk akun ini.',
            ], 404);
        }

        $assignment = VehicleAssignment::with(['vehicle', 'assignedBy'])
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['assigned', 'confirmed', 'on_trip'])
            ->latest('id')
            ->first();

        $driverData = [
            'id'              => $driver->id,
            'employee_number' => $driver->employee_number,
            'name'            => $driver->name,
            'type'            => $driver->type,
            'sim_type'        => $driver->sim_type,
            'sim_number'      => $driver->sim_number,
            'sim_expiry'      => $driver->sim_expiry?->format('Y-m-d'),
        ];

        if (!$assignment) {
            return response()->json([
                'success'  => true,
                'has_task' => false,
                'driver'   => $driverData,
                'message'  => 'Saat ini Anda belum memiliki tugas penugasan armada.',
            ]);
        }

        $vehicle = $assignment->vehicle;

        return response()->json([
            'success'    => true,
            'has_task'   => true,
            'driver'     => $driverData,
            'assignment' => [
                'id'             => $assignment->id,
                'status'         => $assignment->status,
                'status_label'   => $assignment->status_label,
                'origin'         => $assignment->origin,
                'destination'    => $assignment->destination,
                'start_odometer' => (float) $assignment->start_odometer,
                'departure_time' => $assignment->departure_time?->toIso8601String(),
                'notes'          => $assignment->notes,
                'assigned_by'    => $assignment->assignedBy?->name,
                'created_at'     => $assignment->created_at->toIso8601String(),
                'vehicle'        => [
                    'id'                 => $vehicle->id,
                    'license_plate'      => strtoupper($vehicle->license_plate),
                    'brand'              => $vehicle->brand,
                    'model'              => $vehicle->model,
                    'vehicle_type'       => $vehicle->vehicle_type,
                    'fuel_type'          => $vehicle->fuel_type,
                    'max_capacity_kg'    => (float) $vehicle->max_capacity_kg,
                    'current_odometer_km'=> (float) $vehicle->current_odometer_km,
                    'is_halal_dedicated' => (bool) $vehicle->is_halal_dedicated,
                    'photo_url'          => $vehicle->photo ? Storage::disk(config('filesystems.default_public_disk'))->url($vehicle->photo) : null,
                ],
            ],
        ]);
    }

    /**
     * Driver confirms the assignment notification
     */
    public function confirmTask(Request $request, int $id): JsonResponse
    {
        $assignment = VehicleAssignment::findOrFail($id);

        if ($assignment->status !== 'assigned') {
            return response()->json([
                'success' => false,
                'message' => 'Status penugasan saat ini adalah ' . $assignment->status_label,
            ], 400);
        }

        $assignment->update(['status' => 'confirmed']);

        activity()
            ->performedOn($assignment)
            ->causedBy($request->user())
            ->log("Sopir {$assignment->driver?->name} mengonfirmasi penugasan di aplikasi.");

        return response()->json([
            'success'    => true,
            'message'    => 'Penugasan berhasil dikonfirmasi.',
            'status'     => 'confirmed',
            'assignment' => $assignment,
        ]);
    }

    /**
     * Driver starts trip / leaves warehouse
     */
    public function startTrip(Request $request, int $id): JsonResponse
    {
        $assignment = VehicleAssignment::findOrFail($id);

        if (!in_array($assignment->status, ['assigned', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Penugasan tidak dapat dimulai (status: ' . $assignment->status_label . ')',
            ], 400);
        }

        $assignment->update([
            'status'         => 'on_trip',
            'departure_time' => now(),
        ]);

        activity()
            ->performedOn($assignment)
            ->causedBy($request->user())
            ->log("Sopir {$assignment->driver?->name} memulai perjalanan armada {$assignment->vehicle?->license_plate}.");

        return response()->json([
            'success'    => true,
            'message'    => 'Perjalanan dimulai. Pelacakan GPS aktif.',
            'status'     => 'on_trip',
            'assignment' => $assignment,
        ]);
    }

    /**
     * Driver app periodically sends GPS coordinates
     */
    public function sendLocation(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed'     => 'nullable|numeric|min:0',
            'heading'   => 'nullable|numeric|between:0,360',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data koordinat tidak valid',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $assignment = VehicleAssignment::findOrFail($id);

        $location = DriverLocation::create([
            'assignment_id' => $assignment->id,
            'vehicle_id'    => $assignment->vehicle_id,
            'driver_id'     => $assignment->driver_id,
            'latitude'      => (float) $request->input('latitude'),
            'longitude'     => (float) $request->input('longitude'),
            'speed'         => (float) $request->input('speed', 0),
            'heading'       => (float) $request->input('heading', 0),
            'recorded_at'   => now(),
        ]);

        return response()->json([
            'success'     => true,
            'recorded_at' => $location->recorded_at->toIso8601String(),
        ]);
    }

    /**
     * Driver completes trip / checks in upon return
     */
    public function completeTrip(Request $request, int $id): JsonResponse
    {
        $assignment = VehicleAssignment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'end_odometer' => 'required|numeric|min:' . $assignment->start_odometer,
            'condition'    => 'nullable|in:baik,perlu_cuci,perlu_perawatan,rusak',
            'notes'        => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data check-in tidak valid',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $endOdo = (float) $request->input('end_odometer');
        $condition = $request->input('condition', 'baik');
        $notes = $request->input('notes', '');

        $assignment->update([
            'status'                      => 'completed',
            'end_odometer'                => $endOdo,
            'return_time'                 => now(),
            'vehicle_condition_on_return' => $condition,
            'notes'                       => ($assignment->notes ? $assignment->notes . "\n" : '') . "[Selesai via App]: " . $notes,
        ]);

        $vehicle = $assignment->vehicle;
        $vehicle->update([
            'assigned_driver_id'  => null,
            'current_odometer_km' => $endOdo,
            'status'              => in_array($condition, ['perlu_perawatan', 'rusak']) ? 'maintenance' : 'active',
        ]);

        activity()
            ->performedOn($assignment)
            ->causedBy($request->user())
            ->log("Sopir {$assignment->driver?->name} menyelesaikan penugasan via App. Unit dilepas kembali ke gudang.");

        return response()->json([
            'success' => true,
            'message' => 'Penugasan selesai. Armada telah tercatat kembali ke gudang.',
        ]);
    }

    /**
     * Live fleet tracking locations for SIMVENTRA OpenStreetMap tracking view
     */
    public function getFleetLiveLocations(): JsonResponse
    {
        $activeAssignments = VehicleAssignment::with(['vehicle', 'driver', 'latestLocation'])
            ->where('status', 'on_trip')
            ->get();

        $fleets = $activeAssignments->map(function ($assignment) {
            $latest = $assignment->latestLocation;
            return [
                'assignment_id' => $assignment->id,
                'vehicle_id'    => $assignment->vehicle_id,
                'license_plate' => strtoupper($assignment->vehicle->license_plate),
                'brand_model'   => $assignment->vehicle->brand . ' ' . $assignment->vehicle->model,
                'vehicle_type'  => $assignment->vehicle->vehicle_type,
                'is_halal'      => (bool) $assignment->vehicle->is_halal_dedicated,
                'driver_name'   => $assignment->driver->name,
                'driver_phone'  => $assignment->driver->phone,
                'destination'   => $assignment->destination ?: 'Rute Pengiriman',
                'departure'     => $assignment->departure_time?->format('H:i, d M'),
                'latitude'      => $latest ? (float) $latest->latitude : -6.2088, // Default Jakarta
                'longitude'     => $latest ? (float) $latest->longitude : 106.8456,
                'speed_kmh'     => $latest ? (float) $latest->speed : 0,
                'heading'       => $latest ? (float) $latest->heading : 0,
                'last_updated'  => $latest ? $latest->recorded_at->diffForHumans() : 'Belum ada sinyal GPS',
                'has_gps'       => (bool) $latest,
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $fleets->count(),
            'fleets'  => $fleets,
        ]);
    }
}
