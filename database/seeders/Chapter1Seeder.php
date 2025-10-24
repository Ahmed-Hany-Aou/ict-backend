<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chapter;
use App\Models\Slide;

class Chapter1Seeder extends Seeder
{
    public function run(): void
    {
        // --- Create Chapter 1 ---
        // Using updateOrCreate is safer if the seeder might run multiple times
        $chapter = Chapter::updateOrCreate(
            ['chapter_number' => 1], // Find chapter 1 or create it
            [
                'title' => 'Data, Information, and Knowledge',
                'description' => 'Learn the fundamental concepts of data, information, and knowledge in ICT',
                'content' => 'Introduction to the core concepts of Information and Communication Technology',
                'is_published' => true,
                'is_premium' => false,
                'video_url' => 'https://drive.google.com/file/d/1TLDIkDJLpS4GhZCFhcuVYc4D5rE0SWtv/preview'
            ]
        );

        // --- Delete existing slides for this chapter ---
        $chapter->slides()->delete();

        // --- Define Slides as an array ---
        $slides = [
            // Slide 1: Title
            [
                'slide_number' => 1,
                'type' => 'title',
                'content' => [
                    'title' => 'Chapter 1',
                    'subtitle' => 'Data, Information, and Knowledge',
                    'footer' => "ICT Curriculum\nGrade 10 – Egypt"
                ]
            ],
            // Slide 2: What is Data?
            [
                'slide_number' => 2,
                'type' => 'content',
                'content' => [
                    'title' => 'What is Data?',
                    'definition' => 'Data are raw facts, figures, or symbols that have not yet been processed to give them meaning.',
                    'examples' => [
                        'A list of temperatures: 30, 32, 29',
                        'Names in a class: Sara, Omar, Youssef',
                        'Random numbers: 100, 3.14, 42'
                    ]
                ]
            ],
            // Slide 3: What is Information?
            [
                'slide_number' => 3,
                'type' => 'content',
                'content' => [
                    'title' => 'What is Information?',
                    'definition' => 'Information is data that has been processed or organized to have meaning and value.',
                    'examples' => [
                        '"Ahmed scored 23 marks in math."',
                        '"The average temperature this week is 30°C."',
                        'A table showing students\' names and their scores',
                        'A weather report summarizing daily temperatures'
                    ],
                    'keyPoint' => 'Information = Data + Context + Meaning'
                ]
            ],
            // Slide 4: What is Knowledge?
            [
                'slide_number' => 4,
                'type' => 'content',
                'content' => [
                    'title' => 'What is Knowledge?',
                    'definition' => 'Knowledge is the understanding and awareness gained by using information to make decisions or solve problems.',
                    'examples' => [
                        'Knowing that studying regularly improves exam scores',
                        'Realizing that high temperatures mean you should drink more water',
                        'A teacher uses students\' scores to decide who needs extra help'
                    ]
                ]
            ],
            // Slide 5: Characteristics of Information
            [
                'slide_number' => 5,
                'type' => 'content',
                'content' => [
                    'title' => 'Characteristics of Information',
                    'cards' => [
                        ['title' => 'Persistence', 'desc' => 'Hard to erase completely', 'example' => 'A photo posted online'],
                        ['title' => 'Reproducibility', 'desc' => 'Can be copied and shared easily', 'example' => 'Forwarding a message'],
                        ['title' => 'Spreads Easily', 'desc' => 'Can reach many people quickly', 'example' => 'Viral news']
                    ]
                ]
            ],
            // Slide 6: Types of Information
            [
                'slide_number' => 6,
                'type' => 'content',
                'content' => [
                    'title' => 'Types of Information',
                    'table' => [
                        ['type' => 'Primary', 'desc' => 'Direct from the original source', 'example' => 'Survey results, eyewitness account'],
                        ['type' => 'Secondary', 'desc' => 'Processed or summarized from primary sources', 'example' => 'News article, research report']
                    ]
                ]
            ],
            // Slide 7: Data Processing
            [
                'slide_number' => 7,
                'type' => 'content',
                'content' => [
                    'title' => 'Data Processing',
                    'definition' => 'The act of converting data into information by organizing, sorting, or analyzing it.',
                    'examples' => [
                        'Calculating the average score from a list of marks',
                        'Sorting names alphabetically',
                        'Making a chart from survey results'
                    ]
                ]
            ],
            // Slide 8: Information Lifecycle
            [
                'slide_number' => 8,
                'type' => 'content',
                'content' => [
                    'title' => 'Information Lifecycle',
                    'lifecycle' => [
                        ['step' => '1. Data Collection', 'desc' => 'Data is collected'],
                        ['step' => '2. Processing', 'desc' => 'Data is processed into information'],
                        ['step' => '3. Knowledge', 'desc' => 'Information is used to gain knowledge']
                    ]
                ]
            ],
            // Slide 9: Media
            [
                'slide_number' => 9,
                'type' => 'content',
                'content' => [
                    'title' => 'Media',
                    'definition' => 'Tools or methods used to express, transmit, and store information.',
                    'examples' => [
                        'Expression media: Text, images, sound, video',
                        'Transmission media: TV, radio, Internet',
                        'Recording media: Paper, USB drive, cloud storage'
                    ]
                ]
            ],
            // Slide 10: Media Literacy
            [
                'slide_number' => 10,
                'type' => 'content',
                'content' => [
                    'title' => 'Media Literacy',
                    'definition' => 'The ability to understand, interpret, and evaluate information in different media.',
                    'examples' => [
                        'Reading a news article online and checking if it is true',
                        'Sara sees a post on social media and asks her teacher to verify it'
                    ]
                ]
            ],
            // Slide 11: Information Ethics
            [
                'slide_number' => 11,
                'type' => 'content',
                'content' => [
                    'title' => 'Information Ethics',
                    'definition' => 'Guidelines for responsible use and behavior with information online and offline.',
                    'examples' => [
                        'Respecting privacy (not sharing friends\' info without permission)',
                        'Not copying others\' work (avoiding plagiarism)',
                        'Not spreading rumors or fake news'
                    ]
                ]
            ],
            // Slide 12: Digital Age Risks
            [
                'slide_number' => 12,
                'type' => 'content',
                'content' => [
                    'title' => 'Digital Age Risks',
                    'definition' => 'Dangers connected to technology use.',
                    'examples' => [
                        'Cyberbullying: Sending mean messages online',
                        'Internet addiction: Spending too much time online',
                        'Privacy issues: Accidentally sharing personal photos',
                        'Identity theft: Someone uses your account without permission'
                    ]
                ]
            ],
            // Slide 13: Real-World Example
            [
                'slide_number' => 13,
                'type' => 'scenario',
                'content' => [
                    'title' => 'Real-World Example: Ahmed\'s Week',
                    'data' => 'Ahmed records the daily temperature: 30°C, 32°C, 35°C, 33°C, 34°C, 36°C, 31°C',
                    'information' => 'He calculates the weekly average: 33°C and identifies the hottest day: Saturday (36°C)',
                    'knowledge' => 'He decides to go swimming on Saturday (the hottest day)'
                ]
            ],
            // Slide 14: Quick Quiz
            [
                'slide_number' => 14,
                'type' => 'quiz',
                'content' => [
                    'title' => 'Quick Quiz',
                    'questions' => [
                        'What is the difference between data and information?',
                        'Give an example of knowledge from your daily life.',
                        'What are the three characteristics of information in the digital age?'
                    ]
                ]
            ],
            // Slide 15: Test Your Understanding
            [
                'slide_number' => 15,
                'type' => 'quiz',
                'content' => [
                    'title' => 'Test Your Understanding',
                    'questions' => [
                        [
                            'q' => 'Which is a type of transmission media?',
                            'options' => ['Notebook', 'Radio', 'Pencil'],
                            'answer' => 'Radio'
                        ],
                        [
                            'q' => 'What is media literacy?',
                            'options' => ['Writing stories', 'Understanding and checking info', 'Playing games'],
                            'answer' => 'Understanding and checking info'
                        ]
                    ]
                ]
            ],
            // Slide 16: Comprehensive Review
            [
                'slide_number' => 16,
                'type' => 'review',
                'content' => [
                    'title' => 'Comprehensive Review Questions',
                    'questions' => [
                        'Define data, information, and knowledge.',
                        'What are the characteristics of information in the digital age?',
                        'What is the difference between primary and secondary information?',
                        'Give an example of data being processed into information.'
                    ]
                ]
            ],
            // Slide 17: Model Answers
            [
                'slide_number' => 17,
                'type' => 'answers',
                'content' => [
                    'title' => 'Model Answers',
                    'answers' => [
                        ['q' => 'Definitions', 'a' => 'Data: Raw facts | Information: Processed data | Knowledge: Understanding'],
                        ['q' => 'Characteristics', 'a' => 'Information is persistent, reproducible, and spreads easily'],
                        ['q' => 'Types', 'a' => 'Primary: Direct from source | Secondary: Summarized from primary']
                    ]
                ]
            ]
        ];

        // --- Insert all slides using a loop and json_encode ---
        foreach ($slides as $slideData) {
            Slide::create([
                'chapter_id' => $chapter->id,
                'slide_number' => $slideData['slide_number'],
                'type' => $slideData['type'],
                // Explicitly encode the PHP array to a JSON string
                'content' => json_encode($slideData['content']),
            ]);
        }

        
    }
}
