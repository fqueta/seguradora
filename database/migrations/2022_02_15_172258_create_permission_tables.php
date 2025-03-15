<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
// use Spatie\Permission\PermissionRegistrar;

class CreatePermissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('permission.table_names');
        // $columnNames = config('permission.column_names');
        // $teams = config('permission.teams');

        // if (empty($tableNames)) {
        //     throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        // }
        // if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
        //     throw new \Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        // }

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');       // For MySQL 8.0 use string('name', 125);
            $table->json('id_menu')->nullable();
            $table->string('redirect_login')->nullable();
            $table->json('config')->nullable();
            $table->longText('description')->nullable()->default('text');
            $table->string('guard_name')->nullable()->default('web'); // For MySQL 8.0 use string('guard_name', 125);
            $table->timestamps();
            $table->enum('active',['s','n']);
            $table->integer('autor')->nullable();
            $table->string('token','60')->nullable();
            $table->enum('excluido',['n','s']);
            $table->text('reg_excluido')->nullable();
            $table->enum('deletado',['n','s']);
            $table->text('reg_deletado')->nullable();
            $table->unique(['name', 'guard_name']);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        // Schema::drop($tableNames['role_has_permissions']);
        // Schema::drop($tableNames['model_has_roles']);
        // Schema::drop($tableNames['model_has_permissions']);
        // Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
}
