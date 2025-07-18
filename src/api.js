import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL 

// Log a warning if API_BASE_URL is not set as expected
if (!import.meta.env.VITE_API_BASE_URL) {
  console.warn(
    `Warning: VITE_API_BASE_URL is not set. Falling back to default: "${API_BASE_URL}"`
  );
}

const api = axios.create({
  baseURL: API_BASE_URL,
});

export const getFullImageUrl = (relativePath) => {
  if (!relativePath || relativePath.includes('placeholder')) {
    return null;
  }

  const baseUrl = API_BASE_URL.replace('/api', '');
  return relativePath.startsWith('/storage')
    ? `${baseUrl}${relativePath}`
    : `${baseUrl}/storage/${relativePath}`;
};

export default api;
