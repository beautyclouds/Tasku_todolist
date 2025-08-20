<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE board_cards MODIFY COLUMN status ENUM('Pending', 'In Progress', 'Completed', 'Archived') DEFAULT 'Pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE board_cards MODIFY COLUMN status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending'");
    }
};
