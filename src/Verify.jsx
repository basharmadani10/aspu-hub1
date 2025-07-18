import { useState, useEffect } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";

const API_URL = import.meta.env.VITE_API_BASE_URL;

function Verify() {
  const navigate = useNavigate();
  const [otp, setOtp] = useState(["", "", "", ""]);
  const [timer, setTimer] = useState(600);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState("");
  const email = localStorage.getItem("email") || "";

  useEffect(() => {
    if (timer > 0) {
      const interval = setInterval(() => setTimer((prev) => prev - 1), 1000);
      return () => clearInterval(interval);
    }
  }, [timer]);

  const formatTime = (time) => {
    const minutes = Math.floor(time / 60);
    const seconds = time % 60;
    return `${minutes}:${seconds < 10 ? `0${seconds}` : seconds}`;
  };

  const handleChange = (index, value) => {
    if (!/^\d*$/.test(value)) return;
    let newOtp = [...otp];
    newOtp[index] = value;
    setOtp(newOtp);
    setError(""); // Clear error on new input

    if (value && index < 3) {
      document.getElementById(`otp-input-${index + 1}`)?.focus();
    }
  };

  const handleSubmit = async () => {
    setIsSubmitting(true);
    const code = otp.join("");

    if (code.length !== 4) {
      setError("Please enter the full 4-digit verification code.");
      setIsSubmitting(false);
      return;
    }

    try {
      const response = await axios.post(
        `${API_URL}/verify-email`,
        {
          email: email.trim(),
          code: code
        },
        {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        }
      );

      if (response.data.success) {
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        navigate("/Verify/Check");
      } else {
        setError(response.data.error || "Verification failed");
      }
    } catch (error) {
      console.error("Error:", error);
      setError(
        error.response?.data?.error || 
        "OTP verification failed. Please try again."
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="flex flex-col items-center justify-center h-screen bg-gray-100">
      <div className="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 className="text-2xl font-bold mb-4 text-center">Enter Verification Code</h2>
        <p className="text-gray-600 mb-6 text-center">
          We've sent a 4-digit code to <span className="font-semibold">{email}</span>
        </p>

        {/* OTP Input Fields */}
        <div className="flex justify-center gap-3 my-6">
          {otp.map((value, index) => (
            <input
              key={index}
              id={`otp-input-${index}`}
              type="text"
              inputMode="numeric"
              pattern="[0-9]*"
              maxLength={1}
              value={value}
              onChange={(e) => handleChange(index, e.target.value)}
              onKeyDown={(e) => {
                if (e.key === "Backspace" && !value && index > 0) {
                  document.getElementById(`otp-input-${index - 1}`)?.focus();
                }
              }}
              className="w-14 h-14 text-2xl text-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          ))}
        </div>

        {/* Error Message */}
        {error && (
          <div className="text-red-500 text-center mb-4">{error}</div>
        )}

        {/* Timer */}
        <div className="text-center mb-6">
          <p className={timer > 0 ? "text-gray-600" : "text-red-500"}>
            {timer > 0 ? `Code expires in ${formatTime(timer)}` : "Code expired"}
          </p>
        </div>

        {/* Verify Button */}
        <button
          onClick={handleSubmit}
          disabled={otp.includes("") || isSubmitting || timer <= 0}
          className="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed"
        >
          {isSubmitting ? (
            <span className="flex items-center justify-center">
              <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Verifying...
            </span>
          ) : (
            "Verify & Continue"
          )}
        </button>

        {/* Resend Code Option */}
        <div className="text-center mt-4">
          <button 
            onClick={() => setTimer(600)} 
            className="text-blue-600 hover:text-blue-800"
            disabled={timer > 0}
          >
            Resend Code
          </button>
        </div>
      </div>
    </div>
  );
}

export default Verify;