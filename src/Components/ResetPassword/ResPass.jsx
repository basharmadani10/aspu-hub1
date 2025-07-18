import { useState, useEffect } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";

function ResPass() {
    const navigate = useNavigate();
    const [otp, setOtp] = useState(["", "", "", "", "", ""]);
    const [timer, setTimer] = useState(600);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [newPassword, setNewPassword] = useState("");
    const [confirmPassword, setConfirmPassword] = useState("");
    const email = localStorage.getItem("resetEmail") || "";
    const token = localStorage.getItem('token');
    
    // التعديل الرئيسي هنا - إزالة القيمة الافتراضية المحلية
    const API_URL = import.meta.env.VITE_API_BASE_URL;

    useEffect(() => {
        if (!API_URL) {
            console.error("API_URL is not defined!");
            alert("System configuration error - please contact support");
            return;
        }

        if (timer > 0) {
            const interval = setInterval(() => setTimer((prev) => prev - 1), 1000);
            return () => clearInterval(interval);
        }
    }, [timer, API_URL]);

    const formatTime = (time) => {
        const minutes = Math.floor(time / 60);
        const seconds = time % 60;
        return `${minutes}:${seconds < 10 ? `0${seconds}` : seconds}`;
    };

    const handleChange = (index, value) => {
        if (!/^\d*$/.test(value)) return;
        const newOtp = [...otp];
        newOtp[index] = value;
        setOtp(newOtp);

        if (value && index < 5) {
            document.getElementById(`otp-input-${index + 1}`)?.focus();
        }
    };

    const handleSubmit = async () => {
        if (!API_URL) {
            alert("System error - please refresh the page");
            return;
        }

        const code = otp.join("");
        
        if (code.length !== 6) {
            alert("Please enter the full 6-digit verification code.");
            return;
        }

        if (!newPassword || !confirmPassword) {
            alert("Please enter and confirm your new password.");
            return;
        }

        if (newPassword !== confirmPassword) {
            alert("Passwords don't match!");
            return;
        }

        setIsSubmitting(true);

        try {
            const response = await axios.post(
                `${API_URL}/student/password/reset`,
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
                        "Authorization": `Bearer ${token}`
                    },
                    timeout: 10000 // 10 ثواني timeout
                }
            );

            alert("Password reset successfully! Please log in with your new password.");
            localStorage.removeItem("resetEmail");
            navigate('/login');

        } catch (error) {
            console.error("Error:", error);
            if (error.code === "ECONNABORTED") {
                alert("Request timeout - please try again");
            } else if (error.response?.status === 400) {
                alert("Invalid or expired code.");
            } else if (error.response?.status === 401) {
                alert("Session expired - please login again");
                navigate('/login');
            } else if (error.response?.status === 422) {
                alert(error.response.data.errors[Object.keys(error.response.data.errors)[0]][0]);
            } else {
                alert("Failed to reset password. Please try again later.");
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
<div className='register'>
            <div className='Welcome'>
                <div className="circle yellow-circle"></div>
                <div className="circle blue-circle"></div>
                <h2 className='Title'>Welcome to ASPU hub</h2>
                <p className='explain'>
 Xavi Hernandez 
                </p>            
            </div>

        <div className="flex flex-col items-center justify-center h-screen"
        style={{width:'50%'}}
        >
            <div className="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
                <h2 className="text-xl font-bold mb-4">Reset Password</h2>
                <p className="text-gray-600 mb-6">
                    We sent a 6-digit code to <span className="font-semibold">{email}</span>
                </p>

                <div className="mb-6">
                    <label className="block text-gray-700 mb-2">Verification Code</label>
                    <div className="flex gap-2">
                        {otp.map((value, index) => (
                            <input
                                key={index}
                                id={`otp-input-${index}`}
                                type="text"
                                maxLength={1}
                                value={value}
                                onChange={(e) => handleChange(index, e.target.value)}
                                className="w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-blue-500"
                            />
                        ))}
                    </div>
                    <p className="text-green-500 mt-2">
                        Time remaining: {formatTime(timer)}
                        {timer <= 0 && " - Code expired"}
                    </p>
                </div>

                <div className="mb-4">
                    <label className="block text-gray-700 mb-2">New Password</label>
                    <input
                        type="password"
                        value={newPassword}
                        onChange={(e) => setNewPassword(e.target.value)}
                        className="w-full p-2 border border-gray-300 rounded-md"
                        placeholder="Enter new password"
                    />
                </div>

                <div className="mb-6">
                    <label className="block text-gray-700 mb-2">Confirm Password</label>
                    <input
                        type="password"
                        value={confirmPassword}
                        onChange={(e) => setConfirmPassword(e.target.value)}
                        className="w-full p-2 border border-gray-300 rounded-md"
                        placeholder="Confirm new password"
                    />
                </div>

                <button
                    onClick={handleSubmit}
                    disabled={otp.includes("") || isSubmitting || timer <= 0 || !newPassword || !confirmPassword}
                    className="w-full py-2 px-4 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:bg-gray-400"
                >
                    {isSubmitting ? "Processing..." : "Reset Password"}
                </button>

                {timer <= 0 && (
                    <button
                        onClick={() => navigate('/Forget')}
                        className="w-full mt-4 py-2 px-4 bg-red-500 text-white rounded-md hover:bg-red-600"
                    >
                        Request New Code
                    </button>
                )}
            </div>
        </div>
        </div>

        
    );
}
export default ResPass;
