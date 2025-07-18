import React, { useState } from "react";
import { useNavigate } from 'react-router-dom';
import axios from "axios";

const steps = 3;

export default function NewPass() {
    const [activeStep, setActiveStep] = useState(3);
    const [newPassword, setNewPassword] = useState("");
    const [confirmPassword, setConfirmPassword] = useState("");
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();
    
    // Using environment variable with fallback for development
    const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

    const handleReset = async () => {
        if (!API_BASE_URL) {
            alert("System configuration error - please contact support");
            console.error("API_BASE_URL is not defined");
            return;
        }

        if (!newPassword || !confirmPassword) {
            alert("Please fill in all fields!");
            return;
        }

        if (newPassword !== confirmPassword) {
            alert("Passwords don't match!");
            return;
        }

        setLoading(true);

        try {
            const email = localStorage.getItem("resetEmail");
            const code = localStorage.getItem("verifiedCode");

            if (!code || code.length !== 6) {
                alert("Session expired. Please request a new code.");
                navigate('/Forget');
                return;
            }

            const response = await axios.post(
                `${API_BASE_URL}/student/password/reset`,
                { 
                    email,
                    code,
                    password: newPassword,
                    password_confirmation: confirmPassword
                },
                {
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    },
                    timeout: 10000 // 10 second timeout
                }
            );

            alert("Password reset successfully! Please log in with your new password.");
            
            // Clear reset data
            localStorage.removeItem("resetEmail");
            localStorage.removeItem("verifiedCode");
            localStorage.removeItem("resetCode");
            
            navigate('/login');

        } catch (error) {
            console.error("Error:", error);
            if (error.code === "ECONNABORTED") {
                alert("Request timeout - please try again");
            } else if (error.response?.status === 401) {
                alert("Code invalid or expired. Please request a new one.");
                navigate('/Forget');
            } else {
                alert(error.response?.data?.message || "Failed to reset password. Try again.");
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className='register'>
            <div className='Welcome'>
                <div className="circle yellow-circle"></div>
                <div className="circle blue-circle"></div>
                <h2 className='Title'>Welcome to ASPU hub</h2>
                <p className='explain'>
                    Lorem ipsum dolor sit amet consectetur adipisicing elit.
                    Asperiores, aliquam ab dolores quod atque excepturi ut aperiam
                    commodi veritatis tempore architecto ipsum, repellendus ullam.
                </p>
            </div>

            <div className='Forget-Password'>
                <h1>New Password</h1>
                <p className='Did'>
                    Please enter and confirm your new password.
                </p>

                <input
                    type="password"
                    placeholder='New Password'
                    className='Re-email'
                    value={newPassword}
                    onChange={(e) => setNewPassword(e.target.value)}
                    required
                />

                <input
                    type="password"
                    placeholder='Confirm Password'
                    className='Re-email'
                    value={confirmPassword}
                    onChange={(e) => setConfirmPassword(e.target.value)}
                    required
                />

                <button className='Next-step' onClick={handleReset} disabled={loading}>
                    {loading ? "Resetting..." : "Reset Password"}
                </button>

                <div className="flex items-center justify-center space-x-4 mt-10" style={{ marginTop: '35px', gap: '22px', width: "265px" }}>
                    {[...Array(steps)].map((_, index) => (
                        <div
                            key={index}
                            className={`h-2 w-16 rounded-full transition-colors duration-300 ${
                                activeStep - 1 === index ? "bg-yellow-500" : "bg-yellow-200"
                            }`}
                        ></div>
                    ))}
                </div>
            </div>
        </div>
    );
}
