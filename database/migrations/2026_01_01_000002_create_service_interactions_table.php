<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_interactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            // Nullable: a failed login has no authenticated user behind it.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('http_method', 10);
            $table->string('path');
            $table->string('service_name')->nullable();
            $table->json('request_body');
            $table->unsignedSmallInteger('response_status');
            $table->json('response_body');
            $table->string('ip_address', 45);
            $table->timestamp('occurred_at');

            $table->index(['user_id', 'occurred_at']);
            $table->index('service_name');
            $table->index('response_status');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_interactions');
    }
};
