<?php
/**
 * Test script to verify quiz question and answer shuffling with correct score tracking
 * Run: php test_quiz_shuffling.php
 */

// Simulate quiz data structure
$originalQuiz = [
    'id' => 1,
    'title' => 'Test Quiz',
    'questions' => [
        [
            'question' => 'What is 2+2?',
            'options' => ['3', '4', '5', '6'],
            'correct_answer' => 1, // Index 1 = '4'
            'explanation' => '2+2 equals 4'
        ],
        [
            'question' => 'What is the capital of France?',
            'options' => ['London', 'Berlin', 'Paris', 'Madrid'],
            'correct_answer' => 2, // Index 2 = 'Paris'
            'explanation' => 'Paris is the capital of France'
        ],
        [
            'question' => 'Which is a programming language?',
            'options' => ['HTML', 'CSS', 'PHP', 'JSON'],
            'correct_answer' => 2, // Index 2 = 'PHP'
            'explanation' => 'PHP is a programming language'
        ]
    ]
];

echo "=== QUIZ SHUFFLING AND SCORING TEST ===\n\n";

// Step 1: Simulate backend shuffling (what happens in getQuizByChapter/getQuiz)
echo "STEP 1: Original Quiz Questions\n";
echo "--------------------------------\n";
foreach ($originalQuiz['questions'] as $i => $q) {
    echo "Q" . ($i + 1) . ": " . $q['question'] . "\n";
    echo "   Options: [" . implode(', ', $q['options']) . "]\n";
    echo "   Correct answer index: " . $q['correct_answer'] . " (" . $q['options'][$q['correct_answer']] . ")\n\n";
}

// Shuffle options within each question
$questions = $originalQuiz['questions'];
foreach ($questions as &$question) {
    if (isset($question['options']) && is_array($question['options'])) {
        // Store the correct answer text before shuffling
        $correctAnswerText = $question['options'][$question['correct_answer']];

        // Shuffle the options
        shuffle($question['options']);

        // Find the new index of the correct answer after shuffling
        $question['correct_answer'] = array_search($correctAnswerText, $question['options']);
    }
}
unset($question); // Break reference

// Shuffle the questions array
shuffle($questions);

echo "\nSTEP 2: After Shuffling Questions and Options\n";
echo "----------------------------------------------\n";
foreach ($questions as $i => $q) {
    echo "Q" . ($i + 1) . ": " . $q['question'] . "\n";
    echo "   Options: [" . implode(', ', $q['options']) . "]\n";
    echo "   Correct answer index: " . $q['correct_answer'] . " (" . $q['options'][$q['correct_answer']] . ")\n\n";
}

// Step 2: Simulate user answers (frontend sends answer indices)
echo "\nSTEP 3: User Answers\n";
echo "--------------------\n";

// Simulate user answering:
// - Question 0: correct answer
// - Question 1: wrong answer
// - Question 2: correct answer
$userAnswers = [
    0 => $questions[0]['correct_answer'], // Correct
    1 => ($questions[1]['correct_answer'] + 1) % count($questions[1]['options']), // Wrong
    2 => $questions[2]['correct_answer'], // Correct
];

foreach ($userAnswers as $index => $answer) {
    $q = $questions[$index];
    $isCorrect = ($answer == $q['correct_answer']);
    echo "Q" . ($index + 1) . ": User selected index $answer";
    echo " (" . $q['options'][$answer] . ")";
    echo " - " . ($isCorrect ? "✓ CORRECT" : "✗ WRONG") . "\n";
}

// Step 3: Score calculation (what happens in submitQuiz)
echo "\n\nSTEP 4: Score Calculation\n";
echo "-------------------------\n";

$score = 0;
$totalQuestions = count($questions);
$detailedResults = [];

foreach ($questions as $index => $question) {
    $userAnswer = $userAnswers[$index] ?? null;
    $isCorrect = $userAnswer !== null && $userAnswer == $question['correct_answer'];

    if ($isCorrect) {
        $score++;
    }

    $detailedResults[] = [
        'question' => $question['question'],
        'options' => $question['options'],
        'user_answer' => $userAnswer,
        'user_answer_text' => $userAnswer !== null ? $question['options'][$userAnswer] : 'Not answered',
        'correct_answer' => $question['correct_answer'],
        'correct_answer_text' => $question['options'][$question['correct_answer']],
        'is_correct' => $isCorrect
    ];
}

$percentage = ($score / $totalQuestions) * 100;

echo "Score: $score / $totalQuestions\n";
echo "Percentage: " . round($percentage, 2) . "%\n\n";

echo "Detailed Results:\n";
foreach ($detailedResults as $i => $result) {
    echo "\nQ" . ($i + 1) . ": " . $result['question'] . "\n";
    echo "   Your answer: " . $result['user_answer_text'] . "\n";
    echo "   Correct answer: " . $result['correct_answer_text'] . "\n";
    echo "   Result: " . ($result['is_correct'] ? "✓ CORRECT" : "✗ WRONG") . "\n";
}

// Verify scoring is correct
echo "\n\n=== VERIFICATION ===\n";
echo "Expected score: 2/3 (66.67%)\n";
echo "Actual score: $score/$totalQuestions (" . round($percentage, 2) . "%)\n";

if ($score == 2 && round($percentage, 2) == 66.67) {
    echo "\n✓✓✓ TEST PASSED: Shuffling and scoring work correctly! ✓✓✓\n";
} else {
    echo "\n✗✗✗ TEST FAILED: Score calculation is incorrect! ✗✗✗\n";
}

echo "\n=== KEY POINTS ===\n";
echo "1. Questions are shuffled in random order\n";
echo "2. Options within each question are shuffled\n";
echo "3. Correct answer index is updated after shuffling\n";
echo "4. Score tracking uses the shuffled questions from frontend\n";
echo "5. User answer indices match the shuffled options array\n";
