<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('board_cards', function (Blueprint $table) {
            $table->boolean('is_revised')->default(false);
        });
    }

    public function down()
    {
        Schema::table('board_cards', function (Blueprint $table) {
            $table->dropColumn('is_revised');
        });
    }
};
