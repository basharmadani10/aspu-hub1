// ProtectedRoute.jsx
import { Navigate } from 'react-router-dom';

/**
 * ProtectedRoute component to guard routes that require authentication.
 * If a token is not found in localStorage, it redirects to the login page.
 * @param {object} props - The component props.
 * @param {React.ReactNode} props.children - The child components to render if authenticated.
 * @returns {React.ReactNode} The children components if authenticated, or a Navigate component to login.
 */
const ProtectedRoute = ({ children }) => {
  // Retrieve the authentication token from localStorage.
  const token = localStorage.getItem('token');

  // If no token is found, redirect the user to the login page.
  if (!token) {
    return <Navigate to="/login" replace />; // يوجه إلى /login إذا لم يكن هناك توكن
  }

  // If a token exists, render the child components.
  return children;
};

export default ProtectedRoute;
