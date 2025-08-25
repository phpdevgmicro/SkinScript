/**
 * Admin Panel JavaScript
 */

// Initialize admin panel functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeDataTables();
    initializeTooltips();
    initializeSearchFilters();
    initializeCharts();
});

/**
 * Initialize DataTables for better table functionality
 */
function initializeDataTables() {
    // Add search and pagination to tables
    const tables = document.querySelectorAll('.data-table');
    tables.forEach(table => {
        // Simple table enhancement without external dependencies
        addTableSearch(table);
        addTablePagination(table);
    });
}

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize search filters
 */
function initializeSearchFilters() {
    const searchInputs = document.querySelectorAll('.table-search');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const table = this.closest('.card').querySelector('table tbody');
            const rows = table.querySelectorAll('tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
}

/**
 * Add search functionality to table
 */
function addTableSearch(table) {
    const wrapper = table.parentElement;
    const searchDiv = document.createElement('div');
    searchDiv.className = 'mb-3';
    searchDiv.innerHTML = `
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control table-search" placeholder="Search...">
        </div>
    `;
    wrapper.insertBefore(searchDiv, table);
}

/**
 * Add pagination to table
 */
function addTablePagination(table) {
    const rowsPerPage = 10;
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    if (rows.length <= rowsPerPage) return;

    let currentPage = 1;
    const totalPages = Math.ceil(rows.length / rowsPerPage);

    // Hide all rows initially
    rows.forEach(row => row.style.display = 'none');

    // Show first page
    showPage(1);

    // Create pagination controls
    const paginationDiv = document.createElement('div');
    paginationDiv.className = 'd-flex justify-content-between align-items-center mt-3';
    paginationDiv.innerHTML = `
        <div class="pagination-info">
            Showing <span class="current-start">1</span> to <span class="current-end">${Math.min(rowsPerPage, rows.length)}</span> of ${rows.length} entries
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item" id="prev-page">
                    <a class="page-link" href="#" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <li class="page-item" id="next-page">
                    <a class="page-link" href="#" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    `;

    table.parentElement.appendChild(paginationDiv);

    // Add event listeners
    document.getElementById('prev-page').addEventListener('click', function(e) {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            showPage(currentPage);
            updatePaginationInfo();
        }
    });

    document.getElementById('next-page').addEventListener('click', function(e) {
        e.preventDefault();
        if (currentPage < totalPages) {
            currentPage++;
            showPage(currentPage);
            updatePaginationInfo();
        }
    });

    function showPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach((row, index) => {
            if (index >= start && index < end) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Update pagination buttons
        document.getElementById('prev-page').classList.toggle('disabled', page === 1);
        document.getElementById('next-page').classList.toggle('disabled', page === totalPages);
    }

    function updatePaginationInfo() {
        const start = (currentPage - 1) * rowsPerPage + 1;
        const end = Math.min(currentPage * rowsPerPage, rows.length);

        document.querySelector('.current-start').textContent = start;
        document.querySelector('.current-end').textContent = end;
    }
}

/**
 * Initialize charts (for reports page)
 */
function initializeCharts() {
    // This will be implemented when Chart.js is loaded on reports page
    if (typeof Chart !== 'undefined') {
        createDashboardCharts();
    }
}

/**
 * Create dashboard charts
 */
function createDashboardCharts() {
    // Chart configurations will be added based on actual data
    const ctx1 = document.getElementById('formulationsChart');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Formulations Created',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Formulations Over Time'
                    }
                }
            }
        });
    }
}

/**
 * Utility functions
 */

// Show loading state
function showLoading(element) {
    element.classList.add('loading');
    element.innerHTML += '<span class="spinner-border spinner-border-sm ms-2" role="status"></span>';
}

// Hide loading state
function hideLoading(element) {
    element.classList.remove('loading');
    const spinner = element.querySelector('.spinner-border');
    if (spinner) {
        spinner.remove();
    }
}

// Show alert message
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const container = document.querySelector('main .container-fluid') || document.querySelector('main');
    container.insertBefore(alertDiv, container.firstChild);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Format JSON data for display
function formatJsonData(data) {
    if (typeof data === 'string') {
        try {
            data = JSON.parse(data);
        } catch (e) {
            return data;
        }
    }

    if (Array.isArray(data)) {
        return data.join(', ');
    }

    return JSON.stringify(data, null, 2);
}

// Export table to CSV function
function exportTableAsCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) {
        alert('Table not found');
        return;
    }

    let csv = [];
    const rows = table.querySelectorAll('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');

        for (let j = 0; j < cols.length - 1; j++) { // Skip the last column (Actions)
            let cellText = cols[j].innerText.replace(/"/g, '""'); // Escape quotes
            if (cellText.includes(',') || cellText.includes('"') || cellText.includes('\n')) {
                cellText = '"' + cellText + '"'; // Wrap in quotes if contains comma, quote, or newline
            }
            row.push(cellText);
        }
        csv.push(row.join(','));
    }

    // Create and download the CSV file
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');

    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}