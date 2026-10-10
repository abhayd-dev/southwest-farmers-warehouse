<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Scheduling + timekeeping (kitchen spec sections 9-10, contract B.8, B.9).
 * One system for all store staff (store time clock and kitchen timesheets
 * used to be two):
 *  - employee_schedules (+ employee_schedule_changes history)
 *  - store_time_logs gains the rule used, late/early minutes, approval and
 *    corrections (time_log_corrections keeps every original value)
 *  - cross_location_permissions: who may clock in at which other store
 *  - clock_overrides: GM approval for an unscheduled clock-in
 *  - store_settings: early clock-in window and late/early grace minutes
 *  - permissions manage_schedules, approve_time_exceptions (GM),
 *    approve_timesheets (manager), given to the store Super Admin role
 * Existing kitchen shifts / kitchen time logs are copied in once.
 *
 * store_users.staff_code and store_time_logs existed only in a store-repo
 * migration (which never runs on deploy); guarded copies first.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_users', function (Blueprint $table) {
            if (!Schema::hasColumn('store_users', 'staff_code')) {
                $table->string('staff_code')->nullable()->unique()->after('id');
            }
        });

        if (!Schema::hasTable('store_time_logs')) {
            Schema::create('store_time_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_user_id')->index();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->timestamp('clock_in_at')->nullable();
                $table->timestamp('clock_out_at')->nullable();
                $table->decimal('total_hours', 6, 2)->nullable();
                $table->unsignedInteger('break_minutes')->default(0);
                $table->string('status')->default('clocked_in');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('employee_schedules')) {
            Schema::create('employee_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->index();
                $table->unsignedBigInteger('store_user_id')->index();
                $table->date('shift_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('role')->nullable();     // assignment / station (Cashier, Bakery...)
                $table->string('status', 20)->default('scheduled'); // scheduled | cancelled
                $table->text('notes')->nullable();
                $table->string('created_by_name')->nullable();
                $table->string('updated_by_name')->nullable();
                $table->unsignedBigInteger('legacy_kitchen_shift_id')->nullable()->unique();
                $table->timestamps();
                $table->index(['store_id', 'shift_date']);
            });
        }

        if (!Schema::hasTable('employee_schedule_changes')) {
            Schema::create('employee_schedule_changes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_schedule_id')->index();
                $table->string('action', 20); // created | updated | cancelled | deleted
                $table->json('changes')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_name')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        Schema::table('store_time_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('store_time_logs', 'clock_basis')) {
                $table->unsignedBigInteger('employee_schedule_id')->nullable();
                $table->timestamp('scheduled_start_at')->nullable();
                $table->timestamp('scheduled_end_at')->nullable();
                $table->unsignedBigInteger('assigned_store_id')->nullable(); // employee's home store
                // scheduled | cross_location | gm_override | gm_entered | legacy
                $table->string('clock_basis', 20)->default('scheduled');
                $table->unsignedBigInteger('cross_location_permission_id')->nullable();
                $table->string('override_by_name')->nullable();
                $table->text('override_reason')->nullable();
                $table->unsignedInteger('late_minutes')->default(0);
                $table->unsignedInteger('early_leave_minutes')->default(0);
                $table->string('clock_in_by_name')->nullable();
                $table->string('clock_out_by_name')->nullable();
                $table->boolean('corrected')->default(false);
                $table->string('approved_by_name')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('legacy_kitchen_time_log_id')->nullable()->unique();
            }
        });

        if (!Schema::hasTable('time_log_corrections')) {
            Schema::create('time_log_corrections', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_time_log_id')->index();
                $table->string('field', 30); // clock_in_at | clock_out_at | break_minutes
                $table->string('old_value')->nullable();
                $table->string('new_value')->nullable();
                $table->text('reason');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_name')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('cross_location_permissions')) {
            Schema::create('cross_location_permissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_user_id');
                $table->unsignedBigInteger('store_id'); // other store this person may clock in at
                $table->string('granted_by_name')->nullable();
                $table->timestamps();
                $table->unique(['store_user_id', 'store_id']);
            });
        }

        if (!Schema::hasTable('clock_overrides')) {
            Schema::create('clock_overrides', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_user_id');
                $table->unsignedBigInteger('store_id');
                $table->date('work_date');
                $table->text('reason');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->string('approved_by_name')->nullable();
                $table->unsignedBigInteger('used_time_log_id')->nullable();
                $table->timestamps();
                $table->index(['store_user_id', 'store_id', 'work_date']);
            });
        }

        if (Schema::hasTable('store_settings')) {
            Schema::table('store_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('store_settings', 'clock_early_minutes')) {
                    $table->unsignedSmallInteger('clock_early_minutes')->default(15);   // may clock in this long before shift start
                    $table->unsignedSmallInteger('late_grace_minutes')->default(5);     // later than this = Late
                    $table->unsignedSmallInteger('early_leave_grace_minutes')->default(5); // earlier than this = Early departure
                }
            });
        }

        $this->addPermissions();
        $this->copyKitchenTimesheets();
    }

    private function addPermissions(): void
    {
        if (!Schema::hasTable('store_permissions')) {
            return;
        }
        $guard = DB::table('store_permissions')->value('guard_name') ?? 'store_user';
        $names = ['manage_schedules', 'approve_time_exceptions', 'approve_timesheets'];
        foreach ($names as $name) {
            if (!DB::table('store_permissions')->where('name', $name)->exists()) {
                DB::table('store_permissions')->insert(['name' => $name, 'guard_name' => $guard, 'group_name' => 'Timekeeping', 'created_at' => now(), 'updated_at' => now()]);
            }
        }
        $superAdmin = DB::table('store_roles')->where('name', 'Super Admin')->value('id');
        if ($superAdmin && Schema::hasTable('store_role_has_permissions')) {
            foreach (DB::table('store_permissions')->whereIn('name', $names)->pluck('id') as $pid) {
                if (!DB::table('store_role_has_permissions')->where(['permission_id' => $pid, 'role_id' => $superAdmin])->exists()) {
                    DB::table('store_role_has_permissions')->insert(['permission_id' => $pid, 'role_id' => $superAdmin]);
                }
            }
        }
    }

    /** One-time copy of the old kitchen timesheets into the unified tables. */
    private function copyKitchenTimesheets(): void
    {
        if (!Schema::hasTable('kitchen_shifts') || !Schema::hasColumn('kitchen_locations', 'store_id')) {
            return;
        }

        $storeOf = DB::table('kitchen_locations')->whereNotNull('store_id')->pluck('store_id', 'id');

        foreach (DB::table('kitchen_shifts')->orderBy('id')->get() as $s) {
            $storeId = $storeOf[$s->kitchen_location_id] ?? null;
            if (!$storeId || DB::table('employee_schedules')->where('legacy_kitchen_shift_id', $s->id)->exists()) {
                continue;
            }
            DB::table('employee_schedules')->insert([
                'store_id' => $storeId, 'store_user_id' => $s->ware_user_id, 'shift_date' => $s->shift_date,
                'start_time' => $s->start_time, 'end_time' => $s->end_time, 'role' => $s->station,
                'status' => $s->status === 'cancelled' ? 'cancelled' : 'scheduled', 'notes' => $s->notes,
                'created_by_name' => 'Copied from kitchen timesheets', 'legacy_kitchen_shift_id' => $s->id,
                'created_at' => $s->created_at, 'updated_at' => $s->updated_at,
            ]);
        }

        if (!Schema::hasTable('kitchen_time_logs')) {
            return;
        }
        foreach (DB::table('kitchen_time_logs')->orderBy('id')->get() as $l) {
            $storeId = $storeOf[$l->kitchen_location_id] ?? null;
            if (!$storeId || DB::table('store_time_logs')->where('legacy_kitchen_time_log_id', $l->id)->exists()) {
                continue;
            }
            DB::table('store_time_logs')->insert([
                'store_user_id' => $l->ware_user_id, 'store_id' => $storeId, 'assigned_store_id' => $storeId,
                'clock_in_at' => $l->clock_in_at, 'clock_out_at' => $l->clock_out_at, 'total_hours' => $l->total_hours,
                'break_minutes' => $l->break_minutes ?? 0, 'status' => $l->clock_out_at ? 'clocked_out' : 'clocked_in',
                'notes' => $l->notes, 'clock_basis' => 'legacy', 'legacy_kitchen_time_log_id' => $l->id,
                'created_at' => $l->created_at, 'updated_at' => $l->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        foreach (['clock_overrides', 'cross_location_permissions', 'time_log_corrections', 'employee_schedule_changes', 'employee_schedules'] as $t) {
            Schema::dropIfExists($t);
        }
        // store_time_logs keeps its new columns on rollback (it holds live clock data).
        if (Schema::hasTable('store_settings')) {
            Schema::table('store_settings', function (Blueprint $table) {
                foreach (['clock_early_minutes', 'late_grace_minutes', 'early_leave_grace_minutes'] as $col) {
                    if (Schema::hasColumn('store_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
