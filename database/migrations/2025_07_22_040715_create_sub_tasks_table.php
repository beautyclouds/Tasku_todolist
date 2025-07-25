<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sub_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_card_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_done')->default(false); // ✅ Tambahan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_tasks');
    }
};
