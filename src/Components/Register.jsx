import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import './Register.css';
import Carousel from './Carousel/Carousel ';

const API_URL = import.meta.env.VITE_API_BASE_URL;
const AdminApi = import.meta.env.VITE_Admin_BASE_URL; // ✅ Correct

const Register = () => {
  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    password: '',
    confirmPassword: '',
    role: 'student',
    specialization: 'global information technology', // default value
  });

  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');



  // Update form fields on change
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prevData) => ({
      ...prevData,
      [name]: value,
    }));
  };

  // Handle form submission
  const handleSubmit = async (e) => {
    e.preventDefault();

    if (formData.password !== formData.confirmPassword) {
      alert('Passwords do not match!');
      return;
    }

    setLoading(true);
    setError('');

    try {
      const data = {
        first_name: formData.firstName,
        last_name: formData.lastName,
        email: formData.email,
        password: formData.password,
        password_confirmation: formData.confirmPassword,
        role: formData.role,
        specialization: formData.specialization,
        BirthDate: formData.BirthDate || null, // Ensure BirthDate is sent, or null if optional
      };

      console.log('Sending data:', data); // Debugging

      const response = await axios.post(`${API_URL}/register`, data, {
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
      });

      console.log('✅ Server response:', response.data);

      // Check if registration was successful
      const isSuccessful =
        response.data.token ||
        (response.data.message &&
          response.data.message.toLowerCase().includes('successfully'));

      if (isSuccessful) {
        const token = response.data.token;
        if (token) {
          localStorage.setItem('token', token);
          console.log('🔐 Token saved:', token);
        }
        // Save additional user information
        localStorage.setItem(
          'user',
          JSON.stringify({
            firstName: formData.firstName,
            lastName: formData.lastName,
          })
        );
        localStorage.setItem('email', formData.email);
        // **IMPORTANT**: Save the user's specialization to localStorage.
        localStorage.setItem('userSpecialization', formData.specialization);

        alert('Registration successful! Check your email for the verification code.');
        navigate('/Verify');
      } else {
        alert('Registration failed: ' + response.data.message);
      }
    } catch (error) {
      console.error('❌ Registration failed:', error.response?.data);
      if (error.response?.status === 422 && error.response?.data?.errors) {
        // Validation errors (422)
        const errorMessages = Object.values(error.response.data.errors)
          .flat()
          .join('\n');
        alert('Validation failed:\n' + errorMessages);
      } else if (error.response?.status === 500 && error.response?.data?.details) {
        // Internal Server Error with details
        alert('Registration failed (Server Error):\n' + error.response.data.details);
      } else {
        // Other errors or generic message
        alert('Registration failed. Please try again. ' + (error.message || ''));
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="register">
      <div className="Welcome">
        <div className="circle yellow-circle"></div>
        <div className="circle blue-circle"></div>
        <h2 className="newTitle">Welcome to ASPU Hub</h2>
        <p className="explain">
          
          Welcome to ASPU Hub, the perfect platform to manage your accounts and access our services with ease.
        </p>
        <div style={{position: 'relative' , left :"20%" , }}>
  <Carousel
    baseWidth={500}
    autoplay={true}
    autoplayDelay={3000}
    pauseOnHover={true}
    loop={true}
    round={false} 
  />
  
</div>
      </div>

      <div className="Accounts">
        <h1 className="Title">Create a New Account</h1>

        <form onSubmit={handleSubmit}>
          <input
            className="same"
            type="email"
            placeholder="Email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            required
          />

          <input
            className="Test"
            type="text"
            placeholder="First Name"
            name="firstName"
            value={formData.firstName}
            onChange={handleChange}
            required
          />

          <input
            className="Test"
            type="text"
            placeholder="Last Name"
            name="lastName"
            value={formData.lastName}
            onChange={handleChange}
            required
          />

          <input
            className="same"
            type="date"
            placeholder="Date of Birth"
            name="BirthDate"
            value={formData.BirthDate || ''}
            onChange={handleChange}
            required
          />

          <select name="role" value={formData.role} onChange={handleChange} required>
            <option value="student">Student</option>
            <option value="superadmin">Super Admin</option>
            <option value="admin">Admin</option>
          </select>

          <input
            className="same"
            type="password"
            placeholder="Password"
            name="password"
            value={formData.password}
            onChange={handleChange}
            required
          />

          <input
            className="same"
            type="password"
            placeholder="Confirm Password"
            name="confirmPassword"
            value={formData.confirmPassword}
            onChange={handleChange}
            required
          />

          <label>Specialization:</label>
          <select
            name="specialization"
            value={formData.specialization}
            onChange={handleChange}
            required
          >
            <option value="global information technology">
              Global Information Technology
            </option>
            <option value="software">Software</option>
            <option value="networking">Networking</option>
            <option value="ai">AI</option>
          </select>

          <button className="RegisterBtn" type="submit" disabled={loading}>
            {loading ? 'Registering...' : 'Register'}
          </button>
        </form>

        <div className="agree">
          <input className="check" type="checkbox" required />
          <label htmlFor="agree">I agree to the terms and conditions</label>
        </div>

                          <div className='AdminLog'>
<Link to={`${AdminApi}/supervisor/register`}>
  Supervisor-Registration
</Link>                         </div>
        <footer>
          <span className='newfoot'> Already have an account? <Link to="/login">Login</Link>
          </span>
        </footer>
      
      </div>
      
    </div>
  );
};

export default Register;
