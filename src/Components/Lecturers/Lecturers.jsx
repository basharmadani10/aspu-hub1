// src/components/Lecturers.jsx
import React, { useState, useEffect } from 'react';
import {
  FiSearch,
  FiBookOpen, // Default icon for documents
  FiArrowLeft,
  FiSend
} from 'react-icons/fi';
// Import additional icons for different document types
import { FaFilePdf, FaVideo, FaFileAlt } from 'react-icons/fa'; // FaFilePdf for PDF, FaVideo for video, FaFileAlt for summaries

import { useParams, useLocation } from 'react-router-dom'; // Import useLocation

export default function Lecturers() {
  const { subjectId } = useParams();
  const location = useLocation(); // Get current location object
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedDocument, setSelectedDocument] = useState(null); // State to hold the currently selected document for viewing
  const [question, setQuestion] = useState('');
  const [answer, setAnswer] = useState('Welcome! Ask me anything about this document.'); // General welcome message for AI assistant
  const [isLoading, setIsLoading] = useState(false); // Loading state for AI assistant
  const [sessionId] = useState(`student${Math.floor(Math.random() * 1000)}`); // Unique session ID for AI assistant
  const [documentsData, setDocumentsData] = useState([]); // State to hold fetched lectures or summaries
  const [loadingDocuments, setLoadingDocuments] = useState(true); // Loading state for fetching documents
  const [error, setError] = useState(null); // Error state for fetching documents

  // Environment variables for API URLs
  const API_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';
  const Assistant_URL = import.meta.env.VITE_Assistant_BASE_URL; // Ensure this env var name is correct
 
  // Determine if the current page is for lectures or summaries based on the URL path
  const isLecturesPage = location.pathname.includes('/lectures');
  // Set the page title and search placeholder dynamically
  const pageTitle = isLecturesPage ? 'Lectures' : 'Summaries';

  // Function to select the appropriate icon based on document type or URL extension
  const getDocIcon = (doc) => {
    const typeName = doc.type?.name?.toLowerCase() || '';
    const url = doc.url?.toLowerCase() || '';

    if (typeName.includes('pdf') || url.includes('.pdf')) return <FaFilePdf className="text-red-500" />;
    if (typeName.includes('video') || url.includes('.mp4') || url.includes('.mov') || url.includes('.webm')) return <FaVideo className="text-blue-500" />;
    if (typeName.includes('summary')) return <FaFileAlt className="text-green-500" />; // Specific icon for summaries
    // Add more conditions for other types (e.g., 'image', '.png', '.jpg')
    return <FiBookOpen className="text-gray-500" />; // Default icon if no match
  };

  // useEffect hook to fetch documents (lectures or summaries) when subjectId or page type changes
  useEffect(() => {
    const fetchDocuments = async () => {
      try {
        setLoadingDocuments(true);
        setError(null);

        const token = localStorage.getItem('token');
        if (!token) throw new Error('Authentication required');

        let endpoint;
        // Construct the API endpoint based on whether it's a lectures or summaries page
        if (isLecturesPage) {
          endpoint = `${API_URL}/student/subjects/${subjectId}/lectures`; // Fetch lectures
        } else {
          endpoint = `${API_URL}/student/subjects/${subjectId}/summaries`; // Fetch summaries
        }

        const response = await fetch(endpoint, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          const errorData = await response.json().catch(() => ({}));
          throw new Error(
            errorData.message || `HTTP error! status: ${response.status}`
          );
        }

        const data = await response.json();
        // Set documents data based on the API response structure (lectures or summaries array)
        setDocumentsData(Array.isArray(data) ? data : (isLecturesPage ? data.lectures : data.summaries));
      } catch (error) {
        setError(error.message);
        console.error('Fetch error:', error);
      } finally {
        setLoadingDocuments(false);
      }
    };

    if (subjectId) {
      fetchDocuments();
    }
  }, [subjectId, API_URL, isLecturesPage]); // Re-run effect if subjectId, API_URL, or page type changes

  // Filter documents based on search term
  const filtered = documentsData.filter(doc =>
    doc.title.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Function to send a question to the AI assistant
  const askQuestion = async () => {
    if (!question.trim()) return; // Don't send empty questions

    setIsLoading(true);
    setAnswer('...'); // Show a loading indicator

    try {
      const response = await fetch(`${Assistant_URL}/ask`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          question: question,
          session_id: sessionId
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      setAnswer(data.answer); // Set the AI assistant's answer
    } catch (error) {
      console.error('Error fetching answer:', error);
      setAnswer(`Sorry, there was an error: ${error.message}. Please make sure the backend is running.`);
    } finally {
      setIsLoading(false);
      setQuestion(''); // Clear the question input
    }
  };

  // Handle Enter key press for asking questions
  const handleKeyPress = (e) => {
    if (e.key === 'Enter') {
      askQuestion();
    }
  };

  // Conditional rendering: If a document is selected, show the split-view (document viewer + AI assistant)
  if (selectedDocument) {
    return (
      <div className="flex min-h-screen">
        {/* Document pane: Displays the selected lecture/summary */}
        <div className="w-1/2 bg-white p-4 flex flex-col">
          <div className="flex items-center justify-between mb-4">
            <button
              onClick={() => setSelectedDocument(null)} // Button to go back to the list view
              className="flex items-center text-blue-500 hover:text-blue-700 transition-colors"
            >
              <FiArrowLeft className="mr-2" /> Back
            </button>
            {/* Document title displayed at the top */}
            <h1 className="text-2xl font-semibold text-gray-900 bg-gradient-to-tr from-[#4f46e5] to-[#60a5fa] bg-clip-text text-transparent ml-4">
              {selectedDocument.type?.name || 'Document'} {selectedDocument.id}: {selectedDocument.title}
            </h1>
          </div>

          {/* Container for the document viewer (PDF/Video) */}
          {selectedDocument.url && (
              <div className="flex-1 border rounded-lg overflow-hidden relative" style={{ minHeight: '300px' }}>
                  {/* Conditional rendering based on document URL extension */}
                  {selectedDocument.url.toLowerCase().includes('.pdf') ? (
                      <iframe
                          src={selectedDocument.url}
                          title={selectedDocument.title}
                          width="100%"
                          height="100%"
                          style={{ border: 'none', position: 'absolute', top: 0, left: 0 }} // Styles to fill parent div
                      >
                          This browser does not support PDFs. Please download the PDF to view it: <a href={selectedDocument.url}>Download PDF</a>.
                      </iframe>
                  ) : selectedDocument.url.toLowerCase().includes('.mp4') || selectedDocument.url.toLowerCase().includes('.mov') || selectedDocument.url.toLowerCase().includes('.webm') ? (
                      <video controls width="100%" height="100%" style={{ position: 'absolute', top: 0, left: 0 }}>
                          <source src={selectedDocument.url} type="video/mp4" />
                          Your browser does not support the video tag.
                      </video>
                  ) : (
                      // Fallback for unsupported types or direct download link
                      <p className="p-4 text-red-500">
                          Unsupported document type or URL. Please check the file format.
                          <br/>
                          <a href={selectedDocument.url} target="_blank" rel="noopener noreferrer" className="text-blue-500 hover:underline">
                              Try to open directly
                          </a>
                      </p>
                  )}
              </div>
          )}
          {/* Removed the "Lorem ipsum dolor sit amet..." placeholder text */}
        </div>

        {/* AI Assistant pane */}
        <div className="w-1/2 bg-white p-6 overflow-auto flex flex-col">
          <h2 className="text-xl font-semibold mb-4 bg-gradient-to-tr from-[#4f46e5] to-[#60a5fa] bg-clip-text text-transparent">
            AI Assistant
          </h2>

          <div className="flex-1 border rounded p-4 mb-4 overflow-auto">
            <div className="text-gray-700 whitespace-pre-wrap">{answer}</div>
            {isLoading && <div className="text-gray-500 mt-2">Thinking...</div>}
          </div>

          <div className="flex items-center border rounded p-2">
            <input
              type="text"
              value={question}
              onChange={(e) => setQuestion(e.target.value)}
              onKeyPress={handleKeyPress}
              // Dynamic placeholder for AI assistant input
              placeholder={`Ask a question about this ${isLecturesPage ? 'lecture' : 'summary'}...`}
              className="flex-1 p-2 focus:outline-none"
              disabled={isLoading}
            />
            <button
              onClick={askQuestion}
              disabled={isLoading || !question.trim()}
              className="ml-2 p-2 text-blue-500 hover:text-blue-700 disabled:text-gray-400"
            >
              <FiSend size={20} />
            </button>
          </div>
        </div>
      </div>
    );
  }

  // Main grid view: Displays the list of lectures or summaries
  return (
    <div className="min-h-screen bg-gray-100 p-6">
      <div className="mb-4">
        <button
          onClick={() => window.history.back()}
          className="flex items-center text-blue-500 hover:text-blue-700 transition-colors"
        >
          <FiArrowLeft className="mr-1" size={20} />
          Back
        </button>
      </div>

      {/* Search input for filtering documents */}
      <div className="max-w-md mx-auto mb-8 p-3">
        <div className="flex items-center bg-white rounded-full shadow px-4 py-6">
          <FiSearch className="text-gray-400 mr-2" size={20} />
          <input
            type="text"
            // Dynamic placeholder for search input
            placeholder={`Search ${pageTitle.toLowerCase()}...`}
            className="w-full text-gray-700 placeholder-gray-400 bg-transparent focus:outline-none"
            value={searchTerm}
            onChange={e => setSearchTerm(e.target.value)}
          />
        </div>
      </div>

      {/* Document cards display area */}
      <div className="flex justify-center mt-20">
        {loadingDocuments ? (
          <p className="text-gray-600">Loading {pageTitle.toLowerCase()}...</p>
        ) : error ? (
          <p className="text-red-600">Error loading {pageTitle.toLowerCase()}: {error}</p>
        ) : documentsData.length === 0 ? (
          <p className="text-gray-600">No {pageTitle.toLowerCase()} found.</p>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
            {filtered.map(doc => (
              <div
                key={doc.id}
                onClick={() => setSelectedDocument(doc)} // Set the selected document on click
                className="
                  cursor-pointer
                  bg-gradient-to-tr from-[#4f46e5] to-[#60a5fa]
                  p-1 rounded-lg shadow
                  w-[250px] h-[150px]
                  transition hover:scale-105
                "
              >
                <div className="relative bg-gradient-to-tr from-[#4f46e5] to-[#60a5fa] text-white p-7 rounded-[10px] shadow-md flex flex-col items-center text-center" style={{ padding: "15px", marginTop: "20px" }}>
                  <div className="flex justify-center">
                    <div className="bg-blue-100 text-blue-500 p-3 rounded-full">
                      {getDocIcon(doc)} {/* Display dynamic icon based on document type */}
                    </div>
                  </div>
                  <h3 className="mt-2 font-medium text-white">
                    {doc.type?.name || 'Document'} {doc.id} {/* Display document type and ID */}
                  </h3>
                  <p className="text-white text-sm">{doc.title}</p>
                  <span className="mt-2 text-white font-medium hover:underline">
                    AI Assistant
                  </span>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
