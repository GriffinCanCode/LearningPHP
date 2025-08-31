import { Code, Users, Zap, Shield, Globe, Heart } from 'lucide-react';

const AboutPage = () => {
  const features = [
    {
      icon: <Globe className="w-8 h-8" />,
      title: 'Multi-Source Aggregation',
      description: 'We collect news from multiple trusted sources to give you a comprehensive view of current events.',
    },
    {
      icon: <Zap className="w-8 h-8" />,
      title: 'Real-time Updates',
      description: 'Our system continuously fetches the latest news to keep you informed with up-to-date information.',
    },
    {
      icon: <Shield className="w-8 h-8" />,
      title: 'Reliable Sources',
      description: 'We only aggregate from verified and trustworthy news sources to ensure content quality.',
    },
    {
      icon: <Users className="w-8 h-8" />,
      title: 'Category Filtering',
      description: 'Easily filter news by categories like Technology, Business, Sports, and more.',
    },
  ];

  const techStack = [
    { name: 'Frontend', tech: 'React + TypeScript + Tailwind CSS' },
    { name: 'Backend', tech: 'PHP 8.4 with modern architecture' },
    { name: 'Data', tech: 'RESTful API with caching' },
    { name: 'Build', tech: 'Vite for fast development and builds' },
  ];

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Hero Section */}
      <div className="text-center mb-16">
        <h1 className="text-4xl font-bold text-gray-900 mb-6">
          About News Aggregator
        </h1>
        <p className="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
          A modern news aggregation platform built to deliver the latest news from multiple 
          trusted sources in a clean, organized, and user-friendly interface.
        </p>
      </div>

      {/* Mission Section */}
      <div className="bg-white rounded-2xl shadow-lg p-8 mb-16">
        <div className="text-center mb-8">
          <Heart className="w-12 h-12 text-primary mx-auto mb-4" />
          <h2 className="text-3xl font-bold text-gray-900 mb-4">Our Mission</h2>
        </div>
        <div className="max-w-4xl mx-auto text-lg text-gray-700 leading-relaxed">
          <p className="mb-6">
            In today's fast-paced world, staying informed shouldn't mean jumping between dozens 
            of news websites or dealing with cluttered interfaces filled with ads and distractions. 
            Our mission is to create a centralized, clean, and efficient way to access news from 
            multiple trusted sources.
          </p>
          <p>
            We believe that good journalism deserves a good presentation. That's why we've built 
            a platform that respects both the content creators and the readers, providing a 
            streamlined experience that helps you stay informed without the noise.
          </p>
        </div>
      </div>

      {/* Features Section */}
      <div className="mb-16">
        <h2 className="text-3xl font-bold text-gray-900 text-center mb-12">
          Key Features
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          {features.map((feature, index) => (
            <div key={index} className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
              <div className="text-primary mb-4">
                {feature.icon}
              </div>
              <h3 className="text-xl font-semibold text-gray-900 mb-3">
                {feature.title}
              </h3>
              <p className="text-gray-600">
                {feature.description}
              </p>
            </div>
          ))}
        </div>
      </div>

      {/* Technology Section */}
      <div className="bg-gray-50 rounded-2xl p-8 mb-16">
        <div className="text-center mb-8">
          <Code className="w-12 h-12 text-primary mx-auto mb-4" />
          <h2 className="text-3xl font-bold text-gray-900 mb-4">Built with Modern Technology</h2>
          <p className="text-gray-600 max-w-2xl mx-auto">
            We use cutting-edge technologies to ensure fast performance, reliability, and a great user experience.
          </p>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
          {techStack.map((item, index) => (
            <div key={index} className="bg-white rounded-lg p-6 shadow-sm">
              <h3 className="text-lg font-semibold text-gray-900 mb-2">
                {item.name}
              </h3>
              <p className="text-gray-600">
                {item.tech}
              </p>
            </div>
          ))}
        </div>
      </div>

      {/* Stats Section */}
      <div className="text-center mb-16">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div className="bg-white rounded-lg shadow-md p-6">
            <div className="text-3xl font-bold text-primary mb-2">Real-time</div>
            <div className="text-gray-600">News Updates</div>
          </div>
          <div className="bg-white rounded-lg shadow-md p-6">
            <div className="text-3xl font-bold text-primary mb-2">Multiple</div>
            <div className="text-gray-600">Trusted Sources</div>
          </div>
          <div className="bg-white rounded-lg shadow-md p-6">
            <div className="text-3xl font-bold text-primary mb-2">8+</div>
            <div className="text-gray-600">News Categories</div>
          </div>
        </div>
      </div>

      {/* Contact/Feedback Section */}
      <div className="bg-primary text-white rounded-2xl p-8 text-center">
        <h2 className="text-3xl font-bold mb-4">Have Questions or Feedback?</h2>
        <p className="text-lg opacity-90 mb-6 max-w-2xl mx-auto">
          We're always looking to improve and would love to hear from you. Whether it's a bug report, 
          feature request, or just general feedback.
        </p>
        <div className="text-sm opacity-75">
          This is a demo project showcasing modern web development practices.
        </div>
      </div>
    </div>
  );
};

export default AboutPage;
