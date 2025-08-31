const Footer = () => {
  return (
    <footer className="bg-white border-t mt-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="text-center text-gray-500 text-sm">
          <p>
            &copy; {new Date().getFullYear()} News Aggregator. Built with TypeScript, React, and modern web technologies.
          </p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
