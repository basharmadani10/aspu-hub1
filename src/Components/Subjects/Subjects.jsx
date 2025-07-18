// src/components/Subjects/Subjects.jsx
import React, { useState, useEffect } from 'react';
import { FaFileAlt, FaCheck } from 'react-icons/fa';
import pdfMake from 'pdfmake/build/pdfmake'; // Assuming you use pdfMake for something here
import { vfs } from 'pdfmake/build/vfs_fonts'; // Assuming you use vfs_fonts here
import { Link } from 'react-router-dom'; // Crucial import for navigation

// Initialize pdfMake vfs, keep this if relevant to your app
pdfMake.vfs = vfs;

// Assuming these environment variables are correctly configured for your frontend build
const API_URL = import.meta.env.VITE_API_BASE_URL; // Using VITE_API_BASE_URL for clarity
const GenerateBaseUrl = import.meta.env.VITE_Generate_BASE_URL; // Assuming this is used elsewhere

// SubjectCard Component: Displays a single subject card
const SubjectCard = ({ subject }) => (
  <div
    className="relative bg-gradient-to-tr from-[#4f46e5] to-[#60a5fa] text-white p-7 rounded-[10px] shadow-md flex flex-col items-center"
    style={{ padding: '20px', marginTop: '25px' }}
  >
    <div className="relative inline-block" style={{ padding: '22px', height: '110px' }}>
      <FaFileAlt className="text-[40px] text-white mt-6" style={{ marginTop: '18px' }} />
      {/* Display FaCheck if the subject is completed, which it will always be in this view */}
      <FaCheck
        className="absolute bottom-25 right-25 text-[30px] text-green-300"
      />
    </div>

    <div className="text-lg font-bold my-2">{subject.name}</div>
    {/* Assuming subject.hour_count or subject.hours exists, use hour_count as it's more specific from backend */}
    <div className="opacity-90">{subject.hour_count || subject.hours || 0} Hours</div>
    <div className="flex justify-between w-full text-white opacity-90 mt-4">
      {/* Link for Lectures */}
      <Link to={`/subjects/${subject.id}/lectures`} className="hover:opacity-80">
        View Lectures
      </Link>

      {/* Link for Summaries */}
      <Link to={`/subjects/${subject.id}/summaries`} className="hover:opacity-80">
        Summaries
      </Link>
    </div>
  </div>
);

// Main Subjects Component: Fetches and displays all subject cards
export default function Subjects() {
  const [subjects, setSubjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchCompletedSubjects = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem('token');
        if (!token) {
          throw new Error('Authentication required. Please log in.');
        }

        // --- KEY CHANGE: Fetch from /subjects/completed-subject ---
        const response = await fetch(`${API_URL}/student/subjects/completed-subject`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
        });

        if (!response.ok) {
          const errorData = await response.json().catch(() => ({}));
          throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        // --- Adjusting to new response structure: `completed_subjects` ---
        setSubjects(data.completed_subjects || []);

      } catch (err) {
        setError(err.message);
        console.error("Failed to fetch completed subjects:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchCompletedSubjects(); // Call the new function
  }, []); // Empty dependency array means this runs once on mount

  if (loading) {
    return <div className="flex justify-center items-center min-h-screen bg-gray-100"><p className="text-gray-600">Loading subjects...</p></div>;
  }

  if (error) {
    return <div className="flex justify-center items-center min-h-screen bg-gray-100"><p className="text-red-600">Error: {error}</p></div>;
  }

  return (
    <div className="min-h-screen bg-gray-100 p-6">
      <h1 className="text-3xl font-bold text-center mb-8">All Completed Subjects ✅</h1>
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 justify-items-center">
        {subjects.length > 0 ? (
          subjects.map(subject => (
            <SubjectCard key={subject.id} subject={subject} />
          ))
        ) : (
          <p className="col-span-full text-center text-gray-600">No completed subjects found.</p>
        )}
      </div>
    </div>
  );
}
