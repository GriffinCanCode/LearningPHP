// API Response Types
export interface NewsResponse {
  articles: Article[];
  total: number;
  page?: number;
  totalPages?: number;
}

export interface Article {
  id: string;
  title: string;
  summary: string;
  content?: string;
  url: string;
  image_url?: string;
  published_at: string;
  author?: string;
  category: Category;
  source: Source;
}

export interface Category {
  id: string;
  name: string;
  slug: string;
}

export interface Source {
  id: string;
  name: string;
  url: string;
  type: SourceType;
  description?: string;
  is_active: boolean;
}

export interface SourceType {
  id: string;
  name: string;
}

// API Configuration
export interface ApiConfig {
  baseUrl: string;
}

// Component Props
export interface SearchParams {
  category?: string;
  search?: string;
  page?: number;
  source?: string;
}

export interface PaginationProps {
  currentPage: number;
  totalPages: number;
  onPageChange: (page: number) => void;
  className?: string;
}

export interface ArticleCardProps {
  article: Article;
}

export interface HeaderProps {
  onSearch: (query: string) => void;
  searchQuery: string;
}

export interface CategoryFilterProps {
  categories: Category[];
  currentCategory?: string;
  onCategoryChange: (category?: string) => void;
}
