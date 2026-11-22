<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns that were added by hand on the live server and that the code
     * relies on. Live already has them, so this is a no-op there; fresh
     * installs get them. Only missing columns are added.
     */
    private function columns(): array
    {
        return [
            'marriages' => [
                'pdf_id' => fn (Blueprint $t) => $t->unsignedBigInteger('pdf_id')->nullable()->index(),
                'pdf_page_id' => fn (Blueprint $t) => $t->unsignedBigInteger('pdf_page_id')->nullable(),
                'ward_id' => fn (Blueprint $t) => $t->foreignId('ward_id')->nullable()
                    ->constrained('counties')->nullOnDelete()->cascadeOnUpdate(),
                'reviewed_at' => fn (Blueprint $t) => $t->timestamp('reviewed_at')->nullable(),
            ],
            'clerk_management' => [
                'assignment_type' => fn (Blueprint $t) => $t->string('assignment_type', 20)->nullable()->default('manual'),
                'assigned_pages' => fn (Blueprint $t) => $t->json('assigned_pages')->nullable(),
            ],
            'marriage_type_extensions' => [
                'registrar_officer' => fn (Blueprint $t) => $t->string('registrar_officer')->nullable(),
            ],
        ];
    }

    public function up(): void
    {
        foreach ($this->columns() as $tableName => $columns) {
            $missing = array_filter(
                $columns,
                fn ($column) => ! Schema::hasColumn($tableName, $column),
                ARRAY_FILTER_USE_KEY
            );

            if ($missing === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($missing) {
                foreach ($missing as $add) {
                    $add($table);
                }
            });
        }
    }

    public function down(): void
    {
        // Not reversible: on live these columns predate this migration.
    }
};
