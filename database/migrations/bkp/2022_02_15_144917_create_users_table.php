<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_pessoa',['pf','pj']); //pf = pessoa fisica, pj = pessoa juridica
            $table->string('nome');
            $table->string('razao')->nullable();
            $table->string('cpf')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->enum('status',['actived','inactived','pre_registred']);
            $table->enum('genero',['ni','m','f']); //ni = não informado
            $table->enum('verificado',['n','s']);
            $table->integer('id_permission')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->json('config')->nullable();
            $table->json('preferencias')->nullable();
            $table->text('foto_perfil')->nullable();
            $table->enum('ativo',['s','n']);
            $table->integer('autor')->nullable();
            $table->string('token','60')->nullable();
            $table->enum('excluido',['n','s']);
            $table->text('reg_excluido')->nullable();
            $table->enum('deletado',['n','s']);
            $table->text('reg_deletado')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
