<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function role(string $name): Role
    {
        return Role::firstOrCreate(['name' => $name], ['description' => $name]);
    }

    private function userWithRole(string $name): User
    {
        return User::factory()->create(['role_id' => $this->role($name)->id])->fresh('role');
    }

    public function test_sign_up_cannot_choose_admin_role(): void
    {
        $admin = $this->role('admin');
        $this->role('user');

        $this->post('/register', [
            'name' => 'Stranger',
            'email' => 'stranger@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $admin->id,
        ]);

        $user = User::where('email', 'stranger@example.com')->firstOrFail();
        $this->assertSame('user', $user->role->name);
    }

    public function test_sign_up_form_has_no_role_picker(): void
    {
        $this->get('/register')->assertOk()->assertDontSee('name="role_id"', false);
    }

    public function test_non_admin_cannot_list_users_or_change_roles(): void
    {
        $clerk = $this->userWithRole('data_clerk');
        $admin = $this->role('admin');

        $this->actingAs($clerk)->get('/users')->assertForbidden();
        $this->actingAs($clerk)
            ->post("/users/{$clerk->id}/assign-role", ['role_id' => $admin->id])
            ->assertForbidden();

        $this->assertSame('data_clerk', $clerk->fresh('role')->role->name);
    }

    public function test_admin_can_change_someone_elses_role_but_not_their_own(): void
    {
        $admin = $this->userWithRole('admin');
        $newbie = $this->userWithRole('user');
        $clerkRole = $this->role('data_clerk');

        $this->actingAs($admin)->get('/users')->assertOk()->assertSee($newbie->email);

        $this->actingAs($admin)
            ->post("/users/{$newbie->id}/assign-role", ['role_id' => $clerkRole->id])
            ->assertSessionHasNoErrors();
        $this->assertSame('data_clerk', $newbie->fresh('role')->role->name);

        $this->actingAs($admin)
            ->post("/users/{$admin->id}/assign-role", ['role_id' => $clerkRole->id])
            ->assertSessionHasErrors('role_id');
        $this->assertSame('admin', $admin->fresh('role')->role->name);
    }
}
