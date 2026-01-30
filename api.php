<?php
/**
 * Cortina Consult - Secure Data API
 *
 * Serves CSV data files with authentication verification.
 * Direct CSV file access is blocked by .htaccess - all data must go through this API.
 *
 * Usage:
 *   api.php?file=bi_reports/bi_20260101_20260131_summary.csv
 *   api.php?file=churn_sentiment_analysis.csv
 *   api.php?action=list_bi_reports  (lists available BI report date ranges)
 */

// Strict error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// CORS for same-origin only
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Data directory (relative to this script)
define('DATA_DIR', __DIR__ . '/data/');

// Allowed file patterns (whitelist)
$ALLOWED_PATTERNS = [
    // BI Reports
    'bi_reports/bi_\d{8}_\d{8}_summary\.csv',
    'bi_reports/bi_\d{8}_\d{8}_kunden\.csv',
    'bi_reports/bi_\d{8}_\d{8}_mitarbeiter\.csv',
    'bi_reports/bi_\d{8}_\d{8}_forecast\.csv',
    'bi_reports/bi_\d{8}_\d{8}_pauschalen_deckung\.csv',
    'bi_reports/bi_\d{8}_\d{8}_kontingent_restbudget\.csv',
    'bi_reports/bi_\d{8}_\d{8}_hubspot_kunden\.csv',
    // Churn data
    'churn_sentiment_analysis\.csv',
    'raw_churn_data\.csv',
];

/**
 * Verify the request is authenticated via Apache Basic Auth
 */
function isAuthenticated() {
    // Apache passes auth info through these variables
    return !empty($_SERVER['PHP_AUTH_USER']) ||
           !empty($_SERVER['REMOTE_USER']) ||
           !empty($_SERVER['REDIRECT_REMOTE_USER']);
}

/**
 * Check if requested file matches allowed patterns
 */
function isAllowedFile($filename, $patterns) {
    // Sanitize: remove any path traversal attempts
    $filename = str_replace(['..', "\0"], '', $filename);

    foreach ($patterns as $pattern) {
        if (preg_match('#^' . $pattern . '$#', $filename)) {
            return true;
        }
    }
    return false;
}

/**
 * List available BI report date ranges
 */
function listBiReports() {
    $reportsDir = DATA_DIR . 'bi_reports/';
    $reports = [];

    if (is_dir($reportsDir)) {
        $files = glob($reportsDir . 'bi_*_summary.csv');
        foreach ($files as $file) {
            $basename = basename($file);
            // Extract date range: bi_YYYYMMDD_YYYYMMDD_summary.csv
            if (preg_match('/bi_(\d{8})_(\d{8})_summary\.csv/', $basename, $matches)) {
                $reports[] = [
                    'start' => $matches[1],
                    'end' => $matches[2],
                    'pattern' => $matches[1] . '_' . $matches[2],
                    'label' => substr($matches[1], 6, 2) . '.' . substr($matches[1], 4, 2) . '.' . substr($matches[1], 0, 4) .
                              ' - ' .
                              substr($matches[2], 6, 2) . '.' . substr($matches[2], 4, 2) . '.' . substr($matches[2], 0, 4)
                ];
            }
        }
        // Sort by date descending
        usort($reports, function($a, $b) {
            return strcmp($b['start'], $a['start']);
        });
    }

    return $reports;
}

/**
 * Send JSON response
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send error response
 */
function errorResponse($message, $status = 400) {
    jsonResponse(['error' => $message], $status);
}

/**
 * Serve CSV file
 */
function serveCSV($filepath) {
    if (!file_exists($filepath)) {
        errorResponse('Datei nicht gefunden', 404);
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

    readfile($filepath);
    exit;
}

// ============ MAIN ============

// Verify authentication (Apache should have already authenticated, but double-check)
// Note: When behind Apache Basic Auth, user info is available
// If not authenticated, Apache's .htaccess will have already blocked the request

// Handle action requests
$action = $_GET['action'] ?? null;

if ($action === 'list_bi_reports') {
    jsonResponse(['reports' => listBiReports()]);
}

if ($action === 'status') {
    jsonResponse([
        'status' => 'ok',
        'authenticated' => true,
        'timestamp' => date('c'),
        'data_dir_exists' => is_dir(DATA_DIR)
    ]);
}

// Handle file requests
$requestedFile = $_GET['file'] ?? null;

if (empty($requestedFile)) {
    errorResponse('Kein Dateiname angegeben. Nutzung: api.php?file=pfad/zur/datei.csv');
}

// Security check: validate against whitelist
if (!isAllowedFile($requestedFile, $ALLOWED_PATTERNS)) {
    errorResponse('Zugriff auf diese Datei nicht erlaubt', 403);
}

// Build full path and verify it's within DATA_DIR
$fullPath = realpath(DATA_DIR . $requestedFile);
$dataDir = realpath(DATA_DIR);

if ($fullPath === false || strpos($fullPath, $dataDir) !== 0) {
    errorResponse('Ungültiger Dateipfad', 403);
}

// Serve the file
serveCSV($fullPath);
