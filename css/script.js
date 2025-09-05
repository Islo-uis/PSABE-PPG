const navbar = document.getElementById('navbar');
const logo = document.getElementById('logo');
const navLinks = document.getElementById('navLinks');
const rightGroup = document.getElementById('rightGroup');
const menuToggle = document.getElementById('menuToggle');
const desktopLogin = document.getElementById('desktopLogin');
const mobileLoginItem = navLinks.querySelector('.mobile-login');

// Toggle dropdown with improved accessibility
menuToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = navLinks.classList.toggle('show');
    menuToggle.setAttribute('aria-expanded', isOpen.toString());

    if (isOpen) {
        // Focus first menu item when opened
        const firstLink = navLinks.querySelector('a');
        if (firstLink) {
            setTimeout(() => firstLink.focus(), 100);
        }
    }
});

// Close dropdown when clicking outside or pressing Escape
document.addEventListener('click', (e) => {
    if (!navLinks.classList.contains('dropdown')) return;
    if (menuToggle.contains(e.target) || navLinks.contains(e.target)) return;
    closeDropdown();
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && navLinks.classList.contains('show')) {
        closeDropdown();
        menuToggle.focus();
    }
});

function closeDropdown() {
    navLinks.classList.remove('show');
    menuToggle.setAttribute('aria-expanded', 'false');
}

function checkNav() {
    // Reset all states
    navLinks.classList.remove('dropdown', 'show');
    menuToggle.style.display = 'none';
    desktopLogin.style.display = '';
    mobileLoginItem.style.display = 'none';
    menuToggle.setAttribute('aria-expanded', 'false');

    // Ensure nav-links is in the correct parent
    if (navLinks.parentElement !== navbar) {
        navbar.insertBefore(navLinks, rightGroup);
    }

    // Calculate available space more accurately
    const navbarRect = navbar.getBoundingClientRect();
    const logoRect = logo.getBoundingClientRect();
    const rightRect = rightGroup.getBoundingClientRect();

    const navbarWidth = navbarRect.width;
    const logoWidth = logoRect.width;
    const rightWidth = rightRect.width;
    const buffer = 60; // Increased buffer for better spacing
    const availableForCenter = navbarWidth - logoWidth - rightWidth - buffer;

    // Get the actual width needed for nav links
    const tempDiv = document.createElement('div');
    tempDiv.style.position = 'absolute';
    tempDiv.style.visibility = 'hidden';
    tempDiv.style.whiteSpace = 'nowrap';
    tempDiv.innerHTML = navLinks.innerHTML;
    document.body.appendChild(tempDiv);
    const navContentWidth = tempDiv.scrollWidth + 120; // Add gap spacing
    document.body.removeChild(tempDiv);

    // Switch to dropdown if needed
    if (navContentWidth > availableForCenter || window.innerWidth <= 768) {
        if (navLinks.parentElement !== rightGroup) {
            rightGroup.appendChild(navLinks);
        }

        navLinks.classList.add('dropdown');
        menuToggle.style.display = 'inline-block';
        mobileLoginItem.style.display = 'block';
        desktopLogin.style.display = 'none';
    }
}

// Debounce resize events for better performance
let resizeTimeout;
function debouncedResize() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(checkNav, 150);
}

// Initialize and set up event listeners
window.addEventListener('load', checkNav);
window.addEventListener('resize', debouncedResize);
window.addEventListener('orientationchange', () => {
    setTimeout(checkNav, 200); // Delay for orientation change completion
});

// Improve image loading
document.querySelectorAll('img').forEach(img => {
    img.addEventListener('load', function () {
        this.style.opacity = '1';
    });

    if (img.complete) {
        img.style.opacity = '1';
    }
});
