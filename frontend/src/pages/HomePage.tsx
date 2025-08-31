import { useState, useEffect } from 'react';
import { useParams, useSearchParams, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { Newspaper, AlertCircle } from 'lucide-react';
import { newsApi, categoriesApi } from '../services/api';
import ArticleCard from '../components/common/ArticleCard';
import Pagination from '../components/common/Pagination';
import LoadingSpinner from '../components/common/LoadingSpinner';

const HomePage = () => {
  const { category } = useParams<{ category: string }>();
  const [searchParams, setSearchParams] = useSearchParams();
  const searchQuery = searchParams.get('q') || '';
  const currentPage = parseInt(searchParams.get('page') || '1', 10);

  const [selectedCategory, setSelectedCategory] = useState<string | undefined>(category);

  const categories = categoriesApi.getCategories();

  // Update selected category when URL changes
  useEffect(() => {
    setSelectedCategory(category);
  }, [category]);

  // Fetch news data
  const { data, isLoading, error, isError } = useQuery({
    queryKey: ['news', selectedCategory, searchQuery, currentPage],
    queryFn: () => newsApi.getNews({
      category: selectedCategory,
      search: searchQuery || undefined,
      page: currentPage,
    }),
    enabled: true,
  });

  const handlePageChange = (page: number) => {
    const newParams = new URLSearchParams(searchParams);
    if (page > 1) {
      newParams.set('page', page.toString());
    } else {
      newParams.delete('page');
    }
    setSearchParams(newParams);
  };

  const handleCategoryChange = (categorySlug?: string) => {
    // Reset page when changing category
    const newParams = new URLSearchParams(searchParams);
    newParams.delete('page');
    
    if (categorySlug) {
      window.location.href = `/category/${categorySlug}${newParams.toString() ? `?${newParams}` : ''}`;
    } else {
      window.location.href = `/${newParams.toString() ? `?${newParams}` : ''}`;
    }
  };

  if (isLoading) {
    return <LoadingSpinner />;
  }

  if (isError) {
    return (
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="text-center py-12">
          <AlertCircle className="w-16 h-16 mx-auto text-red-500 mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">Error Loading News</h3>
          <p className="text-gray-500">
            {error instanceof Error ? error.message : 'Something went wrong. Please try again later.'}
          </p>
        </div>
      </div>
    );
  }

  const articles = data?.articles || [];
  const totalArticles = data?.total || 0;
  const totalPages = Math.ceil(totalArticles / 12); // Assuming 12 items per page

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Category Filter Bar */}
      <div className="mb-8">
        <div className="flex flex-wrap gap-2">
          <button
            onClick={() => handleCategoryChange()}
            className={`${
              !selectedCategory
                ? 'bg-primary text-white'
                : 'bg-white text-gray-700 hover:bg-gray-50'
            } px-4 py-2 rounded-full text-sm font-medium border border-gray-200 transition-colors`}
          >
            All Categories
          </button>
          {categories.map((cat) => (
            <button
              key={cat.slug}
              onClick={() => handleCategoryChange(cat.slug)}
              className={`${
                selectedCategory === cat.slug
                  ? 'bg-primary text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50'
              } px-4 py-2 rounded-full text-sm font-medium border border-gray-200 transition-colors`}
            >
              {cat.name}
            </button>
          ))}
        </div>
      </div>

      {/* Search Results Info */}
      {searchQuery && (
        <div className="mb-6">
          <div className="bg-blue-50 border-l-4 border-blue-400 p-4">
            <div className="flex">
              <div className="ml-3">
                <p className="text-sm text-blue-700">
                  Showing search results for: <strong>{searchQuery}</strong>
                  <Link to="/" className="ml-2 text-blue-600 hover:text-blue-800 underline">
                    Clear search
                  </Link>
                </p>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Category Header */}
      {selectedCategory && (
        <div className="mb-6">
          <h2 className="text-2xl font-bold text-gray-900">
            {categories.find(c => c.slug === selectedCategory)?.name} News
          </h2>
          <p className="text-gray-600 mt-1">{totalArticles} articles found</p>
        </div>
      )}

      {/* News Grid */}
      {articles.length === 0 ? (
        <div className="text-center py-12">
          <div className="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <Newspaper className="w-12 h-12 text-gray-400" />
          </div>
          <h3 className="text-lg font-medium text-gray-900 mb-2">No articles found</h3>
          <p className="text-gray-500">Try adjusting your search or category filter.</p>
        </div>
      ) : (
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            {articles.map((article) => (
              <ArticleCard key={article.id} article={article} />
            ))}
          </div>
          
          {/* Pagination */}
          <Pagination
            currentPage={currentPage}
            totalPages={totalPages}
            onPageChange={handlePageChange}
          />
        </>
      )}
    </div>
  );
};

export default HomePage;
