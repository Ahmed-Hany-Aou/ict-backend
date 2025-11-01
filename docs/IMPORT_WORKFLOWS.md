# Content Import Workflows

This document explains the **three import scenarios** available in the ICT Learning Platform.

---

## 📦 Three Import Scenarios

### ✅ Scenario 1: Chapter + Slides (No Quiz)

**When to use:**
- You want to create a chapter without a quiz
- You'll add the quiz later
- You're importing content in stages

**How to do it:**
1. Go to **"Import Chapter"** page
2. Paste **Chapter & Slides JSON** in the first field
3. **Leave quiz field empty**
4. Click "Import Chapter"

**Example JSON:**
```json
{
  "title": "Introduction to Networks",
  "description": "Learn networking basics",
  "chapter_number": 3,
  "video_type": "none",
  "is_published": true,
  "is_premium": false,
  "slides": [
    {
      "slide_number": 1,
      "type": "content",
      "content": {
        "title": "What is a Network?",
        "body": "A network connects computers..."
      }
    }
  ]
}
```

---

### ✅ Scenario 2: Chapter + Slides + Quiz (Complete)

**When to use:**
- You have both chapter and quiz ready
- You want to import everything at once
- Maximum efficiency

**How to do it:**
1. Go to **"Import Chapter"** page
2. Paste **Chapter & Slides JSON** in the first field
3. Paste **Quiz JSON** in the second field
4. Click "Import Chapter"

**You need TWO JSON blocks:**

**Chapter JSON:**
```json
{
  "title": "Introduction to Networks",
  "description": "Learn networking basics",
  "chapter_number": 3,
  "slides": [...]
}
```

**Quiz JSON:**
```json
{
  "title": "Networks Quiz",
  "category": "chapter",
  "questions": [
    {
      "question": "What is a LAN?",
      "options": ["A. Local Area Network", "B. Long Area Network"],
      "correct_answer": "A"
    }
  ]
}
```

---

### ✅ Scenario 3: Quiz Only (For Existing Chapter)

**When to use:**
- Chapter already exists (imported in Scenario 1)
- You want to add a quiz now
- You created quiz separately

**How to do it:**
1. Go to **"Import Quiz"** page (NEW!)
2. **Select existing chapter** from dropdown
3. Paste **Quiz JSON**
4. Click "Import Quiz"

**Example:**

**Step 1:** Select chapter from dropdown
```
Chapter: "Introduction to Networks" (Chapter 3)
```

**Step 2:** Paste quiz JSON
```json
{
  "title": "Networks Quiz",
  "description": "Test your networking knowledge",
  "category": "chapter",
  "passing_score": 70,
  "questions": [
    {
      "question": "What is a LAN?",
      "options": [
        "A. Local Area Network",
        "B. Long Area Network",
        "C. Limited Access Network",
        "D. Linear Array Network"
      ],
      "correct_answer": "A",
      "explanation": "LAN stands for Local Area Network"
    }
  ]
}
```

---

## 🎯 Quick Decision Guide

```
Do you have a chapter yet?
├─ NO → Use "Import Chapter"
│   │
│   └─ Do you have a quiz too?
│       ├─ YES → Fill both fields (Scenario 2)
│       └─ NO → Fill chapter only (Scenario 1)
│
└─ YES → Use "Import Quiz"
    └─ Select chapter + paste quiz (Scenario 3)
```

---

## 📍 Where to Find Import Pages

In Filament Admin Panel → **Content Management** section:

1. **Import Chapter** - For Scenarios 1 & 2
2. **Import Quiz** - For Scenario 3

Both pages auto-discovered, no configuration needed!

---

## 🔄 Common Workflows

### Workflow A: Stage-by-Stage
```
Day 1: Import Chapter (Scenario 1)
  ↓
Day 2: Import Quiz (Scenario 3)
```

### Workflow B: All at Once
```
Day 1: Import Chapter + Quiz (Scenario 2)
```

### Workflow C: Bulk Chapters, Then Quizzes
```
Week 1: Import 5 chapters without quizzes (Scenario 1 × 5)
  ↓
Week 2: Import 5 quizzes (Scenario 3 × 5)
```

---

## ⚠️ Important Notes

### Chapter + Slides are MANDATORY Together
- You **cannot** import slides without a chapter
- They must be in the same JSON (slides array inside chapter object)
- This is by design - slides belong to chapters

### Quiz is OPTIONAL
- Can be imported with chapter (Scenario 2)
- Can be imported separately later (Scenario 3)
- Chapters can have multiple quizzes (import multiple times)

### Multiple Quizzes Per Chapter
When using Scenario 3, you'll see a warning if the chapter already has a quiz:
```
⚠️ Chapter "Intro to ICT" already has 1 quiz(zes).
   Importing will add another quiz.
```

This is allowed! You can have:
- Practice Quiz
- Graded Quiz
- Midterm Quiz
- Final Quiz

All for the same chapter.

---

## 📚 Documentation Files

- **CHAPTER_IMPORT_TEMPLATE.md** - Chapter + Slides JSON format
- **QUIZ_IMPORT_TEMPLATE.md** - Quiz JSON format
- **IMPORT_WORKFLOWS.md** - This file (overview)

---

## 🎉 Summary

| Scenario | Page | Fields to Fill | Result |
|----------|------|----------------|--------|
| 1 | Import Chapter | Chapter only | Chapter + Slides |
| 2 | Import Chapter | Chapter + Quiz | Chapter + Slides + Quiz |
| 3 | Import Quiz | Chapter dropdown + Quiz | Quiz attached to existing chapter |

**All three scenarios are now available!** 🚀
