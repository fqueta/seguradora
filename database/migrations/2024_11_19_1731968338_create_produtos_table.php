<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutosTable extends Migration
{
    public function up()
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id()->primary();
            $table->timestamps();
            $table->string('token',50)->nullable();
            $table->string('ref',200)->nullable();
            $table->string('cod_barras',200)->nullable();
            $table->text('categoria')->nullable();
            $table->string('nome',300)->nullable();
            $table->text('titulo')->nullable();
            $table->text('descricao')->nullable();
            $table->text('obs')->nullable();
            $table->float('preco_custo')->nullable();
            $table->float('lucro')->nullable();
            $table->float('imposto')->nullable();
            $table->enum('tipo',['p','s'])->description('p para produto e s para serviços');
            $table->string('meta_descricao',200)->nullable();
            $table->float('valor_parcela')->nullable();
            $table->string('tamanho',60)->nullable();
            $table->integer('tipo_produto')->nullable();
            // $table->string('url',200)->nullable();
            // $table->integer('duracao')->nullable();
            // $table->string('unidade_medida',11)->nullable();
            // $table->enum('ativo',['s','n']);
            // $table->enum('destaque',['n','s']);
            // $table->integer('ordenar')->nullable();
            // $table->integer('autor')->nullable();
            // $table->integer('marca')->nullable();
            // $table->integer('fornecedor')->nullable();
            // $table->enum('excluido',['n','s']);
            // $table->text('reg_excluido')->nullable();
            // $table->enum('deletado',['n','s']);
            // $table->text('reg_deletado')->nullable();
            // $table->string('cor',100)->nullable();
            // $table->string('nota_alt',100)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('produtos');
    }
}
