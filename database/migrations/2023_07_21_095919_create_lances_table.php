<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lances', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('leilao_id')->nullable();
            $table->float('valor_lance', 12, 2)->nullable();
            $table->string('type')->nullable();
            $table->json('config')->nullable();
            $table->integer('author')->nullable();
            $table->enum('ativo',['s','n']);
            $table->enum('superado',['n','s']);
            $table->longText('obs')->nullable();
            $table->string('token')->nullable();
            $table->enum('excluido',['n','s']);
            $table->text('reg_excluido')->nullable();
            $table->enum('deletado',['n','s']);
            $table->text('reg_deletado')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lances');
    }
}
