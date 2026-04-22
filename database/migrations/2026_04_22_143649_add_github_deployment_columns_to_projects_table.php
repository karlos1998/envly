<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('github_repository_id')->nullable()->after('identifier');
            $table->string('github_repository_full_name')->nullable()->after('github_repository_id');
            $table->string('github_workflow_id')->nullable()->after('github_repository_full_name');
            $table->string('github_workflow_name')->nullable()->after('github_workflow_id');
            $table->string('github_deploy_ref')->nullable()->after('github_workflow_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'github_repository_id',
                'github_repository_full_name',
                'github_workflow_id',
                'github_workflow_name',
                'github_deploy_ref',
            ]);
        });
    }
};
