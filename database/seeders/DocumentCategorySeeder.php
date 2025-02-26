<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;

class DocumentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Personal Documents',
                'slug' => 'personal',
                'description' => 'Private personal documents',
                'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'is_private' => true
            ],
            [
                'name' => 'Academic Records',
                'slug' => 'academic',
                'description' => 'Academic certificates and records',
                'allowed_file_types' => ['pdf'],
                'is_private' => true
            ],
            [
                'name' => 'Notifications',
                'slug' => 'notifications',
                'description' => 'Official notifications and circulars',
                'allowed_file_types' => ['pdf'],
                'is_private' => false
            ],
            [
                'name' => 'Commission Documents',
                'slug' => 'commission',
                'description' => 'Commission related documents',
                'allowed_file_types' => ['pdf', 'doc', 'docx'],
                'is_private' => true
            ]
        ];

        foreach ($categories as $category) {
            DocumentCategory::create($category);
        }
    }
}