<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('email_suppressions')) {
            Schema::create('email_suppressions', function (Blueprint $table) {
                $table->id();
                $table->string('email', 191)->unique()->index();
                $table->string('reason', 50)->default('bounce'); // bounce, complaint, unsubscribe, manual
                $table->string('sub_type', 50)->nullable(); // Permanent, Transient, Spam, etc.
                $table->unsignedBigInteger('workspace_id')->nullable()->index();
                $table->string('source', 50)->nullable()->default('sns_webhook');
                $table->text('details')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('campaign_dispatches')) {
            Schema::create('campaign_dispatches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id')->index();
                $table->unsignedBigInteger('group_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->integer('total_sensors')->default(0);
                $table->integer('enqueued_count')->default(0);
                $table->integer('skipped_empty')->default(0);
                $table->integer('skipped_suppressed')->default(0);
                $table->integer('skipped_invalid')->default(0);
                $table->integer('bounces_count')->default(0);
                $table->integer('complaints_count')->default(0);
                $table->string('status', 50)->default('dispatched'); // dispatched, warning, paused
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_suppressions');
        Schema::dropIfExists('campaign_dispatches');
    }
};
