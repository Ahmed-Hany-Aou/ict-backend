# Quiz Import Template

This document provides the JSON template for importing a quiz for an **existing chapter** using the Filament Admin Panel's "Import Quiz" page.

## Overview

The **Import Quiz** feature allows you to add a quiz to an existing chapter that was imported without one.

**Use this when:**
- You imported a chapter without a quiz
- You want to add another quiz to an existing chapter
- You created quizzes separately and want to import them in bulk

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
    },
    {
      "question": "What is the primary purpose of ICT?",
      "options": [
        "A. Entertainment only",
        "B. Communication and information processing",
        "C. Physical storage",
        "D. Manual calculations"
      ],
      "correct_answer": "B",
      "explanation": "ICT is primarily used for communication and processing information electronically."
    }
  ]
}
```

## Field Descriptions

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
| `options` | array | Yes | Array of answer options (typically 4 options: A, B, C, D) |
| `correct_answer` | string | Yes | The correct answer (e.g., "A", "B", "C", "D") |
| `explanation` | string | No | Explanation shown after answering |

## Usage Instructions

### Step 1: Navigate to Import Quiz Page
1. Log into the Filament Admin Panel
2. Click on **"Import Quiz"** in the Content Management section

### Step 2: Select Chapter
1. Use the dropdown to select the chapter you want to add a quiz to
2. **Warning:** If the chapter already has a quiz, you'll see a warning notification
3. Importing will add **another** quiz to the chapter (chapters can have multiple quizzes)

### Step 3: Paste Quiz JSON
1. Paste your quiz JSON into the textarea
2. Make sure it's valid JSON (use jsonlint.com to validate)

### Step 4: Import
1. Click **"Import Quiz"**
2. If successful, you'll be redirected to the quizzes list
3. If there's an error, you'll see a detailed error message

## Quiz Categories

Choose the appropriate category for your quiz:

- **`chapter`** - Regular chapter quiz (default)
- **`midterm`** - Midterm examination
- **`final`** - Final examination
- **`practice`** - Practice quiz (doesn't count toward progress)

## Question Format Guidelines

### Option Format
Always use the format: `"A. Option text"`, `"B. Option text"`, etc.

**Good:**
```json
"options": [
  "A. Hardware",
  "B. Software",
  "C. Networks",
  "D. Internet"
]
```

**Bad:**
```json
"options": [
  "Hardware",
  "Software",
  "Networks",
  "Internet"
]
```

### Correct Answer
Must match the letter prefix of the correct option:

```json
{
  "options": ["A. True", "B. False"],
  "correct_answer": "A"  // ✓ Correct
  // "correct_answer": "True"  // ✗ Wrong
}
```

## Examples

### Example 1: Simple 3-Question Quiz

```json
{
  "title": "Basic ICT Quiz",
  "description": "Quick knowledge check",
  "category": "chapter",
  "passing_score": 66,
  "is_active": true,
  "questions": [
    {
      "question": "What is a computer?",
      "options": [
        "A. An electronic device that processes data",
        "B. A mechanical calculator",
        "C. A type of television",
        "D. A communication tool only"
      ],
      "correct_answer": "A"
    },
    {
      "question": "What is software?",
      "options": [
        "A. The physical components of a computer",
        "B. Programs and applications that run on hardware",
        "C. Internet connection",
        "D. Computer screen"
      ],
      "correct_answer": "B"
    },
    {
      "question": "What does CPU stand for?",
      "options": [
        "A. Computer Processing Unit",
        "B. Central Processing Unit",
        "C. Central Program Utility",
        "D. Computer Program Unit"
      ],
      "correct_answer": "B",
      "explanation": "CPU stands for Central Processing Unit - it's the brain of the computer"
    }
  ]
}
```

### Example 2: Midterm Exam

```json
{
  "title": "ICT Midterm Examination",
  "description": "Comprehensive midterm covering chapters 1-5",
  "category": "midterm",
  "passing_score": 75,
  "is_active": true,
  "questions": [
    {
      "question": "Which layer of the OSI model handles routing?",
      "options": [
        "A. Physical Layer",
        "B. Data Link Layer",
        "C. Network Layer",
        "D. Transport Layer"
      ],
      "correct_answer": "C",
      "explanation": "The Network Layer (Layer 3) is responsible for routing packets between networks"
    }
  ]
}
```

## Common Errors

### Error: "Missing required field: questions"
**Problem:** The `questions` array is missing or empty

**Solution:**
```json
{
  "title": "My Quiz",
  "questions": [  // ← Make sure this exists and has at least 1 question
    {
      "question": "...",
      "options": ["A. ...", "B. ..."],
      "correct_answer": "A"
    }
  ]
}
```

### Error: "Question at index 0 is missing required field: correct_answer"
**Problem:** One of your questions is missing the `correct_answer` field

**Solution:** Check all questions have `question`, `options`, and `correct_answer`

### Error: "Invalid JSON format"
**Problem:** Your JSON syntax is incorrect

**Solution:** Validate your JSON at https://jsonlint.com

## Tips

1. **Start Small:** Test with a 2-3 question quiz first
2. **Validate JSON:** Always validate your JSON before importing
3. **Explanations:** Add explanations to help students learn from mistakes
4. **Passing Score:** Set appropriate passing scores (typically 60-80%)
5. **Question Count:** For chapter quizzes, 5-10 questions is ideal
6. **Multiple Quizzes:** You can import multiple quizzes for the same chapter (e.g., practice quiz + graded quiz)

## Complete Workflow

1. **Import Chapter** (without quiz)
   - Use "Import Chapter" page
   - Leave quiz field empty

2. **Later, Import Quiz**
   - Use "Import Quiz" page
   - Select the chapter from dropdown
   - Paste quiz JSON
   - Import

3. **Or Import Both Together**
   - Use "Import Chapter" page
   - Fill both chapter and quiz fields
   - Import in one operation

Choose the workflow that fits your needs!
