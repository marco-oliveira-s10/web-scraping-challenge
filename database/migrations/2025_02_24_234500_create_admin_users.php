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
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('admin'); // admin, super_admin, etc.
            $table->json('settings')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        
        // Create notifications table for storing admin notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        
        // Create table for scraping logs
        Schema::create('scraping_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // success, error, warning, info
            $table->string('category')->nullable();
            $table->text('message');
            $table->json('context')->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_users');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('scraping_logs');
    }
};
