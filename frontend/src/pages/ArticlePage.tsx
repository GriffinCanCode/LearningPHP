import { useParams, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { format } from 'date-fns';
import { ArrowLeft, ExternalLink, Calendar, User, Tag } from 'lucide-react';
import { newsApi } from '../services/api';
import LoadingSpinner from '../components/common/LoadingSpinner';

const ArticlePage = () => {
  const { id } = useParams<{ id: string }>();

  const { data: article, isLoading, error, isError } = useQuery({
    queryKey: ['article', id],
    queryFn: () => newsApi.getArticle(id!),
    enabled: !!id,
  });

  if (isLoading) {
    return <LoadingSpinner text="Loading article..." />;
  }

  if (isError || !article) {
    return (
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="text-center py-12">
          <h3 className="text-lg font-medium text-gray-900 mb-2">Article Not Found</h3>
          <p className="text-gray-500 mb-4">
            {error instanceof Error ? error.message : 'The article you\'re looking for could not be found.'}
          </p>
          <Link
            to="/"
            className="inline-flex items-center text-primary hover:text-primary-dark font-medium"
          >
            <ArrowLeft className="w-4 h-4 mr-1" />
            Back to Home
          </Link>
        </div>
      </div>
    );
  }

  const formatDate = (dateString: string) => {
    try {
      return format(new Date(dateString), 'MMMM d, yyyy \'at\' h:mm a');
    } catch {
      return dateString;
    }
  };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Back Navigation */}
      <div className="mb-6">
        <Link
          to="/"
          className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
        >
          <ArrowLeft className="w-4 h-4 mr-1" />
          Back to articles
        </Link>
      </div>

      {/* Article Header */}
      <header className="mb-8">
        <div className="flex items-center space-x-2 mb-4">
          <span className={`px-2 py-1 text-xs font-medium rounded-full ${
            article.category.name.toLowerCase() === 'technology' ? 'bg-blue-100 text-blue-800' :
            article.category.name.toLowerCase() === 'business' ? 'bg-green-100 text-green-800' :
            article.category.name.toLowerCase() === 'sports' ? 'bg-yellow-100 text-yellow-800' :
            article.category.name.toLowerCase() === 'politics' ? 'bg-red-100 text-red-800' :
            article.category.name.toLowerCase() === 'health' ? 'bg-purple-100 text-purple-800' :
            article.category.name.toLowerCase() === 'science' ? 'bg-cyan-100 text-cyan-800' :
            article.category.name.toLowerCase() === 'entertainment' ? 'bg-orange-100 text-orange-800' :
            'bg-gray-100 text-gray-800'
          }`}>
            <Tag className="w-3 h-3 inline mr-1" />
            {article.category.name}
          </span>
        </div>
        
        <h1 className="text-3xl font-bold text-gray-900 mb-4">
          {article.title}
        </h1>
        
        <div className="flex flex-wrap items-center text-sm text-gray-500 space-x-4">
          <div className="flex items-center">
            <Calendar className="w-4 h-4 mr-1" />
            {formatDate(article.published_at)}
          </div>
          {article.author && (
            <div className="flex items-center">
              <User className="w-4 h-4 mr-1" />
              {article.author}
            </div>
          )}
          <div className="flex items-center">
            <ExternalLink className="w-4 h-4 mr-1" />
            {article.source.name}
          </div>
        </div>
      </header>

      {/* Article Image */}
      {article.image_url && (
        <div className="mb-8">
          <img
            src={article.image_url}
            alt={article.title}
            className="w-full h-64 md:h-96 object-cover rounded-lg shadow-md"
            onError={(e) => {
              (e.target as HTMLImageElement).style.display = 'none';
            }}
          />
        </div>
      )}

      {/* Article Content */}
      <div className="prose max-w-none mb-8">
        <div className="text-lg leading-relaxed text-gray-700 mb-6">
          {article.summary}
        </div>
        
        {article.content && (
          <div 
            className="leading-relaxed text-gray-700"
            dangerouslySetInnerHTML={{ __html: article.content }}
          />
        )}
      </div>

      {/* Article Actions */}
      <div className="border-t pt-6">
        <div className="flex items-center justify-between">
          <div className="text-sm text-gray-500">
            Source: <strong>{article.source.name}</strong>
          </div>
          
          <a
            href={article.url}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-md hover:bg-primary-dark transition-colors"
          >
            Read Full Article
            <ExternalLink className="w-4 h-4 ml-2" />
          </a>
        </div>
      </div>
    </div>
  );
};

export default ArticlePage;
