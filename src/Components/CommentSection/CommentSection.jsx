import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { FaEllipsisV } from 'react-icons/fa';
import { LuArrowBigUp, LuArrowBigDown } from 'react-icons/lu';
import { GoReport } from "react-icons/go";

function CommentSection({ 
  postId, 
  comments: initialComments = [], 
  showInput = true, 
  addComment, 
  onVote, 
  onReply,
}) {
  const [localComments, setLocalComments] = useState([]);
  const [commentInput, setCommentInput] = useState('');
  const [votedComments, setVotedComments] = useState({});
  const [voteErrors, setVoteErrors] = useState({});
  const [replyInputs, setReplyInputs] = useState({});
  const [replyBoxVisible, setReplyBoxVisible] = useState({});
  const [openCommentMenu, setOpenCommentMenu] = useState({});
  const [editingCommentId, setEditingCommentId] = useState(null);
  const [editingCommentText, setEditingCommentText] = useState('');
  
  // Report modal state
  const [isReportModalOpen, setIsReportModalOpen] = useState(false);
  const [reportingCommentId, setReportingCommentId] = useState(null);
  const [reportReason, setReportReason] = useState('');
  const [reportError, setReportError] = useState('');
  const [reportSuccess, setReportSuccess] = useState({});
const storedProfileImage = localStorage.getItem("custom_profile_image") || 
                           "https://placehold.co/48x48/CCCCCC/333333?text=User";
  const token = localStorage.getItem('token');

  // Define dynamic API URL
const API_URL = import.meta.env.VITE_API_BASE_URL;

  useEffect(() => {
  const normalized = initialComments.map(comment => {
    console.log("Comment data:", comment);  // Debugging line added here.
    return { 
      ...comment, 
      content: comment.comment || comment.content,
    };
  });
  setLocalComments(normalized);

    const initialVotes = {};
    normalized.forEach(comment => {
      if (comment.user_vote) {
        initialVotes[comment.id] = comment.user_vote;
      }
    });
    setVotedComments(initialVotes);
  }, [initialComments]);

  const handleComment = async () => {
    if (!commentInput.trim()) return;
    try {
      await addComment(postId, commentInput);
      setCommentInput('');
    } catch (err) {
      console.error('Failed to add comment', err);
    }
  };

  const handleCommentVote = async (commentId, voteType) => {
    if (!token) {
      console.error('No authentication token found. Please log in.');
      return;
    }
    const currentVote = votedComments[commentId];

    // Remove vote if already same vote type
    
    if (currentVote === voteType) {
      try {
        await axios.post(
          `${API_URL}/student/VoteComment`,
          { comment_id: commentId, vote: "none" },
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
            },
          }
        );
        setVotedComments(prev => ({ ...prev, [commentId]: null }));
      } catch (error) {
        console.error('Error while removing vote:', error, error.response?.data);
      }
      return;
    }

    try {
      await axios.post(
        `${API_URL}/student/VoteComment`,
        { comment_id: commentId, vote: voteType },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
          },
        }
      );
      setVotedComments(prev => ({ ...prev, [commentId]: voteType }));
      setLocalComments(prevComments =>
        prevComments.map(comment => {
          if (comment.id === commentId) {
            let newPositive = comment.positive_votes || 0;
            let newNegative = comment.negative_votes || 0;
            if (currentVote === 'up') newPositive -= 1;
            else if (currentVote === 'down') newNegative -= 1;
            if (voteType === 'up') newPositive += 1;
            else if (voteType === 'down') newNegative += 1;
            return {
              ...comment,
              positive_votes: newPositive,
              negative_votes: newNegative,
            };
          }
          return comment;
        })
      );
    } catch (error) {
      console.error('Error while voting on comment:', error, error.response?.data);
      setVotedComments(prev => ({ ...prev, [commentId]: currentVote }));
    }
  };

  const toggleReplyBox = (commentId) => {
    setReplyBoxVisible(prev => ({ ...prev, [commentId]: !prev[commentId] }));
  };

  const handleReplyInputChange = (commentId, value) => {
    setReplyInputs(prev => ({ ...prev, [commentId]: value }));
  };

  const handleReplySubmit = async (commentId) => {
    const replyText = replyInputs[commentId];
    if (!replyText?.trim()) return;
    try {
      await onReply(postId, commentId, replyText);
      setReplyInputs(prev => ({ ...prev, [commentId]: '' }));
      setReplyBoxVisible(prev => ({ ...prev, [commentId]: false }));
    } catch (err) {
      console.error('Failed to submit reply', err);
    }
  };

  

const displayUserName = (user) => {
  // Try to get current user from localStorage as fallback
  const currentUser = (() => {
    try {
      return JSON.parse(localStorage.getItem("user")) || {};
    } catch (e) {
      return {};
    }
  })();

  // If no user is passed, use the current user's data or fallback to 'Anonymous'.
  if (!user) {
    if (currentUser && Object.keys(currentUser).length > 0) {
      return currentUser.displayName ||
             (currentUser.first_name ? currentUser.first_name + (currentUser.last_name ? ` ${currentUser.last_name}` : '') : '') ||
             `User#${currentUser.id}`;
    }
    return 'Anonymous';
  }

  // If the user is an object, try various properties:
  if (typeof user === "object") {
    if (user.displayName && user.displayName.trim() !== "") return user.displayName;
    
    // Use first_name regardless of last_name existence.
    if (user.first_name) return user.first_name + (user.last_name ? ` ${user.last_name}` : '');
    
    if (user.username && user.username.trim() !== "") return user.username;
    if (user.name && user.name.trim() !== "") return user.name;
    
    // Fallback to "User#<id>"
    return `User#${user.id || user.uid || 'Unknown'}`;
  }
  
  // If user is not an object (e.g., just an ID), return a fallback
  return `User#${user}`;
};


  const toggleCommentMenu = (commentId) => {
    setOpenCommentMenu(prev => ({ ...prev, [commentId]: !prev[commentId] }));
  };

  const handleDeleteComment = async (commentId) => {
    try {
      await axios.delete(`${API_URL}/student/comment/${commentId}/delete`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
      });
      setLocalComments(prevComments => prevComments.filter(comment => comment.id !== commentId));
      setOpenCommentMenu(prev => ({ ...prev, [commentId]: false }));
    } catch (error) {
      console.error('Error deleting comment:', error);
    }
  };

  const handleEditComment = (comment) => {
    setEditingCommentId(comment.id);
    setEditingCommentText(comment.content);
    setOpenCommentMenu(prev => ({ ...prev, [comment.id]: false }));
  };

  const handleSaveEditedComment = async (commentId) => {
    try {
      await axios.post(
        `${API_URL}/student/comment/${commentId}/update`,
        { content: editingCommentText },
        {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${token}`,
          },
        }
      );
      setLocalComments(prevComments =>
        prevComments.map(comment => {
          if (comment.id === commentId) {
            return { 
              ...comment, 
              content: editingCommentText, 
              lastEdited: new Date().toISOString() 
            };
          }
          return comment;
        })
      );
      setEditingCommentId(null);
      setEditingCommentText('');
    } catch (error) {
      console.error('Error updating comment:', error);
    }
  };

  const handleCancelEditComment = () => {
    setEditingCommentId(null);
    setEditingCommentText('');
  };

  // Functions to handle reporting a comment
  const openReportModal = (commentId) => {
    setReportingCommentId(commentId);
    setIsReportModalOpen(true);
    setReportReason('');
    setReportError('');
  };

  const closeReportModal = () => {
    setIsReportModalOpen(false);
    setReportingCommentId(null);
  };
  
  const handleSubmitReport = async () => {
    if (!reportReason.trim()) {
      setReportError("يرجى تقديم سبب للإبلاغ.");
      return;
    }
    if (!reportingCommentId) return;
    try {
      await axios.post(
        `${API_URL}/student/comment/${reportingCommentId}/report`,
        { reason: reportReason },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      closeReportModal();
      setReportSuccess({ ...reportSuccess, [reportingCommentId]: true });
      setTimeout(() => {
        setReportSuccess(prev => ({ ...prev, [reportingCommentId]: false }));
      }, 4000);
    } catch (error) {
      console.error("Failed to report comment:", error);
      setReportError(error.response?.data?.message || "فشل إرسال الإبلاغ.");
    }
  };
  return (
    <>
      <div className="bg-gray-50 p-6 border-t border-gray-100" style={{ margin: '35px 25px 25px 15px', borderRadius: '11px', width: '90%' }}>
        <div className="flex flex-col gap-4">
          {localComments.map((comment) => (

            <div key={comment.id} className="flex gap-3" style={{ position: 'relative' }}>

        <img
  src={storedProfileImage}
  alt="User"
  className="post-avatar"
  style={{
    width: "45px",
    height: "45px",
    borderRadius: "60%",
    objectFit: "cover"
  }}
  onError={(e) => {
    e.target.src = "https://randomuser.me/api/portraits/women/1.jpg";
  }}
/>
              <div className="bg-white p-3 rounded-lg shadow-sm flex-1 relative" style={{paddingRight:"8px"}}>
                <div className="flex justify-between items-start">
                  <p className="font-medium text-sm text-gray-800" >{displayUserName(comment.user)}</p>
                  <div className="flex items-center gap-1">
                    {/* --- تعديل: زر الإبلاغ أصبح يعمل الآن --- */}
                    <button onClick={() => openReportModal(comment.id)} className="text-gray-400 hover:text-red-500 p-1">
                      <GoReport size={23} />
                    </button>
                    <button
                      onClick={() => handleCommentVote(comment.id, 'up')}
                      className={`p-1 ${votedComments[comment.id] === 'up' ? 'text-green-500' : 'text-gray-500 hover:text-green-500'}`}
                    >
                      <LuArrowBigUp size={25} />
                    </button>
                    <span className="text-xs font-medium text-gray-600 mx-1">
                      {(comment.positive_votes || 0) - (comment.negative_votes || 0)}
                    </span>
                    <button
                      onClick={() => handleCommentVote(comment.id, 'down')}
                      className={`p-1 ${votedComments[comment.id] === 'down' ? 'text-red-500' : 'text-gray-500 hover:text-red-500'}`}
                    >
                      <LuArrowBigDown size={25} />
                    </button>
                  </div>
                </div>

                 {/* --- إضافة جديدة: رسالة نجاح الإبلاغ --- */}
                 {reportSuccess[comment.id] && (
                    <p className="text-green-500 text-xs mt-1">تم الإبلاغ بنجاح.</p>
                )}

                <div style={{ position: 'absolute', top: '28px', left: '38px' }}>
                  <button 
                    onClick={() => toggleCommentMenu(comment.id)}
                    style={{ background: 'none', border: 'none', cursor: 'pointer' }}
                  >
                    <FaEllipsisV size={16} style={{ position: 'absolute', top: '10px', left: '-35px' }} />
                  </button>
                  {openCommentMenu[comment.id] && (
                    <div style={{
                      position: 'absolute', top: '20px', right: 0,
                      background: '#fff', border: '1px solid #ccc', borderRadius: '4px',
                      zIndex: 10, boxShadow: '0px 2px 4px rgba(0,0,0,0.1)',
                    }}>
                      <button 
                        onClick={() => handleEditComment(comment)}
                        style={{ padding: '6px 10px', display: 'block', width: '100%', background: 'none', border: 'none', textAlign: 'left', cursor: 'pointer' }}
                      >
                        Edit
                      </button>
                      <button 
                        onClick={() => handleDeleteComment(comment.id)}
                        style={{ padding: '6px 10px', display: 'block', width: '100%', background: 'none', border: 'none', textAlign: 'left', cursor: 'pointer' }}
                      >
                        Delete
                      </button>
                    </div>
                  )}
                </div>

                {editingCommentId === comment.id ? (
                  <div className="mt-2">
                    <input 
                      type="text"
                      value={editingCommentText}
                      onChange={(e) => setEditingCommentText(e.target.value)}
                      className="w-full p-2 border border-gray-300 rounded" style={{outline:'none'}}
                    />
                    <div className="mt-1">
                      <button 
                        onClick={() => handleSaveEditedComment(comment.id)}
                        className="mr-2 px-3 py-1 bg-blue-500 text-white rounded"
                      >
                        Save
                      </button>
                      <button 
                        onClick={handleCancelEditComment}
                        className="px-3 py-1 bg-gray-300 text-black rounded"
                      >
                        Cancel
                      </button>
                    </div>
                  </div>
                ) : (
                  <p className="text-gray-700 mt-1 text-right">
                    {comment.content}
                    {comment.lastEdited && (
                      <span className="text-xs text-gray-400 block">
                        Last Edited: {new Date(comment.lastEdited).toLocaleString()}
                      </span>
                    )}
                  </p>
                )}

                <p className="text-xs text-gray-400 mt-2">
                  {comment.created_at ? new Date(comment.created_at).toLocaleTimeString() : 'No date'}
                </p>

                {voteErrors[comment.id] && (
                  <p className="text-red-500 text-xs mt-1 text-center">
                    {voteErrors[comment.id]}
                  </p>
                )}

                <div className="mt-2">
                  <button
                    onClick={() => toggleReplyBox(comment.id)}
                    className="text-sm text-blue-500 hover:underline"
                  >
                    Reply
                  </button>
                </div>

                {replyBoxVisible[comment.id] && (
                  <div className="mt-2 flex items-center gap-2">
                    <input
                      type="text"
                      placeholder="Write a reply..."
                      value={replyInputs[comment.id] || ''}
                      onChange={(e) => handleReplyInputChange(comment.id, e.target.value)}
                      className="w-full pl-4 pr-2 py-2 border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <button
                      onClick={() => handleReplySubmit(comment.id)}
                      className="px-3 py-1 bg-blue-500 text-white rounded"
                    >
                      Submit
                    </button>
                  </div>
                )}

                {comment.replies && comment.replies.length > 0 && (
                  <div className="ml-8 mt-2 border-l pl-4">
                    {comment.replies.map((reply) => (
                      
                      <div key={reply.id} className="mb-2">
                        <p className="font-medium text-sm text-gray-800">{displayUserName(reply.user)}</p>
                        <p className="text-gray-700 text-right">
                          {reply.content}
                        </p>
                        <p className="text-xs text-gray-400 mt-1">
                          {reply.created_at ? new Date(reply.created_at).toLocaleTimeString() : 'No date'}
                        </p>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          ))}

          {showInput && (
            <div className="flex items-center gap-2 mt-4" style={{ padding: '0px 5px 15px 10px' }}>
            <img
  src={storedProfileImage}
  alt="User"
  className="post-avatar"
  style={{
    width: "45px",
    height: "45px",
    borderRadius: "60%",
    objectFit: "cover"
  }}
  onError={(e) => {
    e.target.src = "https://randomuser.me/api/portraits/women/1.jpg";
  }}
/>
              <div className="relative flex-1" style={{ padding: '18px' }}>
                <input
                  type="text"
                  value={commentInput}
                  onChange={(e) => setCommentInput(e.target.value)}
                  onKeyPress={(e) => e.key === 'Enter' && handleComment()}
                  placeholder="Add a comment..."
                  className="w-full pl-4 pr-12 py-3 border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white"
                  style={{ padding: '10px', marginTop: '23px', borderRadius: '11px', paddingLeft: '25px' }}
                />
                <button
                  onClick={handleComment}
                  className="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-white rounded-full"
                  style={{ marginRight: '25px', marginTop: '10px', fontSize: '82px', color: 'blue' }}
                >
                  {/* <IoSend size={16} /> */}
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
       {/* --- إضافة جديدة: نافذة الإبلاغ عن تعليق --- */}
       {isReportModalOpen && (
          <div className="fixed inset-0 flex items-center justify-center z-50 bg-white bg-opacity-80 p-8">
          <div className="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h2 className="text-xl font-semibold text-gray-800 mb-4">الإبلاغ عن تعليق</h2>
            <p className="text-sm text-gray-600 mb-4">لماذا تقوم بالإبلاغ عن هذا التعليق؟</p>
            <textarea
              value={reportReason}
              onChange={(e) => setReportReason(e.target.value)}
              className="w-full p-3 border border-gray-300 rounded-md mb-2 focus:ring-2 focus:ring-blue-500"
              placeholder="مثال: محتوى غير لائق، بريد مزعج، ..."
              rows={4}
            />
            {reportError && (
              <p className="text-red-500 text-sm mb-4">{reportError}</p>
            )}
            <div className="flex justify-end gap-4">
              <button onClick={closeReportModal} className="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                إلغاء
              </button>
              <button onClick={handleSubmitReport} className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                إرسال الإبلاغ
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}

export default CommentSection;