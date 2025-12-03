<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // foreign key ke sub_tasks
            $table->foreignId('subtask_id')
                ->constrained('sub_tasks')
                ->onDelete('cascade');

            // foreign key ke users
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // tipe komentar: text, file, image, link, dsb
            $table->string('type')->default('text');

            // isi pesan (untuk text atau link)
            $table->text('message')->nullable();

            // path file (untuk image/file upload)
            $table->string('file_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
