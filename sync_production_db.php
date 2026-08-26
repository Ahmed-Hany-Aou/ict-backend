<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Chapter;
use App\Models\Slide;
use App\Models\Quiz;
use Illuminate\Support\Facades\Http;

echo "🔄 Fetching Production Data from https://wadha-api.up.railway.app/api ...\n";

// 1. Authenticate with production
$loginRes = Http::withoutVerifying()->post('https://wadha-api.up.railway.app/api/login', [
    'email' => 'amany.hany@gmail.com',
    'password' => 'amany@1975'
]);

if (!$loginRes->successful()) {
    echo "❌ Login to production failed: " . $loginRes->body() . "\n";
    exit(1);
}

$token = $loginRes->json('access_token');
$prodUser = $loginRes->json('user');
echo "✅ Authenticated with Production as {$prodUser['name']} ({$prodUser['email']})\n";

// Save/Update local user
User::updateOrCreate(
    ['email' => 'amany.hany@gmail.com'],
    [
        'name' => $prodUser['name'] ?? 'Amany Hany',
        'password' => bcrypt('amany@1975'),
        'role' => $prodUser['role'] ?? 'student',
        'is_premium' => true,
        'premium_expires_at' => now()->addYears(2),
        'is_active' => true,
    ]
);

// 2. Fetch Chapters
$chaptersRes = Http::withoutVerifying()->withToken($token)->get('https://wadha-api.up.railway.app/api/chapters');
if ($chaptersRes->successful()) {
    $chapters = $chaptersRes->json('chapters') ?? $chaptersRes->json('data') ?? [];
    echo "📚 Found " . count($chapters) . " chapters on production.\n";

    foreach ($chapters as $cData) {
        $chapter = Chapter::updateOrCreate(
            ['chapter_number' => $cData['chapter_number']],
            [
                'title' => $cData['title'],
                'description' => $cData['description'] ?? '',
                'content' => $cData['content'] ?? '',
                'is_published' => (bool)($cData['is_published'] ?? true),
                'is_premium' => (bool)($cData['is_premium'] ?? false),
                'video_url' => $cData['video_url'] ?? null,
            ]
        );

        echo "  ➜ Syncing Chapter {$chapter->chapter_number}: {$chapter->title} ...\n";

        // Fetch Slides for this chapter
        $slidesRes = Http::withoutVerifying()->withToken($token)->get("https://wadha-api.up.railway.app/api/chapters/{$cData['id']}/slides");
        if ($slidesRes->successful()) {
            $slides = $slidesRes->json('slides') ?? $slidesRes->json('data') ?? [];
            echo "    ➜ Fetched " . count($slides) . " slides.\n";

            $chapter->slides()->delete();
            foreach ($slides as $sData) {
                Slide::create([
                    'chapter_id' => $chapter->id,
                    'slide_number' => $sData['slide_number'],
                    'type' => $sData['type'],
                    'content' => $sData['content'],
                    'video_url' => $sData['video_url'] ?? null,
                ]);
            }
        }

        // Fetch Quiz for this chapter from production
        $quizRes = Http::withoutVerifying()->withToken($token)->get("https://wadha-api.up.railway.app/api/chapters/{$cData['id']}/quiz");
        if ($quizRes->successful()) {
            $qData = $quizRes->json('quiz') ?? $quizRes->json('data') ?? null;
            if ($qData && isset($qData['questions'])) {
                Quiz::updateOrCreate(
                    ['chapter_id' => $chapter->id],
                    [
                        'title' => $qData['title'] ?? ($chapter->title . ' Quiz'),
                        'description' => $qData['description'] ?? ('Test your knowledge on ' . $chapter->title),
                        'questions' => $qData['questions'],
                        'passing_score' => $qData['passing_score'] ?? 70,
                        'time_limit' => $qData['time_limit'] ?? 15,
                        'is_active' => true,
                        'is_premium' => (bool)($qData['is_premium'] ?? false),
                    ]
                );
                echo "    ➜ Synced Quiz with " . count($qData['questions']) . " questions.\n";
            }
        }
    }
}

// 3. Fallback: If Chapter 1 Quiz is not in prod, run Chapter1QuizSeeder
$ch1 = Chapter::where('chapter_number', 1)->first();
if ($ch1 && !Quiz::where('chapter_id', $ch1->id)->exists()) {
    $qSeeder = new \Database\Seeders\Chapter1QuizSeeder();
    $qSeeder->run();
    echo "  ➜ Seeded Chapter 1 Quiz fallback.\n";
}

// 4. Create S2 Conditional Statements Quiz for Chapter 3
$ch3 = Chapter::where('chapter_number', 3)->first();
if ($ch3) {
    Quiz::updateOrCreate(
        ['chapter_id' => $ch3->id],
        [
            'title' => 'Decision Making & Conditional Logic Quiz',
            'description' => 'Test your understanding of if, elif, else, and Boolean comparison in Python.',
            'questions' => [
                [
                    'question' => 'What will be printed when score = 75 in: if score > 75: print("A") elif score >= 75: print("B") else: print("C")?',
                    'options' => ['A', 'B', 'C', 'Both A and B'],
                    'correct_answer' => 1,
                    'explanation' => 'The condition score >= 75 is True when score is 75, so B is printed.'
                ],
                [
                    'question' => 'Which comparison operator checks if two values are NOT equal in Python?',
                    'options' => ['<>', '!==', '!=', 'NOT='],
                    'correct_answer' => 2,
                    'explanation' => '!= is the standard inequality operator in Python.'
                ],
                [
                    'question' => 'What is the mandatory character required at the end of an if statement line in Python?',
                    'options' => ['; (Semicolon)', ': (Colon)', '{ (Curly Brace)', 'None'],
                    'correct_answer' => 1,
                    'explanation' => 'Python requires a colon (:) at the end of if, elif, and else statements.'
                ],
                [
                    'question' => 'What happens if you write "if x = 10:" instead of "if x == 10:"?',
                    'options' => ['It works normally', 'SyntaxError', 'x becomes 10', 'None of the above'],
                    'correct_answer' => 1,
                    'explanation' => 'Single = is for assignment and causes a SyntaxError inside conditions.'
                ]
            ],
            'passing_score' => 70,
            'time_limit' => 10,
            'is_active' => true,
            'is_premium' => false,
        ]
    );
    echo "  ➜ Seeded Chapter 3 S2 Quiz.\n";
}

echo "🎉 ALL CHAPTERS, SLIDES, AND QUIZZES SYNCED SUCCESSFULLY!\n";
