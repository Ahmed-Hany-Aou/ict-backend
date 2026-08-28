<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chapter;
use App\Models\Slide;

class Chapter6Seeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/chapter_6_slides.json');
        if (!file_exists($jsonPath)) {
            $jsonPath = base_path('chapter_6_slides.json');
        }
        if (!file_exists($jsonPath)) {
            $jsonPath = base_path('../chapter_6_slides.json');
        }

        if (file_exists($jsonPath)) {
            $data = json_decode(file_get_contents($jsonPath), true);
            $chapterData = $data['chapter'];
            $slidesData = $data['slides'];

            $chapter = Chapter::updateOrCreate(
                ['chapter_number' => 6],
                [
                    'title' => $chapterData['title'],
                    'description' => $chapterData['description'],
                    'content' => $chapterData['description'],
                    'is_published' => true,
                    'is_premium' => false,
                    'video_url' => 'https://drive.google.com/file/d/1TLDIkDJLpS4GhZCFhcuVYc4D5rE0SWtv/preview'
                ]
            );

            $chapter->slides()->delete();

            foreach ($slidesData as $s) {
                Slide::create([
                    'chapter_id' => $chapter->id,
                    'slide_number' => $s['slide_number'],
                    'type' => $s['type'],
                    'content' => $s['content']
                ]);
            }

            echo "Chapter 6 seeded with " . count($slidesData) . " slides successfully.\n";
        }
    }
}
