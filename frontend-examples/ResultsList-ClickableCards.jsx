import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

/**
 * Example Results List Component with Clickable Cards
 *
 * This component demonstrates:
 * 1. Fetching all user quiz results
 * 2. Making result cards clickable
 * 3. Navigating to detail view
 */

const QuizResultsList = () => {
  const navigate = useNavigate();
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchResults();
  }, []);

  const fetchResults = async () => {
    try {
      const token = localStorage.getItem('token');

      if (!token) {
        navigate('/login');
        return;
      }

      const response = await axios.get(
        `${process.env.REACT_APP_API_URL || 'http://localhost:8000/api'}/quiz/results`,
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        }
      );

      if (response.data.success) {
        setResults(response.data.results);
      }
    } catch (error) {
      console.error('Error fetching results:', error);
      setError('Failed to load quiz results');
    } finally {
      setLoading(false);
    }
  };

  // Navigate to detail view when card is clicked
  const handleCardClick = (resultId) => {
    navigate(`/quiz/results/${resultId}`);
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

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  if (loading) {
    return <div className="loading">Loading results...</div>;
  }

  if (error) {
    return <div className="error">{error}</div>;
  }

  if (results.length === 0) {
    return (
      <div className="no-results">
        <p>No quiz results yet. Take a quiz to see your results here!</p>
        <button onClick={() => navigate('/quizzes')} className="btn-primary">
          Browse Quizzes
        </button>
      </div>
    );
  }

  return (
    <div className="results-page">
      <div className="results-header">
        <h1>My Quiz Results</h1>
        <p className="subtitle">Click on any result to view detailed answers</p>
      </div>

      <div className="results-grid">
        {results.map((result) => {
          const passed = result.percentage >= (result.quiz?.passing_score || 70);

          return (
            <div
              key={result.id}
              className={`result-card ${passed ? 'passed' : 'failed'}`}
              onClick={() => handleCardClick(result.id)}
              style={{ cursor: 'pointer' }}
            >
              {/* Status Badge */}
              <div className={`status-indicator ${passed ? 'passed' : 'failed'}`}>
                {passed ? '✅ Passed' : '❌ Failed'}
              </div>

              {/* Quiz Title */}
              <h3 className="quiz-title">
                {result.quiz?.title || 'Quiz'}
              </h3>

              {/* Chapter Info */}
              {result.quiz?.chapter && (
                <p className="chapter-info">
                  {result.quiz.chapter.title}
                </p>
              )}

              {/* Score Display */}
              <div className="score-display">
                <div className="score-circle" style={{
                  background: `conic-gradient(
                    ${passed ? '#22c55e' : '#ef4444'} ${result.percentage * 3.6}deg,
                    #e5e7eb 0deg
                  )`
                }}>
                  <div className="score-inner">
                    {result.percentage}%
                  </div>
                </div>
              </div>

              {/* Stats Grid */}
              <div className="result-stats">
                <div className="stat-item">
                  <span className="stat-label">Score</span>
                  <span className="stat-value">
                    {result.score}/{result.total_questions}
                  </span>
                </div>
                <div className="stat-item">
                  <span className="stat-label">Time</span>
                  <span className="stat-value">
                    {formatTime(result.time_taken)}
                  </span>
                </div>
                <div className="stat-item">
                  <span className="stat-label">Attempt</span>
                  <span className="stat-value">
                    #{result.attempt_number}
                  </span>
                </div>
              </div>

              {/* Date */}
              <div className="result-date">
                {formatDate(result.created_at)}
              </div>

              {/* View Details Button */}
              <button
                className="btn-view-details"
                onClick={(e) => {
                  e.stopPropagation(); // Prevent card click when button is clicked
                  handleCardClick(result.id);
                }}
              >
                View Details →
              </button>

              {/* Questions Data Indicator */}
              {result.questions_data && result.questions_data.length > 0 && (
                <div className="has-details-badge">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                  </svg>
                  Detailed review available
                </div>
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default QuizResultsList;

/**
 * CSS Styles (add to ResultsList.css):
 *
 * .results-page {
 *   padding: 20px;
 *   max-width: 1200px;
 *   margin: 0 auto;
 * }
 *
 * .results-header {
 *   margin-bottom: 30px;
 *   text-align: center;
 * }
 *
 * .results-header h1 {
 *   font-size: 32px;
 *   margin-bottom: 8px;
 * }
 *
 * .subtitle {
 *   color: #6b7280;
 * }
 *
 * .results-grid {
 *   display: grid;
 *   grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
 *   gap: 24px;
 * }
 *
 * .result-card {
 *   background: white;
 *   border-radius: 16px;
 *   padding: 24px;
 *   box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
 *   transition: all 0.3s ease;
 *   position: relative;
 *   border: 2px solid transparent;
 * }
 *
 * .result-card:hover {
 *   transform: translateY(-4px);
 *   box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
 *   border-color: #667eea;
 * }
 *
 * .result-card.passed {
 *   border-left: 4px solid #22c55e;
 * }
 *
 * .result-card.failed {
 *   border-left: 4px solid #ef4444;
 * }
 *
 * .status-indicator {
 *   position: absolute;
 *   top: 16px;
 *   right: 16px;
 *   padding: 6px 12px;
 *   border-radius: 20px;
 *   font-size: 12px;
 *   font-weight: 600;
 * }
 *
 * .status-indicator.passed {
 *   background: #dcfce7;
 *   color: #16a34a;
 * }
 *
 * .status-indicator.failed {
 *   background: #fee2e2;
 *   color: #dc2626;
 * }
 *
 * .score-display {
 *   display: flex;
 *   justify-content: center;
 *   margin: 20px 0;
 * }
 *
 * .score-circle {
 *   width: 120px;
 *   height: 120px;
 *   border-radius: 50%;
 *   display: flex;
 *   align-items: center;
 *   justify-content: center;
 * }
 *
 * .score-inner {
 *   width: 100px;
 *   height: 100px;
 *   border-radius: 50%;
 *   background: white;
 *   display: flex;
 *   align-items: center;
 *   justify-content: center;
 *   font-size: 28px;
 *   font-weight: 700;
 * }
 *
 * .result-stats {
 *   display: grid;
 *   grid-template-columns: repeat(3, 1fr);
 *   gap: 16px;
 *   margin: 20px 0;
 * }
 *
 * .stat-item {
 *   text-align: center;
 * }
 *
 * .stat-label {
 *   display: block;
 *   font-size: 12px;
 *   color: #6b7280;
 *   margin-bottom: 4px;
 * }
 *
 * .stat-value {
 *   display: block;
 *   font-size: 18px;
 *   font-weight: 600;
 *   color: #1f2937;
 * }
 *
 * .btn-view-details {
 *   width: 100%;
 *   padding: 12px;
 *   background: #667eea;
 *   color: white;
 *   border: none;
 *   border-radius: 8px;
 *   font-weight: 600;
 *   cursor: pointer;
 *   transition: background 0.3s ease;
 * }
 *
 * .btn-view-details:hover {
 *   background: #5568d3;
 * }
 *
 * .has-details-badge {
 *   display: flex;
 *   align-items: center;
 *   gap: 6px;
 *   font-size: 12px;
 *   color: #059669;
 *   margin-top: 12px;
 *   justify-content: center;
 * }
 */
