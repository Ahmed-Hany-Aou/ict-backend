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
        // --- Create Chapter 3: Conditional Statements ---
        $chapter = Chapter::updateOrCreate(
            ['chapter_number' => 3],
            [
                'title' => 'Decision Making & Conditional Statements',
                'description' => 'Master logical branching, comparison operators, and if-elif-else statements for Secondary 2 (S2) Computer Science.',
                'content' => 'Comprehensive guide to conditional logic and decision making in Python.',
                'is_published' => true,
                'is_premium' => false,
                'video_url' => null,
            ]
        );

        // Delete existing slides for this chapter to ensure clean state
        $chapter->slides()->delete();

        // Define the 10 comprehensive slides
        $slides = [
            // Slide 1: Title
            [
                'slide_number' => 1,
                'type' => 'title',
                'content' => [
                    'title' => 'Decision Making & Conditional Statements',
                    'subtitle' => 'Mastering if, elif, and else in Python',
                    'footer' => "Computer Science & Programming\nSecondary 2 (S2) – Egypt Curriculum",
                    'description' => 'Empowering programs to evaluate conditions, make decisions, and adapt dynamically.'
                ]
            ],

            // Slide 2: Real-World Analogy & Hook
            [
                'slide_number' => 2,
                'type' => 'content',
                'content' => [
                    'title' => 'Why Do We Need Decisions in Code?',
                    'definition' => 'By default, computers execute code sequentially line-by-line. Conditional statements allow programs to make decisions and execute different paths based on real-time data.',
                    'keyPoint' => 'Decision Making transforms static calculations into smart, responsive software.',
                    'cards' => [
                        [
                            'title' => '🚦 Smart Traffic Gate',
                            'desc' => 'If pedestrian detected -> Turn red light on',
                            'example' => 'if balance > 50: open_gate()'
                        ],
                        [
                            'title' => '🎮 Game Over Logic',
                            'desc' => 'If player health reaches 0 -> Show Game Over',
                            'example' => 'if hp <= 0: trigger_game_over()'
                        ],
                        [
                            'title' => '🛒 Shopping Cart Promo',
                            'desc' => 'If order > 500 EGP -> Apply 20% discount',
                            'example' => 'if total > 500: apply_discount()'
                        ]
                    ]
                ]
            ],

            // Slide 3: Comparison & Relational Operators
            [
                'slide_number' => 3,
                'type' => 'content',
                'content' => [
                    'title' => 'Comparison Operators (The Foundation)',
                    'definition' => 'A condition is an expression that evaluates to a Boolean value: True or False.',
                    'table' => [
                        ['type' => '== (Equal To)', 'desc' => 'Checks if two values are identical', 'example' => 'x == 10 (True if x is 10)'],
                        ['type' => '!= (Not Equal)', 'desc' => 'Checks if two values are different', 'example' => 'role != "admin" (True if not admin)'],
                        ['type' => '> and >= (Greater)', 'desc' => 'Greater than / Greater than or equal', 'example' => 'score >= 50 (Passing grade)'],
                        ['type' => '< and <= (Less)', 'desc' => 'Less than / Less than or equal', 'example' => 'temperature < 37.5 (Normal temp)']
                    ],
                    'note' => 'CRITICAL TRAP: "=" is used for Assignment (x = 5), while "==" is used for Equality Comparison (x == 5)!'
                ]
            ],

            // Slide 4: Single-Way Decision (if)
            [
                'slide_number' => 4,
                'type' => 'content',
                'content' => [
                    'title' => 'One-Way Branch: The "if" Statement',
                    'definition' => 'The code block inside the "if" statement executes ONLY when the condition evaluates to True. If False, it is skipped.',
                    'points' => [
                        'Colon (:) at the end of the if line is mandatory in Python syntax.',
                        'Indentation (4 spaces or Tab) tells Python which statements belong inside the block.',
                        'Any unindented code after the block will always execute sequentially.'
                    ],
                    'codeSnippet' => [
                        'language' => 'Python',
                        'filename' => 'temperature_check.py',
                        'code' => "# Checking patient temperature\ntemperature = 38.5\n\nif temperature > 37.5:\n    print(\"⚠️ Warning: High temperature detected!\")\n    print(\"Please consult a doctor.\")\n\nprint(\"Check completed.\")",
                        'output' => "⚠️ Warning: High temperature detected!\nPlease consult a doctor.\nCheck completed."
                    ]
                ]
            ],

            // Slide 5: Two-Way Decision (if...else)
            [
                'slide_number' => 5,
                'type' => 'content',
                'content' => [
                    'title' => 'Two-Way Branch: The "if...else" Statement',
                    'definition' => 'The "else" statement provides a guaranteed fallback pathway that executes whenever the "if" condition is False.',
                    'points' => [
                        'The "else" block NEVER takes its own condition — it catches everything else.',
                        'Exactly ONE of the two blocks (if or else) will execute, never both.',
                        'Both branches must be properly indented.'
                    ],
                    'codeSnippet' => [
                        'language' => 'Python',
                        'filename' => 'license_validator.py',
                        'code' => "# Driving License Age Verification\nage = 16\n\nif age >= 18:\n    print(\"✅ Eligible for Driver's License.\")\nelse:\n    print(\"❌ Underage: Must be at least 18 years old.\")",
                        'output' => "❌ Underage: Must be at least 18 years old."
                    ],
                    'note' => 'Use if...else whenever you have an either/or choice in your logic.'
                ]
            ],

            // Slide 6: Multi-Way Branch (if...elif...else)
            [
                'slide_number' => 6,
                'type' => 'content',
                'content' => [
                    'title' => 'Multi-Way Decision: The "elif" Statement',
                    'definition' => '"elif" (short for else-if) lets us evaluate multiple sequential conditions. Python checks them from TOP to BOTTOM and executes the FIRST matching block.',
                    'codeSnippet' => [
                        'language' => 'Python',
                        'filename' => 'grade_evaluator.py',
                        'code' => "# Secondary 2 Student Grading System\nscore = 85\n\nif score >= 90:\n    grade = \"A+ (Excellent!) 🏆\"\nelif score >= 75:\n    grade = \"B (Very Good) 🌟\"\nelif score >= 50:\n    grade = \"C (Passed) 👍\"\nelse:\n    grade = \"F (Needs Improvement) 📚\"\n\nprint(f\"Final Student Result: {grade}\")",
                        'output' => "Final Student Result: B (Very Good) 🌟"
                    ],
                    'note' => 'Once Python finds a True condition, it executes that block and SKIPS all remaining elif and else blocks!'
                ]
            ],

            // Slide 7: Common Pitfalls & Traps
            [
                'slide_number' => 7,
                'type' => 'content',
                'content' => [
                    'title' => 'Common Traps & Debugging Best Practices',
                    'cards' => [
                        [
                            'title' => '🚫 1. Assignment Trap',
                            'desc' => 'Writing if x = 10: causes a SyntaxError',
                            'example' => 'Fix: Always write if x == 10:'
                        ],
                        [
                            'title' => '🚫 2. Missing Colon',
                            'desc' => 'Forgetting ":" at the end of if/elif/else line',
                            'example' => 'Fix: Check end of every condition line'
                        ],
                        [
                            'title' => '🚫 3. Order of Ranges',
                            'desc' => 'Putting score >= 50 before score >= 90 steals all cases',
                            'example' => 'Fix: Arrange conditions from most specific to general'
                        ]
                    ],
                    'note' => 'Pro Tip for Smartboards: Always highlight the 4-space indentation and colon with a colored pen!'
                ]
            ],

            // Slide 8: Live Interactive Quiz
            [
                'slide_number' => 8,
                'type' => 'quiz',
                'content' => [
                    'title' => '⚡ Live Challenge: Test Your Knowledge',
                    'questions' => [
                        [
                            'q' => 'What will be printed when score = 75 in this code? (if score > 75: "A" elif score >= 75: "B" else: "C")',
                            'options' => [
                                'A',
                                'B',
                                'C',
                                'Both A and B'
                            ],
                            'answer' => 'B'
                        ],
                        [
                            'q' => 'Which comparison operator correctly checks if two values are NOT equal in Python?',
                            'options' => [
                                '<>',
                                '!==',
                                '!=',
                                'NOT='
                            ],
                            'answer' => '!='
                        ]
                    ]
                ]
            ],

            // Slide 9: Scenario Case Study
            [
                'slide_number' => 9,
                'type' => 'scenario',
                'content' => [
                    'title' => 'Real-World Case Study: Smart ATM Machine',
                    'scenario' => 'A customer attempts to withdraw 1,000 EGP. Their current bank balance is 800 EGP, and the ATM daily limit is 5,000 EGP.',
                    'breakdown' => [
                        [
                            'type' => '1. State Variables',
                            'content' => 'balance = 800 | requested_cash = 1000 | daily_limit = 5000'
                        ],
                        [
                            'type' => '2. Condition Evaluation',
                            'content' => 'if requested_cash > balance -> True (1000 > 800)'
                        ],
                        [
                            'type' => '3. Decision Outcome',
                            'content' => 'Trigger: print("❌ Insufficient Funds. Transaction Cancelled.")'
                        ]
                    ],
                    'knowledge' => 'Conditional logic ensures financial security, prevents negative balances, and provides clear user feedback.'
                ]
            ],

            // Slide 10: Completion & Next Steps
            [
                'slide_number' => 10,
                'type' => 'completion',
                'content' => [
                    'title' => '🎉 Mastery Unlocked: Conditional Statements',
                    'message' => 'You now have full command over decision-making logic, syntax structure, and multi-branch execution in Python!',
                    'nextSteps' => [
                        'Compound conditions with Logical Operators (and, or, not)',
                        'Nested if statements (decision inside another decision)',
                        'Pattern matching and match-case statements in Python 3.10+'
                    ]
                ]
            ]
        ];

        // Insert each slide into database
        foreach ($slides as $slideData) {
            Slide::create([
                'chapter_id' => $chapter->id,
                'slide_number' => $slideData['slide_number'],
                'type' => $slideData['type'],
                'content' => $slideData['content'],
            ]);
        }
    }
}
