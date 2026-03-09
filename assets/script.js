// assets/script.js
console.log('E-Parking System Ready');

// Optional: Add simple confirmations or UI interactions here
document.addEventListener('DOMContentLoaded', function() {
    // Example: Highlight active menu
    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    
    menuItems.forEach(item => {
        if (item.getAttribute('href').indexOf(currentPath.split('/').pop()) !== -1) {
            item.classList.add('active');
        }
    });
});
