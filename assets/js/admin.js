document.addEventListener('DOMContentLoaded', function() {
    // Initialize all admin functionality
    initAdminDashboard();
});

function initAdminDashboard() {
    // 1. Application Filtering
    setupApplicationFilters();
    
    // 2. Document Download Handlers
    setupDocumentDownloads();
    
    // 3. Table Sorting
    setupTableSorting();
    
    // 4. Pagination Controls
    setupPagination();
    
    // 5. Responsive Menu Toggle
    setupMobileMenu();
}

// ======================
// FILTERING FUNCTIONALITY
// ======================
function setupApplicationFilters() {
    const searchInput = document.getElementById('searchApplications');
    const statusFilter = document.getElementById('statusFilter');
    const schoolFilter = document.getElementById('schoolFilter');
    
    // Combined filter function
    const filterApplications = () => {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const schoolValue = schoolFilter.value;
        
        document.querySelectorAll('.applications-table tbody tr').forEach(row => {
            const matchesSearch = row.textContent.toLowerCase().includes(searchTerm);
            const matchesStatus = !statusValue || row.dataset.status === statusValue;
            const matchesSchool = !schoolValue || row.dataset.school === schoolValue;
            
            row.style.display = (matchesSearch && matchesStatus && matchesSchool) ? '' : 'none';
        });
    };
    
    // Event listeners
    searchInput.addEventListener('input', filterApplications);
    statusFilter.addEventListener('change', filterApplications);
    schoolFilter.addEventListener('change', filterApplications);
}

// ======================
// DOCUMENT DOWNLOADS
// ======================
function setupDocumentDownloads() {
    // Handle bulk document downloads
    document.querySelectorAll('.btn-download').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const appId = this.dataset.id;
            
            try {
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing...';
                
                // Fetch documents via API
                const response = await fetch(`/api/get_documents.php?app_id=${appId}`);
                const documents = await response.json();
                
                // Create zip if multiple documents
                if (documents.length > 1) {
                    await downloadAsZip(documents);
                } else if (documents.length === 1) {
                    // Single document download
                    window.location.href = `/download.php?file=${documents[0].saved_name}`;
                }
                
            } catch (error) {
                console.error('Download failed:', error);
                alert('Failed to prepare documents for download');
            } finally {
                this.innerHTML = '<i class="fas fa-download"></i> Docs';
            }
        });
    });
}

// ======================
// TABLE SORTING
// ======================
function setupTableSorting() {
    const table = document.querySelector('.applications-table table');
    if (!table) return;
    
    const headers = table.querySelectorAll('th[data-sortable]');
    
    headers.forEach(header => {
        header.addEventListener('click', () => {
            const column = header.dataset.column;
            const direction = header.dataset.direction === 'asc' ? 'desc' : 'asc';
            
            // Update all headers
            headers.forEach(h => {
                h.classList.remove('sorted-asc', 'sorted-desc');
                h.dataset.direction = '';
            });
            
            // Set current header
            header.classList.add(`sorted-${direction}`);
            header.dataset.direction = direction;
            
            // Sort table
            sortTable(table, column, direction);
        });
    });
}

function sortTable(table, column, direction) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.querySelector(`td[data-column="${column}"]`).textContent;
        const bValue = b.querySelector(`td[data-column="${column}"]`).textContent;
        
        // Numeric sorting for ID column
        if (column === 'id') {
            return direction === 'asc' ? aValue - bValue : bValue - aValue;
        }
        
        // Date sorting for date columns
        if (column === 'date') {
            return direction === 'asc' 
                ? new Date(aValue) - new Date(bValue)
                : new Date(bValue) - new Date(aValue);
        }
        
        // Default text sorting
        return direction === 'asc'
            ? aValue.localeCompare(bValue)
            : bValue.localeCompare(aValue);
    });
    
    // Rebuild table
    rows.forEach(row => tbody.appendChild(row));
}

// ======================
// PAGINATION
// ======================
function setupPagination() {
    const rowsPerPage = 20;
    const table = document.querySelector('.applications-table tbody');
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    const pageCount = Math.ceil(rows.length / rowsPerPage);
    let currentPage = 1;
    
    const updatePagination = () => {
        // Hide all rows
        rows.forEach(row => row.style.display = 'none');
        
        // Show rows for current page
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        for (let i = start; i < end && i < rows.length; i++) {
            rows[i].style.display = '';
        }
        
        // Update pagination controls
        document.querySelector('.page-numbers').textContent = 
            `Page ${currentPage} of ${pageCount}`;
    };
    
    // Button handlers
    document.querySelector('.pagination .prev').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            updatePagination();
        }
    });
    
    document.querySelector('.pagination .next').addEventListener('click', () => {
        if (currentPage < pageCount) {
            currentPage++;
            updatePagination();
        }
    });
    
    // Initial setup
    updatePagination();
}

// ======================
// MOBILE MENU
// ======================
function setupMobileMenu() {
    const menuToggle = document.createElement('div');
    menuToggle.className = 'mobile-menu-toggle';
    menuToggle.innerHTML = '<i class="fas fa-bars"></i> Menu';
    
    const sidebar = document.querySelector('.admin-sidebar');
    sidebar.prepend(menuToggle);
    
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-visible');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && e.target !== menuToggle) {
            sidebar.classList.remove('mobile-visible');
        }
    });
}

// ======================
// HELPER FUNCTIONS
// ======================
async function downloadAsZip(documents) {
    // Requires JSZip library - include in your project
    if (typeof JSZip === 'undefined') {
        console.error('JSZip library not loaded');
        window.location.href = `/download.php?files=${documents.map(d => d.saved_name).join(',')}`;
        return;
    }
    
    const zip = new JSZip();
    const folder = zip.folder('application_documents');
    
    // Add files to zip
    await Promise.all(documents.map(async doc => {
        const response = await fetch(`/uploads/${doc.saved_name}`);
        const blob = await response.blob();
        folder.file(doc.original_name, blob);
    }));
    
    // Generate and download zip
    const content = await zip.generateAsync({ type: 'blob' });
    const url = URL.createObjectURL(content);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = `application_${documents[0].application_id}_docs.zip`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}