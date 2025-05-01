<?php
header('Content-Type: application/json');

require_once 'logger.php';
$logger = new Logger('logs');

// Database configuration - update these with your Hostinger credentials
$DB_HOST = 'localhost';  // Usually localhost on Hostinger
$DB_NAME = 'u520753964_analytics_db';  // The database name you created
$DB_USER = 'u520753964_spencer';  // The database user you created
$DB_PASS = 'Brittany8!3292!';  // The database password you set

// Error handling function
function sendError($message, $code = 500) {
    global $logger;
    $logger->error($message);
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

try {
    // Create database connection
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    $logger->info("Database connection established");

    // Get and validate input
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input || !isset($input['session_id'])) {
        sendError('Invalid input', 400);
    }
    $logger->debug("Received data: " . json_encode($input));

    // Rate limiting (100 requests per minute per IP)
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM visits WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
    $stmt->execute([$ip]);
    $count = $stmt->fetch()['count'];
    
    if ($count >= 100) {
        $logger->error("Rate limit exceeded for IP: $ip");
        sendError('Rate limit exceeded', 429);
    }

    // Prepare and execute insert statement
    $sql = "INSERT INTO visits (
        session_id, ip_address, isp, city, region, country, 
        coordinates, timezone, user_agent, language, 
        screen_resolution, device_data, url, referrer, load_time,
        visit_count, first_visit, last_visit, first_source
    ) VALUES (
        :session_id, :ip_address, :isp, :city, :region, :country,
        :coordinates, :timezone, :user_agent, :language,
        :screen_resolution, :device_data, :url, :referrer, :load_time,
        :visit_count, :first_visit, :last_visit, :first_source
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'session_id' => $input['session_id'],
        'ip_address' => $input['ip'] ?? $ip,
        'isp' => $input['isp'] ?? null,
        'city' => $input['city'] ?? null,
        'region' => $input['region'] ?? null,
        'country' => $input['country'] ?? null,
        'coordinates' => $input['loc'] ?? null,
        'timezone' => $input['timezone'] ?? null,
        'user_agent' => $input['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'],
        'language' => $input['language'] ?? null,
        'screen_resolution' => $input['screenResolution'] ?? null,
        'device_data' => $input['deviceData'] ?? null,
        'url' => $input['url'] ?? null,
        'referrer' => $input['referrer'] ?? null,
        'load_time' => $input['loadTime'] ?? null,
        'visit_count' => $input['visitCount'] ?? 1,
        'first_visit' => $input['firstVisit'] ?? date('Y-m-d H:i:s'),
        'last_visit' => $input['lastVisit'] ?? date('Y-m-d H:i:s'),
        'first_source' => $input['firstSource'] ?? $input['referrer'] ?? null
    ]);

    $logger->info("Successfully logged visit data for session: " . $input['session_id']);

    // Send success response
    http_response_code(201);
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    // Log error securely (don't expose details to client)
    $logger->error("Database Error: " . $e->getMessage());
    sendError('Database error occurred');
} catch (Exception $e) {
    $logger->error("General Error: " . $e->getMessage());
    sendError('Internal server error');
}
?> 