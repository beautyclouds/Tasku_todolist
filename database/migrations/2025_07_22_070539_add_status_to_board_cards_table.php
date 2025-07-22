<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('board_cards', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'In Progress', 'Completed'])->default('Pending')->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('board_cards', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
