import React, { useState, useEffect } from "react";
import axios from 'axios'; // Use axios for consistency
import { useNavigate, Link } from 'react-router-dom'; // Import Link for navigation
import GradientText from '../gradient/GradientText'; // Assuming this component exists and works

export default function NewSubjects() {
  const [subjects, setSubjects] = useState([]);
  const [selectedSubjects, setSelectedSubjects] = useState([]); // Array of full subject objects
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [isRegistrationAllowed, setIsRegistrationAllowed] = useState(false);
  const [registrationMessage, setRegistrationMessage] = useState("");

  const navigate = useNavigate();
  const token = localStorage.getItem("token");
  const API_URL = import.meta.env.VITE_API_BASE_URL; // Corrected env variable name

  // Fetch registration status and available subjects.
  useEffect(() => {
    const checkStatusAndFetchSubjects = async () => {
      try {
        setLoading(true);
        setError(null);

        // First, check the registration status
        const statusResponse = await axios.get(
          `${API_URL}/student/subjects/registration-status`,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        setIsRegistrationAllowed(statusResponse.data.is_registration_allowed);
        setRegistrationMessage(statusResponse.data.message);

        // If registration is allowed, then fetch the subjects
        if (statusResponse.data.is_registration_allowed) {
          const subjectsResponse = await axios.get(
            `${API_URL}/student/subjects/Unfinished-Subjects`,
            { headers: { Authorization: `Bearer ${token}` } }
          );

          if (subjectsResponse.data.subjects) {
            const formattedSubjects = subjectsResponse.data.subjects.map((subject) => ({
              ...subject,
              hours: subject.hour_count, // Ensure 'hours' property is set from 'hour_count'
            }));
            setSubjects(formattedSubjects);
          } else if (Array.isArray(subjectsResponse.data)) { // Fallback for direct array response
            const formattedSubjects = subjectsResponse.data.map((subject) => ({
              ...subject,
              hours: subject.hour_count,
            }));
            setSubjects(formattedSubjects);
          } else {
            console.error("Unexpected subjects data format:", subjectsResponse.data);
            throw new Error("Unexpected subjects data format from API");
          }
        }
      } catch (err) {
        console.error("Error during NewSubjects setup:", err);
        // Improved error display
        if (err.response && err.response.data && err.response.data.message) {
            setError(err.response.data.message);
        } else if (err.message) {
            setError(err.message);
        } else {
            setError("An unexpected error occurred.");
        }
        setIsRegistrationAllowed(false); // Assume not allowed if error occurs
        setRegistrationMessage(err.response?.data?.message || "Could not determine registration status.");
      } finally {
        setLoading(false);
      }
    };

    if (token) { // Only fetch if token exists
        checkStatusAndFetchSubjects();
    } else {
        setLoading(false);
        setError("Authentication token is missing. Please log in.");
        setRegistrationMessage("You must be logged in to register for subjects.");
    }
  }, [API_URL, token]);

  // Toggle the selection for a subject.
  const handleCheckboxChange = (subject) => { // 'subject' is the full object
    setSelectedSubjects((prevSelected) => {
      const isSelected = prevSelected.some((item) => item.id === subject.id);
      return isSelected
        ? prevSelected.filter((item) => item.id !== subject.id)
        : [...prevSelected, subject]; // Store the full subject object
    });
  };

  // On submit, update localStorage, call the registration API, and filter out the selected subjects.
  const handleSubmit = async () => {
    if (selectedSubjects.length === 0) {
      alert("Please select at least one subject to register.");
      return;
    }

    const totalHours = selectedSubjects.reduce(
      (sum, subject) => sum + (subject.hours || subject.hour_count || 0), // Use hours property
      0
    );

    try {
      const payload = {
        subjects: selectedSubjects.map((subject) => subject.id), // Send only IDs to backend
      };

      const response = await axios.post(
        `${API_URL}/student/subjects/register-for-semester`,
        payload,
        { headers: { Authorization: `Bearer ${token}` } }
      );

      // Frontend update (localStorage) should ideally happen AFTER backend success
      alert("Subjects for the current semester registered successfully.");

      localStorage.setItem("dummyUserSubjects", JSON.stringify(selectedSubjects));
      localStorage.setItem("dummyUserSubjectsTimestamp", Date.now().toString());
      localStorage.setItem("dummyUserSubjectsHours", totalHours.toString());

      const previousCumulative = localStorage.getItem("cumulativeHours") || "0";
      const newCumulative = parseInt(previousCumulative, 10) + totalHours;
      localStorage.setItem("cumulativeHours", newCumulative.toString());
      window.dispatchEvent(new Event("localStorageUpdated"));

      // Remove the registered subjects from the list so they don't show again.
      setSubjects((prevSubjects) =>
        prevSubjects.filter(
          (subject) =>
            !selectedSubjects.some(
              (selected) => selected.id === subject.id
            )
        )
      );
      // After successful registration, set registration status to false to prevent re-registration
      setIsRegistrationAllowed(false);
      setRegistrationMessage('You have successfully registered subjects for this semester.');
      setSelectedSubjects([]); // Clear selected subjects

    } catch (err) {
      console.error("Error registering subjects:", err);
      // Improved error display
      if (err.response && err.response.data && err.response.data.message) {
        alert(err.response.data.message);
        // If registration fails because "already registered", update frontend status
        if (err.response.status === 403 && err.response.data.message.includes('already registered')) {
            setIsRegistrationAllowed(false);
            setRegistrationMessage(err.response.data.message);
        }
      } else if (err.message) {
        alert("An error occurred: " + err.message);
      } else {
        alert("An unknown error occurred while registering subjects.");
      }
      setError(err.message || 'Registration failed'); // Update local error state as well
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <div className="animate-spin rounded-full h-32 w-32 border-t-2 border-b-2 border-indigo-500"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-4">
        <div className="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
          <p className="font-bold">Error!</p>
          <p>{error}</p>
          <Link to="/Home" className="text-red-700 underline mt-2 block hover:text-red-900">
            Go to Home
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10">
      <h2 className="text-2xl font-semibold text-gray-800 mb-4 text-center">Subject Registration</h2>

      {!isRegistrationAllowed && (
        <div className="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
          <p className="font-bold">Registration Status</p>
          <p>{registrationMessage}</p>
          <Link to="/Home" className="text-yellow-700 underline mt-2 block hover:text-yellow-900">
            Go to Home
          </Link>
        </div>
      )}

      {isRegistrationAllowed && subjects.length === 0 && (
          <div className="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
              <p className="font-bold">No Subjects Available</p>
              <p>There are no new subjects available for registration at this time. Please check back later or contact support.</p>
          </div>
      )}

      {isRegistrationAllowed && subjects.length > 0 && (
        <>
          <p className="text-gray-600 mb-4">Select the subjects you want to register for this semester (you can only do this once).</p>
          <div className="space-y-3 mb-6">
            {subjects.map((subject) => (
              <div
                key={subject.id}
                className="flex items-center p-3 border border-gray-200 rounded-md bg-gray-50 hover:bg-gray-100 transition-colors duration-150"
              >
                <input
                  type="checkbox"
                  id={`subject-${subject.id}`}
                  checked={selectedSubjects.some(item => item.id === subject.id)} // Check if subject object is in array
                  onChange={() => handleCheckboxChange(subject)} // Pass full subject object
                  className="form-checkbox h-5 w-5 text-indigo-600 transition duration-150 ease-in-out"
                />
                <label htmlFor={`subject-${subject.id}`} className="ml-3 text-gray-700 font-medium flex-grow">
                  {subject.name} ({subject.hours} hours)
                </label>
              </div>
            ))}
          </div>
          <button
            onClick={handleSubmit}
            disabled={selectedSubjects.length === 0}
            className={`w-full py-3 px-4 rounded-md font-semibold text-white transition-all duration-200 ${
              selectedSubjects.length === 0
                ? "bg-gray-400 cursor-not-allowed"
                : "bg-indigo-600 hover:bg-indigo-700 shadow-md"
            }`}
          >
            Register Selected Subjects
          </button>
        </>
      )}
    </div>
  );
}