# PHP_Laravel12_Log_Monitoring_With_ELK_Stack

## Project Overview

This project demonstrates how to implement **log monitoring in Laravel 12 using the ELK Stack (Elasticsearch, Logstash, Kibana)**. It focuses on structured JSON logging, centralized log storage, and real-time visualization using Kibana dashboards.

The system helps developers monitor application behavior, debug issues, analyze errors, and gain insights into application performance.

---

## Prerequisites

* PHP 8.2+
* Composer
* Docker & Docker Compose
* Laravel 12
* Basic knowledge of Laravel

---

## Step 1: Create Laravel Project

```bash
composer create-project laravel/laravel laravel-log-monitoring
cd laravel-log-monitoring

composer require monolog/monolog
composer require elasticsearch/elasticsearch

mkdir -p storage/logs/json
mkdir -p docker/elk
```

---

## Step 2: Configure Laravel Logging

### config/logging.php

Configure multiple log channels including JSON and Elasticsearch for ELK integration.

* Stack channel combines daily + Elasticsearch
* JSON logs stored for Logstash ingestion
* Custom Elasticsearch logger

---

## Step 3: Elasticsearch Logger

Create a custom logger to push logs directly into Elasticsearch using Monolog.

```bash
php artisan make:class Logging/ElasticsearchLogger
```

This logger uses `ElasticsearchHandler` and formats logs for indexing.

---

## Step 4: Custom JSON Log Formatter

```bash
php artisan make:class Logging/JsonLogFormatter
```

Adds contextual metadata to logs:

* Hostname
* IP address
* User ID
* Request URL and method
* ISO8601 timestamps

---

## Step 5: Set Up ELK Stack with Docker

### docker-compose.yml

Services included:

* **Elasticsearch** – Log storage and search engine
* **Logstash** – Log ingestion and processing
* **Kibana** – Log visualization and dashboards

Start the stack:

```bash
docker-compose up -d
```

Verify Elasticsearch:

```bash
curl http://localhost:9200
```

---

## Step 6: Configure Logstash Pipeline

Logstash reads Laravel JSON logs and processes them:

* Parses timestamps
* Adds GeoIP data
* Parses user-agent
* Normalizes log levels
* Sends logs to Elasticsearch daily indices

---

## Step 7: Log Controller

The `LogController` provides APIs to:

* Generate test logs (info, warning, error, debug)
* Search logs with filters
* Fetch aggregated statistics

Endpoints:

* `/api/logs/generate`
* `/api/logs/search`
* `/api/logs/statistics`

---

## Step 8: Log Monitoring Dashboard

A TailwindCSS + Chart.js dashboard provides:

* Total log count
* Logs by severity
* Logs over time
* Search and filtering
* Manual test log generation

Access:

```
http://localhost:8000/logs/dashboard
```

---

## Step 9: Routes

```php
Route::get('/logs/dashboard', fn () => view('logs.dashboard'));

Route::prefix('api')->group(function () {
    Route::get('/logs/generate', [LogController::class, 'generateLogs']);
    Route::get('/logs/search', [LogController::class, 'searchLogs']);
    Route::get('/logs/statistics', [LogController::class, 'getStatistics']);
});
```
### Screenshort
<img width="1829" height="968" alt="image" src="https://github.com/user-attachments/assets/adec7423-dd41-444e-bf17-cc989b0f5bc7" />
<img width="1816" height="734" alt="image" src="https://github.com/user-attachments/assets/b12f0173-d7e6-444d-8f00-63e910738d7a" />


---

## Step 10: Kibana Setup

1. Open Kibana: `http://localhost:5601`
2. Create Index Pattern: `laravel-logs-*`
3. Use Discover to view logs
4. Create visualizations:

   * Logs by level
   * Logs over time
   * Recent error messages
   * Total log count

Save dashboard as **Laravel Log Monitoring**.

---

## Step 11: Artisan Command for Log Testing

Generate bulk test logs from CLI:

```bash
php artisan logs:generate 50
```

This command simulates random log activity across all levels.

---

## Optional Advanced Features

### Real-Time Log Streaming

* Laravel WebSockets
* Pusher

### Alerting

* ElastAlert configuration
* Email alerts for frequent errors

### Performance Monitoring Middleware

Logs request duration, memory usage, status code, user, and IP for each request.

---

## Troubleshooting

* Elasticsearch not starting: Increase Docker memory
* Logs missing in Kibana: Verify Logstash pipeline and index pattern
* Permission issues: Ensure `storage/` is writable
* Connection refused: Confirm all ELK containers are running

---

## Conclusion

This project demonstrates a complete **Laravel 12 log monitoring system using the ELK Stack**, providing:

* Structured JSON logging
* Centralized log storage
* Search and filtering
* Real-time dashboards
* Statistical analysis

Ideal for production-grade monitoring, debugging, and performance analysis.

---

## License

MIT License
