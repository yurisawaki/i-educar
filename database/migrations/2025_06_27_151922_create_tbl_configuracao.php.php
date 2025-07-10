<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_configuracao', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('tempo');
            $table->integer('distancia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_configuracao');
    }
};
