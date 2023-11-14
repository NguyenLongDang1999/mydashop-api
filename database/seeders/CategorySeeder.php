<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class CategorySeeder extends Seeder
{
    protected \Faker\Generator $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('category')->delete();
        $this->createCategory(null, 3);
    }

    protected function createCategory($parent_id = null, $levels = 1)
    {
        if ($levels === 0) {
            return;
        }

        $categories = [];

        for ($i = 0; $i < 5; $i++) {
            $name = $this->faker->unique()->word;
            $slug = Str::slug($name);

            $category = [
                'name' => $name,
                'slug' => $slug,
                'description' => $this->faker->sentence,
                'image_uri' => $this->faker->imageUrl(400, 400),
                'status' => $this->faker->randomElement([10, 20]),
                'popular' => $this->faker->randomElement([10, 20]),
                'meta_title' => $this->faker->sentence,
                'meta_description' => $this->faker->sentence,
                'parent_id' => $parent_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $categories[] = $category;

            $this->createCategory(DB::table('category')->insertGetId($category), $levels - 1);
        }

        return $categories;
    }
}
