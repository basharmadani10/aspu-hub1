import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import GradientText from '../gradient/GradientText'; // Assuming this component exists

export default function CheckSubjects() {
  const [subjects, setSubjects] = useState([]);
  const [selectedSubjects, setSelectedSubjects] = useState({}); // Stores { subjectId: boolean }
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState('');

  const navigate = useNavigate();

  const token = localStorage.getItem('token');
  const API_URL = import.meta.env.VITE_API_BASE_URL; // Corrected env variable name

  useEffect(() => {
    // Check if token exists before making API call
    if (!token) {
        setError('Authentication token not found. Please log in.');
        setLoading(false);
        return;
    }

    axios
      .get(`${API_URL}/student/subjects/all-for-setup`, {
        headers: { Authorization: `Bearer ${token}` }
      })
      .then((response) => {
        const data = response.data;
        let subjectsArray = [];
        if (Array.isArray(data)) {
          subjectsArray = data;
        } else if (data.subjects && Array.isArray(data.subjects)) {
          subjectsArray = data.subjects;
        } else {
          console.error("Unexpected response structure:", data);
          setError("Unexpected data format from server. Please try again.");
          setLoading(false);
          return;
        }
        setSubjects(subjectsArray);
        setLoading(false);
      })
      .catch((err) => {
        console.error('Error fetching subjects:', err);
        // Improved error handling
        if (err.response && err.response.data && err.response.data.message) {
            setError(err.response.data.message);
        } else if (err.message) {
            setError(err.message);
        } else {
            setError('Failed to fetch subjects. Please check your network connection.');
        }
        setLoading(false);
      });
  }, [token, API_URL]); // Add API_URL to dependency array

  const handleCheckboxChange = (subjectId) => {
    setSelectedSubjects((prevSelected) => ({
      ...prevSelected,
      [subjectId]: !prevSelected[subjectId],
    }));
  };

  const handleSubmit = () => {
    const selectedSubjectIds = Object.keys(selectedSubjects).filter(
      (subjectId) => selectedSubjects[subjectId]
    );

 

    // --- FIX FOR TOTAL HOURS CALCULATION ---
    // Map selected IDs back to full subject objects to get their hours
    const selectedSubjectsFullData = subjects.filter(subject =>
        selectedSubjectIds.includes(String(subject.id)) // Ensure comparison is type-safe
    );

    const totalHours = selectedSubjectsFullData.reduce(
        (sum, subject) => sum + (subject.hour_count || subject.hours || 0), // Use hour_count or hours
        0
    );
    // --- END FIX ---

    // Update cumulative hours in local storage (client-side only for display)
    // Note: The backend recalculates cumulative hours, so this is mainly for immediate UI feedback.
    localStorage.setItem('cumulativeHours', totalHours.toString());
    window.dispatchEvent(new Event("localStorageUpdated"));

    axios
      .post(
        `${API_URL}/student/subjects/submit-initial`,
        { subjects: selectedSubjectIds }, // Payload is correct: array of IDs
        { headers: { Authorization: `Bearer ${token}` } }
      )
      .then((response) => {
        setSuccessMessage('Subjects selected successfully! Redirecting...');
        // Navigate to /Home after displaying the success message for a short while.
        setTimeout(() => {
          navigate('/Home');
        }, 1500);
      })
      .catch((err) => {
        console.error('Error submitting subjects:', err);
        // --- Improved Error Handling for Frontend ---
        if (err.response && err.response.data && err.response.data.errors) {
          // Validation errors from Laravel (status 422)
          const validationErrors = Object.values(err.response.data.errors).flat().join(', ');
          setError(`Validation failed: ${validationErrors}`);
        } else if (err.response && err.response.data && err.response.data.message) {
          // Other backend messages (e.g., 403, 500)
          setError(err.response.data.message);
        } else {
          setError('Failed to submit subjects. Please check your network connection or server logs.');
        }
        // --- End Improved Error Handling ---
      });
  };

  return (
    <div className="max-w-xl mx-auto p-4"
        style={{marginLeft:"400px" , textAlign:'center', marginTop:"100px" , height:"min-content"} }
    >
        <GradientText
            colors={["#4f46e5", "#60a5fa", "#60a5fa", "#60a5fa", "#60a5fa"]}
            animationSpeed={3}
            showBorder={false}
            className="custom-class"
        >
            <h1 style={{marginBottom:'40px' ,marginLeft:"185px"}}>University Subjects</h1>
        </GradientText>

        {loading && <p className="text-gray-600">Loading subjects...</p>}
        {error && <p className="text-red-500">{error}</p>}
        {successMessage && <p className="text-green-500 mb-4">{successMessage}</p>}

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 ml-24">
            {subjects.map((subject) => {
                const isSelected = !!selectedSubjects[subject.id];
                return (
                    <div
                        key={subject.id}
                        onClick={() => handleCheckboxChange(subject.id)}
                        className={`cursor-pointer rounded-lg p-4 shadow-md border transition duration-300 ${
                            isSelected
                                ? 'bg-gradient-to-r from-blue-600 to-blue-400 text-white border-blue-600 scale-105'
                                : 'bg-white text-gray-800 hover:scale-[1.02] border-gray-300'
                        }`}
                        style={{ width: '250px' }}
                    >
                        <h3 className="font-semibold text-lg text-center">{subject.name}</h3>
                    </div>
                );
            })}
        </div>

        {subjects.length > 0 && (
            <button
                onClick={handleSubmit}
                className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                style={{marginTop:'25px',padding:"10px",marginLeft:"30px" , background:'linear-gradient(to top right, #4f46e5, #60a5fa)',
                    cursor:"pointer"
                }}
            >
                Submit Selection
            </button>
        )}
    </div>
  );
              }
