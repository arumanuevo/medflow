<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workspace_collaborators', function (Blueprint $table) {
            $table->boolean('has_restricted_access')->default(false)->after('is_paused');
        });

        Schema::create('collaborator_sensor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_collaborator_id')->constrained('workspace_collaborators')->cascadeOnDelete();
            $table->foreignId('sensor_id')->constrained('sensors')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collaborator_sensor');

        Schema::table('workspace_collaborators', function (Blueprint $table) {
            $table->dropColumn('has_restricted_access');
        });
    }
};
