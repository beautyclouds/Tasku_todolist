<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subtask_read_statuses', function (Blueprint $table) {
            $table->id();
            
            // ⭐ PAKE unsignedBigInteger BIAR JELAS TIPENYA SAMA KAYAK ID TABEL ASAL
            $table->unsignedBigInteger('subtask_id'); 
            $table->unsignedBigInteger('user_id'); 
            
            $table->timestamp('last_read_at')->nullable(); 

            $table->timestamps(); 

            $table->unique(['subtask_id', 'user_id']); 
            
            // ⭐ DEFINE FOREIGN KEY SECARA MANUAL SETELAH KOLOM DIBUAT
            $table->foreign('subtask_id')->references('id')->on('sub_tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtask_read_statuses');
    }
};