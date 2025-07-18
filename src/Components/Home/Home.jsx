import React, { useState, useEffect, useCallback } from 'react';
import api, { getFullImageUrl } from '../../api'; // Adjust the import path as needed
import HeaderApp from '../HeaderApp/HeaderApp';
import Posts from '../Posts/Posts';
import axios from 'axios';
import GradientText from '../gradient/GradientText';

function Home() {
    const [newPostContent, setNewPostContent] = useState('');
    const [title, setTitle] = useState('');
    const [tags, setTags] = useState([]);
    const [availableTags, setAvailableTags] = useState([]);
    const [userCommunities, setUserCommunities] = useState([1, 2, 3, 4]);
    const [postingCommunity, setPostingCommunity] = useState(1);
    const [previewImage, setPreviewImage] = useState(null);
    const [imageFile, setImageFile] = useState(null);
    const [showPostModal, setShowPostModal] = useState(false);
    const [posts, setPosts] = useState([]);
    const [filteredPosts, setFilteredPosts] = useState([]);
    const [refreshNotifications, setRefreshNotifications] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState(null);
    const [userSpecialization, setUserSpecialization] = useState(null);
    const token = localStorage.getItem('token');
    const communityLabels = {
        1: 'Global',
        2: 'SoftWare',
        3: 'Networking',
        4: 'Ai'
    };

    const API_URL = import.meta.env.VITE_API_BASE_URL;

    useEffect(() => {
        const storedSpec = localStorage.getItem('userSpecialization');
        if (storedSpec) {
            setUserSpecialization(storedSpec);
        }
    }, []);

    const allowedCommunityIds = [1];
    if (userSpecialization) {
        const matchingEntry = Object.entries(communityLabels).find(
            ([id, label]) => label.toLowerCase() === userSpecialization.toLowerCase()
        );
        if (matchingEntry && Number(matchingEntry[0]) !== 1) {
            allowedCommunityIds.push(Number(matchingEntry[0]));
        }
    }

    const getCurrentUserId = useCallback(async () => {
        if (!token) {
            return null;
        }
        try {
            const response = await api.get('/user', {
                headers: { Authorization: `Bearer ${token}` },
            });
            return response.data?.id || null;
        } catch (error) {
            console.error("Failed to get user ID:", error);
            return null;
        }
    }, [token]);

    const [currentUserId, setCurrentUserId] = useState(null);

    useEffect(() => {
        const fetchUserId = async () => {
            const id = await getCurrentUserId();
            setCurrentUserId(id);
        };
        fetchUserId();
    }, [token, getCurrentUserId]);

    const fetchTags = useCallback(async () => {
        if (!token) return;
        try {
            const response = await api.get(`${API_URL}/student/tags`, {
                headers: { Authorization: `Bearer ${token}` },
            });
            const fetchedTags = response.data.tags || response.data || [];
            setAvailableTags(fetchedTags);

            if (fetchedTags.length > 0) {
                const currentSelectedTagIsValid = tags.length > 0 && fetchedTags.some(ft =>
                    (typeof ft === 'string' ? ft.toLowerCase() : ft.name?.toLowerCase()) ===
                    (typeof tags[0] === 'string' ? tags[0]?.toLowerCase() : tags[0]?.name?.toLowerCase())
                );
                if (!currentSelectedTagIsValid) {
                    setTags([fetchedTags[0]]);
                }
            } else {
                setTags([]);
            }
        } catch (err) {
            console.error("Failed to fetch tags", err);
            setAvailableTags([]);
            setTags([]);
        }
    }, [token, tags, API_URL]);

    useEffect(() => {
        if (availableTags.length > 0) {
            if (tags.length === 0) {
                setTags([availableTags[0]]);
            } else {
                const currentTagValue = typeof tags[0] === 'string' ? tags[0] : tags[0]?.name;
                const currentTagLower = currentTagValue?.toLowerCase();

                const matchedTag = availableTags.find(tag =>
                    (typeof tag === 'string' ? tag.toLowerCase() : tag.name?.toLowerCase()) === currentTagLower
                );

                if (!matchedTag) {
                    setTags([availableTags[0]]);
                } else if ((typeof matchedTag === 'string' ? matchedTag : matchedTag.name) !== currentTagValue) {
                    setTags([matchedTag]);
                }
            }
        } else {
            if (tags.length > 0) {
                setTags([]);
            }
        }
    }, [availableTags, tags]);

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
                } catch (e) {
                    postTagsArray = [post.tags.toString()];
                }
            } else if (post.subject) {
                postTagsArray = [post.subject.toString()];
            } else {
                postTagsArray = ['N/A'];
            }
            if (!Array.isArray(postTagsArray)) {
                postTagsArray = [postTagsArray.toString()];
            }

            const photos = post.photos || [];
            let imageUrl = null;
            if (post.image_url && post.image_url.startsWith('blob:')) {
                imageUrl = post.image_url;
            } else if (photos.length > 0) {
                imageUrl = getFullImageUrl(photos[0].photo);
            }

            // تحديد صورة المؤلف: استخدم post.user.image أولاً، ثم الصورة الافتراضية
            const authorProfileImage = (post.user && post.user.image)
                ? getFullImageUrl(post.user.image)
                : "https://placehold.co/48x48/CCCCCC/333333?text=User";

            const authorDisplayName = post.author_name || (post.user ? `${post.user.first_name} ${post.user.last_name}`.trim() : 'Anonymous');

            // --- DEBUG LOGS ---
            console.log("Processing post for Home:");
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
                author_image: authorProfileImage,
                author: authorDisplayName,
                created_at: post.created_at || new Date().toISOString(),
                image_url: imageUrl,
                photos: photos,
                positiveVotes: post.positiveVotes !== undefined ? post.positiveVotes : 0,
                negativeVotes: post.negativeVotes !== undefined ? post.negativeVotes : 0,
            };
        });
    }, []);

    const fetchPosts = useCallback(async () => {
        if (!token) {
            setIsLoading(false);
            setError("Authentication token not found. Please log in.");
            setPosts([]);
            setFilteredPosts([]);
            return;
        }
        setIsLoading(true);
        setError(null);
        try {
            const response = await api.get(`${API_URL}/student/post/get`, {
                headers: { Authorization: `Bearer ${token}` },
            });

            const combinedRawPosts = Array.isArray(response.data)
                ? response.data
                : [
                    ...(response.data.posts_from_subscribed_communities || []),
                    ...(response.data.user_own_posts || []),
                ];

            const processed = processPosts(combinedRawPosts);

            const uniquePosts = processed.filter((post, index, self) =>
                index === self.findIndex((p) => p.id === post.id)
            );

            const sortedPosts = uniquePosts.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            setPosts(sortedPosts);
            setFilteredPosts(sortedPosts);
        } catch (err) {
            setError(err.response?.data?.message || "Failed to load posts. Please try again later.");
            console.error("Failed to fetch posts", err);
            setPosts([]);
            setFilteredPosts([]);
        } finally {
            setIsLoading(false);
        }
    }, [token, processPosts, API_URL]);

    const handleImageChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setPreviewImage(URL.createObjectURL(file));
            setImageFile(file);
        } else {
            setPreviewImage(null);
            setImageFile(null);
        }
    };

    const handlePost = async () => {
        if (!title || !newPostContent) {
            setError("Title and content are required.");
            return;
        }
        if (!token) {
            setError("Authentication token not found. Please log in.");
            return;
        }
        if (availableTags.length > 0 && tags.length === 0) {
            setError("Please select a tag for your post.");
            return;
        }

        const currentTagValue = typeof tags[0] === 'string' ? tags[0] : tags[0]?.name;
        const normalizedTagsForSending = tags.map(tag =>
            typeof tag === 'string' ? tag.toLowerCase() : tag.name?.toLowerCase()
        ).filter(Boolean);
        if (availableTags.length > 0) {
            const allowedLowercase = availableTags.map(tag => (typeof tag === 'string' ? tag.toLowerCase() : tag.name?.toLowerCase()));
            const isValid = normalizedTagsForSending.every(tag => allowedLowercase.includes(tag));
            if (!isValid && normalizedTagsForSending.length > 0) {
                setError("The selected tag is not valid. Please choose from the list.");
                return;
            }
        }
        setError(null);

        try {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('content', newPostContent);
            formData.append('tags', JSON.stringify(normalizedTagsForSending));
            formData.append('typePost', "Ask");
            formData.append('community_id', postingCommunity.toString());
            if (imageFile) {
                formData.append('images', imageFile);
            }

            const response = await api.post(
                `${API_URL}/student/post/Add`,
                formData,
                {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        'Content-Type': 'multipart/form-data',
                    },
                }
            );

            const newPostFromServer = response.data?.post;
            let newPostEntry = processPosts([newPostFromServer])[0];

            setPosts(prevPosts => [newPostEntry, ...prevPosts].sort((a, b) => new Date(b.created_at) - new Date(a.created_at)));
            setFilteredPosts(prevFilteredPosts => [newPostEntry, ...prevFilteredPosts].sort((a, b) => new Date(b.created_at) - new Date(a.created_at)));

            setTitle('');
            setNewPostContent('');
            setTags(availableTags.length > 0 ? [availableTags[0]] : []);
            setPreviewImage(null);
            setImageFile(null);
            setShowPostModal(false);
            setRefreshNotifications(prev => !prev);

        } catch (err) {
            setError(err.response?.data?.message || "Failed to publish post. Please try again.");
            console.error("Failed to publish post", err.response || err);
        }
    };

    const handleVote = async (postId, voteType) => {
        if (!token) return;
        try {
            const response = await api.post(
                `${API_URL}/student/VotePost`,
                { post_id: postId, vote: voteType },
                { headers: { Authorization: `Bearer ${token}` } }
            );

            const updatedVotes = response.data.votes;

            const updatePostVotes = (prevPosts) => prevPosts.map(post =>
                post.id === postId
                    ? {
                        ...post,
                        positiveVotes: updatedVotes.positive,
                        negativeVotes: updatedVotes.negative,
                    }
                    : post
            );
            setPosts(updatePostVotes);
            setFilteredPosts(updatePostVotes);

        } catch (err) {
            setError(err.response?.data?.message || err.message);
            console.error("Failed to vote", err);
        }
    };

    const addComment = async (postId, commentContent, parentCommentId = null) => {
        if (!commentContent.trim() || !token) return;
        try {
            await api.post(
                `${API_URL}/student/AddComment`,
                { post_id: postId, content: commentContent, ...(parentCommentId && { parent_comment_id: parentCommentId }) },
                { headers: { Authorization: `Bearer ${token}`, } }
            );

            fetchPosts();
            setRefreshNotifications(prev => !prev);

        } catch (err) {
            setError(err.response?.data?.message || "Failed to add comment.");
            console.error("Failed to add comment", err);
        }
    };

    useEffect(() => {
        if (token) {
            fetchPosts();
            fetchTags();
        } else {
            setPosts([]);
            setFilteredPosts([]);
            setIsLoading(false);
        }
    }, [token, fetchPosts, fetchTags]);

    useEffect(() => {
        if (!token) return () => { };

        const notificationPolling = setInterval(() => {
            fetchPosts();
        }, 30000);
        return () => clearInterval(notificationPolling);
    }, [token, fetchPosts]);

    const handleSearch = (query) => {
        if (!query) {
            setFilteredPosts(posts);
            return;
        }
        const lowerCaseQuery = query.toLowerCase();
        const searchFilteredPosts = posts.filter(post => {
            const titleMatch = post.title?.toLowerCase().includes(lowerCaseQuery);
            const contentMatch = post.content?.toLowerCase().includes(lowerCaseQuery);
            const tagMatch = post.tags?.some(tag => tag.toLowerCase().includes(lowerCaseQuery));
            return titleMatch || contentMatch || tagMatch;
        });

        const sortedPosts = [...searchFilteredPosts].sort((a, b) => {
            const aMatchesQuery = a.title?.toLowerCase().includes(lowerCaseQuery) || a.content?.toLowerCase().includes(lowerCaseQuery) || a.tags.some(tag => tag.toLowerCase().includes(lowerCaseQuery));
            const bMatchesQuery = b.title?.toLowerCase().includes(lowerCaseQuery) || b.content?.toLowerCase().includes(lowerCaseQuery) || b.tags.some(tag => tag.toLowerCase().includes(lowerCaseQuery));

            if (aMatchesQuery && !bMatchesQuery) return -1;
            if (!aMatchesQuery && bMatchesQuery) return 1;
            return new Date(b.created_at) - new Date(a.created_at);
        });
        setFilteredPosts(sortedPosts);
    };

    const handleDeletePost = (postId) => {
        const filterFunc = (prevPosts) => prevPosts.filter(p => p.id !== postId);
        setPosts(filterFunc);
        setFilteredPosts(filterFunc);
    };

    const handleEditPost = (postId, updatedData) => {
        const updateFunc = (prevPosts) => prevPosts.map(p =>
            p.id === postId ? { ...p, ...updatedData } : p
        );
        setPosts(updateFunc);
        setFilteredPosts(updateFunc);
    };

    return (
        <div className="min-h-screen bg-gray-50 flex flex-col items-center">
            <HeaderApp onSearch={handleSearch} refreshNotifications={refreshNotifications} />

            {error && (
                <div className="w-full max-w-2xl p-3 my-2 bg-red-100 text-red-700 rounded shadow-md text-center fixed top-16 left-1/2 transform -translate-x-1/2 z-20">
                    {error}
                </div>
            )}

            <div className="w-full max-w-2xl px-4 py-8 flex flex-col items-center gap-8 mt-16">
                {isLoading && posts.length === 0 && !error && (
                    <div className="w-full max-w-2xl p-3 my-2 text-blue-700 text-center">Loading posts...</div>
                )}

                {!showPostModal && token && (
                    <div
                        className="w-full max-w-xl bg-gray rounded-xl shadow-md p-6 cursor-pointer hover:bg-gray-50 transition-colors mx-auto mt-4" style={{marginLeft:"150px" , marginTop:"25px"}}
                        onClick={() => { setError(null); setShowPostModal(true); }}
                    >
                     <input
                            type="text"
                            placeholder="Click to create a new post..."
                            readOnly
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-lg font-medium bg-gray-100 cursor-pointer"
                       style={{padding :"20px"}} />
                    </div>
                )}

                  {showPostModal && (
                                    <div className="fixed inset-0 flex items-center justify-center z-50 bg-white p-4" >
                                        <div className="w-full max-w-lg max-h-[90vh] overflow-y-auto">
                                            <div className="flex justify-between items-center mb-4">
                                              <GradientText 
                  colors={["#4f46e5", "#60a5fa", "#60a5fa", "#60a5fa", "#60a5fa"]}
                  animationSpeed={3}
                  showBorder={false}
                  className="custom-class"
                >
                   <h1 style={{marginTop:'10px'}}>Create a new post</h1>  
                </GradientText>
                                <button
                                    onClick={() => setShowPostModal(false)}
                                    className="text-gray-500 hover:text-gray-700 text-2xl leading-none absolute top-35 right-127"
                                    aria-label="Close modal"
                                >&times;</button>
                            </div>
                            <div className="flex flex-col gap-4">
                                <select
                                    value={postingCommunity}
                                    onChange={(e) => setPostingCommunity(Number(e.target.value))}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-indigo-700"
                                >
                                    {userCommunities
                                        .filter((id) => allowedCommunityIds.includes(id))
                                        .map((id) => (
                                            <option key={id} value={id}>
                                                {communityLabels[id]}
                                            </option>
                                        ))}
                                </select>
                                <input
                                    type="text"
                                    placeholder="Title"
                                    value={title}
                                    onChange={e => setTitle(e.target.value)}
                                    className={`
                                        w-full px-4 py-2 border border-gray-300 rounded-lg
                                        focus:outline-none focus:ring-2 focus:ring-blue-500
                                        text-lg font-medium
                                        text-black
                                        placeholder:text-transparent
                                        placeholder:bg-clip-text
                                        placeholder:bg-gradient-to-tr
                                        placeholder:from-[#4f46e5]
                                        placeholder:to-[#60a5fa]
                                    `}
                                />
                                <textarea
                                    placeholder="What's on your mind?"
                                    value={newPostContent}
                                    onChange={(e) => setNewPostContent(e.target.value)}
                                    rows="4"
                                    className={`
                                        w-full px-4 py-2 border border-gray-300 rounded-lg
                                        focus:outline-none focus:ring-2 focus:ring-blue-500
                                        text-lg font-medium
                                        text-black
                                        placeholder:bg-gradient-to-tr
                                        placeholder:from-[#4f46e5]
                                        placeholder:to-[#60a5fa]
                                        placeholder:bg-clip-text
                                        placeholder:text-transparent
                                    `}
                                    style={{ padding: '8px' }}
                                />
                                <div className="flex flex-col gap-2">
                                    <label className="text-sm font-semibold text-gray-700 text-blue-500">Select Tags:</label>
                                    {availableTags.length === 0 && (
                                        <p className="text-gray-500 text-sm">{isLoading ? "Loading tags..." : "No tags available"}</p>
                                    )}
                                    {availableTags.map(tagObject => {
                                        const tagName = typeof tagObject === 'string' ? tagObject : tagObject.name;
                                        const isChecked = tags.some(t => (typeof t === 'string' ? t : t.name) === tagName);
                                        return (
                                            <label key={tagName} className="inline-flex items-center space-x-2 text-sm text-gray-800">
                                                <input
                                                    type="checkbox"
                                                    checked={isChecked}
                                                    onChange={(e) => {
                                                        if (e.target.checked) {
                                                            setTags(prev => [...prev, tagObject]);
                                                        } else {
                                                            setTags(prev => prev.filter(t => (typeof t === 'string' ? t : t.name) !== tagName));
                                                        }
                                                    }}
                                                    className="form-checkbox text-blue-600"
                                                />
                                                <span className="text-indigo-700">{tagName}</span>
                                            </label>
                                        );
                                    })}
                                </div>
                                <label className="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700 hover:text-blue-600 text-blue-500">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    Upload Image
                                    <input
                                        type="file"
                                        accept="image/*"
                                        onChange={handleImageChange}
                                        className="hidden"
                                    />
                                </label>
                                {previewImage && (
                                    <div className="mt-2">
                                        <img
                                            src={previewImage}
                                            alt="Preview"
                                            className="w-auto h-40 object-cover rounded-lg border-2 border-gray-300"
                                        />
                                        <button
                                            onClick={() => { setPreviewImage(null); setImageFile(null); }}
                                            className="mt-1 text-xs text-red-500 hover:text-red-700"
                                        >Remove image</button>
                                    </div>
                                )}
                                <button
                                    onClick={handlePost}
                                    className="w-full sm:w-auto px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 flex items-center justify-center gap-2 transition-colors"
                                    disabled={isLoading}
                                    style={{ background: 'linear-gradient(to top right,rgb(90, 83, 234), #60a5fa) ' }}
                                >
                                    <span className="font-medium">Post</span>
                                    {isLoading && <svg className="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>}
                                </button>
                            </div>
                        </div>
                    </div>
                )}

                {token ? (
                    <Posts
                        posts={filteredPosts}
                        onVote={handleVote}
                        addComment={addComment}
                        isLoading={isLoading && posts.length > 0}
                        currentUserId={currentUserId}
                        onDeletePost={handleDeletePost}
                        onEditPost={handleEditPost}
                    />
                ) : (
                    !isLoading && <p className="text-gray-500">Please log in to see posts.</p>
                )}
                {token && posts.length === 0 && !isLoading && !error && <p className="text-gray-500">No posts yet. Be the first to create one!</p>}
            </div>
        </div>
    );
}

export default Home;
