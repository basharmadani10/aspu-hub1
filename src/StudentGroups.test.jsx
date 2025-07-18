import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import axios from 'axios';
import StudentGroups from './StudentGroups';
import '@testing-library/jest-dom';

// Mock data
const mockPosts = [{
  id: 1,
  title: 'Test Post',
  content: 'Test Content',
  community_id: 1,
  user: { id: 1, first_name: 'Test', last_name: 'User' },
  created_at: new Date().toISOString()
}];

const mockAvailableTags = ['test', 'tag1', 'tag2'];

// Mock axios
jest.mock('axios');
axios.get.mockResolvedValue({
  data: {
    posts_from_subscribed_communities: mockPosts,
    user_own_posts: []
  }
});
// Mock child components
jjest.mock('../HeaderApp/HeaderApp', () => {
  return function MockHeader({ onSearch }) {
    return (
      <div data-testid="header-app">
        <input 
          data-testid="search-input"
          onChange={(e) => onSearch(e.target.value)} 
        />
      </div>
    );
  };
});

jest.mock('../Posts/Posts', () => {
  return function MockPosts({ posts }) {
    return (
      <div data-testid="posts">
        {posts.map(post => (
          <div key={post.id} data-testid={`post-${post.id}`}>
            {post.title}
          </div>
        ))}
      </div>
    );
  };
});

describe('StudentGroups Component', () => {
  beforeEach(() => {
    localStorage.setItem('token', 'test-token');
    localStorage.setItem('userSpecialization', 'Software');
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  test('renders and displays posts', async () => {
    render(<StudentGroups />);
    

    // Mock API responses
    axios.get.mockImplementation((url) => {
      if (url.includes('/student/post/get')) {
        return Promise.resolve({
          data: {
            posts_from_subscribed_communities: mockPosts,
            user_own_posts: []
          }
        });
      }
      if (url.includes('/student/tags')) {
        return Promise.resolve({ data: { tags: mockAvailableTags } });
      }
      if (url.includes('/user')) {
        return Promise.resolve({ data: { id: 1 } });
      }
      return Promise.reject(new Error('Not mocked URL'));
    });

    axios.post.mockImplementation((url) => {
      if (url.includes('/student/VotePost')) {
        return Promise.resolve({
          data: { votes: { positive: 6, negative: 2 } }
        });
      }
      if (url.includes('/student/AddComment')) {
        return Promise.resolve({
          data: {
            comment: { id: 101, content: 'New comment' },
            user: { id: 1, name: 'Test User' }
          }
        });
      }
      return Promise.reject(new Error('Not mocked URL'));
    });
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  test('renders without crashing', async () => {
    render(<StudentGroups />);
    await waitFor(() => {
      expect(screen.getByTestId('header-app')).toBeInTheDocument();
    });
  });

  test('displays loading state initially', () => {
    render(<StudentGroups />);
    expect(screen.getByText('Loading posts...')).toBeInTheDocument();
  });

  test('loads and displays posts', async () => {
    render(<StudentGroups />);
    await waitFor(() => {
      expect(screen.getByText('Test Post 1')).toBeInTheDocument();
      expect(screen.getByText('Test Post 2')).toBeInTheDocument();
    });
  });

  test('filters posts by community', async () => {
    render(<StudentGroups />);
    await waitFor(() => {
      expect(screen.getByText('Test Post 1')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('Software'));
    await waitFor(() => {
      expect(screen.queryByText('Test Post 1')).not.toBeInTheDocument();
      expect(screen.getByText('Test Post 2')).toBeInTheDocument();
    });
  });

  test('handles search functionality', async () => {
    render(<StudentGroups />);
    await waitFor(() => {
      expect(screen.getByText('Test Post 1')).toBeInTheDocument();
    });

    fireEvent.change(screen.getByTestId('search-input'), { 
      target: { value: 'content 1' } 
    });

    await waitFor(() => {
      expect(screen.getByText('Test Post 1')).toBeInTheDocument();
      expect(screen.queryByText('Test Post 2')).not.toBeInTheDocument();
    });
  });

  test('handles voting on posts', async () => {
    render(<StudentGroups />);
    await waitFor(() => {
      expect(screen.getByText('Test Post 1')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByTestId('upvote-1'));
    await waitFor(() => {
      expect(axios.post).toHaveBeenCalledWith(
        expect.stringContaining('/student/VotePost'),
        { post_id: 1, vote: 'up' },
        expect.any(Object)
      );
    });
  });

  test('handles adding comments', async () => {
    render(<StudentGroups />);
    await waitFor(() => {
      expect(screen.getByText('Test Post 1')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByTestId('comment-1'));
    await waitFor(() => {
      expect(axios.post).toHaveBeenCalledWith(
        expect.stringContaining('/student/AddComment'),
        { post_id: 1, content: 'Test comment' },
        expect.any(Object)
      );
    });
  });

  test('shows error when API fails', async () => {
    axios.get.mockRejectedValueOnce(new Error('API Error'));
    render(<StudentGroups />);
    await waitFor(() => {
      expect(screen.getByText('Failed to load posts.')).toBeInTheDocument();
    });
  });

  test('handles unauthenticated users', async () => {
    Storage.prototype.getItem = jest.fn(() => null);
    render(<StudentGroups />);
    await waitFor(() => {
      expect(screen.getByText('Authentication token not found. Please log in.')).toBeInTheDocument();
    });
  });
});