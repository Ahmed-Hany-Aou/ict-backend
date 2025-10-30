import React, { useState, useEffect } from 'react';
import axios from 'axios';

/**
 * Example Quiz Component with Time Tracking
 *
 * This component demonstrates:
 * 1. Tracking quiz start time
 * 2. Displaying elapsed time to user
 * 3. Sending time_taken when submitting
 */

const QuizComponent = ({ quizId, quizData }) => {
  const [answers, setAnswers] = useState({});
  const [currentQuestion, setCurrentQuestion] = useState(0);
  const [startTime] = useState(Date.now()); // Store quiz start time
  const [elapsedTime, setElapsedTime] = useState(0);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Update elapsed time every second
  useEffect(() => {
    const timer = setInterval(() => {
      const elapsed = Math.floor((Date.now() - startTime) / 1000);
      setElapsedTime(elapsed);
    }, 1000);

    // Cleanup on unmount
    return () => clearInterval(timer);
  }, [startTime]);

  // Format time for display (MM:SS)
  const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  };

  // Handle answer selection
  const handleAnswerSelect = (questionIndex, answerIndex) => {
    setAnswers({
      ...answers,
      [questionIndex]: answerIndex
    });
  };

  // Submit quiz with time tracking
  const handleSubmit = async () => {
    setIsSubmitting(true);

    try {
      // Calculate total time taken in seconds
      const timeTakenSeconds = Math.floor((Date.now() - startTime) / 1000);

      const token = localStorage.getItem('token');

      const response = await axios.post(
        `${process.env.REACT_APP_API_URL || 'http://localhost:8000/api'}/quizzes/${quizId}/submit`,
        {
          answers: answers,
          time_taken: timeTakenSeconds  // Send time in seconds
        },
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        }
      );

      if (response.data.success) {
        // Redirect to results page
        window.location.href = `/quiz/results/${response.data.result.id}`;
        // OR using react-router: navigate(`/quiz/results/${response.data.result.id}`);
      } else {
        alert('Failed to submit quiz. Please try again.');
      }
    } catch (error) {
      console.error('Error submitting quiz:', error);
      alert(error.response?.data?.message || 'Failed to submit quiz. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  // Check if all questions are answered
  const allQuestionsAnswered = () => {
    return quizData.questions.every((_, index) => answers[index] !== undefined);
  };

  return (
    <div className="quiz-container">
      {/* Timer Display */}
      <div className="quiz-header">
        <div className="timer-display">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 6v6l4 2"/>
          </svg>
          <span>Time: {formatTime(elapsedTime)}</span>
        </div>
        <div className="progress-display">
          Question {currentQuestion + 1} of {quizData.questions.length}
        </div>
      </div>

      {/* Question Display */}
      <div className="question-section">
        <h2>{quizData.questions[currentQuestion].question}</h2>

        <div className="options-list">
          {quizData.questions[currentQuestion].options.map((option, index) => (
            <div
              key={index}
              className={`option ${answers[currentQuestion] === index ? 'selected' : ''}`}
              onClick={() => handleAnswerSelect(currentQuestion, index)}
            >
              <input
                type="radio"
                name={`question-${currentQuestion}`}
                checked={answers[currentQuestion] === index}
                onChange={() => handleAnswerSelect(currentQuestion, index)}
              />
              <label>{option}</label>
            </div>
          ))}
        </div>
      </div>

      {/* Navigation */}
      <div className="quiz-navigation">
        <button
          onClick={() => setCurrentQuestion(Math.max(0, currentQuestion - 1))}
          disabled={currentQuestion === 0}
          className="btn-previous"
        >
          ← Previous
        </button>

        {currentQuestion < quizData.questions.length - 1 ? (
          <button
            onClick={() => setCurrentQuestion(currentQuestion + 1)}
            className="btn-next"
          >
            Next →
          </button>
        ) : (
          <button
            onClick={handleSubmit}
            disabled={!allQuestionsAnswered() || isSubmitting}
            className="btn-submit"
          >
            {isSubmitting ? 'Submitting...' : 'Submit Quiz'}
          </button>
        )}
      </div>

      {/* Question Overview */}
      <div className="questions-overview">
        <p>Questions: {Object.keys(answers).length} / {quizData.questions.length} answered</p>
        <div className="question-dots">
          {quizData.questions.map((_, index) => (
            <span
              key={index}
              className={`dot ${answers[index] !== undefined ? 'answered' : ''} ${index === currentQuestion ? 'current' : ''}`}
              onClick={() => setCurrentQuestion(index)}
            />
          ))}
        </div>
      </div>
    </div>
  );
};

export default QuizComponent;

/**
 * IMPORTANT NOTES:
 *
 * 1. The time tracking starts when the component mounts (when user starts the quiz)
 * 2. Time is sent in SECONDS (not milliseconds) to match the backend
 * 3. The timer updates every second to show real-time progress
 * 4. Time is calculated at submission, not stored in state (to prevent manipulation)
 *
 * CSS Styles for Timer (add to your Quiz.css):
 *
 * .timer-display {
 *   display: flex;
 *   align-items: center;
 *   gap: 8px;
 *   padding: 12px 20px;
 *   background: #f0f9ff;
 *   border-radius: 8px;
 *   font-weight: 600;
 *   color: #0369a1;
 * }
 *
 * .questions-overview {
 *   margin-top: 20px;
 *   text-align: center;
 * }
 *
 * .question-dots {
 *   display: flex;
 *   gap: 8px;
 *   justify-content: center;
 *   margin-top: 10px;
 * }
 *
 * .dot {
 *   width: 12px;
 *   height: 12px;
 *   border-radius: 50%;
 *   background: #e5e7eb;
 *   cursor: pointer;
 *   transition: all 0.2s;
 * }
 *
 * .dot.answered {
 *   background: #22c55e;
 * }
 *
 * .dot.current {
 *   transform: scale(1.3);
 *   box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.3);
 * }
 */
