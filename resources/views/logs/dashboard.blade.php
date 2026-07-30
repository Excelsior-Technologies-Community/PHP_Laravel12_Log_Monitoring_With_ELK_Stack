<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Laravel Log Monitoring Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <style>
        body {
            background: #eef2f7;
            font-family: "Segoe UI", sans-serif;
        }

        h2 {
            font-weight: 700;
            color: #1f2937;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
            transition: .3s;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, .15);
        }

        .card-header {
            background: #ffffff;
            border-bottom: 1px solid #ececec;
            font-weight: 600;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            width: 5px;
            height: 100%;
            background: #0d6efd;
        }

        .stat-card h6 {
            color: #6c757d;
            font-size: 15px;
        }

        .stat-card h2 {
            font-size: 35px;
            font-weight: bold;
            margin-top: 10px;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-success {
            background: #198754;
        }

        .btn-primary {
            background: #0d6efd;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-warning {
            color: #fff;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #dcdcdc;
            height: 48px;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: #0d6efd;
            color: #fff;
        }

        .table thead th {
            border: none;
            padding: 16px;
        }

        .table td {
            vertical-align: middle;
            padding: 15px;
        }

        .table tbody tr:hover {
            background: #f8fbff;
        }

        .badge {
            font-size: 12px;
            padding: 8px 12px;
            border-radius: 20px;
        }

        .pagination {
            gap: 8px;
        }

        .pagination .page-link {
            border: none;
            border-radius: 10px;
            color: #0d6efd;
            min-width: 40px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
        }

        .pagination .page-item.active .page-link {
            background: #0d6efd;
            color: #fff;
        }

        .pagination .page-link:hover {
            background: #0d6efd;
            color: #fff;
        }

        #loading {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .08);
        }
    </style>

</head>

<body>

    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2>
                    <i class="bi bi-file-earmark-text"></i>
                    Laravel Log Monitoring
                </h2>

                <small class="text-muted">
                    JSON Log Monitoring Dashboard
                </small>

            </div>

            <div>

                <button
                    class="btn btn-success"
                    id="refreshLogs">

                    <i class="bi bi-arrow-clockwise"></i>

                    Refresh

                </button>

            </div>

        </div>


        <!-- Statistics -->

        <div class="row mb-4">

            <div class="col-md-3">

                <div class="card stat-card text-center p-3">

                    <h6>Total Logs</h6>

                    <h2 id="totalLogs">

                        0

                    </h2>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card stat-card text-center p-3">

                    <h6>Info</h6>

                    <h2
                        class="text-primary"
                        id="infoLogs">

                        0

                    </h2>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card stat-card text-center p-3">

                    <h6>Warning</h6>

                    <h2
                        class="text-warning"
                        id="warningLogs">

                        0

                    </h2>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card stat-card text-center p-3">

                    <h6>Error</h6>

                    <h2
                        class="text-danger"
                        id="errorLogs">

                        0

                    </h2>

                </div>

            </div>

        </div>


        <!-- Generate Logs -->

        <div class="card mb-4">

            <div class="card-header">

                <strong>

                    Generate Test Logs

                </strong>

            </div>

            <div class="card-body">

                <button
                    class="btn btn-primary generate-log"
                    data-type="info">

                    INFO

                </button>

                <button
                    class="btn btn-warning generate-log"
                    data-type="warning">

                    WARNING

                </button>

                <button
                    class="btn btn-danger generate-log"
                    data-type="error">

                    ERROR

                </button>

                <button
                    class="btn btn-secondary generate-log"
                    data-type="debug">

                    DEBUG

                </button>

            </div>

        </div>


        <!-- Filters -->

        <div class="card mb-4">

            <div class="card-header">

                <strong>

                    Search Filters

                </strong>

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-4">

                        <input
                            type="text"
                            class="form-control"
                            id="keyword"
                            placeholder="Search message...">

                    </div>

                    <div class="col-md-3">

                        <select
                            id="level"
                            class="form-select">

                            <option value="">
                                All Levels
                            </option>

                            <option value="INFO">
                                INFO
                            </option>

                            <option value="WARNING">
                                WARNING
                            </option>

                            <option value="ERROR">
                                ERROR
                            </option>

                            <option value="DEBUG">
                                DEBUG
                            </option>

                        </select>

                    </div>

                    <div class="col-md-3">

                        <input
                            type="date"
                            id="date"
                            class="form-control">

                    </div>

                    <div class="col-md-2 d-grid">

                        <button
                            class="btn btn-primary"
                            id="searchBtn">

                            Search

                        </button>

                    </div>

                </div>

            </div>

        </div>


        <!-- Actions -->

        <div class="mb-4">

            <button
                class="btn btn-success"
                id="exportBtn">

                <i class="bi bi-download"></i>

                Export CSV

            </button>

            <button
                class="btn btn-danger"
                id="clearBtn">

                <i class="bi bi-trash"></i>

                Clear Logs

            </button>

        </div>
        <div id="loading" class="text-center my-3" style="display:none;">

            <div class="spinner-border text-primary" role="status">

                <span class="visually-hidden">
                    Loading...
                </span>

            </div>

            <p class="mt-2">
                Loading logs...
            </p>

        </div>

        <!-- Table -->

        <div class="card">

            <div class="card-header">

                <strong>

                    Latest Logs

                </strong>

            </div>

            <div class="card-body p-0">

                <table
                    class="table table-bordered table-hover mb-0">

                    <thead>

                        <tr>

                            <th width="180">

                                Timestamp

                            </th>

                            <th width="120">

                                Level

                            </th>

                            <th>

                                Message

                            </th>

                            <th width="140">

                                IP Address

                            </th>

                            <th>

                                URL

                            </th>

                        </tr>

                    </thead>

                    <tbody id="logTableBody">

                    </tbody>

                </table>

            </div>

        </div>

        <div class="card-footer bg-white">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <small
                        id="paginationInfo"
                        class="text-muted">

                        Showing 0 to 0 of 0 logs

                    </small>

                </div>

                <nav>

                    <ul
                        class="pagination pagination-sm mb-0"
                        id="pagination">

                    </ul>

                </nav>

            </div>

        </div>

    </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentPage = 1;

        let perPage = 5;

        let lastPage = 1;
        document.addEventListener("DOMContentLoaded", function() {

            loadLogs(currentPage);

            loadStatistics();

        });


        function loadLogs(page = 1) {

            let keyword = document.getElementById("keyword").value;
            let level = document.getElementById("level").value;
            let date = document.getElementById("date").value;

            fetch(`/api/logs/search?page=${page}&per_page=${perPage}&q=${keyword}&level=${level}&date=${date}`)

                .then(response => response.json())

                .then(data => {

                    currentPage = data.current_page;
                    lastPage = data.last_page;

                    let tbody = document.getElementById("logTableBody");
                    tbody.innerHTML = "";

                    if (data.logs.length === 0) {

                        tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">
                            No Logs Found
                        </td>
                    </tr>
                `;

                        document.getElementById("pagination").innerHTML = "";
                        document.getElementById("paginationInfo").innerHTML = "Showing 0 to 0 of 0 logs";

                        return;
                    }

                    data.logs.forEach(log => {

                        let badge = "";

                        switch (log.level) {

                            case "INFO":
                                badge = `<span class="badge bg-primary">INFO</span>`;
                                break;

                            case "WARNING":
                                badge = `<span class="badge bg-warning text-dark">WARNING</span>`;
                                break;

                            case "ERROR":
                                badge = `<span class="badge bg-danger">ERROR</span>`;
                                break;

                            case "DEBUG":
                                badge = `<span class="badge bg-secondary">DEBUG</span>`;
                                break;

                            default:
                                badge = `<span class="badge bg-dark">${log.level}</span>`;
                        }

                        tbody.innerHTML += `
                    <tr>
                        <td>${log.timestamp}</td>
                        <td>${badge}</td>
                        <td>${log.message}</td>
                        <td>${log.context.ip}</td>
                        <td>${log.context.url}</td>
                    </tr>
                `;

                    });

                    document.getElementById("paginationInfo").innerHTML =
                        `Showing ${data.from} to ${data.to} of ${data.total} logs`;

                    renderPagination();

                });

        }

        function renderPagination() {

            let pagination = document.getElementById("pagination");

            pagination.innerHTML = "";

            for (let i = 1; i <= lastPage; i++) {

                pagination.innerHTML += `

        <li class="page-item ${i==currentPage ? 'active' : ''}">

            <a class="page-link"
               href="#"
               onclick="loadLogs(${i});return false;">

                ${i}

            </a>

        </li>

        `;
            }

        }



        function loadStatistics() {

            fetch("/api/logs/statistics")

                .then(response => response.json())

                .then(data => {

                    document.getElementById("totalLogs").innerHTML =
                        data.total_logs;

                    document.getElementById("infoLogs").innerHTML = 0;

                    document.getElementById("warningLogs").innerHTML = 0;

                    document.getElementById("errorLogs").innerHTML = 0;


                    data.logs_per_level.forEach(item => {

                        if (item.key == "INFO") {
                            document.getElementById("infoLogs").innerHTML =
                                item.doc_count;
                        }

                        if (item.key == "WARNING") {
                            document.getElementById("warningLogs").innerHTML =
                                item.doc_count;
                        }

                        if (item.key == "ERROR") {
                            document.getElementById("errorLogs").innerHTML =
                                item.doc_count;
                        }

                    });

                });

        }



        document.getElementById("searchBtn")

            .addEventListener("click", function() {

                currentPage = 1;

                loadLogs(currentPage);

            });



        document.getElementById("refreshLogs")

            .addEventListener("click", function() {

                loadLogs(currentPage);

                loadStatistics();

            });



        document.querySelectorAll(".generate-log")

            .forEach(button => {

                button.addEventListener("click", function() {

                    let type =
                        this.dataset.type;

                    fetch(`/api/logs/generate?type=${type}`)

                        .then(response => response.json())

                        .then(data => {

                            alert(data.message);

                            loadLogs(currentPage);

                            loadStatistics();

                        });

                });

            });



        document.getElementById("exportBtn")

            .addEventListener("click", function() {

                window.location.href =
                    "/api/logs/export";

            });



        document.getElementById("clearBtn")

            .addEventListener("click", function() {

                if (confirm("Are you sure you want to clear all logs?")) {

                    fetch("/api/logs/clear", {

                            method: "DELETE"

                        })

                        .then(response => response.json())

                        .then(data => {

                            alert(data.message);

                            loadLogs(currentPage);

                            loadStatistics();

                        });

                }

            });
    </script>

</body>

</html>