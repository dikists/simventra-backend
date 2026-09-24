<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use App\Models\Employee;
use App\Models\Incident;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use App\Services\DriverMatchingService;
use Carbon\Carbon;
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

        // Find associated employee/driver profile using centralized service
        $driver = DriverMatchingService::findDriverForUser($user);

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
     * Get Current Active Assignment / Task for logged-in Driver
     */
    public function getTask(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = DriverMatchingService::findDriverForUser($user);

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
                'origin'                => $assignment->origin,
                'destination'           => $assignment->destination,
                'destination_latitude'  => $assignment->destination_latitude ? (float) $assignment->destination_latitude : null,
                'destination_longitude' => $assignment->destination_longitude ? (float) $assignment->destination_longitude : null,
                'start_odometer'        => (float) $assignment->start_odometer,
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
     * Driver app periodically sends GPS coordinates (single point)
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

        // ── Heartbeat: perbarui timestamp lokasi terakhir di assignment
        $assignment->update(['last_ping_at' => now()]);

        return response()->json([
            'success'     => true,
            'recorded_at' => $location->recorded_at->toIso8601String(),
        ]);

    }

    /**
     * Batch GPS location upload — menerima array koordinat sekaligus.
     * Mengurangi jumlah HTTP request dari mobile app secara signifikan.
     */
    public function sendLocationBatch(Request $request, int $id): JsonResponse
    {
        $maxPoints = config('simventra.gps.max_points_per_batch', 20);

        $validator = Validator::make($request->all(), [
            'locations'              => "required|array|min:1|max:{$maxPoints}",
            'locations.*.latitude'   => 'required|numeric|between:-90,90',
            'locations.*.longitude'  => 'required|numeric|between:-180,180',
            'locations.*.speed'      => 'nullable|numeric|min:0',
            'locations.*.heading'    => 'nullable|numeric|between:0,360',
            'locations.*.timestamp'  => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data batch lokasi tidak valid',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $assignment = VehicleAssignment::findOrFail($id);

        $locations = collect($request->input('locations'));
        $insertData = [];
        $now = now();

        foreach ($locations as $point) {
            $insertData[] = [
                'assignment_id' => $assignment->id,
                'vehicle_id'    => $assignment->vehicle_id,
                'driver_id'     => $assignment->driver_id,
                'latitude'      => (float) $point['latitude'],
                'longitude'     => (float) $point['longitude'],
                'speed'         => (float) ($point['speed'] ?? 0),
                'heading'       => (float) ($point['heading'] ?? 0),
                'recorded_at'   => isset($point['timestamp']) ? Carbon::parse($point['timestamp']) : $now,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        // Bulk insert — satu query untuk semua titik
        DriverLocation::insert($insertData);

        // Update heartbeat
        $assignment->update(['last_ping_at' => $now]);

        return response()->json([
            'success'       => true,
            'points_saved'  => count($insertData),
            'recorded_at'   => $now->toIso8601String(),
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
     * Live fleet tracking locations for SIMVENTRA OpenStreetMap tracking view.
     *
     * OPTIMIZED: Menghilangkan N+1 query pada trail locations dengan eager loading.
     */
    public function getFleetLiveLocations(): JsonResponse
    {
        $trailLimit = (int) config('simventra.gps.trail_points', 30);

        // Eager load locations untuk semua assignment sekaligus (menghindari N+1)
        $activeAssignments = VehicleAssignment::with([
                'vehicle',
                'driver',
                'latestLocation',
            ])
            ->where('status', 'on_trip')
            ->get();

        // Batch load trail locations: ambil semua lokasi terakhir sekaligus
        $assignmentIds = $activeAssignments->pluck('id');
        $trailLocations = DriverLocation::whereIn('assignment_id', $assignmentIds)
            ->orderByDesc('recorded_at')
            ->get()
            ->groupBy('assignment_id')
            ->map(fn ($locs) => $locs->take($trailLimit)->reverse()->values());

        $timeoutMinutes = (int) config('simventra.heartbeat.timeout_minutes', 5);
        $cutoffAt = now()->subMinutes($timeoutMinutes);

        $fleets = $activeAssignments->map(function (VehicleAssignment $assignment) use ($cutoffAt, $trailLocations) {
            $latest = $assignment->latestLocation;
            $refTime = $assignment->last_ping_at ?? $assignment->departure_time ?? $assignment->updated_at;
            $isSilent = $refTime ? Carbon::instance($refTime)->isBefore($cutoffAt) : false;
            $silenceMinutes = $refTime ? max(1, (int) abs(now()->diffInMinutes($refTime))) : 0;

            // Trail dari batch pre-loaded (bukan per-assignment query)
            $trail = ($trailLocations[$assignment->id] ?? collect())->map(function ($loc) {
                return [(float) $loc->latitude, (float) $loc->longitude];
            });

            return [
                'assignment_id' => $assignment->id,
                'vehicle_id'    => $assignment->vehicle_id,
                'license_plate' => strtoupper($assignment->vehicle->license_plate),
                'brand_model'   => $assignment->vehicle->brand . ' ' . $assignment->vehicle->model,
                'vehicle_type'  => $assignment->vehicle->vehicle_type,
                'is_halal'      => (bool) $assignment->vehicle->is_halal_dedicated,
                'driver_name'   => $assignment->driver->name,
                'driver_phone'  => $assignment->driver->phone,
                'destination'           => $assignment->destination ?: 'Rute Pengiriman',
                'destination_latitude'  => $assignment->destination_latitude ? (float) $assignment->destination_latitude : null,
                'destination_longitude' => $assignment->destination_longitude ? (float) $assignment->destination_longitude : null,
                'departure'             => $assignment->departure_time?->format('H:i, d M'),
                'latitude'              => $latest ? (float) $latest->latitude : config('simventra.warehouse.lat'),
                'longitude'             => $latest ? (float) $latest->longitude : config('simventra.warehouse.lng'),
                'speed_kmh'             => $latest ? (float) $latest->speed : 0,
                'heading'               => $latest ? (float) $latest->heading : 0,
                'last_updated'          => $latest ? $latest->recorded_at->diffForHumans() : 'Belum ada sinyal GPS',
                'has_gps'               => (bool) $latest,
                'is_silent'             => $isSilent,
                'silence_minutes'       => $silenceMinutes,
                'trail'                 => $trail,
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $fleets->count(),
            'fleets'  => $fleets,
        ]);
    }

    /**
     * Riwayat penugasan yang sudah selesai untuk sopir
     */
    public function getTripHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = DriverMatchingService::findDriverForUser($user);

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Profil sopir tidak ditemukan.',
            ], 404);
        }

        $limit = min((int) $request->input('limit', 10), 50);

        $history = VehicleAssignment::with(['vehicle', 'assignedBy'])
            ->where('driver_id', $driver->id)
            ->where('status', 'completed')
            ->latest('return_time')
            ->limit($limit)
            ->get()
            ->map(function (VehicleAssignment $a) {
                return [
                    'id'              => $a->id,
                    'vehicle'         => strtoupper($a->vehicle?->license_plate ?? '-'),
                    'brand_model'     => ($a->vehicle?->brand ?? '') . ' ' . ($a->vehicle?->model ?? ''),
                    'origin'          => $a->origin,
                    'destination'     => $a->destination,
                    'departure_time'  => $a->departure_time?->format('d M Y, H:i'),
                    'return_time'     => $a->return_time?->format('d M Y, H:i'),
                    'distance_km'     => $a->distance_traveled,
                    'condition'       => $a->vehicle_condition_on_return,
                    'assigned_by'     => $a->assignedBy?->name,
                ];
            });

        return response()->json([
            'success' => true,
            'count'   => $history->count(),
            'trips'   => $history,
        ]);
    }

    /**
     * Sopir melaporkan insiden / panic alert dari aplikasi mobile
     */
    public function reportIncident(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:keamanan,kontaminasi,kecelakaan,pelanggaran,lainnya',
            'severity'    => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'location'    => 'nullable|string',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $driver = DriverMatchingService::findDriverForUser($user);

        // Cari penugasan aktif jika ada
        $activeTask = null;
        if ($driver) {
            $activeTask = VehicleAssignment::where('driver_id', $driver->id)
                ->whereIn('status', ['assigned', 'confirmed', 'on_trip'])
                ->latest()
                ->first();
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('incidents', config('filesystems.default_public_disk', 'public'));
        }

        // Cegah duplikasi laporan akibat double click / spam klik dari mobile app
        $recentDuplicate = Incident::where('reported_by', $user->id)
            ->where('title', $request->input('title'))
            ->where('status', 'open')
            ->where('created_at', '>=', now()->subSeconds(30))
            ->latest()
            ->first();

        if ($recentDuplicate) {
            return response()->json([
                'success'         => true,
                'message'         => 'Laporan insiden serupa sudah berhasil diterima oleh Control Tower.',
                'incident_number' => $recentDuplicate->incident_number,
                'incident'        => $recentDuplicate,
            ], 200);
        }

        $incidentNumber = Incident::generateIncidentNumber();

        $incident = Incident::create([
            'incident_number' => $incidentNumber,
            'type'            => $request->input('type'),
            'severity'        => $request->input('severity'),
            'title'           => $request->input('title'),
            'description'     => $request->input('description'),
            'occurred_at'     => now(),
            'location'        => $request->input('location'),
            'latitude'        => $request->input('latitude'),
            'longitude'       => $request->input('longitude'),
            'vehicle_id'      => $activeTask?->vehicle_id,
            'driver_id'       => $driver?->id,
            'reported_by'     => $user->id,
            'assignment_id'   => $activeTask?->id,
            'status'          => 'open',
            'photo'           => $photoPath,
        ]);

        return response()->json([
            'success'         => true,
            'message'         => 'Laporan insiden berhasil dikirim ke Control Tower.',
            'incident_number' => $incidentNumber,
            'incident'        => $incident,
        ], 201);
    }
}
