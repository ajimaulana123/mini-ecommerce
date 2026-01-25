document.addEventListener('DOMContentLoaded', function () {
    // Refresh button functionality
    const refreshBtn = document.querySelector('.action-btn.refresh');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            const icon = this.querySelector('svg');
            icon.style.transition = 'transform 0.5s';
            icon.style.transform = 'rotate(360deg)';

            setTimeout(() => {
                icon.style.transform = 'rotate(0deg)';
            }, 500);

            // Reload the page
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    }

    // View all button functionality
    const viewAllBtn = document.querySelector('.action-btn.view-all');
    if (viewAllBtn) {
        viewAllBtn.addEventListener('click', function () {
            // Could be implemented to show all orders
            alert('Fitur "View All" akan menampilkan semua transaksi.');
        });
    }
});
