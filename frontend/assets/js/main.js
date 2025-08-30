// Main JavaScript for NewsAggregator Frontend

document.addEventListener('DOMContentLoaded', function() {
    // Initialize smooth scrolling for anchor links
    initSmoothScrolling();
    
    // Initialize search functionality
    initSearchEnhancements();
    
    // Initialize lazy loading for images
    initLazyLoading();
    
    // Initialize mobile menu
    initMobileMenu();
    
    // Initialize auto-refresh for news
    initAutoRefresh();
});

function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

function initSearchEnhancements() {
    const searchInput = document.querySelector('input[name="search"]');
    const searchForm = document.querySelector('form');
    let searchTimeout;
    
    if (searchInput) {
        // Add search icon animation
        searchInput.addEventListener('focus', function() {
            this.parentElement.classList.add('ring-2', 'ring-primary');
        });
        
        searchInput.addEventListener('blur', function() {
            this.parentElement.classList.remove('ring-2', 'ring-primary');
        });
        
        // Add real-time search suggestions (placeholder for future enhancement)
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    // Future: Implement search suggestions
                    console.log('Search query:', query);
                }, 300);
            }
        });
    }
}

function initLazyLoading() {
    // Simple intersection observer for image lazy loading
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

function initMobileMenu() {
    // Create mobile menu toggle if needed
    const navigation = document.querySelector('nav .hidden.md\\:block');
    
    if (navigation) {
        const mobileMenuButton = document.createElement('button');
        mobileMenuButton.innerHTML = `
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        `;
        mobileMenuButton.className = 'md:hidden p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100';
        
        // Insert mobile menu button
        const navContainer = navigation.parentElement;
        navContainer.insertBefore(mobileMenuButton, navigation);
        
        // Toggle mobile menu
        mobileMenuButton.addEventListener('click', function() {
            navigation.classList.toggle('hidden');
            navigation.classList.toggle('block');
            
            // Change icon
            const icon = this.querySelector('svg');
            if (navigation.classList.contains('hidden')) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
            }
        });
    }
}

function initAutoRefresh() {
    // Check if user wants auto-refresh (could be stored in localStorage)
    const autoRefresh = localStorage.getItem('newsAutoRefresh') === 'true';
    
    if (autoRefresh) {
        // Refresh every 5 minutes
        setInterval(() => {
            if (window.location.pathname === '/' && !document.hidden) {
                window.location.reload();
            }
        }, 5 * 60 * 1000);
    }
}

// Utility functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Fade in
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Fade out and remove
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)}d ago`;
    
    return date.toLocaleDateString();
}

// Handle external links
document.addEventListener('click', function(e) {
    if (e.target.tagName === 'A' && e.target.href && e.target.href.startsWith('http') && !e.target.href.includes(window.location.hostname)) {
        // Add visual feedback for external links
        e.target.style.opacity = '0.7';
        setTimeout(() => {
            e.target.style.opacity = '1';
        }, 200);
    }
});

// Add loading states to buttons
document.addEventListener('click', function(e) {
    if (e.target.tagName === 'BUTTON' || (e.target.tagName === 'A' && e.target.getAttribute('href'))) {
        const element = e.target;
        
        // Don't add loading state to external links
        if (element.tagName === 'A' && element.getAttribute('href').startsWith('http') && !element.getAttribute('href').includes(window.location.hostname)) {
            return;
        }
        
        const originalText = element.textContent;
        element.disabled = true;
        element.textContent = 'Loading...';
        
        // Reset after a short delay (for form submissions) or on page unload
        setTimeout(() => {
            element.disabled = false;
            element.textContent = originalText;
        }, 1000);
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }
    
    // Escape to clear search
    if (e.key === 'Escape') {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput && document.activeElement === searchInput) {
            searchInput.blur();
            if (searchInput.value) {
                window.location.href = '/';
            }
        }
    }
});
