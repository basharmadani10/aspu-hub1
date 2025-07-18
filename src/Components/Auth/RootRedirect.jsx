// RootRedirect.jsx
import { Navigate } from 'react-router-dom';

/**
 * RootRedirect component to handle redirection from the root path "/".
 * It checks for an authentication token and redirects to "/Home" if present,
 * otherwise redirects to "/register".
 * @returns {React.ReactElement} A Navigate component for redirection.
 */
const RootRedirect = () => {
  // Retrieve the authentication token from localStorage.
  const token = localStorage.getItem('token');

  // Redirect to "/Home" if a token exists, otherwise redirects to "/register".
  return <Navigate to={token ? "/Home" : "/register"} replace />; // يوجه إلى /register إذا لم يكن هناك توكن
};

export default RootRedirect;
