<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sics', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('token','60')->nullable();
            $table->string('protocolo','60')->nullable();
            $table->string('nome')->nullable();
            $table->string('type')->nullable();
            $table->integer('status')->nullable();
            $table->integer('situacao')->nullable();
            $table->integer('motivo')->nullable();
            $table->enum('ativo',['s','n']);
            $table->longText('mensagem')->nullable();
            $table->longText('resposta')->nullable();
            $table->longText('arquivo')->nullable();
            $table->json('recurso')->nullable();
            $table->longText('obs')->nullable();
            $table->json('meta')->nullable();
            $table->json('config')->nullable();
            $table->integer('id_requerente')->nullable();
            $table->integer('autor')->nullable();
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
        Schema::dropIfExists('sics');
    }
}
