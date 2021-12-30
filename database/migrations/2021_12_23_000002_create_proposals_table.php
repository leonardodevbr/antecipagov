<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProposalsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'proposals';

    /**
     * Run the migrations.
     * @table proposals
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('product_id');
            $table->string('code', 100);
            $table->string('external_proposal_id', 50)->nullable();
            $table->decimal('amount', 9)->nullable();
            $table->decimal('payment_flow', 9)->nullable();
            $table->decimal('net_amount', 9)->nullable();
            $table->string('status', 100)->nullable();
            $table->integer('quota_qty')->nullable();
            $table->decimal('quota_amount', 9)->nullable();
            $table->decimal('fine_amount', 9)->nullable();
            $table->decimal('insurance_amount', 9)->nullable();
            $table->decimal('tax', 5)->nullable();
            $table->decimal('late_tax', 5)->nullable();
            $table->decimal('iof', 5)->nullable();
            $table->decimal('tac', 5)->nullable();
            $table->decimal('cet', 5)->nullable();
            $table->date('release_date')->nullable();
            $table->string('accepted', 100)->nullable();
            $table->string('modality', 100)->nullable();
            $table->date('loaned_at')->nullable();
            $table->dateTime('last_quota_at')->nullable();

            $table->index(["account_id"], 'fk_proposals_accounts_idx');
            $table->index(["product_id"], 'fk_proposals_products_idx');

            $table->nullableTimestamps();


            $table->foreign('account_id', 'fk_proposals_accounts_idx')
                ->references('id')->on('accounts')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreign('product_id', 'fk_proposals_products_idx')
                ->references('id')->on('products')
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
