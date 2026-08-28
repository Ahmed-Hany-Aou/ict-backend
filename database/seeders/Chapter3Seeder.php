<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chapter;
use App\Models\Slide;

class Chapter3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/chapter_3_slides.json');
        if (!file_exists($jsonPath)) {
            $jsonPath = base_path('chapter_3_slides.json');
        }
        if (!file_exists($jsonPath)) {
            $jsonPath = base_path('../chapter_3_slides.json');
        }

        if (file_exists($jsonPath)) {
            $data = json_decode(file_get_contents($jsonPath), true);
            $chapterData = $data['chapter'] ?? [];
            $slidesData = $data['slides'] ?? [];

            $chapter = Chapter::updateOrCreate(
                ['chapter_number' => 3],
                [
                    'title' => $chapterData['title'] ?? 'Decision Making & Conditional Logic',
                    'description' => $chapterData['description'] ?? 'Master logical branching, comparison operators, and if-elif-else statements for Secondary 2 (S2).',
                    'content' => $chapterData['description'] ?? 'Comprehensive guide to conditional logic and decision making in Python.',
                    'is_published' => true,
                    'is_premium' => false,
                    'video_url' => 'https://drive.google.com/file/d/1TLDIkDJLpS4GhZCFhcuVYc4D5rE0SWtv/preview',
                ]
            );

            $chapter->slides()->delete();

            foreach ($slidesData as $s) {
                Slide::create([
                    'chapter_id' => $chapter->id,
                    'slide_number' => $s['slide_number'],
                    'type' => $s['type'],
                    'content' => $s['content'],
                ]);
            }

            echo "Chapter 3 seeded with " . count($slidesData) . " slides successfully.\n";
        }
    }
}
