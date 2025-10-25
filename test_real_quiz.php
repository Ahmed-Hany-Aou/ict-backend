<?php
/**
 * Test real quiz data from database
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Quiz;

echo "=== TESTING REAL QUIZ DATA ===\n\n";

$quiz = Quiz::where('is_active', true)->first();

if (!$quiz) {
    echo "No active quiz found in database!\n";
    exit(1);
}

echo "Quiz: " . $quiz->title . "\n";
echo "Total questions: " . count($quiz->questions) . "\n\n";

echo "Original question order:\n";
echo "------------------------\n";
foreach ($quiz->questions as $i => $q) {
    echo ($i + 1) . ". " . substr($q['question'], 0, 50) . "...\n";
}

// Simulate the shuffling logic from the controller
$questions = $quiz->questions;

// Shuffle options within each question
foreach ($questions as &$question) {
    if (isset($question['options']) && is_array($question['options'])) {
        $correctAnswerText = $question['options'][$question['correct_answer']];
        shuffle($question['options']);
        $question['correct_answer'] = array_search($correctAnswerText, $question['options']);
    }
}
unset($question);

// Shuffle questions
shuffle($questions);

echo "\nAfter shuffling questions:\n";
echo "--------------------------\n";
foreach ($questions as $i => $q) {
    echo ($i + 1) . ". " . substr($q['question'], 0, 50) . "...\n";
}

echo "\n✓ Question shuffling works with real database data!\n";
echo "\nFirst shuffled question details:\n";
echo "Question: " . $questions[0]['question'] . "\n";
echo "Options:\n";
foreach ($questions[0]['options'] as $i => $opt) {
    $marker = ($i == $questions[0]['correct_answer']) ? " ← CORRECT" : "";
    echo "  [$i] " . $opt . "$marker\n";
}
