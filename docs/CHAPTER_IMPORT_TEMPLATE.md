# Chapter Import Template

This document provides the JSON templates needed to import a new chapter using the Filament Admin Panel's "Import Chapter" page.

## Overview

The Chapter Importer allows you to create a complete chapter (with all slides and quiz) in one operation by pasting two JSON blocks:

1. **Chapter & Slides JSON** - Contains chapter information and all slides
2. **Quiz JSON** - Contains the quiz for the chapter

## Chapter & Slides JSON Template

```json
{
  "title": "Introduction to ICT",
  "description": "Learn the fundamental concepts of Information and Communication Technology",
  "chapter_number": 1,
  "video_type": "none",
  "video_url": null,
  "meeting_link": null,
  "meeting_datetime": null,
  "is_published": true,
  "is_premium": false,
  "content": null,
  "slides": [
    {
      "slide_number": 1,
      "type": "content",
      "content": {
        "title": "What is ICT?",
        "body": "Information and Communication Technology (ICT) refers to technologies that provide access to information through telecommunications..."
      }
    },
    {
      "slide_number": 2,
      "type": "content",
      "content": {
        "title": "Components of ICT",
        "body": "ICT consists of three main components:\n1. Hardware\n2. Software\n3. Networks"
      }
    },
    {
      "slide_number": 3,
      "type": "content",
      "content": {
        "title": "ICT in Daily Life",
        "body": "Watch this video to see how ICT impacts our daily activities"
      },
      "video_url": "https://youtube.com/watch?v=example"
    }
  ]
}
```

## Quiz JSON Template

```json
{
  "title": "Chapter 1 Quiz",
  "description": "Test your knowledge of ICT fundamentals",
  "category": "chapter",
  "passing_score": 70,
  "is_active": true,
  "questions": [
    {
      "question": "What does ICT stand for?",
      "options": [
        "A. Internet Communication Technology",
        "B. Information and Communication Technology",
        "C. Integrated Computer Technology",
        "D. International Computing Technology"
      ],
      "correct_answer": "B",
      "explanation": "ICT stands for Information and Communication Technology"
    },
    {
      "question": "Which of the following is NOT a component of ICT?",
      "options": [
        "A. Hardware",
        "B. Software",
        "C. Networks",
        "D. Books"
      ],
      "correct_answer": "D",
      "explanation": "Books are not part of ICT components. ICT consists of hardware, software, and networks."
    }
  ]
}
```

## Field Descriptions

### Chapter Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `title` | string | Yes | The chapter title |
| `description` | string | Yes | Brief description of the chapter |
| `chapter_number` | integer | Yes | Chapter number (used for ordering) |
| `video_type` | string | No | Options: "none", "recorded", "scheduled" (default: "none") |
| `video_url` | string | No | URL for recorded or scheduled video |
| `meeting_link` | string | No | Google Meet or other meeting link |
| `meeting_datetime` | datetime | No | Date and time for scheduled meetings (ISO 8601 format) |
| `is_published` | boolean | No | Whether the chapter is visible to students (default: true) |
| `is_premium` | boolean | No | Whether the chapter requires premium subscription (default: false) |
| `content` | mixed | No | Additional chapter content |
| `slides` | array | Yes | Array of slide objects (see below) |

### Slide Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `slide_number` | integer | Yes | Slide number within the chapter |
| `type` | string | No | Slide type: "title", "content", "quiz", "scenario", "review", "answers" (default: "content") |
| `content` | object | Yes | Slide content (JSON object with title, body, etc.) |
| `video_url` | string | No | URL for video slides |
| `meeting_link` | string | No | Meeting link for scheduled sessions |

### Quiz Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `title` | string | Yes | Quiz title |
| `description` | string | No | Quiz description |
| `category` | string | No | Quiz category: "chapter", "midterm", "final", "practice" (default: "chapter") |
| `passing_score` | integer | No | Minimum score to pass (default: 70) |
| `is_active` | boolean | No | Whether the quiz is active (default: true) |
| `questions` | array | Yes | Array of question objects (see below) |

### Question Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `question` | string | Yes | The question text |
| `options` | array | Yes | Array of answer options (typically 4 options) |
| `correct_answer` | string | Yes | The correct answer (e.g., "A", "B", "C", "D") |
| `explanation` | string | No | Explanation shown after answering |

## Usage Instructions

1. Navigate to the Filament Admin Panel
2. Click on "Import Chapter" in the Content Management section
3. Paste your Chapter & Slides JSON into the first textarea
4. Paste your Quiz JSON into the second textarea
5. Click "Import Chapter"
6. If successful, you'll be redirected to the chapters list
7. If there's an error, a notification will show what went wrong

## Tips

- **Validation**: The importer validates all required fields before importing
- **Transaction Safety**: All data is imported in a single database transaction - if anything fails, nothing is saved
- **Error Messages**: Detailed error messages help you fix JSON formatting or missing fields
- **Slide Numbers**: Make sure slide numbers are sequential starting from 1
- **JSON Format**: Use a JSON validator (like jsonlint.com) to check your JSON before importing

## Example: Complete Chapter with 3 Slides

See the templates above for a working example of a chapter with 3 slides and 2 quiz questions.
