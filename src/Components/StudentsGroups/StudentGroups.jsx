// StudentGroups.jsx
import React, { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import HeaderApp from '../HeaderApp/HeaderApp';
import Posts from '../Posts/Posts';
import { getFullImageUrl } from '../../api';

function StudentGroups() {
  const [posts, setPosts] = useState([]);
  const [allPosts, setAllPosts] = useState([]);
  const [userCommunities, setUserCommunities] = useState([]);
  const [activeCommunity, setActiveCommunity] = useState(1);
  const [loading, setLoading] = useState(true);
  const [availableTags, setAvailableTags] = useState([]);
  const [tags, setTags] = useState([]);
  const [error, setError] = useState(null);
  const [userSpecialization, setUserSpecialization] = useState(null);
  const [refreshNotifications, setRefreshNotifications] = useState(false);
  const [currentUserId, setCurrentUserId] = useState(null);
  const API_URL = import.meta.env.VITE_API_BASE_URL;

  const token = localStorage.getItem('token');

  const communityLabels = {
    1: 'Global',
    2: 'Software',
    3: 'Networking',
    4: 'AI'
  };

  const getCurrentUserId = useCallback(async () => {
    if (!token) {
        return null;
    }
    try {
        const response = await axios.get(`${API_URL}/user`, {
            headers: { Authorization: `Bearer ${token}` },
        });
        return response.data?.id || null;
    } catch (error) {
        console.error("Failed to get user ID:", error);
        return null;
    }
  }, [token, API_URL]);

  useEffect(() => {
    const fetchId = async () => {
      const id = await getCurrentUserId();
      setCurrentUserId(id);
    };
    fetchId();

    const storedSpec = localStorage.getItem('userSpecialization');
    if (storedSpec) {
      setUserSpecialization(storedSpec);
      const specLower = storedSpec.toLowerCase();
      const matchingEntry = Object.entries(communityLabels).find(
        ([id, label]) => label.toLowerCase() === specLower
      );
      const communities = [1];
      if (matchingEntry && Number(matchingEntry[0]) !== 1) {
        communities.push(Number(matchingEntry[0]));
      }
      setUserCommunities(communities);
      setActiveCommunity(1);
    }
  }, [getCurrentUserId]);

  const nestComments = (comments) => {
    if (!Array.isArray(comments)) return [];
    const map = {};
    const nested = [];
    comments.forEach(comment => {
      map[comment.id] = { ...comment, replies: comment.replies || [] };
    });
    comments.forEach(comment => {
      if (comment.parent_comment_id && map[comment.parent_comment_id]) {
        map[comment.parent_comment_id].replies.push(map[comment.id]);
      } else {
        nested.push(map[comment.id]);
      }
    });
    return nested;
  };

  const processPosts = useCallback((postArray) => {
    if (!Array.isArray(postArray)) return [];
    return postArray.map(post => {
      let postTagsArray = [];
      if (post.tags) {
        try {
          postTagsArray = typeof post.tags === 'string' ? JSON.parse(post.tags) : post.tags;
        } catch {
          postTagsArray = [post.tags.toString()];
        }
      } else if (post.subject) {
        postTagsArray = [post.subject.toString()];
      } else {
        postTagsArray = ['N/A'];
      }

      const photos = post.photos || [];
      let imageUrl = null;
      if (post.image_url) {
        imageUrl = getFullImageUrl(post.image_url);
      } else if (photos.length > 0) {
        imageUrl = getFullImageUrl(photos[0].photo);
      }

      // تحديد صورة المؤلف: استخدم post.user.image أولاً، ثم الصورة الافتراضية
      const authorProfileImage = (post.user && post.user.image)
        ? getFullImageUrl(post.user.image)
        : "https://placehold.co/48x48/CCCCCC/333333?text=User";

      const authorDisplayName = post.author_name || (post.user ? `${post.user.first_name} ${post.user.last_name}`.trim() : 'Anonymous');

      // --- DEBUG LOGS ---
      console.log("Processing post for StudentGroups:");
      console.log("  post.user:", post.user);
      console.log("  post.user.image:", post.user?.image);
      console.log("  authorProfileImage (after getFullImageUrl):", authorProfileImage);
      // --- END DEBUG LOGS ---

      return {
        ...post,
        community_id: post.community_id || 1,
        comments: nestComments(post.comments || []),
        tags: postTagsArray,
        user: post.user || { name: "Anonymous" },
        created_at: post.created_at || new Date().toISOString(),
        image_url: imageUrl,
        photos: photos.map(p => ({ ...p, photo: getFullImageUrl(p.photo) })),
        positiveVotes: post.positiveVotes !== undefined ? post.positiveVotes : 0,
        negativeVotes: post.negativeVotes !== undefined ? post.negativeVotes : 0,
        author_image: authorProfileImage,
        author: authorDisplayName,
      };
    });
  }, []);

  const fetchTags = useCallback(async () => {
    if (!token) return;
    try {
      const response = await axios.get(`${API_URL}/student/tags`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      const fetchedTags = response.data.tags || response.data || [];
      setAvailableTags(fetchedTags);
      if (fetchedTags.length > 0 && (!tags.length || !fetchedTags.includes(tags[0]))) {
        setTags([fetchedTags[0]]);
      }
    } catch (err) {
      console.error("Failed to fetch tags", err);
      setAvailableTags([]);
      setTags([]);
    }
  }, [token, tags, API_URL]);

  const fetchPosts = useCallback(async () => {
    if (!token) {
      setLoading(false);
      setError("Authentication token not found. Please log in.");
      setPosts([]);
      setAllPosts([]);
      return;
    }
    setLoading(true);
    setError(null);
    try {
      const response = await axios.get(`${API_URL}/student/post/get`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      const combinedRawPosts = [
        ...(response.data.posts_from_subscribed_communities || []),
        ...(response.data.user_own_posts || []),
      ];
      const processed = processPosts(combinedRawPosts);
      const uniquePosts = processed.filter((post, index, self) =>
        index === self.findIndex((p) => p.id === post.id)
      );
      const sortedPosts = uniquePosts.sort(
        (a, b) => new Date(b.created_at) - new Date(a.created_at)
      );
      setAllPosts(sortedPosts);
      updateFilteredPosts(sortedPosts, activeCommunity);
      setRefreshNotifications(prev => !prev);
    } catch (err) {
      setError(err.response?.data?.message || "Failed to load posts.");
      console.error("Fetch posts error", err);
      setPosts([]);
      setAllPosts([]);
    } finally {
      setLoading(false);
    }
  }, [token, processPosts, activeCommunity, API_URL]);

  const updateFilteredPosts = (posts, community) => {
    setPosts(posts.filter(post => post.community_id === community));
  };

  const handleVote = async (postId, voteType) => {
    if (!token) return;
    try {
      const response = await axios.post(
        `${API_URL}/student/VotePost`,
        { post_id: postId, vote: voteType },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      const updatedVotes = response.data.votes;
      const updateVotes = (prevPosts) =>
        prevPosts.map(post =>
          post.id === postId
            ? { ...post, positiveVotes: updatedVotes.positive, negativeVotes: updatedVotes.negative }
            : post
        );
      setPosts(updateVotes);
      setAllPosts(updateVotes);
    } catch (err) {
      console.error("Vote error", err);
      setError(err.response?.data?.message || err.message);
    }
  };

  const handleComment = async (postId, content, parentCommentId = null) => {
    if (!content.trim() || !token) return;
    try {
      const payload = { post_id: postId, content };
      if (parentCommentId) payload.parent_comment_id = parentCommentId;
      const response = await axios.post(
        `${API_URL}/student/AddComment`,
        payload,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      const newComment = {
        ...response.data.comment,
        content: response.data.comment.comment || content,
        user: response.data.user || { name: "You" },
        replies: [],
      };
      const updateComments = (comments, targetId) =>
        comments.map(comment => {
          if (comment.id === targetId) {
            return { ...comment, replies: [...(comment.replies || []), newComment] };
          } else if (comment.replies) {
            return { ...comment, replies: updateComments(comment.replies, targetId) };
          }
          return comment;
        });
      const updatePostComments = posts =>
        posts.map(post => {
          if (post.id === postId) {
            const updated = parentCommentId
              ? updateComments(post.comments || [], parentCommentId)
              : [...(post.comments || []), newComment];
            return { ...post, comments: updated };
          }
          return post;
        });
      setPosts(updatePostComments);
      setAllPosts(updatePostComments);
      setRefreshNotifications(prev => !prev);
    } catch (err) {
      console.error("Comment error", err);
      setError("Failed to add comment.");
    }
  };

  const handleSearch = (query) => {
    if (!query) {
      updateFilteredPosts(allPosts, activeCommunity);
      return;
    }
    const lowerCaseQuery = query.toLowerCase();
    const searchFilteredPosts = allPosts.filter(post => {
      const titleMatch = post.title?.toLowerCase().includes(lowerCaseQuery);
      const contentMatch = post.content?.toLowerCase().includes(lowerCaseQuery);
      const tagMatch = post.tags?.some(tag => tag.toLowerCase().includes(lowerCaseQuery));
      return titleMatch || contentMatch || tagMatch;
    });

    const sortedPosts = [...searchFilteredPosts].sort((a, b) => {
      const aMatchesQuery = a.title?.toLowerCase().includes(lowerCaseQuery) ||
        a.content?.toLowerCase().includes(lowerCaseQuery) ||
        a.tags.some(tag => tag.toLowerCase().includes(lowerCaseQuery));
      const bMatchesQuery = b.title?.toLowerCase().includes(lowerCaseQuery) ||
        b.content?.toLowerCase().includes(lowerCaseQuery) ||
        b.tags.some(tag => tag.toLowerCase().includes(lowerCaseQuery));

      if (aMatchesQuery && !bMatchesQuery) return -1;
      if (!aMatchesQuery && bMatchesQuery) return 1;
      return new Date(b.created_at) - new Date(a.created_at);
    });
    setPosts(sortedPosts.filter(post => post.community_id === activeCommunity));
  };

  const handleDeletePost = async () => {
    await fetchPosts();
    setRefreshNotifications(prev => !prev);
  };

  const handleEditPost = async () => {
    await fetchPosts();
    setRefreshNotifications(prev => !prev);
  };

  useEffect(() => {
    if (allPosts.length) updateFilteredPosts(allPosts, activeCommunity);
  }, [activeCommunity, allPosts]);

  useEffect(() => {
    if (token) {
      fetchPosts();
      fetchTags();
    } else {
      setPosts([]);
      setAllPosts([]);
      setLoading(false);
    }
  }, [token, fetchPosts, fetchTags]);

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col items-center">
      <HeaderApp
        onSearch={handleSearch}
        refreshNotifications={refreshNotifications}
      />
      <div className="w-full max-w-2xl px-4 py-8 flex flex-col items-start gap-6">
        {/* Community Filter */}
        <div className="flex flex-wrap gap-2 w-full">
          {userCommunities.map((communityId) => (
            <button
              style={{ padding: '10px', cursor: 'pointer' }}
              key={communityId}
              onClick={() => setActiveCommunity(communityId)}
              className={`px-3 py-1 rounded-full text-sm font-medium ${
                activeCommunity === communityId
                  ? 'bg-blue-400 text-white'
                  : 'text-gray-700 hover:bg-gray-200'
              }`}
            >
              {communityLabels[communityId] || `Community ${communityId}`}
            </button>
          ))}
        </div>
        {/* Error */}
        {error && (
          <div className="w-full bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {error}
          </div>
        )}
        {/* Posts */}
        {loading ? (
          <div className="w-full text-center text-gray-500">Loading posts...</div>
        ) : (
          <Posts
            posts={posts}
            showInput={true}
            onVote={handleVote}
            addComment={handleComment}
            onDeletePost={handleDeletePost}
            onEditPost={handleEditPost}
          />
        )}
      </div>
    </div>
  );
}

export default StudentGroups;
