<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:modules-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea permisos basados en los módulos del CRM - MVD';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $modulesJson = [
        'menu' => [
            [
                'slug' => 'dashboard',
                'module' => 'general',
                'view_all' => false,
            ],
            [
                'slug' => 'manufacturing',
                'module' => 'manufacturing',
                'view_all' => false,
            ],
            [
                'slug' => 'raw-materials',
                'module' => 'stock',
                'view_all' => true,
            ],
            [
                'slug' => 'suppliers',
                'module' => 'stock',
                'view_all' => true,
            ],
            [
                'slug' => 'supplier-orders',
                'module' => 'stock',
                'view_all' => true,
            ],
            [
                'slug' => 'stock',
                'module' => 'stock',
                'view_all' => false,
            ],
            [
                'slug' => 'accounting',
                'module' => 'accounting',
                'submenus' => [
                    'invoices',
                    'receipts',
                    'entries'
                ],
                'view_all' => false,
            ],
            [
                'slug' => 'clients',
                'module' => 'crm',
                'view_all' => false,
            ],
            [
                'slug' => 'ecommerce',
                'module' => 'ecommerce',
                'submenus' => [
                    'orders',
                    'products',
                    'product-categories',
                    'settings',
                    'product-flavors'
                ],
                'view_all' => true,
            ],
            [
                'slug' => 'productions',
                'module' => 'manufacturing',
                'view_all' => true,
            ],
            [
                'slug' => 'bypass_raw_material_check',
                'module' => 'manufacturing',
                'view_all' => false,
            ],
            [
                'slug' => 'marketing',
                'module' => 'marketing',
                'submenus' => [
                    'coupons',
                    'settings'
                ],
                'view_all' => false,
            ],
            [
                'slug' => 'omnichannel',
                'module' => 'marketing',
                'submenus' => [
                    'chats',
                    'settings'
                ],
                'view_all' => false,
            ],
            [
                'slug' => 'datacenter',
                'module' => 'datacenter',
                'view_all' => true,
            ],
            [
                'slug' => 'crm',
                'module' => 'crm',
                'view_all' => false,
            ],
            [
                'slug' => 'stores',
                'module' => 'management',
                'view_all' => false,
            ],
            [
                'slug' => 'roles',
                'module' => 'management',
                'view_all' => false,
            ],
            [
                'slug' => 'company_settings',
                'module' => 'management',
                'view_all' => false,
            ],
            [
                'slug' => 'open_close_stores',
                'module' => 'management',
                'view_all' => false,
            ],
            [
                'slug' => 'point-of-sale',
                'module' => 'point-of-sale',
                'view_all' => false,
            ],
            [
                'slug' => 'sales-commerce',
                'module' => 'ecommerce',
                'view_all' => false,
            ],
            [
                'slug' => 'users',
                'module' => 'management',
                'view_all' => false,
            ],
            [
                'slug' => 'user-accounts',
                'module' => 'management',
                'view_all' => false,
            ],
        ]
      ];


        // Asegurar que el rol de administrador existe
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);

        foreach ($modulesJson['menu'] as $module) {
          $this->createPermission($module['slug'], $module['view_all'], $adminRole, $module['module']);

          if (array_key_exists('submenus', $module)) {
              foreach ($module['submenus'] as $submenuSlug) {
                  $this->createPermission($submenuSlug, false, $adminRole, $module['module']);
              }
          }
      }


        $this->info('Todos los permisos han sido creados y asignados al rol Administrador.');
    }

    private function createPermission($slug, $viewAll, $adminRole, $module)
    {
        // Crear o buscar el permiso base y asignar el módulo
        $permissionName = 'access_' . $slug;
        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            ['module' => $module]
        );
        $adminRole->givePermissionTo($permission);
        $this->info('Permiso creado y asignado al rol Administrador: ' . $permissionName);

        // Si es necesario crear el permiso de vista total
        if ($viewAll) {
            $viewAllPermissionName = 'view_all_' . $slug;
            $viewAllPermission = Permission::firstOrCreate(
                ['name' => $viewAllPermissionName],
                ['module' => $module]
            );
            $adminRole->givePermissionTo($viewAllPermission);
            $this->info('Permiso de vista total creado y asignado al rol Administrador: ' . $viewAllPermissionName);
        }
    }

}
