import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import './QuizResultDetail.css';

const QuizResultDetail = () => {
  const { resultId } = useParams();
  const navigate = useNavigate();
  const [result, setResult] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchResultDetails();
  }, [resultId]);

  const fetchResultDetails = async () => {
    try {
      const token = localStorage.getItem('token');

      if (!token) {
        navigate('/login');
        return;
      }

      const response = await axios.get(
        `${process.env.REACT_APP_API_URL || 'http://localhost:8000/api'}/quiz/results/${resultId}/detailed`,
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        }
      );

      if (response.data.success) {
        setResult(response.data.result);
      } else {
        setError('Failed to load result details');
      }
    } catch (error) {
      console.error('Error fetching details:', error);
      if (error.response?.status === 401) {
        localStorage.removeItem('token');
        navigate('/login');
      } else {
        setError(error.response?.data?.message || 'Failed to load details');
      }
    } finally {
      setLoading(false);
    }
  };

  const formatTime = (seconds) => {
    if (!seconds) return 'N/A';
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    if (minutes > 0) {
      return `${minutes}m ${remainingSeconds}s`;
    }
    return `${remainingSeconds}s`;
  };

  const getGradeColor = (percentage) => {
    if (percentage >= 80) return '#22c55e'; // Green
    if (percentage >= 60) return '#eab308'; // Yellow
    return '#ef4444'; // Red
  };

  if (loading) {
    return (
      <div className="loading-container">
        <div className="spinner"></div>
        <p>Loading result details...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="error-container">
        <div className="error-message">
          <h2>Error</h2>
          <p>{error}</p>
          <button onClick={() => navigate(-1)} className="btn-back">
            Go Back
          </button>
        </div>
      </div>
    );
  }

  if (!result) {
    return (
      <div className="error-container">
        <p>No result data found</p>
        <button onClick={() => navigate(-1)} className="btn-back">
          Go Back
        </button>
      </div>
    );
  }

  const correctCount = result.questions.filter(q => q.is_correct).length;
  const incorrectCount = result.total_questions - correctCount;

  return (
    <div className="quiz-detail-page">
      <div className="quiz-detail-container">
        {/* Back Button */}
        <button onClick={() => navigate(-1)} className="btn-back-top">
          ← Back to Results
        </button>

        {/* Header Summary Card */}
        <div className="summary-card">
          <div className="summary-header">
            <div>
              <h1 className="quiz-title">{result.quiz_title}</h1>
              <p className="chapter-name">{result.chapter_name}</p>
              <p className="attempt-info">Attempt #{result.attempt_number}</p>
            </div>
            <div className={`status-badge ${result.passed ? 'passed' : 'failed'}`}>
              {result.passed ? '✅ PASSED' : '❌ FAILED'}
            </div>
          </div>

          <div className="stats-grid">
            <div className="stat-card">
              <div className="stat-icon" style={{ backgroundColor: getGradeColor(result.percentage) }}>
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2">
                  <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                </svg>
              </div>
              <div className="stat-content">
                <div className="stat-value">{result.percentage}%</div>
                <div className="stat-label">Score</div>
              </div>
            </div>

            <div className="stat-card">
              <div className="stat-icon" style={{ backgroundColor: '#22c55e' }}>
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2">
                  <path d="M20 6L9 17L4 12"/>
                </svg>
              </div>
              <div className="stat-content">
                <div className="stat-value">{correctCount}/{result.total_questions}</div>
                <div className="stat-label">Correct Answers</div>
              </div>
            </div>

            <div className="stat-card">
              <div className="stat-icon" style={{ backgroundColor: '#ef4444' }}>
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </div>
              <div className="stat-content">
                <div className="stat-value">{incorrectCount}</div>
                <div className="stat-label">Wrong Answers</div>
              </div>
            </div>

            <div className="stat-card">
              <div className="stat-icon" style={{ backgroundColor: '#8b5cf6' }}>
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M12 6v6l4 2"/>
                </svg>
              </div>
              <div className="stat-content">
                <div className="stat-value">{formatTime(result.time_taken)}</div>
                <div className="stat-label">Time Taken</div>
              </div>
            </div>
          </div>

          <div className="passing-score-info">
            Passing Score: {result.passing_score}%
          </div>
        </div>

        {/* Questions Review Section */}
        <div className="questions-review">
          <div className="review-header">
            <h2>Question Review</h2>
            <div className="review-stats">
              <span className="correct-badge">✓ {correctCount} Correct</span>
              <span className="incorrect-badge">✗ {incorrectCount} Wrong</span>
            </div>
          </div>

          <div className="questions-list">
            {result.questions.map((q, index) => (
              <div
                key={index}
                className={`question-card ${q.is_correct ? 'correct' : 'incorrect'}`}
              >
                <div className="question-header">
                  <div className="question-number">
                    Question {q.question_number}
                  </div>
                  <div className={`status-badge-small ${q.is_correct ? 'correct' : 'incorrect'}`}>
                    {q.is_correct ? '✅ Correct' : '❌ Wrong'}
                  </div>
                </div>

                <p className="question-text">{q.question}</p>

                <div className="options-list">
                  {q.options.map((option, optIndex) => {
                    const isCorrect = optIndex === q.correct_answer;
                    const isUserAnswer = optIndex === q.user_answer;

                    return (
                      <div
                        key={optIndex}
                        className={`option ${isCorrect ? 'correct-option' : ''} ${isUserAnswer && !isCorrect ? 'wrong-option' : ''} ${isUserAnswer && isCorrect ? 'user-correct' : ''}`}
                      >
                        <div className="option-content">
                          <span className="option-letter">
                            {String.fromCharCode(65 + optIndex)}.
                          </span>
                          <span className="option-text">{option}</span>
                        </div>
                        <div className="option-indicators">
                          {isUserAnswer && (
                            <span className="indicator user-indicator">
                              Your Answer
                            </span>
                          )}
                          {isCorrect && (
                            <span className="indicator correct-indicator">
                              ✓ Correct
                            </span>
                          )}
                        </div>
                      </div>
                    );
                  })}
                </div>

                <div className="answer-summary">
                  <div className="answer-row">
                    <strong>Your Answer:</strong>
                    <span className={q.is_correct ? 'text-success' : 'text-error'}>
                      {q.user_answer_text}
                    </span>
                  </div>
                  {!q.is_correct && (
                    <div className="answer-row">
                      <strong>Correct Answer:</strong>
                      <span className="text-success">
                        {q.correct_answer_text}
                      </span>
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Bottom Actions */}
        <div className="bottom-actions">
          <button onClick={() => navigate(-1)} className="btn-back">
            ← Back to Results
          </button>
          <button onClick={() => navigate('/dashboard')} className="btn-dashboard">
            Go to Dashboard
          </button>
        </div>
      </div>
    </div>
  );
};

export default QuizResultDetail;
