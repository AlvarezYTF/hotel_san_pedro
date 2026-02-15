<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("shift_controls", function (Blueprint $table) {
            $table->id();
            $table->boolean("operational_enabled")->default(true);
            $table->foreignId("updated_by")
                ->nullable()
                ->constrained("users")
                ->nullOnDelete();
            $table->string("note", 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("shift_controls");
    }
};

