// $(document).ready(function() {
//     $('form').parsley();
// });


document.addEventListener('DOMContentLoaded', () => {
    const searchTrigger = document.getElementById('search-trigger');
    const closeSearch = document.getElementById('close-search');
    const searchOverlay = document.getElementById('search-overlay');
    const trendingSection = document.getElementById('trending-section');

    if (!searchTrigger || !closeSearch || !searchOverlay) {
        return;
    }

    searchTrigger.addEventListener('click', (e) => {
        e.preventDefault();

        if (window.innerWidth < 640) {
            trendingSection.style.opacity = '0';
            trendingSection.style.transform = 'translateY(-10px)';
            setTimeout(() => { trendingSection.classList.add('hidden'); }, 300);
        }

        searchOverlay.classList.remove('hidden');
        requestAnimationFrame(() => {
            searchOverlay.classList.add('opacity-100');
            document.getElementById('search-input').focus();
        });
    });

    closeSearch.addEventListener('click', () => {
        searchOverlay.classList.remove('opacity-100');
        setTimeout(() => {
            searchOverlay.classList.add('hidden');
            if (window.innerWidth < 640) {
                trendingSection.classList.remove('hidden');
                requestAnimationFrame(() => {
                    trendingSection.style.opacity = '1';
                    trendingSection.style.transform = 'translateY(0)';
                });
            }
        }, 200);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !searchOverlay.classList.contains('hidden')) {
            closeSearch.click();
        }
    });
});