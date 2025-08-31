import { useQuery } from '@tanstack/react-query';
import { ExternalLink, Globe, CheckCircle, XCircle } from 'lucide-react';
import { sourcesApi } from '../services/api';
import LoadingSpinner from '../components/common/LoadingSpinner';

const SourcesPage = () => {
  const { data: sources, isLoading, error, isError } = useQuery({
    queryKey: ['sources'],
    queryFn: sourcesApi.getSources,
  });

  if (isLoading) {
    return <LoadingSpinner text="Loading sources..." />;
  }

  if (isError) {
    return (
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="text-center py-12">
          <h3 className="text-lg font-medium text-gray-900 mb-2">Error Loading Sources</h3>
          <p className="text-gray-500">
            {error instanceof Error ? error.message : 'Something went wrong. Please try again later.'}
          </p>
        </div>
      </div>
    );
  }

  const activeSources = sources?.filter(source => source.is_active) || [];
  const inactiveSources = sources?.filter(source => !source.is_active) || [];

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Page Header */}
      <div className="text-center mb-12">
        <h1 className="text-3xl font-bold text-gray-900 mb-4">News Sources</h1>
        <p className="text-lg text-gray-600 max-w-2xl mx-auto">
          We aggregate news from trusted sources across various categories to bring you 
          comprehensive coverage of current events.
        </p>
      </div>

      {/* Active Sources */}
      {activeSources.length > 0 && (
        <div className="mb-12">
          <h2 className="text-2xl font-semibold text-gray-900 mb-6 flex items-center">
            <CheckCircle className="w-6 h-6 text-green-500 mr-2" />
            Active Sources ({activeSources.length})
          </h2>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {activeSources.map((source) => (
              <div
                key={source.id}
                className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
              >
                <div className="flex items-start justify-between mb-4">
                  <div className="flex items-center">
                    <Globe className="w-6 h-6 text-primary mr-2" />
                    <h3 className="text-lg font-semibold text-gray-900">
                      {source.name}
                    </h3>
                  </div>
                  <CheckCircle className="w-5 h-5 text-green-500" />
                </div>
                
                {source.description && (
                  <p className="text-gray-600 text-sm mb-4">
                    {source.description}
                  </p>
                )}
                
                <div className="flex items-center justify-between">
                  <span className="inline-block px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                    {source.type.name}
                  </span>
                  
                  <a
                    href={source.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center text-primary hover:text-primary-dark text-sm font-medium"
                  >
                    Visit Site
                    <ExternalLink className="w-3 h-3 ml-1" />
                  </a>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Inactive Sources */}
      {inactiveSources.length > 0 && (
        <div>
          <h2 className="text-2xl font-semibold text-gray-900 mb-6 flex items-center">
            <XCircle className="w-6 h-6 text-gray-400 mr-2" />
            Inactive Sources ({inactiveSources.length})
          </h2>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {inactiveSources.map((source) => (
              <div
                key={source.id}
                className="bg-white rounded-lg shadow-md p-6 opacity-60"
              >
                <div className="flex items-start justify-between mb-4">
                  <div className="flex items-center">
                    <Globe className="w-6 h-6 text-gray-400 mr-2" />
                    <h3 className="text-lg font-semibold text-gray-700">
                      {source.name}
                    </h3>
                  </div>
                  <XCircle className="w-5 h-5 text-gray-400" />
                </div>
                
                {source.description && (
                  <p className="text-gray-500 text-sm mb-4">
                    {source.description}
                  </p>
                )}
                
                <div className="flex items-center justify-between">
                  <span className="inline-block px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">
                    {source.type.name}
                  </span>
                  
                  <span className="text-gray-400 text-sm">
                    Temporarily Unavailable
                  </span>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Empty State */}
      {(!sources || sources.length === 0) && (
        <div className="text-center py-12">
          <Globe className="w-16 h-16 mx-auto text-gray-300 mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">No Sources Available</h3>
          <p className="text-gray-500">
            We're working on adding news sources. Please check back later.
          </p>
        </div>
      )}
    </div>
  );
};

export default SourcesPage;
