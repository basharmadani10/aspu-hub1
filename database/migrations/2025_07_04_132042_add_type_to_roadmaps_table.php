<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('roadmaps', function (Blueprint $table) {
            if (!Schema::hasColumn('roadmaps', 'type')) {
                $table->string('type')->default('Outside')->after('specialization_id'); // Or 'Inside'
            }
        });
    }


    public function down(): void
    {
        Schema::table('roadmaps', function (Blueprint $table) {
            if (Schema::hasColumn('roadmaps', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
