'use client';

import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { FaHome, FaRoad, FaUser, FaSearch, FaBell } from 'react-icons/fa';
import { MdGroups2 } from 'react-icons/md';
import axios from 'axios';
import './HeaderApp.css';
import photo from '../../assets/images.jpg';
import { TbLogout2 } from "react-icons/tb";

export default function HeaderApp({ refreshNotifications, onSearch }) {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [dropdownOpen, setDropdownOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const [profilePhoto, setProfilePhoto] = useState("");
  const [showLogoutMsg, setShowLogoutMsg] = useState(false); // <-- الإشعار

  const navigate = useNavigate();
  const token = localStorage.getItem('token');
  const API_URL = import.meta.env.VITE_API_BASE_URL;

  const availableTags = ['Python', 'JavaScript', 'Java', 'C++', 'Ruby', 'Php'];

  const filteredTags = availableTags.filter(tag =>
    tag.toLowerCase().includes(searchQuery.toLowerCase())
  );

  useEffect(() => {
    const storedPhoto = localStorage.getItem("profilePhoto");
    if (storedPhoto) setProfilePhoto(storedPhoto);
  }, []);

  const fetchNotifications = async () => {
    try {
      const response = await axios.get(`${API_URL}/student/get_all_notification`, {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });

      const rawNotifications = response.data || [];
      const lastClearedTime = localStorage.getItem("lastNotificationsClear");

      const processedNotifications = rawNotifications.map(notification => {
        const username = notification.comment?.user?.first_name ||
                         notification.user?.first_name ||
                         (notification.body && notification.body.split("'")[1]) ||
                         'User';

        const commentContent = notification.comment?.content ||
                               (notification.body && notification.body.split("'")[3]) ||
                               'New comment';

        return {
          id: notification.id || Math.random().toString(36).substr(2, 9),
          title: notification.title || 'New Comment',
          username: username,
          comment: commentContent,
          createdAt: notification.created_at || new Date().toISOString()
        };
      });

      const newNotifications = lastClearedTime
        ? processedNotifications.filter(n => new Date(n.createdAt) > new Date(lastClearedTime))
        : processedNotifications;

      setNotifications(newNotifications);
      setUnreadCount(newNotifications.length);
    } catch (error) {
      console.error('Failed to fetch notifications', error);
      setNotifications([]);
      setUnreadCount(0);
    }
  };

  useEffect(() => {
    fetchNotifications();
    const notificationPolling = setInterval(fetchNotifications, 30000);
    return () => clearInterval(notificationPolling);
  }, [token]);

  const handleSearchChange = (e) => {
    const query = e.target.value;
    setSearchQuery(query);
    if (onSearch) onSearch(query);
  };

  const toggleDropdown = () => {
    setDropdownOpen(prev => !prev);
    if (!dropdownOpen) fetchNotifications();
  };

  const markAllAsRead = () => {
    const now = new Date().toISOString();
    localStorage.setItem("lastNotificationsClear", now);
    setNotifications([]);
    setUnreadCount(0);
  };

  const handleLogout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('userData');
  setShowLogoutMsg(true);
  setTimeout(() => {
    setShowLogoutMsg(false);
    navigate('/'); // يوجه للتسجيل
  }, 2000);
};

  return (
    <>
      <header className="bg-white" style={{ background: 'linear-gradient(to top right, #4f46e5, #60a5fa)' }}>
        <nav className="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8">
          <div className="flex lg:flex-1">
            <a href="#" className="-m-1.5 p-1.5"></a>
            <div className="relative">
              <div className="search-container">
                <input
                  className="aheadLogo border rounded p-2"
                  placeholder="Search by Subject or Tag"
                  value={searchQuery}
                  onChange={handleSearchChange}
                />
                <span className="search-icon absolute right-2 top-2 text-gray-600">
                  <FaSearch style={{ color: 'white' }} />
                </span>
              </div>
              {searchQuery && (
                <div className="mt-1 text-white-600 text-sm" style={{ color: 'white' }}>
                  {filteredTags.length > 0 
                    ? `Matching tags: ${filteredTags.join(', ')}`
                    : "No matching tags found."}
                </div>
              )}
            </div>
          </div>

          <div className="hidden lg:flex lg:gap-x-12" style={{ width: '50%' }}>
            <Link to="/Home" className="text-gray-900 flex flex-col items-center text-sm font-semibold">
              <FaHome className="size-6" />
              <span>Home</span>
            </Link>
            <Link to="/Roadmap" className="text-gray-900 flex flex-col items-center text-sm font-semibold">
              <FaRoad className="size-6" />
              <span>Roadmap</span>
            </Link>
            <Link to="/Groups" className="text-gray-900 flex flex-col items-center text-sm font-semibold">
              <MdGroups2 className="size-6" />
              <span>Groups</span>
            </Link>
            <Link to="/Profile" className="text-gray-900 flex flex-col items-center text-sm font-semibold">
              <FaUser className="size-6" />
              <span>Account</span>
            </Link>
          </div>

          <div className="flex items-center gap-4 relative">
            <TbLogout2 
              size={25} 
              style={{ cursor: 'pointer', marginRight: '20px' , color:"white" }} 
              onClick={handleLogout}
            />

            <div className="relative cursor-pointer" onClick={toggleDropdown}>
              <FaBell style={{ color: 'white' }} className="text-gray-700 size-6" />
              {unreadCount > 0 && (
                <span className="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">
                  {unreadCount}
                </span>
              )}
            </div>

            {dropdownOpen && (
              <div className="absolute top-10 right-0 bg-white border rounded-lg shadow-lg w-72 z-10 p-4">
                <div className="flex justify-between items-center mb-2">
                  <h4 className="font-semibold text-gray-700">Notifications</h4>
                  {unreadCount > 0 && (
                    <button onClick={markAllAsRead} className="text-blue-600 text-xs underline hover:text-blue-800">
                      Mark all as read
                    </button>
                  )}
                </div>
                <ul className="space-y-2 max-h-60 overflow-auto">
                  {notifications.length > 0 ? (
                    notifications.map(notification => (
                      <li key={notification.id} className={`text-sm border-b pb-2 ${!notification.is_read ? 'bg-blue-50' : ''}`}>
                        <p className="text-gray-600 font-semibold">{notification.title}</p>
                        <p className="text-gray-500">User: {notification.username}</p>
                        <p className="text-gray-500">Comment: {notification.comment}</p>
                        {!notification.is_read && (
                          <p className="text-xs text-blue-500 mt-1">New</p>
                        )}
                        <p className="text-xs text-gray-400 mt-1">
                          {new Date(notification.createdAt).toLocaleString()}
                        </p>
                      </li>
                    ))
                  ) : (
                    <li className="text-sm text-gray-500">No notifications yet</li>
                  )}
                </ul>
              </div>
            )}
          </div>
        </nav>
      </header>

      {/* ✅ إشعار تسجيل الخروج */}
    {showLogoutMsg && (
      <div style={{
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        background: '#4f46e5',
        color: 'white',
        padding: '10px 20px',
        borderRadius: '10px',
        boxShadow: '0 0 10px rgba(0,0,0,0.2)',
        zIndex: 9999
      }}>
        You've been logged out. Redirecting to login...
      </div>
    )}
  </>
)}
