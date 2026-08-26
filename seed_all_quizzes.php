<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Chapter;
use App\Models\Quiz;

$chapters = Chapter::all();

foreach ($chapters as $chapter) {
    if (Quiz::where('chapter_id', $chapter->id)->exists()) {
        echo "✅ Quiz already exists for Chapter {$chapter->chapter_number}: {$chapter->title}\n";
        continue;
    }

    if ($chapter->chapter_number == 2) {
        Quiz::create([
            'chapter_id' => $chapter->id,
            'title' => 'Living Safely in the Information Society Quiz',
            'description' => 'Test your knowledge on cybersecurity, privacy, passwords, and digital safety.',
            'questions' => [
                [
                    'question' => 'Which of the following creates the strongest password?',
                    'options' => ['password123', 'MyDogName2020', 'P@ssw0rd!#99xL', '12345678'],
                    'correct_answer' => 2,
                    'explanation' => 'Strong passwords mix uppercase, lowercase, numbers, and special symbols.'
                ],
                [
                    'question' => 'What is Phishing?',
                    'options' => [
                        'A sport played in rivers',
                        'A fraudulent attempt to obtain sensitive information like passwords by impersonating a trustworthy entity',
                        'Cleaning temporary files on a computer',
                        'Installing antivirus software'
                    ],
                    'correct_answer' => 1,
                    'explanation' => 'Phishing is a social engineering attack used to steal user data.'
                ],
                [
                    'question' => 'What does Two-Factor Authentication (2FA) provide?',
                    'options' => [
                        'Double the internet speed',
                        'An extra layer of security beyond just a password',
                        'Two computer screens',
                        'A backup of all files'
                    ],
                    'correct_answer' => 1,
                    'explanation' => '2FA requires two distinct forms of identification before granting access.'
                ]
            ],
            'passing_score' => 70,
            'time_limit' => 15,
            'is_active' => true,
            'is_premium' => false,
        ]);
        echo "✅ Created Quiz for Chapter 2: {$chapter->title}\n";
    } elseif ($chapter->chapter_number == 3) {
        Quiz::create([
            'chapter_id' => $chapter->id,
            'title' => 'Conditional Statements & Decision Logic Quiz',
            'description' => 'Test your mastery of if, elif, else, comparison operators, and Python logic.',
            'questions' => [
                [
                    'question' => 'What will be printed when score = 75 in: if score > 75: print("A") elif score >= 75: print("B") else: print("C")?',
                    'options' => ['A', 'B', 'C', 'Both A and B'],
                    'correct_answer' => 1,
                    'explanation' => 'The condition score >= 75 evaluates to True when score is 75, so "B" is printed.'
                ],
                [
                    'question' => 'Which comparison operator checks if two values are NOT equal in Python?',
                    'options' => ['<>', '!==', '!=', 'NOT='],
                    'correct_answer' => 2,
                    'explanation' => '!= is the standard inequality comparison operator in Python.'
                ],
                [
                    'question' => 'What mandatory character must be at the end of an if/elif/else line in Python?',
                    'options' => ['; (Semicolon)', ': (Colon)', '{ (Curly Brace)', 'None'],
                    'correct_answer' => 1,
                    'explanation' => 'Python requires a colon (:) at the end of conditional headers.'
                ],
                [
                    'question' => 'What happens if you write "if x = 10:" instead of "if x == 10:"?',
                    'options' => ['It evaluates True', 'SyntaxError', 'Assigns 10 to x without error', 'None of the above'],
                    'correct_answer' => 1,
                    'explanation' => 'Single = is assignment and results in a SyntaxError in conditional checks.'
                ]
            ],
            'passing_score' => 70,
            'time_limit' => 10,
            'is_active' => true,
            'is_premium' => false,
        ]);
        echo "✅ Created Quiz for Chapter 3: {$chapter->title}\n";
    }
}

echo "🎉 All Quizzes Seeded Successfully!\n";
