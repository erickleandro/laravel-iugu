<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionTable extends Migration
{
    protected $table;
    protected $iuguSubscriptionModelIdColumn;
    protected $iuguSubscriptionModelPlanColumn;

    public function __construct()
    {
        $this->table = getenv('GUPAYMENT_SIGNATURE_TABLE') ?: config('services.iugu.signature_table', 'subscriptions');
        $this->iuguSubscriptionModelIdColumn = getenv('IUGU_SUBSCRIPTION_MODEL_ID_COLUMN') ?: config('services.iugu.subscription_model_id_column', 'iugu_id');
        $this->iuguSubscriptionModelPlanColumn = getenv('IUGU_SUBSCRIPTION_MODEL_PLAN_COLUMN') ?: config('services.iugu.subscription_model_plan_column', 'iugu_plan');
    }

    public function up()
    {
        Schema::create($this->table, function ($table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('name');
            $table->string($this->iuguSubscriptionModelIdColumn);
            $table->string($this->iuguSubscriptionModelPlanColumn);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop($this->table);
    }
}