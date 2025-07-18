import React, { useState, useEffect } from 'react';
import { FaFileAlt, FaCheck, FaDownload } from 'react-icons/fa';
import pdfMake from 'pdfmake/build/pdfmake';
import { vfs } from 'pdfmake/build/vfs_fonts';
import { Link } from 'react-router-dom';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_BASE_URL;
const GenerateBaseUrl = import.meta.env.VITE_Generate_BASE_URL;
pdfMake.vfs = vfs;

const SubjectCard = ({ subject, onGenerateQuiz }) => (
    <div className="relative bg-gradient-to-br from-indigo-600 to-blue-500 text-white p-8 rounded-xl shadow-lg flex flex-col items-center text-center transform hover:scale-105 transition-all duration-300 ease-in-out cursor-pointer border border-transparent hover:border-white">
        <div className="relative inline-block p-5 bg-white bg-opacity-20 rounded-full mb-4 shadow-inner">
            <FaFileAlt className="text-5xl text-white" />
        </div>
        <div className="text-xl font-extrabold my-2 tracking-wide">{subject.name}</div>
        <div className="opacity-90 text-sm">{subject.hours} Hours</div>
        <div className="flex flex-wrap justify-center gap-3 w-full mt-6">
            <button
                onClick={() => onGenerateQuiz(subject)}
                className="bg-white text-indigo-700 font-semibold px-5 py-2 rounded-lg hover:bg-gray-100 transition-colors duration-200 shadow-md focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 text-sm"
            >
                Generate Quiz
            </button>
            <Link
                to={`/subjects/${subject.id}/lectures`}
                className="bg-white text-indigo-700 font-semibold px-5 py-2 rounded-lg hover:bg-gray-100 transition-colors duration-200 shadow-md focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 text-sm"
            >
                View Lectures
            </Link>
            <Link
                to={`/subjects/${subject.id}/summaries`}
                className="bg-white text-indigo-700 font-semibold px-5 py-2 rounded-lg hover:bg-gray-100 transition-colors duration-200 shadow-md focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 text-sm"
            >
                Summaries
            </Link>
        </div>
    </div>
);

export default function EntireSubjects() {
    const [subjects, setSubjects] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [quizData, setQuizData] = useState({
        loading: false,
        content: '',
        subjectName: ''
    });

    useEffect(() => {
        const fetchCurrentSemesterSubjects = async () => {
            try {
                const response = await axios.get(`${API_URL}/student/user/subjects/current-semester`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Accept': 'application/json'
                    }
                });

                let subjectsData = response.data;

                if (response.data && Array.isArray(response.data.data)) {
                    subjectsData = response.data.data;
                } else if (Array.isArray(response.data)) {
                    subjectsData = response.data;
                } else if (response.data && Array.isArray(response.data.subjects)) {
                    subjectsData = response.data.subjects;
                } else {
                    throw new Error('Unexpected API response format');
                }

                const formattedSubjects = subjectsData.map(subject => ({
                    id: subject.id,
                    name: subject.name,
                    hours: subject.hour_count || subject.hours || 0,
                    registered_at: subject.registered_at
                }));

                setSubjects(formattedSubjects);
            } catch (err) {
                setError(err.message);
                console.error('Error fetching subjects:', err);
            } finally {
                setLoading(false);
            }
        };

        fetchCurrentSemesterSubjects();
    }, []);

    const generateQuiz = async (subject) => {
        setQuizData({
            loading: true,
            content: '',
            subjectName: subject.name
        });

        try {
            const res = await fetch(`${GenerateBaseUrl}/generate-questions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify({ subject: subject.name })
            });

            if (!res.ok) throw new Error(await res.text());

            const data = await res.json();
            setQuizData(prev => ({
                ...prev,
                content: data.questions || data.content || data,
                loading: false
            }));
        } catch (err) {
            console.error('Error generating quiz:', err);
            setQuizData(prev => ({
                ...prev,
                content: 'Error generating quiz. Please try again.',
                loading: false
            }));
        }
    };

    const downloadQuizPDF = () => {
        const docDefinition = {
            content: [
                { text: `Quiz for ${quizData.subjectName}`, style: 'header' },
                { text: '\n' },
                {
                    text: quizData.content,
                    style: 'content',
                    lineHeight: 1.5
                }
            ],
            styles: {
                header: {
                    fontSize: 18,
                    bold: true,
                    alignment: 'center',
                    margin: [0, 0, 0, 10]
                },
                content: {
                    fontSize: 14
                }
            }
        };

        pdfMake.createPdf(docDefinition).download(`${quizData.subjectName.replace(/\s+/g, '_')}_quiz.pdf`);
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center min-h-screen bg-gray-50">
                <div className="animate-spin rounded-full h-24 w-24 border-t-4 border-b-4 border-indigo-500"></div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex justify-center items-center min-h-screen bg-red-50">
                <div className="text-red-600 text-lg p-6 bg-white rounded-lg shadow-md border border-red-300">
                    Error: {error}
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gray-50 p-8 pt-24 flex flex-col items-center">
            <h1 className="text-4xl font-extrabold text-gray-800 mb-10 text-center leading-tight">
                All Current Subjects 📚
            </h1>

            {/* Quiz Display Section */}
            {quizData.content && (
                <div className="w-full max-w-5xl bg-white p-8 rounded-xl shadow-xl mb-12 border border-gray-200">
                    <div className="flex justify-between items-center mb-6 border-b pb-4">
                        <h2 className="text-2xl font-bold text-indigo-700">
                            Quiz for {quizData.subjectName}
                        </h2>
                        <button
                            onClick={downloadQuizPDF}
                            className="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg transition-colors duration-200 shadow-md text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50"
                        >
                            <FaDownload /> Download PDF
                        </button>
                    </div>
                    <div className="whitespace-pre-line text-gray-700 text-lg leading-relaxed">
                        {quizData.content}
                    </div>
                </div>
            )}

            {subjects.length === 0 ? (
                <p className="text-gray-600 text-lg mt-8 p-6 bg-white rounded-lg shadow-md border border-gray-200">
                    No subjects found for the current semester. Please check back later! 🤷‍♀️
                </p>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 w-full max-w-6xl">
                    {subjects.map((subject) => (
                        <SubjectCard
                            key={subject.id}
                            subject={subject}
                            onGenerateQuiz={generateQuiz}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
