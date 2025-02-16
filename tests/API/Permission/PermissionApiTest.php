<?php

namespace Tests\API\Permission;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionApiTest extends TestCase
{
    private string $token = "";
    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $myPassword = 'my_password';
        $this->user = User::factory()->create(['username' => 'test_user', 'password' => Hash::make($myPassword)]);
        $existingUser = [
            'username' => $this->user->username,
            'password' => $myPassword,
        ];
        $this->token = $this->postJson(route('auth.login'), $existingUser)->json('data.token');
    }

    #[Test]
    public function it_tests_permission_create_request()
    {
        /* SETUP */
        $newPermission = [
            'name' => 'test_permission',
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.permission', $newPermission));

        /* ASSERT */
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'name' => $newPermission['name'],
                ]
            ]);
    }

    #[Test]
    public function it_tests_permission_update_request()
    {
        /* SETUP */
        $oldPermission = Permission::query()->create([
            'name' => 'old_permission',
        ]);
        $newPermission = [
            'name' => 'new_permission',
            'permission' => $oldPermission->id,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson(route('permissions.update.permission', $newPermission));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'message' => 'Permission updated successfully!',
            ]);
        $this->assertEquals($newPermission['name'], Permission::query()->find($oldPermission->id)?->name);
    }

    #[Test]
    public function it_tests_permission_delete_request()
    {
        /* SETUP */
        $permission = Permission::query()->create([
            'name' => 'old_permission',
        ]);
        $parameters = [
            'permission' => $permission->id,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('permissions.delete.permission', $parameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'message' => 'Permission deleted successfully!',
            ]);
        $this->assertNull(Permission::query()->find($permission->id));
    }

    #[Test]
    public function it_tests_unique_validation_for_permission_create_request()
    {
        /* SETUP */
        $firstPermission = [
            'name' => 'test_permission_0',
        ];
        $secondPermission = [
            'name' => 'test_permission_0',
        ];

        /* EXECUTE */
        // First permission creation
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.permission', $firstPermission));
        // Second permission creation request which should fail
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.permission', $secondPermission));

        /* ASSERT */
        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    #[Test]
    public function it_tests_list_permissions_request()
    {
        /* SETUP */
        $firstPermission = [
            'name' => 'test_permission_0',
        ];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.permission', $firstPermission));
        $secondPermission = [
            'name' => 'test_permission_1',
        ];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.permission', $secondPermission));

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson(route('permissions.list.permissions'));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'name' => $firstPermission['name'],
                    ],
                    [
                        'name' => $secondPermission['name'],
                    ],
                ]
            ]);
    }

    #[Test]
    public function it_tests_role_create_request()
    {
        /* SETUP */
        $newRole = [
            'name' => 'test_role',
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.role', $newRole));

        /* ASSERT */
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'name' => $newRole['name'],
                ]
            ]);
    }

    #[Test]
    public function it_tests_role_update_request()
    {
        /* SETUP */
        $oldRole = Role::query()->create([
            'name' => 'old_role',
        ]);
        $newRole = [
            'name' => 'new_role',
            'role' => $oldRole->id,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson(route('permissions.update.role', $newRole));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'message' => 'Role updated successfully!',
            ]);
        $this->assertEquals($newRole['name'], Role::query()->find($oldRole->id)?->name);
    }

    #[Test]
    public function it_tests_role_delete_request()
    {
        /* SETUP */
        $role = Role::query()->create([
            'name' => 'old_role',
        ]);
        $parameters = [
            'role' => $role->id,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('permissions.delete.role', $parameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'message' => 'Role deleted successfully!',
            ]);
        $this->assertNull(Role::query()->find($role->id));
    }

    #[Test]
    public function it_tests_list_all_roles_request()
    {
        /* SETUP */
        $firstRole = [
            'name' => 'test_role_0',
        ];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.role', $firstRole));
        $secondRole = [
            'name' => 'test_role_1',
        ];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.role', $secondRole));

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson(route('permissions.list.roles'));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'name' => $firstRole['name'],
                    ],
                    [
                        'name' => $secondRole['name'],
                    ],
                ]
            ]);
    }

    #[Test]
    public function it_tests_assign_role_request()
    {
        /* SETUP */
        $firstRole = [
            'name' => 'test_role_0',
        ];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.role', $firstRole));
        $parameters = [
            'role' => 'test_role_0',
            'user' => $this->user->id,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.users.assign.role', $parameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'message' => 'Role assigned successfully!',
            ]);
        $this->assertEquals(
            ['test_role_0'],
            $this->user->getRoleNames()->toArray()
        );
    }

    #[Test]
    public function it_tests_remove_role_request()
    {
        /* SETUP */
        $firstRole = [
            'name' => 'test_role_0',
        ];
        $secondRole = [
            'name' => 'test_role_1',
        ];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.role', $firstRole));
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.role', $secondRole));
        $assignParametersFirstRole = [
            'role' => 'test_role_0',
            'user' => $this->user->id,
        ];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.users.assign.role', $assignParametersFirstRole));
        $assignParametersSecondRole = [
            'role' => 'test_role_1',
            'user' => $this->user->id,
        ];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.users.assign.role', $assignParametersSecondRole));
        $this->assertEquals(
            ['test_role_0', 'test_role_1'],
            $this->user->getRoleNames()->toArray()
        );
        $removeParameters = [
            'role' => 'test_role_0',
            'user' => $this->user->id,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('permissions.users.remove.role', $removeParameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'message' => 'Role removed successfully!',
            ]);
        $this->assertEquals(
            ['test_role_1'],
            $this->user->refresh()->getRoleNames()->toArray()
        );
    }

    #[Test]
    public function it_tests_grant_permission_to_role_request()
    {
        /* SETUP */
        $newPermission = [
            'name' => 'test_permission',
        ];
        $permissionResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.permission', $newPermission));
        $firstRole = [
            'name' => 'test_role',
        ];
        $roleResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.role', $firstRole));
        $parameters = [
            'role' => $roleResponse->json('data.id'),
            'permission' => $permissionResponse->json('data.id'),
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.grant.permission', $parameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'message' => 'Permission granted to role successfully!',
            ]);
        $this->assertEquals(
            ['test_permission'],
            Role::query()->first()->permissions()->get()->pluck('name')->toArray()
        );
    }

    #[Test]
    public function it_tests_grant_permission_to_role_request_when_permission_is_deleted()
    {
        /* SETUP */
        $newPermission = [
            'name' => 'test_permission',
        ];
        $permissionResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.permission', $newPermission));
        $firstRole = [
            'name' => 'test_role',
        ];
        $roleResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.create.role', $firstRole));
        $parameters = [
            'role' => $roleResponse->json('data.id'),
            'permission' => $permissionResponse->json('data.id'),
        ];
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('permissions.grant.permission', $parameters));
        $response->assertOk()
            ->assertJson([
                'message' => 'Permission granted to role successfully!',
            ]);
        $this->assertEquals(
            ['test_permission'],
            Role::query()->first()->permissions()->get()->pluck('name')->toArray()
        );
        $permissionId = $permissionResponse->json('data.id');
        $parameters = [
            'permission' => $permissionId,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('permissions.delete.permission', $parameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'message' => 'Permission deleted successfully!',
            ]);
        $this->assertEmpty(Role::query()->first()->refresh()->permissions()->get()->pluck('name')->toArray());
        $this->assertNull(Permission::query()->find($permissionId));
    }
}
