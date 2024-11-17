<?php

use App\Models\User;
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
        Schema::create('background_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('class');
            $table->string('method');
            $table->json('params')->nullable();
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'cancelled']);
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->integer('priority')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamps();
            $table->timestamp('scheduled_at')->nullable();
            $table->foreignIdFor(User::class);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('background_jobs');
    }
};
