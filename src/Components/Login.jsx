import React, { useState } from 'react';
import './Login.css';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';

export default function Login() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const navigate = useNavigate();  // Hook for navigation
  const token = localStorage.getItem('token');
const AdminApi = import.meta.env.VITE_Admin_BASE_URL; // ✅ Correct
  const API_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';


    const SendData = async (event) => {
        event.preventDefault(); // Prevent form reload
    
   try {
      const response = await axios.post(
        `${API_URL}/student/login`,
        { email, password },
        {
            headers: { 
                'Content-Type': 'application/json',
       
            }
        }
      );

      console.log("Response:", response.data);

            if (response.status === 200) {
                alert("Login Successful!");
               // localStorage.setItem("userEmail", email);  // Store email for later use
               localStorage.setItem("userData", JSON.stringify(response.data.user));
                localStorage.setItem("token", response.data.token); // Store token for authentication   
                
                navigate('/Home'); // Redirect on success
           
            }
    
        } catch (error) {
            console.error("Login Error:", error.response?.data || error);
    
            // Handle 404 (User not found)
            if (error.response?.status === 404) {
                alert("User not found! Please register first.");
                navigate('/register');
            } else {
                alert(error.response?.data?.message || "Login Failed! Please check your credentials.");
            }
        }
    };
    
        

    return (
        <div className='register'>
            <div className='Welcome'>
                <div className="circle yellow-circle"></div>
                <div className="circle blue-circle"></div>
                <h2 className='newTitle'>WELCOME TO ASPU HUB</h2>
                <p className='explain'>
                             Welcome to ASPU Hub, the perfect platform to manage your accounts and access our services with ease.
        our platform has many of features to help the student and provide the communication between the students in a good way <br></br> 
        and include The roadMap with thier types internally or externally  and in speceific way
        also it`s have an Ai things that make u excited to start learning and make it more easier and familiar<br />
        Let`s dive in !!!! 
                </p>            
            </div>

            <div className='Accounts-Log'>
                <h3 className='space'>Please enter Your<br/>Email and Password</h3>
                
                <form onSubmit={SendData}>
                    <input 
                        className='same-Log' 
                        type='email' 
                        placeholder='Email' 
                        value={email}
                        onChange={(event) => setEmail(event.target.value)}
                        required
                    />

                    <input 
                        className='same-Log' 
                        type="password" 
                        placeholder='Password' 
                        value={password}
                        onChange={(event) => setPassword(event.target.value)}
                        required
                    />
                    <div className='Forget'>
                        <Link to='/Forget'><p>Forget Your Password?</p></Link>
                        <p>Keep LogIn</p>
                        <input type="checkbox" />
                    </div>

                    <button type="submit" className='Login-Btn'>Login</button>
                </form>

                <footer>
                  <p className='DontNew'>Don't Have an Account? <Link to='/'>Register</Link></p>  
    
                </footer>
            </div>
        </div>
    );
}
