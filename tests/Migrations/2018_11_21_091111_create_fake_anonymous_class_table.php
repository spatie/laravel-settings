<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fake-anonymous-class-table', function (Blueprint $table): void {
            $table->id();

            $table->timestamps();
        });
    }
};
