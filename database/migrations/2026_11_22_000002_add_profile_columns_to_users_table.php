<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns the User model and profile page use. Live already has most of
     * them (added by hand); it lacks phone and email_verified_at. Fresh
     * installs get all of them. Only missing columns are added.
     */
    private function columns(): array
    {
        return [
            'email_verified_at' => fn (Blueprint $t) => $t->timestamp('email_verified_at')->nullable(),
            'remember_token' => fn (Blueprint $t) => $t->rememberToken(),
            'avatar' => fn (Blueprint $t) => $t->string('avatar')->nullable(),
            'phone' => fn (Blueprint $t) => $t->string('phone', 20)->nullable(),
            'bio' => fn (Blueprint $t) => $t->text('bio')->nullable(),
            'country' => fn (Blueprint $t) => $t->string('country')->nullable(),
            'city' => fn (Blueprint $t) => $t->string('city')->nullable(),
            'postal_code' => fn (Blueprint $t) => $t->string('postal_code')->nullable(),
            'tax_id' => fn (Blueprint $t) => $t->string('tax_id')->nullable(),
            'social' => fn (Blueprint $t) => $t->json('social')->nullable(),
        ];
    }

    public function up(): void
    {
        $missing = array_filter(
            $this->columns(),
            fn ($column) => ! Schema::hasColumn('users', $column),
            ARRAY_FILTER_USE_KEY
        );

        if ($missing === []) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($missing) {
            foreach ($missing as $add) {
                $add($table);
            }
        });
    }

    public function down(): void
    {
        // Not reversible: on live most of these columns predate this migration.
    }
};
