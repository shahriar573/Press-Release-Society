<?php 
/**
 * Press Release Council Dashboard
 * 
 * CONFIGURATION:
 * - Edit database/config.php to change admin credentials (default: admin / admin123)
 * - Edit api/api.php to connect to a real database or modify sample data
 * - To use DB: uncomment DB code in database/config.php and modify api/api.php queries
 */
include 'database/config.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Press Release Council Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header / Navbar -->
    <header class="navbar">
        <div class="nav-container">
            <div class="logo-title">
                <div class="logo">📰</div>
                <h1>Press Release Council</h1>
            </div>
            <nav class="nav-links">
                <a href="#" onclick="loadTable('Members'); return false;">Members</a>
                <a href="#" onclick="loadTable('PressRelease'); return false;">Press Releases</a>
                <a href="#" onclick="loadTable('MediaOutlets'); return false;">Media</a>
                <a href="#" onclick="loadTable('DistributionRecords'); return false;">Distribution</a>
                <a href="#" onclick="loadTable('Events'); return false;">Events</a>
                <a href="joins.php" target="_blank" style="color: #4CAF50;">🔗 Join Operations</a>
            </nav>
            <form class="nav-search" onsubmit="performSearch(event)">
                <input type="text" id="searchInput" placeholder="Search…" aria-label="Search" />
                <select id="searchScope" aria-label="Scope">
                    <option value="all" selected>All</option>
                    <option value="Members">Members</option>
                    <option value="PressRelease">Press Releases</option>
                    <option value="MediaOutlets">Media Outlets</option>
                    <option value="DistributionRecords">Distribution</option>
                    <option value="Events">Events</option>
                </select>
                <button type="submit" class="btn-login">Search</button>
            </form>
            <div class="nav-actions">
                <button id="loginBtn" class="btn-login" onclick="showLoginModal()">Admin Login</button>
                <button id="logoutBtn" class="btn-logout" onclick="logout()" style="display:none;">Logout</button>
            </div>
        </div>
    </header>

    <!-- Optional Sidebar for quick links -->
    <aside class="sidebar" id="sidebar">
        <h3>Quick Links</h3>
        <ul>
            <li><a href="#" onclick="loadTable('Members'); return false;">👥 Members</a></li>
            <li><a href="#" onclick="loadTable('PressRelease'); return false;">📄 Press Releases</a></li>
            <li><a href="#" onclick="loadTable('MediaOutlets'); return false;">📡 Media Outlets</a></li>
            <li><a href="#" onclick="loadTable('DistributionRecords'); return false;">📊 Distribution</a></li>
            <li><a href="#" onclick="loadTable('Events'); return false;">📅 Events</a></li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Search Results Section -->
        <section class="search-section" id="searchResults" style="display:none;">
            <div class="search-header">
                <h2>Search Results</h2>
                <div class="search-meta">
                    <span id="searchSummary"></span>
                    <button class="btn-secondary btn-clear" onclick="clearSearch()">Clear</button>
                </div>
            </div>
            <div id="searchResultsContainer" class="results-container">
                <!-- Filled dynamically -->
            </div>
        </section>
        <!-- Dashboard Cards Section -->
        <section class="dashboard">
            <h2>Dashboard Overview</h2>
            <div class="cards-container">
                <div class="card" onclick="loadTable('Members')">
                    <div class="card-icon">👥</div>
                    <h3>Members</h3>
                    <p>Manage council members</p>
                </div>
                <div class="card" onclick="loadTable('PressRelease')">
                    <div class="card-icon">📄</div>
                    <h3>Press Releases</h3>
                    <p>View and edit releases</p>
                </div>
                <div class="card" onclick="loadTable('MediaOutlets')">
                    <div class="card-icon">📡</div>
                    <h3>Media Outlets</h3>
                    <p>Manage media contacts</p>
                </div>
                <div class="card" onclick="loadTable('DistributionRecords')">
                    <div class="card-icon">📊</div>
                    <h3>Distribution Records</h3>
                    <p>Track distributions</p>
                </div>
                <div class="card" onclick="loadTable('Events')">
                    <div class="card-icon">📅</div>
                    <h3>Events</h3>
                    <p>Upcoming events</p>
                </div>
            </div>
        </section>

        <!-- Tables Section -->
        <section class="table-section" id="tableSection" style="display:none;">
            <div class="table-header">
                <h2 id="tableTitle">Data</h2>
                <div class="table-actions" id="adminActions" style="display:none;">
                    <button class="btn-action btn-add" onclick="showAddModal()">+ Add New</button>
                </div>
            </div>
            <div id="tableContainer">
                <p>Loading...</p>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Press Release Council. All rights reserved.</p>
        <p>Contact: <a href="mailto:info@presscouncil.org">info@presscouncil.org</a></p>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLoginModal()">&times;</span>
            <h2>Admin Login</h2>
            <form id="loginForm" onsubmit="login(event)">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div id="loginError" class="error-msg"></div>
                <button type="submit" class="btn-primary">Login</button>
            </form>
            <p class="hint">Default: admin / admin123</p>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="dataModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDataModal()">&times;</span>
            <h2 id="dataModalTitle">Add Record</h2>
            <form id="dataForm" onsubmit="saveData(event)">
                <div id="formFields"></div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save</button>
                    <button type="button" class="btn-secondary" onclick="closeDataModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        let isAdmin = false;
        let currentTable = null;
        let currentData = [];
        let highlightId = null;

        // Check admin status on load
        window.addEventListener('DOMContentLoaded', checkAuthStatus);

        function checkAuthStatus() {
            fetch('api/auth.php')
                .then(r => r.json())
                .then(data => {
                    isAdmin = data.is_admin || false;
                    updateUIForAuth();
                });
        }

        function updateUIForAuth() {
            document.getElementById('loginBtn').style.display = isAdmin ? 'none' : 'inline-block';
            document.getElementById('logoutBtn').style.display = isAdmin ? 'inline-block' : 'none';
            document.getElementById('adminActions').style.display = isAdmin ? 'flex' : 'none';
        }

        function showLoginModal() {
            document.getElementById('loginModal').style.display = 'block';
            document.getElementById('loginError').textContent = '';
        }

        function closeLoginModal() {
            document.getElementById('loginModal').style.display = 'none';
        }

        function login(e) {
            e.preventDefault();
            const user = document.getElementById('username').value;
            const pass = document.getElementById('password').value;
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('user', user);
            formData.append('pass', pass);

            fetch('api/auth.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        isAdmin = true;
                        updateUIForAuth();
                        closeLoginModal();
                        alert('Login successful!');
                    } else {
                        document.getElementById('loginError').textContent = data.msg || 'Login failed';
                    }
                });
        }

        function logout() {
            const formData = new FormData();
            formData.append('action', 'logout');
            fetch('api/auth.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    isAdmin = false;
                    updateUIForAuth();
                    alert('Logged out successfully');
                });
        }

        function loadTable(tableName) {
            currentTable = tableName;
            document.getElementById('tableTitle').textContent = tableName.replace(/([A-Z])/g, ' $1').trim();
            document.getElementById('tableSection').style.display = 'block';
            document.getElementById('tableContainer').innerHTML = '<p>Loading...</p>';

            fetch('api/api.php?table=' + tableName)
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        document.getElementById('tableContainer').innerHTML = '<p class="error">Error: ' + data.error + '</p>';
                        return;
                    }
                    currentData = data.rows || [];
                    renderTable(data.rows);
                })
                .catch(err => {
                    document.getElementById('tableContainer').innerHTML = '<p class="error">Failed to load data</p>';
                });
        }

        function renderTable(rows) {
            if (!rows || rows.length === 0) {
                document.getElementById('tableContainer').innerHTML = '<p>No records found.</p>';
                return;
            }

            const keys = Object.keys(rows[0]);
            let html = '<table class="data-table"><thead><tr>';
            keys.forEach(k => html += '<th>' + k + '</th>');
            if (isAdmin) {
                html += '<th>Actions</th>';
            }
            html += '</tr></thead><tbody>';

            rows.forEach((row, idx) => {
                const rowClass = (highlightId && row.id == highlightId) ? ' class="highlight"' : '';
                html += `<tr data-row-id="${row.id || ''}"${rowClass}>`;
                keys.forEach(k => html += '<td>' + (row[k] || '') + '</td>');
                if (isAdmin) {
                    html += '<td class="actions-cell">';
                    html += '<button class="btn-edit" onclick="editRecord(' + idx + ')">Edit</button> ';
                    html += '<button class="btn-delete" onclick="deleteRecord(' + idx + ')">Delete</button>';
                    html += '</td>';
                }
                html += '</tr>';
            });

            html += '</tbody></table>';
            document.getElementById('tableContainer').innerHTML = html;
            // Reset highlight after initial render scroll
            if (highlightId) {
                const el = document.querySelector(`tr[data-row-id="${highlightId}"]`);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                // keep highlight for a moment then remove
                setTimeout(() => { highlightId = null; }, 1500);
            }
        }

        function showAddModal() {
            if (!currentTable) {
                alert('Please select a table first');
                return;
            }
            
            document.getElementById('dataModalTitle').textContent = 'Add New Record';
            const html = generateFormFields(currentTable, {});
            document.getElementById('formFields').innerHTML = html;
            document.getElementById('dataModal').style.display = 'block';
        }

        function editRecord(idx) {
            const record = currentData[idx];
            document.getElementById('dataModalTitle').textContent = 'Edit Record';
            const html = generateFormFields(currentTable, record);
            document.getElementById('formFields').innerHTML = html;
            document.getElementById('dataModal').style.display = 'block';
        }

        function generateFormFields(tableName, record) {
            let html = '';
            const isEdit = record && record.id;

            // Define fields per table
            const tableFields = {
                'Members': [
                    { name: 'name', label: 'Name', type: 'text', required: true },
                    { name: 'role', label: 'Role', type: 'text', required: false },
                    { name: 'email', label: 'Email', type: 'email', required: false },
                    { name: 'phone', label: 'Phone', type: 'tel', required: false },
                    { name: 'bio', label: 'Bio', type: 'textarea', required: false },
                    { name: 'is_active', label: 'Active', type: 'select', options: [
                        { value: '1', label: 'Yes' },
                        { value: '0', label: 'No' }
                    ], required: false }
                ],
                'PressReleases': [
                    { name: 'title', label: 'Title', type: 'text', required: true },
                    { name: 'slug', label: 'Slug', type: 'text', required: false },
                    { name: 'summary', label: 'Summary', type: 'textarea', required: false },
                    { name: 'content', label: 'Content', type: 'textarea', required: false },
                    { name: 'published_at', label: 'Published Date', type: 'datetime-local', required: false },
                    { name: 'status', label: 'Status', type: 'select', options: [
                        { value: 'Draft', label: 'Draft' },
                        { value: 'Published', label: 'Published' },
                        { value: 'Archived', label: 'Archived' }
                    ], required: true },
                    { name: 'author_id', label: 'Author ID', type: 'number', required: false }
                ],
                'MediaOutlets': [
                    { name: 'name', label: 'Name', type: 'text', required: true },
                    { name: 'contact_person', label: 'Contact Person', type: 'text', required: false },
                    { name: 'email', label: 'Email', type: 'email', required: false },
                    { name: 'phone', label: 'Phone', type: 'tel', required: false },
                    { name: 'outlet_type', label: 'Outlet Type', type: 'text', required: false }
                ],
                'DistributionRecords': [
                    { name: 'release_id', label: 'Release ID', type: 'number', required: true },
                    { name: 'media_outlet_id', label: 'Media Outlet ID', type: 'number', required: false },
                    { name: 'sent_to', label: 'Sent To', type: 'text', required: false },
                    { name: 'sent_at', label: 'Sent Date', type: 'datetime-local', required: false },
                    { name: 'status', label: 'Status', type: 'select', options: [
                        { value: 'Sent', label: 'Sent' },
                        { value: 'Failed', label: 'Failed' },
                        { value: 'Queued', label: 'Queued' }
                    ], required: true },
                    { name: 'note', label: 'Note', type: 'textarea', required: false }
                ],
                'Events': [
                    { name: 'title', label: 'Title', type: 'text', required: true },
                    { name: 'description', label: 'Description', type: 'textarea', required: false },
                    { name: 'event_date', label: 'Event Date', type: 'datetime-local', required: false },
                    { name: 'location', label: 'Location', type: 'text', required: false },
                    { name: 'created_by', label: 'Created By (Member ID)', type: 'number', required: false },
                    { name: 'related_release_id', label: 'Related Release ID', type: 'number', required: false }
                ]
            };

            const fields = tableFields[tableName] || [];

            // Add hidden ID field for edit mode
            if (isEdit) {
                html += `<input type="hidden" name="id" value="${record.id}">`;
            }

            fields.forEach(field => {
                const value = record[field.name] || '';
                html += '<div class="form-group">';
                html += `<label>${field.label}${field.required ? ' *' : ''}:</label>`;

                if (field.type === 'textarea') {
                    html += `<textarea name="${field.name}" ${field.required ? 'required' : ''}>${value}</textarea>`;
                } else if (field.type === 'select') {
                    html += `<select name="${field.name}" ${field.required ? 'required' : ''}>`;
                    html += '<option value="">-- Select --</option>';
                    field.options.forEach(opt => {
                        const selected = value == opt.value ? 'selected' : '';
                        html += `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
                    });
                    html += '</select>';
                } else {
                    // Format datetime values for datetime-local input
                    let inputValue = value;
                    if (field.type === 'datetime-local' && value) {
                        // Convert MySQL datetime to HTML5 datetime-local format
                        inputValue = value.substring(0, 16); // YYYY-MM-DD HH:MM
                    }
                    html += `<input type="${field.type}" name="${field.name}" value="${inputValue}" ${field.required ? 'required' : ''}>`;
                }

                html += '</div>';
            });

            return html;
        }

        function deleteRecord(idx) {
            const record = currentData[idx];
            const recordId = record.id;
            
            if (!confirm('Are you sure you want to delete this record?')) {
                return;
            }

            fetch(`api/api_crud.php?action=delete&table=${currentTable}&id=${recordId}`, {
                method: 'DELETE'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Record deleted successfully!');
                    loadTable(currentTable); // Reload the table
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete record'));
                }
            })
            .catch(err => {
                alert('Error: Failed to delete record');
                console.error(err);
            });
        }

        function saveData(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = {};
            let recordId = null;

            // Convert FormData to object
            for (let [key, value] of formData.entries()) {
                if (key === 'id') {
                    recordId = value;
                }
                data[key] = value;
            }

            // Determine if this is CREATE or UPDATE
            const isUpdate = recordId && recordId !== '';
            const action = isUpdate ? 'update' : 'create';
            const url = isUpdate 
                ? `api/api_crud.php?action=update&table=${currentTable}&id=${recordId}`
                : `api/api_crud.php?action=create&table=${currentTable}`;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(response => {
                if (response.success) {
                    alert(isUpdate ? 'Record updated successfully!' : 'Record created successfully!');
                    closeDataModal();
                    loadTable(currentTable); // Reload the table
                } else {
                    alert('Error: ' + (response.error || 'Failed to save record'));
                }
            })
            .catch(err => {
                alert('Error: Failed to save record');
                console.error(err);
            });
        }

        function closeDataModal() {
            document.getElementById('dataModal').style.display = 'none';
        }

        // Close modals on outside click
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // ===== Unified Search =====
        function performSearch(e) {
            if (e) e.preventDefault();
            const q = (document.getElementById('searchInput').value || '').trim();
            const scope = document.getElementById('searchScope').value || 'all';
            if (!q) {
                alert('Please enter a search query.');
                return false;
            }
            const resultsSection = document.getElementById('searchResults');
            const resultsContainer = document.getElementById('searchResultsContainer');
            const summaryEl = document.getElementById('searchSummary');
            resultsSection.style.display = 'block';
            resultsContainer.innerHTML = '<p>Searching…</p>';
            summaryEl.textContent = '';

            const url = `api/api_search.php?q=${encodeURIComponent(q)}&scope=${encodeURIComponent(scope)}&limit=25`;
            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        resultsContainer.innerHTML = `<p class="error">Error: ${data.error}</p>`;
                        return;
                    }
                    renderSearchResults(data);
                })
                .catch(err => {
                    console.error(err);
                    resultsContainer.innerHTML = '<p class="error">Failed to search</p>';
                });
            return false;
        }

        function renderSearchResults(data) {
            const resultsContainer = document.getElementById('searchResultsContainer');
            const summaryEl = document.getElementById('searchSummary');
            const rows = data.rows || [];
            const count = data.count || 0;
            const total = data.total || 0;
            summaryEl.textContent = `${count} of ${total} results`;

            if (!rows.length) {
                resultsContainer.innerHTML = '<p>No results found.</p>';
                return;
            }

            let html = '';
            rows.forEach(r => {
                const safeTitle = (r.title || '').toString();
                const safeSnippet = (r.snippet || '').toString();
                const type = r.type || 'Item';
                const id = r.id;
                html += `
                <div class="result-item">
                    <span class="badge badge-type">${type}</span>
                    <div class="result-body">
                        <h4>${safeTitle}</h4>
                        <p>${safeSnippet}</p>
                    </div>
                    <div class="result-actions">
                        <button class="btn-action btn-add" onclick="openResult('${type}', ${id})">Open</button>
                    </div>
                </div>`;
            });
            resultsContainer.innerHTML = html;
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            document.getElementById('searchResults').style.display = 'none';
            document.getElementById('searchResultsContainer').innerHTML = '';
            document.getElementById('searchSummary').textContent = '';
        }

        function openResult(type, id) {
            // Map type to table name directly
            highlightId = id;
            loadTable(type);
            // Scroll to table section
            const section = document.getElementById('tableSection');
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    </script>
</body>
</html>