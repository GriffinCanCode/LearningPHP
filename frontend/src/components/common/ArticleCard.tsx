import { Link } from 'react-router-dom';
import { format } from 'date-fns';
import { ExternalLink } from 'lucide-react';
import type { Article } from '../../types';

interface ArticleCardProps {
  article: Article;
}

const getCategoryColor = (category: string) => {
  const colors: { [key: string]: string } = {
    technology: 'bg-blue-100 text-blue-800',
    business: 'bg-green-100 text-green-800',
    sports: 'bg-yellow-100 text-yellow-800',
    politics: 'bg-red-100 text-red-800',
    health: 'bg-purple-100 text-purple-800',
    science: 'bg-cyan-100 text-cyan-800',
    entertainment: 'bg-orange-100 text-orange-800',
  };
  
  return colors[category.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

const ArticleCard: React.FC<ArticleCardProps> = ({ article }) => {
  const formatDate = (dateString: string) => {
    try {
      return format(new Date(dateString), 'MMM d, yyyy \'at\' h:mm a');
    } catch {
      return dateString;
    }
  };

  const truncateText = (text: string, length: number = 150) => {
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
  };

  return (
    <article className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
      {article.image_url && (
        <div className="aspect-w-16 aspect-h-9">
          <img
            src={article.image_url}
            alt={article.title}
            className="w-full h-48 object-cover"
            onError={(e) => {
              (e.target as HTMLImageElement).style.display = 'none';
            }}
          />
        </div>
      )}
      
      <div className="p-6">
        {/* Category and Date */}
        <div className="flex items-center justify-between mb-2">
          <span className={`${getCategoryColor(article.category.name)} px-2 py-1 text-xs font-medium rounded-full`}>
            {article.category.name}
          </span>
          <time className="text-xs text-gray-500">
            {formatDate(article.published_at)}
          </time>
        </div>
        
        {/* Title */}
        <h3 className="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
          <Link
            to={`/article/${article.id}`}
            className="hover:text-primary transition-colors"
          >
            {article.title}
          </Link>
        </h3>
        
        {/* Summary */}
        <p className="text-gray-600 text-sm mb-4 line-clamp-3">
          {truncateText(article.summary)}
        </p>
        
        {/* Footer */}
        <div className="flex items-center justify-between">
          <div className="flex items-center text-xs text-gray-500">
            <span>{article.source.name}</span>
            {article.author && (
              <>
                <span className="mx-1">•</span>
                <span>{article.author}</span>
              </>
            )}
          </div>
          
          <a
            href={article.url}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center text-primary hover:text-primary-dark text-sm font-medium group"
          >
            Read More
            <ExternalLink className="w-3 h-3 ml-1 group-hover:translate-x-0.5 transition-transform" />
          </a>
        </div>
      </div>
    </article>
  );
};

export default ArticleCard;
