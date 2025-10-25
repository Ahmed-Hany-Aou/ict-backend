<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Chapter;

class Chapter1QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Chapter 1
        $chapter = Chapter::where('chapter_number', 1)->first();

        if (!$chapter) {
            $this->command->error('Chapter 1 not found. Please run Chapter1Seeder first.');
            return;
        }

        // Delete existing quiz for this chapter
        Quiz::where('chapter_id', $chapter->id)->delete();

        // Create quiz with 25 questions
        $questions = [
            // Questions 1-5: Basic Concepts
            [
                'question' => 'What are data?',
                'options' => [
                    'Processed information with meaning',
                    'Raw facts, figures, or symbols without meaning',
                    'Knowledge gained from experience',
                    'Digital files stored on a computer'
                ],
                'correct_answer' => 1, // Index of correct answer (0-based)
                'explanation' => 'Data are raw facts, figures, or symbols that have not yet been processed to give them meaning.'
            ],
            [
                'question' => 'Which of the following is an example of data?',
                'options' => [
                    'Ahmed scored 23 marks in math',
                    'The average temperature is 30°C',
                    'A list of random numbers: 100, 3.14, 42',
                    'Studying regularly improves exam scores'
                ],
                'correct_answer' => 2,
                'explanation' => 'A list of random numbers is raw data without context or meaning.'
            ],
            [
                'question' => 'What is information?',
                'options' => [
                    'Raw facts without meaning',
                    'Data that has been processed to have meaning and value',
                    'Understanding gained from experience',
                    'Any digital content on the internet'
                ],
                'correct_answer' => 1,
                'explanation' => 'Information is data that has been processed or organized to have meaning and value.'
            ],
            [
                'question' => 'Information equals:',
                'options' => [
                    'Data + Storage',
                    'Data + Technology',
                    'Data + Context + Meaning',
                    'Data + Internet'
                ],
                'correct_answer' => 2,
                'explanation' => 'Information = Data + Context + Meaning'
            ],
            [
                'question' => 'What is knowledge?',
                'options' => [
                    'Data stored in databases',
                    'Information found on websites',
                    'Understanding and awareness gained by using information',
                    'Facts memorized for an exam'
                ],
                'correct_answer' => 2,
                'explanation' => 'Knowledge is the understanding and awareness gained by using information to make decisions or solve problems.'
            ],

            // Questions 6-10: Characteristics and Types
            [
                'question' => 'Which is NOT a characteristic of information in the digital age?',
                'options' => [
                    'Persistence - hard to erase completely',
                    'Reproducibility - can be copied easily',
                    'Spreads easily - reaches many people quickly',
                    'Temporary - disappears quickly'
                ],
                'correct_answer' => 3,
                'explanation' => 'Information in the digital age is persistent (hard to erase), not temporary.'
            ],
            [
                'question' => 'A photo posted online is an example of which characteristic?',
                'options' => [
                    'Persistence',
                    'Reproducibility',
                    'Spreads easily',
                    'All of the above'
                ],
                'correct_answer' => 3,
                'explanation' => 'A posted photo demonstrates all three characteristics: it persists online, can be reproduced, and spreads easily.'
            ],
            [
                'question' => 'What is primary information?',
                'options' => [
                    'The most important information',
                    'Direct from the original source',
                    'Processed from secondary sources',
                    'Information found first'
                ],
                'correct_answer' => 1,
                'explanation' => 'Primary information is direct from the original source, like survey results or eyewitness accounts.'
            ],
            [
                'question' => 'Which is an example of secondary information?',
                'options' => [
                    'Survey results you conducted',
                    'Your eyewitness account',
                    'A news article summarizing research',
                    'Original research data'
                ],
                'correct_answer' => 2,
                'explanation' => 'A news article is secondary information because it is processed or summarized from primary sources.'
            ],
            [
                'question' => 'What is data processing?',
                'options' => [
                    'Storing data on computers',
                    'Converting data into information by organizing or analyzing it',
                    'Deleting unnecessary files',
                    'Backing up data'
                ],
                'correct_answer' => 1,
                'explanation' => 'Data processing is converting data into information by organizing, sorting, or analyzing it.'
            ],

            // Questions 11-15: Data Processing and Lifecycle
            [
                'question' => 'Which is an example of data processing?',
                'options' => [
                    'Writing down temperature readings',
                    'Calculating the average score from marks',
                    'Collecting survey responses',
                    'Reading a book'
                ],
                'correct_answer' => 1,
                'explanation' => 'Calculating the average is processing raw data (individual marks) into information (average score).'
            ],
            [
                'question' => 'What is the first step in the information lifecycle?',
                'options' => [
                    'Processing',
                    'Data Collection',
                    'Knowledge',
                    'Analysis'
                ],
                'correct_answer' => 1,
                'explanation' => 'The information lifecycle starts with Data Collection, then Processing, then Knowledge.'
            ],
            [
                'question' => 'In the information lifecycle, data is processed into:',
                'options' => [
                    'Knowledge',
                    'Information',
                    'Wisdom',
                    'Media'
                ],
                'correct_answer' => 1,
                'explanation' => 'In the lifecycle: Data is collected → Data is processed into Information → Information is used to gain Knowledge.'
            ],
            [
                'question' => 'Ahmed records daily temperatures. What stage is this?',
                'options' => [
                    'Data Collection',
                    'Processing',
                    'Knowledge',
                    'Information'
                ],
                'correct_answer' => 0,
                'explanation' => 'Recording temperatures is the Data Collection stage.'
            ],
            [
                'question' => 'Ahmed calculates the average temperature. What stage is this?',
                'options' => [
                    'Data Collection',
                    'Processing',
                    'Knowledge',
                    'Media'
                ],
                'correct_answer' => 1,
                'explanation' => 'Calculating the average is Processing - converting data into information.'
            ],

            // Questions 16-20: Media and Media Literacy
            [
                'question' => 'What is media?',
                'options' => [
                    'Only social media platforms',
                    'Tools or methods to express, transmit, and store information',
                    'Television and radio only',
                    'Newspapers and magazines'
                ],
                'correct_answer' => 1,
                'explanation' => 'Media are tools or methods used to express, transmit, and store information.'
            ],
            [
                'question' => 'Which is an example of expression media?',
                'options' => [
                    'Television',
                    'Internet',
                    'Text and images',
                    'USB drive'
                ],
                'correct_answer' => 2,
                'explanation' => 'Expression media includes text, images, sound, and video - the forms information takes.'
            ],
            [
                'question' => 'Which is an example of transmission media?',
                'options' => [
                    'Text',
                    'TV and Internet',
                    'Paper',
                    'Images'
                ],
                'correct_answer' => 1,
                'explanation' => 'Transmission media includes TV, radio, and Internet - channels that deliver information.'
            ],
            [
                'question' => 'Cloud storage is an example of:',
                'options' => [
                    'Expression media',
                    'Transmission media',
                    'Recording media',
                    'Social media'
                ],
                'correct_answer' => 2,
                'explanation' => 'Cloud storage is recording media - it stores information.'
            ],
            [
                'question' => 'What is media literacy?',
                'options' => [
                    'Ability to read and write',
                    'Ability to understand, interpret, and evaluate information in different media',
                    'Knowing how to use social media',
                    'Creating videos and photos'
                ],
                'correct_answer' => 1,
                'explanation' => 'Media literacy is the ability to understand, interpret, and evaluate information in different media.'
            ],

            // Questions 21-25: Ethics and Digital Age Risks
            [
                'question' => 'What is information ethics?',
                'options' => [
                    'Rules for using computers',
                    'Guidelines for responsible use and behavior with information',
                    'Laws about internet use',
                    'Social media policies'
                ],
                'correct_answer' => 1,
                'explanation' => 'Information ethics are guidelines for responsible use and behavior with information online and offline.'
            ],
            [
                'question' => 'Which is an example of good information ethics?',
                'options' => [
                    'Copying others\' work without credit',
                    'Sharing friends\' photos without permission',
                    'Not spreading rumors or fake news',
                    'Posting everything you find online'
                ],
                'correct_answer' => 2,
                'explanation' => 'Not spreading rumors or fake news is responsible information behavior.'
            ],
            [
                'question' => 'What is plagiarism?',
                'options' => [
                    'Sharing information online',
                    'Copying others\' work without giving credit',
                    'Using social media',
                    'Reading online articles'
                ],
                'correct_answer' => 1,
                'explanation' => 'Plagiarism is copying others\' work without proper attribution.'
            ],
            [
                'question' => 'Which is a digital age risk?',
                'options' => [
                    'Using computers for homework',
                    'Cyberbullying',
                    'Learning online',
                    'Sending emails'
                ],
                'correct_answer' => 1,
                'explanation' => 'Cyberbullying is a danger connected to technology use.'
            ],
            [
                'question' => 'Sara sees a post on social media. What shows media literacy?',
                'options' => [
                    'Immediately sharing it with friends',
                    'Believing everything she reads',
                    'Asking her teacher to verify if it is true',
                    'Ignoring all online information'
                ],
                'correct_answer' => 2,
                'explanation' => 'Verifying information before trusting it demonstrates media literacy - the ability to evaluate information critically.'
            ]
        ];

        Quiz::create([
            'chapter_id' => $chapter->id,
            'title' => 'Chapter 1: Data, Information, and Knowledge - Final Quiz',
            'description' => 'Test your understanding of data, information, and knowledge concepts covered in Chapter 1. This quiz covers basic concepts, characteristics of information, data processing, media literacy, and digital ethics.',
            'questions' => $questions,
            'passing_score' => 70,
            'is_active' => true
        ]);

        $this->command->info('Chapter 1 quiz created successfully with 25 questions!');
    }
}
