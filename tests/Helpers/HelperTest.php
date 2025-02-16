<?php

namespace Tests\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HelperTest extends TestCase
{
    #[Test]
    public function it_tests_is_admin_helper()
    {
        /* SETUP */
        $myPassword = 'my_password';
        $user = User::factory()->create(['username' => 'test_user', 'password' => Hash::make($myPassword)]);
        $existingUser = [
            'username' => $user->username,
            'password' => $myPassword,
        ];
        $this->postJson(route('auth.login'), $existingUser)->json('data.token');
        $permission = Permission::query()->create(['name' => 'full_access']);
        $role = Role::query()->create(['name' => 'admin']);
        $role->givePermissionTo($permission);

        /* EXECUTE */
        $user->assignRole($role->name);

        /* ASSERT */
        $this->assertTrue($user->hasRole($role->name));
        $this->assertTrue(is_admin());
    }
}
