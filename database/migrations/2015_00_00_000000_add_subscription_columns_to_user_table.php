<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscriptionColumnsToUserTable extends Migration
{
    protected $table;
    protected $column;

    public function __construct()
    {
        $model = getenv('IUGU_MODEL') ?: config('services.iugu.user_model', 'App\User');
        $this->table = (new $model)->getTable();
        $this->column = getenv('IUGU_USER_MODEL_COLUMN') ?: config('services.iugu.user_model_column', 'iugu_id');
    }

    public function up()
    {
        Schema::table($this->table, function ($table) {
            $table->string($this->column)->nullable();
        });
    }

    public function down()
    {
        Schema::table($this->table, function ($table) {
            $table->dropColumn($this->column);
        });
    }
}