import React, { useEffect, useState } from 'react';
import { LuArrowBigUp, LuArrowBigDown } from "react-icons/lu";
import { FaEllipsisV } from "react-icons/fa";
import CommentSection from '../CommentSection/CommentSection';
import axios from 'axios';
import api, { getFullImageUrl } from '../../api'; // Adjust the import path as needed
import GradientText from '../gradient/GradientText';

import { GoReport } from "react-icons/go";

function Posts({
  posts = [],
  userVotes = {},
  currentUserId,
  showInput = true,
  onVote,
  addComment,
  onDeletePost,
  onEditPost,
  defaultImage
}) {
  const [voteErrors, setVoteErrors] = useState({});
  const [openComments, setOpenComments] = useState({});
  const [openPostMenu, setOpenPostMenu] = useState({});
  const [editingPostId, setEditingPostId] = useState(null);
  const [editingTitle, setEditingTitle] = useState('');
  const [editingContent, setEditingContent] = useState('');
  const [newposts, setNewPosts] = useState([]);
  const [reportSuccess, setReportSuccess] = useState({});
  // New state variables for report modal
  const [isReportModalOpen, setIsReportModalOpen] = useState(false);
  const [reportingPostId, setReportingPostId] = useState(null);
  const [reportReason, setReportReason] = useState('');
  const [reportError, setReportError] = useState('');
  const storedProfileImage = localStorage.getItem("custom_profile_image") ||
    "https://placehold.co/48x48/CCCCCC/333333?text=User";
  const token = localStorage.getItem('token');

  const communityLabels = {
    1: 'Global',
    2: 'SoftWare',
    3: 'Networking',
    4: 'Ai'
  };

  // For Vite, use import.meta.env with variables prefixed by VITE_
  const API_URL = import.meta.env.VITE_API_BASE_URL;

  // Create an axios instance with default headers and a timeout.
  const axiosInstance = axios.create({
    baseURL: API_URL,
    headers: {
      'Authorization': token ? `Bearer ${token}` : '',
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    timeout: 5000, // 5 seconds timeout for the requests
  });

  useEffect(() => {
    setNewPosts(posts);
  }, [posts]);

  // Sort posts by net votes (positiveVotes - negativeVotes) in descending order.
  const sortedPosts = [...newposts].sort((a, b) => {
    const aVotes = (a.positiveVotes || 0) - (a.negativeVotes || 0);
    const bVotes = (b.positiveVotes || 0) - (b.negativeVotes || 0);
    return bVotes - aVotes;
  });

  const handleVote = async (postId, voteType) => {
    if (userVotes[postId]) {
      setVoteErrors(prev => ({ ...prev, [postId]: "You have already voted on this post." }));
      return;
    }

    setVoteErrors(prev => ({ ...prev, [postId]: '' }));
    try {
      if (onVote) {
        await onVote(postId, voteType);
      }
    } catch (err) {
      console.error("Failed to vote", err);
      if (err.response) {
        console.log("Error response data:", err.response.data);
      }
      setVoteErrors(prev => ({ ...prev, [postId]: err.message || "Failed to cast vote." }));
    }
  };

  const toggleComments = (postId) => {
    setOpenComments(prev => ({ ...prev, [postId]: !prev[postId] }));
  };

  const closeModal = (postId) => {
    setOpenComments(prev => ({ ...prev, [postId]: false }));
  };

  const handleReply = async (postId, parentCommentId, replyContent) => {
    try {
      if (addComment) {
        await addComment(postId, replyContent, parentCommentId);
      }
    } catch (err) {
      console.error("Error replying to comment", err);
      if (err.response) {
        console.log("Error response data:", err.response.data);
      }
    }
  };

  const displayUserName = (user) => {
    if (!user) return 'You';
    if (user.displayName) return user.displayName;
    if (user.first_name && user.last_name) return `${user.first_name} ${user.last_name}`;
    // **Modified:** Check for f_name and l_name first, then name.
    if (user.f_name && user.l_name) return `${user.f_name} ${user.l_name}`;
    return user.name || `User#${user.id || user.uid || 'Unknown'}`;
  };

  const togglePostMenu = (postId) => {
    setOpenPostMenu(prev => ({ ...prev, [postId]: !prev[postId] }));
  };

  const handleDeletePost = async (postId) => {
    try {
      const response = await axiosInstance.delete(`${API_URL}/student/post/${postId}/delete`, {
        headers: {
          Authorization: token ? `Bearer ${token}` : '',
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      });
      console.log("Delete response:", response.data);
      // Update local state to remove the deleted post.
      setNewPosts(prevPosts => prevPosts.filter(post => post.id !== postId));

      if (onDeletePost) {
        await onDeletePost(postId);
      }
      setOpenPostMenu(prev => ({ ...prev, [postId]: false }));
    } catch (error) {
      console.error("Error deleting post:", error);
      if (error.response) {
        console.log("Error response data:", error.response.data);
        console.error("Server responded with", error.response.status);
      } else if (error.request) {
        console.error("No response received:", error.request);
      } else {
        console.error("Error setting up request:", error.message);
      }
      setVoteErrors(prev => ({
        ...prev,
        [postId]: error.message || "Failed to delete post."
      }));
    }
  };

  const handleEditPost = (post) => {
    setEditingPostId(post.id);
    setEditingTitle(post.title);
    setEditingContent(post.content);
    setOpenPostMenu(prev => ({ ...prev, [post.id]: false }));
  };

  const handleSaveEdit = async (postId) => {
    const updatedData = {
      title: editingTitle,
      content: editingContent,
    };

    try {
      const response = await axiosInstance.post(
        `${API_URL}/student/post/${postId}/update`,
        updatedData,
        {
          headers: {
            Authorization: token ? `Bearer ${token}` : '',
            Accept: 'application/json',
            'Content-Type': 'application/json',
          },
        }
      );
      console.log("Edit response:", response.data);
      // Update local state to incorporate the edited post data.
      setNewPosts(prevPosts =>
        prevPosts.map(post =>
          post.id === postId ? { ...post, ...updatedData } : post
        )
      );

      if (onEditPost) {
        await onEditPost(postId, updatedData);
      }
      setEditingPostId(null);
      setEditingTitle('');
      setEditingContent('');
    } catch (error) {
      console.error("Error updating post:", error);
      if (error.response) {
        console.log("Error response data:", error.response.data);
        console.error("Server responded with", error.response.status);
      } else if (error.request) {
        console.error("No response received:", error.request);
      } else {
        console.error("Error setting up request:", error.message);
      }
      setVoteErrors(prev => ({
        ...prev,
        [postId]: error.message || "Failed to update post."
      }));
    }
  };

  const handleCancelEdit = () => {
    setEditingPostId(null);
    setEditingTitle('');
    setEditingContent('');
  };

  const isAuthor = (postAuthorId) => {
    return currentUserId && postAuthorId === currentUserId;
  };

  // ===== Report Modal Functions for Posts =====
  const openReportModal = (postId) => {
    setReportingPostId(postId);
    setIsReportModalOpen(true);
    setReportReason('');
    setReportError('');
  };

  const closeReportModal = () => {
    setIsReportModalOpen(false);
    setReportingPostId(null);
  };

  const handleSubmitReport = async () => {
    if (!reportReason.trim()) {
      setReportError("Please provide a reason for reporting this post.");
      return;
    }
    try {
      await axiosInstance.post(
        `${API_URL}/student/posts/${reportingPostId}/report`,
        { reason: reportReason }
      );
      closeReportModal();
      setReportSuccess(prev => ({ ...prev, [reportingPostId]: true }));
      setTimeout(() => {
        setReportSuccess(prev => ({ ...prev, [reportingPostId]: false }));
      }, 3000);
    } catch (error) {
      console.error("Failed to report post:", error);
      setReportError(error.response?.data?.message || "Failed to submit report.");
    }
  };

  return (
    <div className="w-full flex flex-col gap-8">
      {sortedPosts.length === 0 ? (
        <div className="bg-white rounded-xl shadow-md p-8 text-center">
          <p className="text-gray-500 text-lg">No posts available yet. Be the first to post!</p>
        </div>
      ) : (
        sortedPosts.map((post) => (
          <div
            key={post.id}
            className="w-full bg-white rounded-xl shadow-md overflow-hidden"
            style={{ padding: '60px', width: '58vw', direction: 'rtl', position: 'relative', paddingTop: '90px' }}
          >
            <div className="p-6 border-b border-gray-100 flex justify-between items-start">
              <div className="flex items-center gap-4" style={{ marginTop: '-55px' }}>
                <img
                  // **CRITICAL:** Ensure post.author_image is a full, valid URL from the backend.
                  src={post.author_image || "https://placehold.co/48x48/CCCCCC/333333?text=User"}
                  alt="User"
                  className="post-avatar"
                  style={{
                    width: "45px",
                    height: "45px",
                    borderRadius: "60%",
                    objectFit: "cover"
                  }}
                  onError={(e) => {
                    // Fallback if image fails to load
                    e.target.onerror = null;
                    e.target.src = "https://placehold.co/48x48/CCCCCC/333333?text=User";
                  }}
                />


                <div>
                  <p className="font-semibold text-gray-800">
                    {displayUserName(post.user)}
                    {post.community_id && (
                      <span className="ml-2 text-xs font-normal text-gray-500">
                        (Community {communityLabels[post.community_id] || post.community_id})
                      </span>
                    )}
                  </p>
                  <p className="text-xs text-gray-500 flex items-center">
                    {/* Updated Report Button with better spacing and color */}
                    <button
                      onClick={() => openReportModal(post.id)}
                      className="mr-4 cursor-pointer text-red-500 hover:text-red-600 focus:outline-none"
                    >
                      <GoReport size={23} />
                    </button>
                    {post.lastEdited && (
                      <span className="text-xs text-gray-400">
                        (Edited: {new Date(post.lastEdited).toLocaleString()})
                      </span>
                    )}
                  </p>
                  {reportSuccess[post.id] && (
                    <p className="text-green-500 mt-2">Reported successfully!</p>
                  )}
                </div>
              </div>
              {(isAuthor(post.userId || post.user?.uid) || (!post.userId && !post.user?.uid)) && (
                <div style={{ position: 'absolute', top: '10px', right: '10px' }}>
                  <button
                    onClick={(e) => { e.stopPropagation(); togglePostMenu(post.id); }}
                    className="p-2 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300"
                    aria-label="Post options"
                  >
                    <FaEllipsisV size={20} className="text-gray-600" />
                  </button>
                  {openPostMenu[post.id] && (
                    <div className="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20">
                      <button
                        onClick={() => handleEditPost(post)}
                        className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                      >
                        Edit
                      </button>
                      <button
                        onClick={() => handleDeletePost(post.id)}
                        className="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 hover:text-red-700"
                      >
                        Delete
                      </button>
                    </div>
                  )}
                </div>
              )}
            </div>

            <div className="flex">
              <div className="flex flex-col items-center gap-1 px-2 py-4" style={{ position: 'absolute', left: '2%', top: '2%' }}>
                <button
                  onClick={() => handleVote(post.id, 'up')}
                  disabled={!!userVotes[post.id]}
                  className={`p-1 rounded-full ${userVotes[post.id] === 'up' ? 'text-green-500' : 'text-gray-500 hover:text-green-500 disabled:text-gray-300 disabled:cursor-not-allowed'}`}
                  aria-label="Upvote"
                >
                  <LuArrowBigUp style={{ fontSize: '32px' }} />
                </button>
                <span className="text-sm font-medium text-gray-700">
                  {(post.positiveVotes || 0) - (post.negativeVotes || 0)}
                </span>
                <button
                  onClick={() => handleVote(post.id, 'down')}
                  disabled={!!userVotes[post.id]}
                  className={`p-1 rounded-full ${userVotes[post.id] === 'down' ? 'text-red-500' : 'text-gray-500 hover:text-red-500 disabled:text-gray-300 disabled:cursor-not-allowed'}`}
                  aria-label="Downvote"
                >
                  <LuArrowBigDown style={{ fontSize: '32px' }} />
                </button>
                {voteErrors[post.id] && (
                  <p className="text-red-500 text-xs mt-1 text-center max-w-[100px]">
                    {voteErrors[post.id]}
                  </p>
                )}
              </div>

              <div className="p-6 flex-grow" style={{ marginLeft: '60px' }}>
                {editingPostId === post.id ? (
                  <div className="w-full">
                    <input
                      type="text"
                      value={editingTitle}
                      onChange={(e) => setEditingTitle(e.target.value)}
                      className="w-full p-3 border border-gray-300 rounded-md mb-3 focus:ring-2 focus:ring-blue-500"
                      placeholder="Edit Title"
                    />
                    <textarea
                      value={editingContent}
                      onChange={(e) => setEditingContent(e.target.value)}
                      className="w-full p-3 border border-gray-300 rounded-md mb-3 focus:ring-2 focus:ring-blue-500"
                      placeholder="Edit Content"
                      rows={4}
                    />
                    <div className="flex gap-2">
                      <button
                        onClick={() => handleSaveEdit(post.id)}
                        className="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                      >
                        Save
                      </button>
                      <button
                        onClick={handleCancelEdit}
                        className="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                      >
                        Cancel
                      </button>
                    </div>
                  </div>
                ) : (
                  <div className="w-full">
                    <h3
                      className="font-bold text-xl text-gray-900 mb-2"
                      style={{ marginTop: '0px', width: 'calc(100% - 22px)', borderRadius: '10px', padding: '12px 0', direction: 'rtl', marginLeft: '0px' }}
                    >
                      {post.title}
                    </h3>
                    <p
                      className="text-gray-700 whitespace-pre-line"
                      style={{ width: 'calc(100% - 22px)', paddingLeft: '0px', direction: 'rtl', marginBottom: '20px' }}
                    >
                      {post.content}
                    </p>
                    {post.image_url && (
                      <div className="mt-4">
                        {/* **CRITICAL:** Ensure post.image_url is a full, valid URL from the backend. */}
                        <img
                          src={post.image_url}
                          alt={post.title || "Post image"}
                          className="w-full h-auto max-h-96 rounded-lg shadow-md object-contain"
                          onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = "https://placehold.co/600x400/CCCCCC/333333?text=Image+Not+Found";
                          }}
                        />
                      </div>
                    )}
                    <div className="mt-4 flex flex-wrap gap-2" style={{ marginTop: '20px', color: '#406ECB', textAlign: 'center' }}>
                      {post.tags && Array.isArray(post.tags) && post.tags.map((tag, index) => (
                        <span
                          key={index}
                          className="bg-blue-100 text-blue-800 text-xs rounded-full"
                          style={{
                            borderRadius: '12px',
                            padding: '8px',
                            width: '80px',
                            height: '50px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            //background: 'linear-gradient(to top right,rgb(167, 163, 255), #60a5fa)'

                          }}
                        >
                          {tag}
                        </span>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>

            <div className="px-6 pb-4 pt-2 border-t border-gray-100">
              <button
                onClick={() => toggleComments(post.id)}
                className="text-blue-600 hover:underline focus:outline-none text-sm font-medium"
                style={{ marginTop: '20px' }}
              >
                {openComments[post.id] ? "Hide Comments" : "View Comments"}
              </button>
            </div>
            {openComments[post.id] && (
              <div className="fixed inset-0 flex items-center justify-center z-50 bg-gray-100 bg-opacity-80 p-4">
                <div
                  className="bg-white rounded-lg shadow-xl p-6 z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                  onClick={(e) => e.stopPropagation()}
                >
                  <div className="flex justify-between items-center mb-4 pb-3"
                    style={{
                      borderBottom: '1px solid',                                   // make room for the border
                      borderImage: 'linear-gradient(to top right, #4f46e5, #60a5fa) 1'  // gradient + slice
                    }}
                  >
                    <GradientText
                      colors={["#4f46e5", "#60a5fa", "#60a5fa", "#60a5fa", "#60a5fa"]}
                      animationSpeed={3}
                      showBorder={false}
                      className="custom-class"
                    >
                      <h1 style={{ marginBottom: '10px' }}>Comments</h1>
                    </GradientText>
                    <button
                      onClick={() => closeModal(post.id)}
                      className="text-gray-500 hover:text-gray-700 text-2xl leading-none"
                      aria-label="Close comments"
                    >
                      &times;
                    </button>
                  </div>
                  <CommentSection
                    postId={post.id}
                    comments={post.comments || []}
                    showInput={showInput}
                    addComment={addComment}
                    onReply={handleReply}
                    currentUserId={currentUserId}
                  />
                </div>
              </div>
            )}
          </div>
        ))
      )}
      {/* Report Modal */}
      {isReportModalOpen && (
        <div className="fixed inset-0 flex items-center justify-center z-50 bg-white bg-opacity-80 p-8">
          <div className="bg-white rounded-xl shadow-lg p-6 w-full max-w-lg">
            <h2 className="text-2xl font-bold text-gray-800 mb-4">Report Post</h2>
            <p className="text-gray-600 mb-2">Please provide a reason for reporting this post:</p>
            <textarea
              value={reportReason}
              onChange={(e) => setReportReason(e.target.value)}
              placeholder="Enter your reason here..."
              className="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-400 transition duration-200 ease-in-out"
              rows={4}
            />
            {reportError && (
              <p className="text-sm text-red-500 mt-2">{reportError}</p>
            )}
            <div className="flex justify-end mt-6 space-x-3">
              <button
                onClick={closeReportModal}
                className="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition duration-150"
              >
                Cancel
              </button>
              <button
                onClick={handleSubmitReport}
                className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition duration-150"
              >
                Submit Report
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default Posts;


