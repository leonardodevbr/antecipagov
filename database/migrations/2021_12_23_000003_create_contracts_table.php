<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'contracts';

    /**
     * Run the migrations.
     * @table contracts
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('proposal_id');
            $table->string('year_number');
            $table->string('uasg');
            $table->string('uasg_name');

            $table->index(["proposal_id"], 'fk_contracts_proposals_idx');
            $table->nullableTimestamps();

            $table->foreign('proposal_id', 'fk_contracts_proposals_idx')
                ->references('id')->on('proposals')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}
