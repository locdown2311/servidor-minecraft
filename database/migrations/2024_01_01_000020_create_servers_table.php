<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('status', [
                'pending', 'provisioning', 'running', 'stopped', 'expired', 'error'
            ])->default('pending');
            $table->integer('port')->nullable();
            $table->integer('ftp_port')->nullable();
            $table->string('ftp_user')->nullable();
            $table->string('ftp_password')->nullable();
            $table->string('container_id')->nullable();
            $table->string('ftp_container_id')->nullable();
            $table->string('minecraft_version')->default('latest');
            $table->string('server_type')->default('VANILLA');
            $table->string('motd')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
