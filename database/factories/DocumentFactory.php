<?php

namespace Database\Factories;

use App\Models\{User, DocumentCategory};
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => DocumentCategory::factory(),
            'title' => $this->faker->sentence(),
            'file_path' => $this->faker->filePath(),
            'description' => $this->faker->optional()->paragraph(),
            'is_private' => $this->faker->boolean(70),
        ];
    }
} 