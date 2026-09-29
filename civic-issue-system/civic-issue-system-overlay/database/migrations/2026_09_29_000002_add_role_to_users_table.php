<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['citizen', 'admin', 'staff'])->default('citizen')->after('email');
            $table->foreignId('department_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->string('phone', 20)->nullable()->after('department_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn(['role', 'phone']);
        });
    }
};
