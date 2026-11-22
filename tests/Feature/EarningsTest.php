<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\EarningsCalculator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EarningsTest extends TestCase
{
    // The live migrations can't build a fresh database (the live DB came from
    // a dump), so build only the tables these tests touch.
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->timestamps();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password');
            $t->rememberToken();
            $t->foreignId('role_id')->nullable();
            foreach (['phone', 'bio', 'avatar', 'country', 'city'] as $c) {
                $t->string($c)->nullable();
            }
            $t->timestamps();
        });
        Schema::create('system', function (Blueprint $t) {
            $t->id();
            foreach ([
                'name', 'logo', 'favicon', 'slogan', 'timezone', 'date_format', 'time_format',
                'currency', 'currency_symbol', 'primary_color', 'secondary_color', 'contact_email',
                'contact_phone', 'address', 'meta_description', 'meta_keywords', 'facebook_url',
                'twitter_url', 'instagram_url', 'linkedin_url', 'pagination_limit', 'custom_css',
                'custom_js', 'settings',
            ] as $c) {
                $t->text($c)->nullable();
            }
            $t->boolean('maintenance_mode')->default(false);
            $t->timestamps();
        });
        Schema::create('marriages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('created_by')->nullable();
            $t->string('system_status')->nullable();
            $t->timestamps();
        });
    }

    private function role(string $name): int
    {
        return DB::table('roles')->where('name', $name)->value('id')
            ?? DB::table('roles')->insertGetId(['name' => $name]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role_id' => $this->role($role)])->save();

        return $user->fresh('role');
    }

    private function entries(User $user, int $count, string $status = 'Pending', $at = null): void
    {
        $at ??= now();
        $rows = array_fill(0, $count, [
            'created_by' => $user->id,
            'system_status' => $status,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
        DB::table('marriages')->insert($rows);
    }

    public function test_fifty_entries_make_one_paid_day(): void
    {
        $calc = app(EarningsCalculator::class);

        $this->assertSame(0, $calc->summarise(49)['earnings']);
        $this->assertSame(1000, $calc->summarise(50)['earnings']);
        $this->assertSame(1000, $calc->summarise(99)['earnings']);
        $this->assertSame(2000, $calc->summarise(100)['earnings']);
        $this->assertSame(1, $calc->summarise(51)['towards_next_day']);
        $this->assertSame(49, $calc->summarise(51)['needed_for_next_day']);
    }

    public function test_clerk_sees_own_earnings_excluding_cancelled(): void
    {
        $clerk = $this->userWithRole('data_clerk');
        $this->entries($clerk, 120);
        $this->entries($clerk, 10, 'Cancelled');
        $this->entries($clerk, 50, 'Pending', now()->subMonthNoOverflow()); // last month

        $this->actingAs($clerk)->get('/earnings')
            ->assertOk()
            ->assertSee('My Earnings')
            ->assertSee('KES 2,000')
            ->assertSee('20 / 50', false)
            ->assertDontSee('All staff');
    }

    public function test_clerk_cannot_view_another_persons_earnings(): void
    {
        $clerk = $this->userWithRole('data_clerk');
        $other = $this->userWithRole('marriage_teller');

        $this->actingAs($clerk)->get("/earnings/{$other->id}")->assertForbidden();
    }

    public function test_admin_sees_all_staff_and_details(): void
    {
        $admin = $this->userWithRole('admin');
        $clerk = $this->userWithRole('data_clerk');
        $teller = $this->userWithRole('marriage_teller');
        $this->entries($clerk, 150);
        $this->entries($teller, 50);

        $this->actingAs($admin)->get('/earnings')
            ->assertOk()
            ->assertSee('All staff')
            ->assertSee($clerk->name)
            ->assertSee('KES 3,000')
            ->assertSee('KES 4,000');

        $this->actingAs($admin)->get("/earnings/{$clerk->id}")
            ->assertOk()
            ->assertSee('KES 3,000');
    }

    public function test_month_filter_and_bad_input(): void
    {
        $clerk = $this->userWithRole('data_clerk');
        $this->entries($clerk, 50, 'Pending', now()->subMonthNoOverflow());

        $this->actingAs($clerk)->get('/earnings?month=' . now()->subMonthNoOverflow()->format('Y-m'))
            ->assertOk()->assertSee('KES 1,000');

        $this->actingAs($clerk)->get('/earnings?month=garbage')->assertOk();
    }

    public function test_guests_are_redirected(): void
    {
        $this->get('/earnings')->assertRedirect();
    }
}
