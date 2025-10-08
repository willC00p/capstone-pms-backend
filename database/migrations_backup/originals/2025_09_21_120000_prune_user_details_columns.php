<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure table exists
        if (!Schema::hasTable('user_details')) {
            Schema::create('user_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('firstname')->nullable();
                $table->string('lastname')->nullable();
                $table->string('department')->nullable();
                $table->string('contact_number')->nullable();
                $table->string('plate_number')->nullable();
                $table->string('student_no')->nullable();
                $table->string('course')->nullable();
                $table->string('yr_section')->nullable();
                $table->string('position')->nullable();
                $table->string('or_cr_path')->nullable();
                $table->boolean('from_pending')->default(false);
                $table->timestamps();
            });
            return;
        }

        // For existing table: drop columns not in the canonical list
        $keep = [
            'id','user_id','firstname','lastname','department','contact_number','plate_number','student_no','course','yr_section','position','or_cr_path','from_pending','created_at','updated_at'
        ];

        Schema::table('user_details', function (Blueprint $table) use ($keep) {
            // Collect existing columns and drop those not in keep
            $columns = Schema::getColumnListing('user_details');
            foreach ($columns as $col) {
                if (!in_array($col, $keep)) {
                    try {
                        $table->dropColumn($col);
                    } catch (\Exception $e) {
                        // ignore drop failures for safety in some environments
                    }
                }
            }

            // Ensure essential columns exist
            if (!Schema::hasColumn('user_details', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('user_details', 'firstname')) {
                $table->string('firstname')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('user_details', 'lastname')) {
                $table->string('lastname')->nullable()->after('firstname');
            }
            if (!Schema::hasColumn('user_details', 'department')) {
                $table->string('department')->nullable()->after('lastname');
            }
            if (!Schema::hasColumn('user_details', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('department');
            }
            if (!Schema::hasColumn('user_details', 'plate_number')) {
                $table->string('plate_number')->nullable()->after('contact_number');
            }
            if (!Schema::hasColumn('user_details', 'student_no')) {
                $table->string('student_no')->nullable()->after('plate_number');
            }
            if (!Schema::hasColumn('user_details', 'course')) {
                $table->string('course')->nullable()->after('student_no');
            }
            if (!Schema::hasColumn('user_details', 'yr_section')) {
                $table->string('yr_section')->nullable()->after('course');
            }
            if (!Schema::hasColumn('user_details', 'position')) {
                $table->string('position')->nullable()->after('yr_section');
            }
            if (!Schema::hasColumn('user_details', 'or_cr_path')) {
                $table->string('or_cr_path')->nullable()->after('position');
            }
            if (!Schema::hasColumn('user_details', 'from_pending')) {
                $table->boolean('from_pending')->default(false)->after('or_cr_path');
            }
        });
    }

    public function down(): void
    {
        // no-op: do not try to reconstruct dropped columns
    }
};
