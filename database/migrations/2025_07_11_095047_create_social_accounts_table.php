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
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('handle_id');
            $table->string('platform');
            $table->string('url')->nullable();
            $table->string('name')->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_image')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('verification_type')->nullable();
            $table->boolean('official_account')->default(false);
            $table->string('account_type')->nullable();
            $table->timestamps();

            $table->foreign('handle_id')->references('id')->on('handles')->onDelete('cascade');
            $table->unique(['handle_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
