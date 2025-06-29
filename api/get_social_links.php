<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $socialLinks = getSocialLinks();
    echo json_encode($socialLinks);
} catch (Exception $e) {
    error_log("Error in get_social_links.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>