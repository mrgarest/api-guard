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
        Schema::create('ag_jwt_clients', function (Blueprint $table) {
            $table->id()->from(100);
            $table->nullableMorphs('owner');
            $table->string('name');
            $table->string('profile', 36)->default('default');
            $table->string('client_id', 64)->unique();
            $table->text('secret');
            $table->boolean('revoked')->default(false);
            $table->json('scopes')->nullable();
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ag_jwt_clients');
    }
};
