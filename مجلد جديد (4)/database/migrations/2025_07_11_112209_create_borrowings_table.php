<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('book_id')->constrained();
            $table->date('borrow_date');
            $table->date('return_date');
            $table->enum('status', ['borrowed', 'returned'])->default('borrowed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};
