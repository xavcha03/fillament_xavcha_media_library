<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            if (! Schema::hasColumn('media_files', 'checksum')) {
                $table->string('checksum', 64)->nullable()->after('path');
            }
        });

        Schema::table('media_files', function (Blueprint $table) {
            // Empêche les doublons sur un même disk quand checksum est renseigné.
            // (Si checksum null, l'index unique n'est pas contraignant en MySQL.)
            $table->unique(['disk', 'checksum'], 'media_files_disk_checksum_unique');
        });
    }

    public function down(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            $table->dropUnique('media_files_disk_checksum_unique');
            $table->dropColumn('checksum');
        });
    }
};

