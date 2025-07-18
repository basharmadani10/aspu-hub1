import React, { useState } from "react";
import './Forget.css';
import { useNavigate } from 'react-router-dom';
import axios from "axios";

const steps = 1;

export default function Forget() {
    const [activeStep, setActiveStep] = useState(1);
    const [email, setEmail] = useState("");
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();
    const [isSubmitting, setIsSubmitting] = useState(false);
    
    // استخدام متغير البيئة هنا
    const API_URL = import.meta.env.VITE_API_BASE_URL;

    const handleNext = async () => {
        if (!API_URL) {
            alert("System configuration error - please contact support");
            console.error("API_URL is not defined");
            return;
        }

        if (!email) {
            alert("Please enter your email!");
            return;
        }

        if (isSubmitting) return;
        setIsSubmitting(true);
        setLoading(true);

        try {
            const response = await axios.post(
                `${API_URL}/student/password/send-code`,
                { email },
                {
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    },
                    timeout: 10000 // إضافة timeout لمدة 10 ثواني
                }
            );

            // تخزين البيانات في localStorage
            localStorage.setItem("resetEmail", email);
            localStorage.setItem("resetCode", response.data.code);
            localStorage.setItem("token", response.data.token);
            navigate('/ResPass');

        } catch (error) {
            console.error("API Error:", {
                url: API_URL,
                error: error.message,
                code: error.code
            });
            
            if (error.code === "ECONNABORTED") {
                alert("Request timeout - please try again");
            } else if (error.response?.status === 404) {
                alert("Email not found - please check and try again");
            } else {
                alert(error.response?.data?.message || "Failed to send reset code. Try again.");
            }
        } finally {
            setLoading(false);
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
                    Lorem ipsum dolor sit amet consectetur adipisicing elit.
                    Asperiores, aliquam ab dolores quod atque excepturi ut aperiam
                    commodi veritatis tempore architecto ipsum, repellendus ullam.
                </p>
            </div>

            <div className='Forget-Password'>
                <h1>Forget Password</h1>
                <p className='Did'>
                    Did you forget your password? <br />
                    Don't worry ... Enter your E-mail <br />
                    We will send a mail to reset your password.
                </p>

                <input
                    type="email"
                    placeholder='Email'
                    className='Re-email'
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                />

                <button className='Next-step' onClick={handleNext} disabled={loading}>
                    {loading ? "Sending..." : "Next"}
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
