import axios, { type AxiosResponse } from 'axios';
import type { NewsResponse, Article, Source, Category } from '../types';

const API_BASE_URL = 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error('API Error:', error);
    throw error;
  }
);

// News API
export const newsApi = {
  // Get all news with optional filtering
  getNews: async (params?: {
    category?: string;
    source?: string;
    search?: string;
    page?: number;
  }): Promise<NewsResponse> => {
    let endpoint = '/news';
    
    if (params?.category) {
      endpoint = `/news/category/${params.category}`;
    } else if (params?.source) {
      endpoint = `/news/source/${params.source}`;
    } else if (params?.search) {
      endpoint = `/news/search/${encodeURIComponent(params.search)}`;
    }
    
    const queryParams = new URLSearchParams();
    if (params?.page && params.page > 1) {
      queryParams.append('page', params.page.toString());
    }
    
    const url = queryParams.toString() ? `${endpoint}?${queryParams}` : endpoint;
    const response: AxiosResponse<NewsResponse> = await api.get(url);
    return response.data;
  },

  // Get single article by ID
  getArticle: async (id: string): Promise<Article> => {
    const response: AxiosResponse<Article> = await api.get(`/news/${id}`);
    return response.data;
  },
};

// Sources API
export const sourcesApi = {
  // Get all news sources
  getSources: async (): Promise<Source[]> => {
    const response: AxiosResponse<Source[]> = await api.get('/sources');
    return response.data;
  },
};

// Categories API (static for now, but could be dynamic)
export const categoriesApi = {
  getCategories: (): Category[] => [
    { id: '1', name: 'Technology', slug: 'technology' },
    { id: '2', name: 'Business', slug: 'business' },
    { id: '3', name: 'Sports', slug: 'sports' },
    { id: '4', name: 'Politics', slug: 'politics' },
    { id: '5', name: 'Health', slug: 'health' },
    { id: '6', name: 'Science', slug: 'science' },
    { id: '7', name: 'Entertainment', slug: 'entertainment' },
    { id: '8', name: 'General', slug: 'general' },
  ],
};

export { api };
