<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionForLink extends Migration {
    public function up() {
        DB::table('role_permissions')->where('name', 'like', 'link-%')->delete();

        $ops = ['View All', 'View Own', 'Create All', 'Create Own', 'Update All', 'Update Own', 'Delete All', 'Delete Own'];

        foreach ($ops as $op) {
            $dbOpName = strtolower(str_replace(' ', '-', $op));
            $roleIdsWithBookPermission = DB::table('role_permissions')
                ->leftJoin('permission_role', 'role_permissions.id', '=', 'permission_role.permission_id')
                ->leftJoin('roles', 'roles.id', '=', 'permission_role.role_id')
                ->where('role_permissions.name', '=', 'book-' . $dbOpName)->get(['roles.id'])->pluck('id');

            $permId = DB::table('role_permissions')->insertGetId([
                'name' => 'link-' . $dbOpName,
                'display_name' => $op . ' ' . 'Links',
                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
            ]);

            $rowsToInsert = $roleIdsWithBookPermission->filter(function($roleId) {
                return !is_null($roleId);
            })->map(function($roleId) use ($permId) {
                return [
                    'role_id' => $roleId,
                    'permission_id' => $permId
                ];
            })->toArray();

            // Assign view permission to all current roles
            DB::table('permission_role')->insert($rowsToInsert);
        }
    }

    public function down() {
        DB::table('role_permissions')->where('name', 'like', 'link-%')->delete();

        $ops = ['Create', 'Update', 'Delete', 'View-All', 'View-Own'];

        foreach ($ops as $op) {
            $dbOpName = strtolower(str_replace(' ', '-', $op));
            $roleIdsWithBookPermission = DB::table('role_permissions')
                ->leftJoin('permission_role', 'role_permissions.id', '=', 'permission_role.permission_id')
                ->leftJoin('roles', 'roles.id', '=', 'permission_role.role_id')
                ->where('role_permissions.name', '=', 'book-' . $dbOpName)->get(['roles.id'])->pluck('id');

            $permId = DB::table('role_permissions')->insertGetId([
                'name' => 'link-' . $dbOpName,
                'display_name' => $op . ' ' . 'Links',
                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
            ]);

            $rowsToInsert = $roleIdsWithBookPermission->filter(function($roleId) {
                return !is_null($roleId);
            })->map(function($roleId) use ($permId) {
                return [
                    'role_id' => $roleId,
                    'permission_id' => $permId
                ];
            })->toArray();

            // Assign view permission to all current roles
            DB::table('permission_role')->insert($rowsToInsert);
        }
    }
}