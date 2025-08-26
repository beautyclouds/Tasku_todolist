<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom user_id ke tabel board_cards
        Schema::table('board_cards', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->nullable() // Atur nullable sementara jika data sudah ada
                  ->after('id')
                  ->constrained()
                  ->onDelete('cascade');
        });

        // 2. Buat tabel pivot untuk kolaborasi antar-user
        Schema::create('board_collaborator', function (Blueprint $table) {
            $table->foreignId('board_card_id')
                  ->constrained('board_cards')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->timestamps();
            $table->primary(['board_card_id', 'user_id']);
        });

        // 3. Hapus tabel lama yang tidak diperlukan
        Schema::dropIfExists('board_card_member');
        Schema::dropIfExists('members');
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        // Re-create tabel members dan board_card_member jika perlu
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('photo')->nullable();
            $table->timestamps();
        });
        Schema::create('board_card_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_card_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
        
        // Hapus tabel board_collaborator
        Schema::dropIfExists('board_collaborator');

        // Hapus kolom user_id dari board_cards
        Schema::table('board_cards', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
