import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';
import HeaderApp from '../HeaderApp/HeaderApp';
import {
  FaFilePdf,
  FaVideo,
  FaFileAlt,
  FaEye
} from 'react-icons/fa';

const SubjectDocumentsPage = () => {
  const { subjectId } = useParams();
  const [documents, setDocuments] = useState([]);
  const [subjectName, setSubjectName] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const API_URL = import.meta.env.VITE_API_BASE_URL;
  const token = localStorage.getItem('token');

  // Icon selector
  const getDocIcon = (docType) => {
    const type = docType?.toLowerCase() || '';
    if (type.includes('pdf')) return <FaFilePdf className="text-red-500" />;
    if (type.includes('video') || type.includes('mp4')) return <FaVideo className="text-blue-500" />;
    return <FaFileAlt className="text-gray-500" />;
  };

  useEffect(() => {
    const fetchDocuments = async () => {
      setLoading(true);
      try {
        const config = {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
          }
        };

        const [lecturesRes, summariesRes] = await Promise.all([
          axios.get(`${API_URL}/student/subjects/${subjectId}/lectures`, config),
          axios.get(`${API_URL}/student/subjects/${subjectId}/summaries`, config)
        ]);

        setSubjectName(
          lecturesRes.data.subject_name ||
          summariesRes.data.subject_name ||
          'Subject'
        );

        const allDocs = [
          ...(lecturesRes.data.lectures || []),
          ...(summariesRes.data.summaries || [])
        ];

        // Sort by uploaded date (newest first)
        allDocs.sort((a, b) => new Date(b.uploaded_at) - new Date(a.uploaded_at));
        setDocuments(allDocs);
      } catch (err) {
        console.error("Error fetching subject documents:", err);
        setError(err.response?.data?.message || 'Failed to load documents.');
      } finally {
        setLoading(false);
      }
    };

    if (subjectId && token) {
      fetchDocuments();
    }
  }, [subjectId, API_URL, token]);

  const handleShowDocument = (url) => {
    if (url?.startsWith('http://') || url?.startsWith('https://')) {
      window.open(url, '_blank');
    } else {
      // fallback in case relative URL
      const fullUrl = `https://aspu-hub.com${url.startsWith('/') ? '' : '/'}${url}`;
      window.open(fullUrl, '_blank');
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-screen bg-gray-100">
        <div className="animate-spin rounded-full h-20 w-20 border-t-4 border-blue-500 border-solid"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-4">
        <HeaderApp />
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
          <strong className="font-bold">Error: </strong>
          <span className="ml-2">{error}</span>
          <Link to="/entire-subjects" className="ml-4 text-blue-600 underline">
            Back to Subjects
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="flex flex-col items-center min-h-screen bg-gray-100 pt-24 px-4">
      <HeaderApp />
      <div className="w-full max-w-4xl bg-white p-6 rounded-lg shadow-xl mt-8">
        <h1 className="text-4xl font-extrabold text-gray-900 mb-6 text-center">
          Documents for: <span className="text-indigo-600">{subjectName}</span>
        </h1>
        <Link
          to="/entire-subjects"
          className="inline-flex items-center px-4 py-2 mb-6 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100"
        >
          ← Back to Subjects
        </Link>

        {documents.length === 0 ? (
          <div className="text-center text-gray-600 p-6 bg-gray-50 rounded-lg shadow-inner">
            No lectures or summaries available for this subject yet.
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
            {documents.map((doc) => (
              <div
                key={doc.id}
                className="border border-gray-200 rounded-lg p-4 shadow hover:shadow-lg transition-transform hover:-translate-y-1"
              >
                <div className="flex items-start mb-4">
                  <div className="text-3xl mr-4">{getDocIcon(doc.type.name)}</div>
                  <div>
                    <h2 className="text-lg font-semibold text-gray-800 break-words">{doc.title}</h2>
                    <p className="text-sm text-gray-500">
                      Type: <span className="font-medium">{doc.type.name}</span>
                    </p>
                    {doc.type.description && (
                      <p className="text-sm text-gray-600 mt-1 line-clamp-2">{doc.type.description}</p>
                    )}
                    <p className="text-xs text-gray-400 mt-1">
                      Uploaded: {new Date(doc.uploaded_at).toLocaleDateString()}
                    </p>
                  </div>
                </div>

                <button
                  onClick={() => handleShowDocument(doc.url)}
                  className="flex items-center justify-center w-full px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition"
                >
                  <FaEye className="mr-2" />
                  Show Document
                </button>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default SubjectDocumentsPage;
