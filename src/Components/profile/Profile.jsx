// Profile.jsx
import React, { useState, useEffect } from "react";
import { NavLink } from "react-router-dom";
import HeaderApp from "../HeaderApp/HeaderApp";
import Posts from "../Posts/Posts";
import "./Profile.css";
import axios from "axios";
import Subjects from "../Subjects/Subjects";
import EntireSubjects from "../EntireSubjects/EntireSubjects";
import { IoIosAddCircle } from "react-icons/io";
import NewSubjects from "../NewSubjects/NewSubjects";

const Profile = ({ showInput = false, addComment = false }) => {
  const [activeTab, setActiveTab] = useState(null);
  const [isEditing, setIsEditing] = useState(false);
  const [showNewSubjects, setShowNewSubjects] = useState(false);
  const [name, setName] = useState("");
  const [profileData, setProfileData] = useState({
    major: "",
    year: "",
    profile_image: "",
    bio: "",
  });

  const API_URL = import.meta.env.VITE_API_BASE_URL;
  const IMAGE_BASE_URL = import.meta.env.VITE_IMAGE_BASE_URL || 'http://127.0.0.1:8000';
  const token = localStorage.getItem('token');

  const constructFullImageUrl = (relativePath) => {
    if (!relativePath || relativePath === "local_placeholder_path.jpg") {
      return "https://placehold.co/120x120/CCCCCC/333333?text=User";
    }
    if (relativePath.startsWith('http')) return relativePath;
    return `${IMAGE_BASE_URL}/${relativePath}`;
  };

  const [selectedImage, setSelectedImage] = useState(null);
  const [previewImage, setPreviewImage] = useState("");
  const [cumulativeHours, setCumulativeHours] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [posts, setPosts] = useState([]);
  const [lastRequestTime, setLastRequestTime] = useState(0);
  const [retryCount, setRetryCount] = useState(0);
  const [showSpecializationModal, setShowSpecializationModal] = useState(false);

  const fetchWithRetry = async (url, options = {}, retries = 3) => {
    const now = Date.now();
    const timeSinceLastRequest = now - lastRequestTime;
    if (timeSinceLastRequest < 500) {
      await new Promise((resolve) => setTimeout(resolve, 500 - timeSinceLastRequest));
    }
    try {
      setLastRequestTime(Date.now());
      const response = await axios({
        url,
        ...options,
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/json",
          ...options.headers,
        },
      });
      return response.data;
    } catch (error) {
      if (error.response?.status === 429 && retries > 0) {
        setRetryCount((prev) => prev + 1);
        const delay = Math.pow(2, 4 - retries) * 1000;
        await new Promise((resolve) => setTimeout(resolve, delay));
        return fetchWithRetry(url, options, retries - 1);
      }
      throw error;
    }
  };

  useEffect(() => {
    let isMounted = true;
    const fetchData = async () => {
      try {
        if (!isMounted) return;
        setLoading(true);
        setError(null);

        const headers = {
          Authorization: `Bearer ${token}`,
          Accept: "application/json"
        };

        const [userData, postsData] = await Promise.all([
          fetchWithRetry(`${API_URL}/student/Getuserinfo`, { method: "get", headers }),
          fetchWithRetry(`${API_URL}/student/userpost`, { method: "get", headers })
        ]);

        if (!isMounted) return;

        const customName = localStorage.getItem("custom_name");
        const displayName = customName || userData.name || "Your Name";
        setName(`${userData.f_name} ${userData.l_name}`);
        const defaultProfileImage = userData.profile_image || "https://placehold.co/48x48/CCCCCC/333333?text=User";

        setProfileData({
          major: userData.major || "Engineering",
          year: userData.year || "",
          profile_image: defaultProfileImage,
          bio: userData.bio || "No bio provided",
        });
        setPreviewImage(defaultProfileImage);
        setCumulativeHours(userData.cumulative_hours || 0);

        const savedSpecialization = localStorage.getItem("userSpecialization") || localStorage.getItem("specialization");
        if (savedSpecialization) {
          setProfileData((prevData) => ({
            ...prevData,
            major: savedSpecialization,
            bio: `Specialization: ${savedSpecialization}`,
          }));
        }

        const userPosts = (postsData.posts || []).map((post) => {
          let imageUrl = null;
          if (post.image_url) {
            imageUrl = post.image_url.startsWith("blob:") || post.image_url.startsWith("http")
              ? post.image_url
              : constructFullImageUrl(post.image_url);
          } else if (post.photos && post.photos.length > 0) {
            imageUrl = constructFullImageUrl(post.photos[0].photo);
          }
          return {
            ...post,
            community_id: post.community_id || 1,
            tags: post.tags ? JSON.parse(post.tags) : [],
            author: displayName,
            image_url: imageUrl,
            author_image: constructFullImageUrl(post.user_image || defaultProfileImage),
          };
        });

        setPosts(userPosts);
        setLoading(false);
      } catch (err) {
        if (!isMounted) return;
        console.error("Error fetching data:", err);
        setError(err.response?.data?.message || "Failed to load profile data. Please try again later.");
        setLoading(false);
      }
    };
    fetchData();
    return () => { isMounted = false; };
  }, [retryCount]);

  const handleImageChange = async (e) => {
    const file = e.target.files[0];
    if (file) {
      setSelectedImage(file);
      const reader = new FileReader();
      reader.onloadend = () => setPreviewImage(reader.result);
      reader.readAsDataURL(file);

      try {
        const formData = new FormData();
        formData.append("profile_image", file);
        const response = await axios.post(`${IMAGE_BASE_URL}/student/profile/upload-image`, formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "multipart/form-data"
          }
        });
        const imageUrl = response.data.image_url;
        setProfileData((prevData) => ({ ...prevData, profile_image: imageUrl }));
        setPreviewImage(imageUrl);
      } catch (error) {
        console.error("Image upload failed:", error);
        alert("فشل رفع الصورة. حاول مرة أخرى.");
      }
    }
  };

 if (loading) {
    return (
      <div className="profile-container">
        <HeaderApp />
        <div className="profile-skeleton">
          <div className="skeleton-avatar"></div>
          <div className="skeleton-name"></div>
          <div className="skeleton-stats">
            <div></div>
            <div></div>
            <div></div>
          </div>
          <div className="skeleton-tabs">
            <div></div>
            <div></div>
            <div></div>
          </div>
          <div className="skeleton-content"></div>
        </div>
      </div>
    );
  }
if (error) {
    return (
      <div className="profile-container">
        <HeaderApp />
        <div className="profile-error">
          <h2>Error Loading Profile</h2>
          <p>{error}</p>
          <button onClick={handleRetry} className="retry-button">Retry</button>
        </div>
      </div>
    );
  }

  return (
    <div className="profile-container">
      <HeaderApp />
            <div className="profile-header"></div>

      <div className="profile-content">
        <button className="profile-btn">
          <label htmlFor="profile-image-input">
            <img src={constructFullImageUrl(profileData.profile_image)} alt="Profile" />
          </label>
        </button>
        <input type="file" id="profile-image-input" accept="image/*" style={{ display: "none" }} onChange={handleImageChange} />
        <div className="user-information">
          {isEditing ? (
            <input type="text" value={name} onChange={(e) => setName(e.target.value)} onBlur={() => setIsEditing(false)} onKeyDown={(e) => e.key === "Enter" && setIsEditing(false)} autoFocus className="edit-name-input" />
          ) : (
            <h1 className="profile-name">{name}</h1>
          )}
<button className="profile-edit-button" onClick={() => setIsEditing(true)}>✏️ Edit</button>

        </div>
      </div>
      <div className="profile-stats">
        <p><strong>Major:</strong> {profileData.major}</p>
        <p><strong>Year:</strong> {profileData.year}</p>
        <p><strong>Bio:</strong> {profileData.bio} | <strong>Sum of Hours:</strong> {cumulativeHours} /166</p>
      </div>
      <div className="profile-tabs">
        <div className="tabs-container">
          <NavLink to="#" className={`tab ${activeTab === "posts" ? "active-tab" : ""}`} onClick={() => setActiveTab(activeTab === "posts" ? null : "posts")}>Posts</NavLink>
          <NavLink to="#" className={`tab ${activeTab === "NewSubjects" ? "active-tab" : ""}`} onClick={() => setActiveTab(activeTab === "NewSubjects" ? null : "NewSubjects")}>New Subjects</NavLink>
          <NavLink to="#" className={`tab ${activeTab === "mysubjects" ? "active-tab" : ""}`} onClick={() => setActiveTab(activeTab === "mysubjects" ? null : "mysubjects")}>My Subjects</NavLink>
          <NavLink to="/entire-subjects" className="tab" style={({ isActive }) => isActive ? { fontWeight: 'bold', color: '#4f46e5' } : {}}>Entire Subjects</NavLink>
        </div>
        <div className="content-section">
          {activeTab === "posts" && <Posts posts={posts} currentUserId={name} showInput={showInput} addComment={addComment} />}
          {activeTab === "NewSubjects" && <div className="NewSubjects-content" style={{ display: "flex", alignItems: "center", flexDirection: "column", marginTop: "50px" }}><h3>You can't add new subjects until you finish this semester</h3><IoIosAddCircle onClick={() => setShowNewSubjects(true)} size={36} style={{ marginTop: "20px", cursor: "pointer" }} /></div>}
          {activeTab === "mysubjects" && <div className="mysubjects-content"><h3><Subjects /></h3></div>}
        </div>
      </div>
      {activeTab === "NewSubjects" && showNewSubjects && <NewSubjects />}
      {showSpecializationModal && (
        <div className="specialization-modal">
          <div className="modal-overlay" onClick={() => setShowSpecializationModal(false)} />
          <div className="modal-content">
            <h2>Please select your specialization</h2>
            <button onClick={() => handleSpecializationSelection("Software Engineering")}>Software Engineering</button>
            <button onClick={() => handleSpecializationSelection("Networks")}>Networks</button>
            <button onClick={() => handleSpecializationSelection("AI")}>AI</button>
          </div>
        </div>
      )}
    </div>
  );
};

export default Profile;
