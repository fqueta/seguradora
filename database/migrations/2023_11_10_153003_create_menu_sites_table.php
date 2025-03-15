<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_sites', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('categoria')->nullable();
            $table->string('description')->nullable();
            $table->string('url')->nullable();
            $table->integer('page_id')->nullable();
            $table->string('permission')->nullable();
            $table->string('pai')->nullable();
            $table->string('ordenar')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('actived')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_sites');
    }
}
