<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        // Populate existing users with username from email prefix
        $users = \Illuminate\Support\Facades\DB::table('users')->get();
        foreach ($users as $user) {
            $baseUsername = strtolower(explode('@', $user->email)[0]);
            $baseUsername = preg_replace('/[^a-z0-9_.]/', '', $baseUsername);
            if (empty($baseUsername)) {
                $baseUsername = 'user_' . $user->id;
            }
            \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->update([
                'username' => $baseUsername,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
