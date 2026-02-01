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
        Schema::create('ag_hmac_keys', function (Blueprint $table) {
            $table->id()->from(100);
            $table->nullableMorphs('owner');
            $table->string('name');
            $table->string('access_key', 64)->unique();
            $table->string('secret', 100);
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
        Schema::dropIfExists('ag_hmac_keys');
    }
};
