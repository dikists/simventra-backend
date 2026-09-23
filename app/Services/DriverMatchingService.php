<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mencocokkan User account dengan Employee/Driver record.
 *
 * Logika matching terpusat — menggantikan duplikasi di DriverApiController,
 * VehicleDetail, dan RecallPendingAssignmentsCommand.
 *
 * Strategi matching (berurutan, berhenti saat match pertama):
 *   1. Direct link via user_id
 *   2. Kesamaan email
 *   3. Kesamaan phone (exact + last-7-digit)
 *   4. Kesamaan nama (exact → contains → first name)
 *   5. Email prefix match (agus@gmail.com → "agus")
 *   6. Fallback: driver tidak terhubung yang belum punya user
 */
class DriverMatchingService
{
    /**
     * Temukan Employee (driver) record yang cocok dengan User account.
     * Jika cocok dan belum ter-link, otomatis bind user_id.
     */
    public static function findDriverForUser(User $user): ?Employee
    {
        // 1. Direct link by user_id
        $driver = Employee::where('user_id', $user->id)->first();
        if ($driver) {
            return $driver;
        }

        // 2. Match by email
        if (!empty($user->email)) {
            $driver = Employee::where('email', $user->email)->first();
            if ($driver) {
                return self::linkAndReturn($driver, $user);
            }
        }

        // 3. Match by phone (exact or last 7-8 digits)
        if (!empty($user->phone)) {
            $driver = Employee::where('phone', $user->phone)->first();
            if ($driver) {
                return self::linkAndReturn($driver, $user);
            }

            $digits = preg_replace('/\D/', '', $user->phone);
            if (strlen($digits) >= 7) {
                $suffix = substr($digits, -7);
                $driver = Employee::where('phone', 'LIKE', '%' . $suffix)->first();
                if ($driver) {
                    return self::linkAndReturn($driver, $user);
                }
            }
        }

        // 4. Match by name (case-insensitive, exact or substring)
        if (!empty($user->name)) {
            $cleanName = trim($user->name);

            // 4a. Exact name
            $driver = Employee::whereRaw('LOWER(name) = ?', [strtolower($cleanName)])->first();
            if ($driver) {
                return self::linkAndReturn($driver, $user);
            }

            // 4b. User name contained in employee name (e.g., 'Agus' in 'Agus Dwiyantoro')
            $driver = self::findDriverByNameFragment($cleanName);
            if ($driver) {
                return self::linkAndReturn($driver, $user);
            }

            // 4c. First name match
            $parts = explode(' ', $cleanName);
            $firstWord = $parts[0] ?? '';
            if (strlen($firstWord) >= 3 && $firstWord !== $cleanName) {
                $driver = self::findDriverByNameFragment($firstWord);
                if ($driver) {
                    return self::linkAndReturn($driver, $user);
                }
            }
        }

        // 5. Match by email prefix (e.g. agus@gmail.com → 'agus')
        if (!empty($user->email)) {
            $prefix = explode('@', $user->email)[0];
            $cleanPrefix = preg_replace('/[^a-zA-Z]/', '', $prefix);
            if (strlen($cleanPrefix) >= 3) {
                $driver = self::findDriverByNameFragment($cleanPrefix);
                if ($driver) {
                    return self::linkAndReturn($driver, $user);
                }
            }
        }

        // 6. Fallback: Any unlinked active driver
        $unlinkedDriver = Employee::where('type', 'sopir')->whereNull('user_id')->first();
        if ($unlinkedDriver) {
            return self::linkAndReturn($unlinkedDriver, $user);
        }

        return null;
    }

    /**
     * Cari User account yang terkait dengan Employee record.
     * Berguna saat dispatch/recall dari web, di mana kita punya Employee tapi butuh User.
     */
    public static function findUserForDriver(Employee $driver): ?User
    {
        // 1. Direct link
        if ($driver->user_id) {
            return User::find($driver->user_id);
        }

        // 2. By email
        if (!empty($driver->email)) {
            $user = User::where('email', $driver->email)->first();
            if ($user) {
                $driver->update(['user_id' => $user->id]);
                return $user;
            }
        }

        // 3. By phone
        if (!empty($driver->phone)) {
            $user = User::where('phone', $driver->phone)->first();
            if ($user) {
                $driver->update(['user_id' => $user->id]);
                return $user;
            }

            $suffix = substr(preg_replace('/\D/', '', $driver->phone), -7);
            if (strlen($suffix) >= 7) {
                $user = User::where('phone', 'LIKE', '%' . $suffix)->first();
                if ($user) {
                    $driver->update(['user_id' => $user->id]);
                    return $user;
                }
            }
        }

        // 4. By name
        if (!empty($driver->name)) {
            $user = User::whereRaw('LOWER(name) = ?', [strtolower(trim($driver->name))])->first();
            if (!$user) {
                $user = User::where('name', 'LIKE', '%' . trim($driver->name) . '%')->first();
            }
            if ($user) {
                $driver->update(['user_id' => $user->id]);
                return $user;
            }
        }

        return null;
    }

    /**
     * Internal: Cari driver (type=sopir atau punya SIM) yang namanya mengandung fragment.
     */
    private static function findDriverByNameFragment(string $fragment): ?Employee
    {
        return Employee::where(function ($q) {
                $q->where('type', 'sopir')->orWhereNotNull('sim_number');
            })
            ->where('name', 'LIKE', '%' . $fragment . '%')
            ->first();
    }

    /**
     * Internal: Bind user_id ke employee dan return.
     */
    private static function linkAndReturn(Employee $driver, User $user): Employee
    {
        if (!$driver->user_id) {
            $driver->update(['user_id' => $user->id]);

            Log::info("[DriverMatching]: Employee #{$driver->id} ({$driver->name}) auto-linked ke User #{$user->id} ({$user->name})");
        }

        return $driver;
    }
}
