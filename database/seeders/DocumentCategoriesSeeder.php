<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;

class DocumentCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Personal Documents', 'slug' => 'personal-documents', 'description' => 'Identity documents, certificates, etc.'],
            ['name' => 'Academic Records', 'slug' => 'academic-records', 'description' => 'Transcripts, degrees, certificates'],
            ['name' => 'Formation Documents', 'slug' => 'formation-documents', 'description' => 'Vows, ordination certificates'],
            ['name' => 'Medical Records', 'slug' => 'medical-records', 'description' => 'Health reports, prescriptions'],
            ['name' => 'Official Letters', 'slug' => 'official-letters', 'description' => 'Appointment letters, transfers'],
            ['name' => 'Financial Documents', 'slug' => 'financial-documents', 'description' => 'Statements, receipts'],
            ['name' => 'Ministry Reports', 'slug' => 'ministry-reports', 'description' => 'Work reports, evaluations'],
        ];

        foreach ($categories as $category) {
            DocumentCategory::create($category);
        }
    }
} 