<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ElectronicsSubcategorySeeder::class);
        $this->call(FoodAndBeveragesSubcategorySeeder::class);
        $this->call(MarketsAndDailyNeedsSubcategorySeeder::class);
        $this->call(HouseholdItemsSubcategorySeeder::class);
        $this->call(HealthAndBeautySubcategorySeeder::class);  
        $this->call(DeliveryCostSeeder::class);  
    } 
}
