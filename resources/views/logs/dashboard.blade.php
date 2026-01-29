<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Log Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8 text-gray-800">
            <i class="fas fa-chart-line mr-3"></i>Laravel Log Monitoring Dashboard
        </h1>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" id="statsContainer">
            <!-- Stats will be loaded here -->
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Logs by Level</h2>
                <canvas id="levelChart" height="250"></canvas>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Logs Over Time</h2>
                <canvas id="timeChart" height="250"></canvas>
            </div>
        </div>

        <!-- Search Section -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h2 class="text-xl font-semibold mb-4">Search Logs</h2>
            <form id="searchForm" class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Search Query</label>
                        <input type="text" id="searchQuery" class="w-full p-2 border rounded"
                            placeholder="Search in logs...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Log Level</label>
                        <select id="logLevel" class="w-full p-2 border rounded">
                            <option value="">All Levels</option>
                            <option value="INFO">INFO</option>
                            <option value="WARNING">WARNING</option>
                            <option value="ERROR">ERROR</option>
                            <option value="DEBUG">DEBUG</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Time Range</label>
                        <select id="timeRange" class="w-full p-2 border rounded">
                            <option value="now-1h">Last 1 hour</option>
                            <option value="now-24h">Last 24 hours</option>
                            <option value="now-7d" selected>Last 7 days</option>
                            <option value="now-30d">Last 30 days</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                    </div>
                </div>
            </form>

            <!-- Logs Table -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-3 text-left">Timestamp</th>
                            <th class="p-3 text-left">Level</th>
                            <th class="p-3 text-left">Message</th>
                            <th class="p-3 text-left">User</th>
                            <th class="p-3 text-left">IP</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody">
                        <!-- Logs will be loaded here -->
                    </tbody>
                </table>
            </div>
            <div class="mt-4 text-center" id="loadingSpinner">
                <i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i>
                <p class="mt-2">Loading logs...</p>
            </div>
        </div>

        <!-- Test Log Generation -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Generate Test Logs</h2>
            <div class="flex space-x-4">
                <button onclick="generateLog('info')"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    <i class="fas fa-info-circle mr-2"></i>Generate INFO
                </button>
                <button onclick="generateLog('warning')"
                    class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Generate WARNING
                </button>
                <button onclick="generateLog('error')" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    <i class="fas fa-exclamation-circle mr-2"></i>Generate ERROR
                </button>
                <button onclick="generateLog('debug')"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    <i class="fas fa-bug mr-2"></i>Generate DEBUG
                </button>
            </div>
        </div>
    </div>

    <script>
        // Initialize charts
        let levelChart, timeChart;

        // Load statistics on page load
        document.addEventListener('DOMContentLoaded', function () {
            initializeCharts();
            loadStatistics();
            loadLogs();
        });

        // Search form submission
        document.getElementById('searchForm').addEventListener('submit', function (e) {
            e.preventDefault();
            loadLogs();
        });

        async function loadStatistics() {
            try {
                const response = await fetch('/api/logs/statistics');

                // Check if response is HTML (error page)
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('text/html')) {
                    const html = await response.text();
                    console.error('Server returned HTML error page');
                    showError('Server error. Check Laravel logs.');
                    return;
                }

                const data = await response.json();

                if (data.error) {
                    showWarning(data.error);
                    return;
                }

                // Update stats cards
                updateStatsCards(data);

                // Update charts if data exists
                if (data.logs_per_level && data.logs_per_hour) {
                    updateCharts(data);
                }

            } catch (error) {
                console.error('Error loading statistics:', error);
                showWarning('Failed to load statistics');
            }
        }

        function updateStatsCards(data) {
            const statsContainer = document.getElementById('statsContainer');

            // Get counts safely
            const totalLogs = data.total_logs || 0;
            const infoCount = getLevelCount(data.logs_per_level, 'INFO');
            const warningCount = getLevelCount(data.logs_per_level, 'WARNING');
            const errorCount = getLevelCount(data.logs_per_level, 'ERROR');

            statsContainer.innerHTML = `
            <div class="bg-blue-50 p-6 rounded-lg shadow">
                <div class="text-blue-600 text-2xl mb-2">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Total Logs</h3>
                <p class="text-3xl font-bold">${totalLogs}</p>
            </div>
            <div class="bg-green-50 p-6 rounded-lg shadow">
                <div class="text-green-600 text-2xl mb-2">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">INFO Logs</h3>
                <p class="text-3xl font-bold">${infoCount}</p>
            </div>
            <div class="bg-yellow-50 p-6 rounded-lg shadow">
                <div class="text-yellow-600 text-2xl mb-2">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">WARNING Logs</h3>
                <p class="text-3xl font-bold">${warningCount}</p>
            </div>
            <div class="bg-red-50 p-6 rounded-lg shadow">
                <div class="text-red-600 text-2xl mb-2">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">ERROR Logs</h3>
                <p class="text-3xl font-bold">${errorCount}</p>
            </div>
        `;
        }

        function getLevelCount(levels, targetLevel) {
            if (!levels || !Array.isArray(levels)) return 0;
            const level = levels.find(l => l.key === targetLevel);
            return level ? level.doc_count : 0;
        }

        async function loadLogs() {
            const searchQuery = document.getElementById('searchQuery').value;
            const logLevel = document.getElementById('logLevel').value;
            const timeRange = document.getElementById('timeRange').value;

            const params = new URLSearchParams({
                q: searchQuery,
                level: logLevel,
                from: timeRange,
                size: 50
            });

            try {
                const loadingSpinner = document.getElementById('loadingSpinner');
                loadingSpinner.classList.remove('hidden');

                const response = await fetch(`/api/logs/search?${params}`);

                // Check if response is HTML (error page)
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('text/html')) {
                    const html = await response.text();
                    console.error('Server returned HTML error page');
                    showError('Server error. Check Laravel logs.');
                    loadingSpinner.classList.add('hidden');
                    return;
                }

                const data = await response.json();

                loadingSpinner.classList.add('hidden');

                if (data.error) {
                    showWarning(data.error);
                    return;
                }

                if (!data.logs || data.logs.length === 0) {
                    document.getElementById('logsTableBody').innerHTML = `
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">
                            No logs found.
                        </td>
                    </tr>
                `;
                    return;
                }

                displayLogs(data.logs);

            } catch (error) {
                console.error('Error loading logs:', error);
                const loadingSpinner = document.getElementById('loadingSpinner');
                loadingSpinner.classList.add('hidden');
                showError('Failed to load logs: ' + error.message);
            }
        }

        function displayLogs(logs) {
            const tableBody = document.getElementById('logsTableBody');

            const logRows = logs.map(log => {
                // Safely extract values
                const timestamp = log['@timestamp'] || log.datetime || log.timestamp || 'N/A';
                const level = log.level || 'INFO';
                const message = log.message || '';
                const userId = log.extra?.user_id || log.context?.user_id || 'Guest';
                const ip = log.extra?.ip || log.context?.ip || 'N/A';

                return `
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 text-sm">${formatDate(timestamp)}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs font-semibold ${getLevelClass(level)}">
                            ${level}
                        </span>
                    </td>
                    <td class="p-3">${truncate(message, 100)}</td>
                    <td class="p-3">${userId}</td>
                    <td class="p-3">${ip}</td>
                </tr>
            `;
            }).join('');

            tableBody.innerHTML = logRows;
        }

        function getLevelClass(level) {
            // Convert to string and uppercase safely
            const levelStr = String(level || '').toUpperCase();

            switch (levelStr) {
                case 'ERROR': return 'bg-red-100 text-red-800';
                case 'WARNING': return 'bg-yellow-100 text-yellow-800';
                case 'INFO': return 'bg-blue-100 text-blue-800';
                case 'DEBUG': return 'bg-gray-100 text-gray-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        function showWarning(message) {
            const statsContainer = document.getElementById('statsContainer');
            statsContainer.innerHTML = `
            <div class="col-span-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            ${message}
                        </p>
                    </div>
                </div>
            </div>
        `;
        }

        function showError(message) {
            const tableBody = document.getElementById('logsTableBody');
            tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="p-4 text-center text-red-600">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    ${message}
                </td>
            </tr>
        `;
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                return date.toLocaleString();
            } catch (e) {
                return dateString;
            }
        }

        function truncate(text, length) {
            if (!text) return 'N/A';
            return text.length > length ? text.substring(0, length) + '...' : text;
        }

        async function generateLog(type) {
            try {
                const response = await fetch(`/api/logs/generate?type=${type}`);

                // Check if response is HTML (error page)
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('text/html')) {
                    const html = await response.text();
                    console.error('Server returned HTML error page');
                    alert('Error generating log. Check server console.');
                    return;
                }

                const data = await response.json();

                alert(`${data.message}`);

                // Refresh data
                setTimeout(() => {
                    loadStatistics();
                    loadLogs();
                }, 1000);

            } catch (error) {
                console.error('Error generating log:', error);
                alert('Log generated locally (check storage/logs/json/laravel-json.log)');

                // Still refresh
                setTimeout(() => {
                    loadStatistics();
                    loadLogs();
                }, 500);
            }
        }

        function initializeCharts() {
            const levelCtx = document.getElementById('levelChart').getContext('2d');
            const timeCtx = document.getElementById('timeChart').getContext('2d');

            levelChart = new Chart(levelCtx, {
                type: 'doughnut',
                data: {
                    labels: ['INFO', 'WARNING', 'ERROR', 'DEBUG'],
                    datasets: [{
                        data: [0, 0, 0, 0],
                        backgroundColor: [
                            '#3B82F6', // blue
                            '#F59E0B', // yellow
                            '#EF4444', // red
                            '#6B7280'  // gray
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            timeChart = new Chart(timeCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Logs per Hour',
                        data: [],
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function updateCharts(data) {
            if (!data || !data.logs_per_level || !data.logs_per_hour) return;

            // Update level chart
            const levelData = {
                'INFO': getLevelCount(data.logs_per_level, 'INFO'),
                'WARNING': getLevelCount(data.logs_per_level, 'WARNING'),
                'ERROR': getLevelCount(data.logs_per_level, 'ERROR'),
                'DEBUG': getLevelCount(data.logs_per_level, 'DEBUG')
            };

            levelChart.data.datasets[0].data = Object.values(levelData);
            levelChart.update();

            // Update time chart if we have data
            if (data.logs_per_hour.length > 0) {
                const timeLabels = data.logs_per_hour.map(bucket => {
                    const date = new Date(bucket.key_as_string);
                    return date.toLocaleTimeString([], { hour: '2-digit' });
                });

                const timeData = data.logs_per_hour.map(bucket => bucket.doc_count);

                timeChart.data.labels = timeLabels;
                timeChart.data.datasets[0].data = timeData;
                timeChart.update();
            }
        }
    </script>
</body>

</html>