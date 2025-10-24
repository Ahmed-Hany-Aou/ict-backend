<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chapter;
use App\Models\Slide;

class Chapter2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- Create Chapter 2 ---
        $chapter = Chapter::updateOrCreate(
            ['chapter_number' => 2], // Find chapter 2 or create it
            [
                'title' => 'Living Safely in the Information Society',
                'description' => 'Understanding your digital rights and responsibilities online, focusing on privacy and creative work.',
                'video_url' => null, // No intro video specified for this chapter yet
                'is_published' => true,
            ]
        );

        // --- Delete existing slides for this chapter ---
        // This ensures that if we run the seeder again, we don't get duplicate slides.
        $chapter->slides()->delete();

        // --- Define Slides ---
        // We follow the structure and guidelines provided.
        $slides = [
            // --- Slide 1: Title ---
            [
                'slide_number' => 1,
                'type' => 'title',
                'content' => [
                    'title' => 'Chapter 2',
                    'subtitle' => 'Living Safely in the Information Society',
                    'description' => 'Your guide to privacy and using creative work online in Egypt. (Grade 10 ICT)'
                ]
            ],
            // --- Slide 2: Introduction ---
            [
                'slide_number' => 2,
                'type' => 'content',
                'content' => [
                    'title' => 'What We Will Learn',
                    'points' => [
                        "What is 'Personal Information' and why it's important.",
                        "Understanding your right to Privacy online.",
                        "What are 'Intellectual Property Rights' (like Copyright).",
                        "How to use photos, music, and text safely and legally.",
                        "What 'Creative Commons' means for sharing work.",
                        "Being a good digital citizen in Egypt.",
                    ],
                    'note' => "This chapter helps you use the internet safely and respect others' work!"
                ]
            ],
            // --- Slide 3: Personal Information Definition ---
            [
                'slide_number' => 3,
                'type' => 'content',
                'content' => [
                    'title' => "What is Personal Information?",
                    'definition' => "**Personal information** is any detail about a living person that can identify them, either alone or when combined with other details.",
                    'keyPoint' => "Think of it as any clue that points directly to YOU.",
                    'examples' => [
                        "Your full name (like 'Ahmed Hassan')",
                        "Your home address in Cairo",
                        "Your date of birth",
                        "Your phone number or email",
                        "Your school name or grades",
                        "Your National ID number ('My Number' in the book is like our National ID)",
                        "Photos where your face is clear",
                        "Fingerprints or eye scans (biometric data)",
                    ]
                ]
            ],
             // --- Slide 4: Four Basic Items & ID Codes ---
            [
                'slide_number' => 4,
                'type' => 'content',
                'content' => [
                    'title' => "Basic Info vs. ID Codes",
                    'points' => [
                       "The **Four Basic Items** are your: Name, Address, Date of Birth, and Gender. These are very common identifiers.",
                       "**Personal Identification Codes** are unique numbers given by the government or official bodies. Examples include your National ID number, passport number, or driver's license number.",
                    ],
                     'note' => "These codes are especially sensitive because they are unique official identifiers."
                ]
            ],
            // --- Slide 5: Special Care-Required Info ---
             [
                'slide_number' => 5,
                'type' => 'content',
                'content' => [
                    'title' => "Sensitive Personal Information",
                    'definition' => "**Special Care-Required Personal Information** is data that could be used unfairly against someone or cause prejudice (unfair judgment).",
                     'keyPoint' => "This information needs extra protection by law.",
                     'examples' => [
                        "Your race or ethnic origin",
                        "Your religious beliefs ('creed')",
                        "Your family background or social status",
                        "Your medical history (illnesses, hospital visits)",
                        "Any history of crimes ('criminal record')",
                    ],
                    'note' => "Sharing this type of information requires more care and often specific permission."
                ]
            ],
            // --- Slide 6: Privacy & Protection Law ---
            [
                'slide_number' => 6,
                'type' => 'content',
                'content' => [
                    'title' => "Your Right to Privacy",
                    'definition' => "**Privacy** is your right to control your personal information and who gets to see or use it.",
                    'keyPoint' => "In Egypt, like many countries, there are laws to protect your data (similar to the 'Act on the Protection of Personal Information' mentioned).",
                     'points' => [
                        "Generally, companies or people **cannot share your personal info** with others ('third parties') without your permission.",
                        "There are exceptions, like when the police need information for an investigation (based on laws) or in emergencies.",
                     ],
                      'note' => "Think before you click 'Agree'! Understand how websites use your data."
                ]
            ],
             // --- Slide 7: Connecting Idea - Digital Citizenship Story ---
            [
                'slide_number' => 7,
                'type' => 'scenario',
                'content' => [
                    'title' => "Ahmed's Online Project",
                    'scenario' => "Ahmed, a Grade 10 student in Alexandria, is creating a presentation about Egyptian landmarks for school. He finds amazing photos online and a cool song by an Egyptian artist he wants to use.",
                    'breakdown' => [
                        ['type' => 'Privacy Question', 'content' => "He also wants to include photos of his friends visiting the pyramids. Should he post their photos without asking them first?"],
                        ['type' => 'Copyright Question', 'content' => "Can Ahmed just download the photos and the song he found online and use them freely in his project?"],
                    ],
                    'note' => "Let's explore the rules Ahmed needs to follow to be a good digital citizen."
                ]
            ],
            // --- Slide 8: Intellectual Property ---
            [
                'slide_number' => 8,
                'type' => 'content',
                'content' => [
                    'title' => "What is Intellectual Property?",
                    'definition' => "**Intellectual Property (IP) Rights** are legal rights given to people for the creations of their minds. It protects things you create using your ideas and creativity.",
                    'keyPoint' => "Think of it like owning a physical object, but for ideas and creative works.",
                     'examples' => [
                        "**Copyright:** Protects creative works like books, music, photos, videos, software code (Ahmed's song and photos).",
                        "**Patent:** Protects inventions (like a new type of phone).",
                        "**Trademark:** Protects brand names, logos, and slogans (like company logos).",
                    ],
                    'note' => "These rights encourage people to create new things!"
                ]
            ],
             // --- Slide 9: Copyright Basics ---
            [
                'slide_number' => 9,
                'type' => 'content',
                'content' => [
                    'title' => "Understanding Copyright",
                    'definition' => "**Copyright** automatically protects original creative works as soon as they are created (e.g., written down, recorded, saved as a file). You don't usually need to register it.",
                    'keyPoint' => "The creator (author, artist, musician) gets exclusive rights.",
                     'points' => [
                        "**Right to Copy:** Only the creator can make copies.",
                        "**Right to Distribute:** Only the creator can share or sell copies.",
                        "**Right to Adapt:** Only the creator can make changes (like translating a book or remixing a song).",
                        "**Right to Perform/Display:** Only the creator can perform the music or show the photo publicly.",
                    ],
                     'note' => "For Ahmed: The photographer owns the copyright to the landmark photos, and the musician owns the copyright to the song."
                ]
            ],
            // --- Slide 10: Using Copyrighted Work (Exceptions) ---
            [
                'slide_number' => 10,
                'type' => 'content',
                'content' => [
                    'title' => "Can Ahmed Use the Photos and Song?",
                    'keyPoint' => "Using someone else's copyrighted work without permission is usually illegal ('infringement'). But there are some exceptions:",
                     'points' => [
                        "**Permission:** Ahmed can ask the creators for permission (sometimes they charge money).",
                        "**Public Domain:** If the work is very old, the copyright might have expired (usually many decades after the creator dies).",
                        "**Fair Use / Educational Use:** Using small parts for schoolwork, criticism, or news reporting *might* be allowed, but the rules can be tricky. It's safer to use works that explicitly allow reuse.",
                        "**Licensing (like Creative Commons):** Some creators allow specific uses of their work under certain conditions.",
                    ],
                     'note' => "Just because it's online doesn't mean it's free to use!"
                ]
            ],
             // --- Slide 11: Creative Commons ---
            [
                'slide_number' => 11,
                'type' => 'content',
                'content' => [
                    'title' => "Creative Commons (CC) Licenses",
                    'definition' => "**Creative Commons** is a system that lets creators easily tell others how they can legally share and reuse their work.",
                     'keyPoint' => "Look for CC symbols on websites like Flickr (photos) or Wikimedia Commons.",
                    'cards' => [
                        ['title' => 'BY (Attribution)', 'desc' => "You MUST give credit to the original creator.", 'example' => "Ahmed must say 'Photo by Fatima Ali'"],
                        ['title' => 'NC (NonCommercial)', 'desc' => "You CANNOT use the work to make money.", 'example' => "Ahmed can use it for school, but not sell t-shirts with the photo."],
                        ['title' => 'ND (NoDerivatives)', 'desc' => "You CANNOT change or alter the work.", 'example' => "Ahmed cannot edit the photo's colors."],
                        ['title' => 'SA (ShareAlike)', 'desc' => "If you change the work, you MUST share your new version under the SAME CC license.", 'example' => "If Ahmed remixes a CC song, his remix must also be CC."]
                    ],
                     'note' => "These licenses make it easier for people like Ahmed to find music and photos they can legally use for projects."
                ]
            ],
            // --- Slide 12: Ahmed's Solution & Privacy Recap ---
            [
                'slide_number' => 12,
                'type' => 'scenario',
                'content' => [
                    'title' => "Ahmed's Responsible Choices",
                    'scenario' => "Ahmed learns about privacy and copyright. What should he do?",
                    'breakdown' => [
                        ['type' => 'Privacy Action', 'content' => "Ahmed decides NOT to post photos of his friends without asking their permission first. He respects their privacy."],
                        ['type' => 'Copyright Action', 'content' => "He searches for landmark photos on websites with Creative Commons licenses (like Wikimedia Commons) and finds photos licensed 'CC BY'. He also finds music with a 'CC BY-NC' license."],
                         ['type' => 'Result', 'content' => "In his presentation, he gives credit to the photographers ('Photo by...') and uses the music legally for his non-commercial school project. He is being a good digital citizen!"],
                    ],
                     'note' => "Being safe and respectful online involves understanding both privacy and copyright."
                ]
            ],
            // --- Slide 13: Egyptian Scenario ---
             [
                'slide_number' => 13,
                'type' => 'scenario',
                'content' => [
                    'title' => "Real-World Scenario in Egypt",
                    'scenario' => "Fatima takes a beautiful photo of Khan el-Khalili market in Cairo and posts it on her Instagram. A local news website uses her photo in an article without asking her or giving her credit.",
                    'breakdown' => [
                        ['type' => 'Question', 'content' => "Did the news website violate Fatima's rights?"],
                        ['type' => 'Answer', 'content' => "Yes, most likely. Fatima automatically owns the copyright to her photo. The website should have asked for permission or checked if she used a Creative Commons license allowing reuse."],
                        ['type' => 'Action', 'content' => "Fatima could contact the website and ask them to give her credit or remove the photo."],
                    ],
                    'note' => "Copyright protects creators in Egypt too, even on social media."
                ]
            ],
            // --- Slide 14: Quick Quiz (Text) ---
            [
                'slide_number' => 14,
                'type' => 'quiz',
                'content' => [
                    'title' => "Quick Check (Part 1)",
                    'questions' => [
                        "What is the main difference between Personal Information and Special Care-Required Personal Information?",
                        "What does 'Intellectual Property' protect?",
                        "If you find a photo online, can you always use it for free in your school project? Why or why not?",
                    ]
                ]
            ],
            // --- Slide 15: Quick Quiz (Multiple Choice) ---
            [
                'slide_number' => 15,
                'type' => 'quiz',
                'content' => [
                    'title' => "Quick Check (Part 2 - Choose)",
                    'questions' => [
                        [
                            'q' => "Which of these is NOT usually considered one of the 'Four Basic Items' of personal information?",
                            'options' => ["Name", "Address", "Favorite Color", "Date of Birth"],
                            'answer' => "Favorite Color"
                        ],
                        [
                            'q' => "Ahmed uses a song with a 'CC BY-NC' license for a school video. Can he upload the video to YouTube and make money from ads?",
                            'options' => ["Yes, as long as he gives credit.", "No, because of the 'NC' (NonCommercial) condition.", "Yes, because it's for education.", "Only if he also uses 'SA' (ShareAlike)."],
                            'answer' => "No, because of the 'NC' (NonCommercial) condition."
                        ],
                         [
                            'q' => "When does copyright protection start for a song you wrote?",
                            'options' => ["Only after you register it.", "As soon as you record it or write it down.", "After you publish it online.", "When you get famous."],
                            'answer' => "As soon as you record it or write it down."
                        ]
                    ]
                ]
            ],
            // --- Slide 16: Review Questions ---
            [
                'slide_number' => 16,
                'type' => 'review',
                'content' => [
                    'title' => "Let's Review!",
                    'questions' => [
                        "Why is it important to protect your personal information online?",
                        "Give an Egyptian example of when someone might need permission to use copyrighted material.",
                        "What do the Creative Commons symbols BY, NC, and ND mean?",
                        "How can you be a responsible digital citizen regarding privacy and copyright?",
                    ]
                ]
            ],
            // --- Slide 17: Model Answers (Quiz Part 1) ---
            [
                'slide_number' => 17,
                'type' => 'answers',
                'content' => [
                    'title' => "Answers (Quick Check Part 1)",
                    'answers' => [
                        ['q' => "Difference between Personal Info and Special Care-Required:", 'a' => "Special Care-Required info (like health, religion) is more sensitive and could be used for unfair discrimination, so it needs extra legal protection."],
                        ['q' => "What does Intellectual Property protect?", 'a' => "It protects creations of the mind, like inventions (patents), brand names (trademarks), and creative works like photos, music, and books (copyright)."],
                        ['q' => "Can you use any online photo for school?", 'a' => "No, not always. The photo is likely protected by copyright. You need permission, or it must be in the public domain, or have a license (like Creative Commons) that allows your use."],
                    ]
                ]
            ],
             // --- Slide 18: Follow-up / Search Task ---
            [
                'slide_number' => 18,
                'type' => 'completion', // Using completion type for final thoughts
                'content' => [
                    'title' => "Chapter 2 Complete!",
                    'message' => "Great job understanding digital safety! You learned about protecting your information and respecting others' creations.",
                    'nextSteps' => [ // Re-using nextSteps for the follow-up questions
                         "**Think & Discuss:** How do Egyptian laws protect your personal data online compared to the examples in the book?",
                         "**Search Task:** Find two Egyptian websites that use Creative Commons licenses for their content (e.g., photos, articles). What licenses do they use?",
                         "Prepare your thoughts for our next meeting or discuss with friends!"
                    ]
                ]
            ]
        ];

        // --- Insert all slides ---
        foreach ($slides as $slideData) {
            // We use json_encode here because the Slide model expects the 'content' attribute to be a JSON string
            // when creating/updating records directly like this. The model's cast handles decoding it later.
            Slide::create([
                'chapter_id' => $chapter->id,
                'slide_number' => $slideData['slide_number'],
                'type' => $slideData['type'],
                'content' => json_encode($slideData['content']), // Encode content back to JSON string for DB
            ]);
        }
    }
}
