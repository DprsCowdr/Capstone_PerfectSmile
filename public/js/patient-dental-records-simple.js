/**
 * Simplified Patient Dental Records JavaScript
 * Minimal JavaScript functionality - most data is handled by PHP
 */

// Simple chart toggle functionality (only JavaScript needed)
function toggleChart(index) {
    const content = document.getElementById(`chart-${index}`);
    const button = content.previousElementSibling.querySelector('.visual-chart-toggle');
    
    if (content.classList.contains('show')) {
        content.classList.remove('show');
        button.innerHTML = '<i class="fas fa-eye mr-1"></i>View Chart';
        button.className = 'visual-chart-toggle';
    } else {
        content.classList.add('show');
        button.innerHTML = '<i class="fas fa-eye-slash mr-1"></i>Hide Chart';
        button.className = 'visual-chart-toggle bg-gray-600';
    }
}

// Basic sidebar toggle for mobile
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggleTop');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            // Simple mobile sidebar toggle - you can implement based on your sidebar structure
            console.log('Sidebar toggle clicked');
        });
    }
});

// Export function for global access
window.toggleChart = toggleChart;
