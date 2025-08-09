<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=ncnhsdb;charset=utf8", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all events
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform to match calendar format
    $transformedEvents = [];
    foreach ($events as $event) {
        $transformedEvents[] = [
            'id' => $event['id'],
            'date' => $event['event_date'],
            'title' => $event['title'],
            'type' => $event['event_type'],
            'time' => formatTime($event['start_time'], $event['end_time']),
            'location' => $event['location'],
            'description' => $event['description'] ?? '',
            'organizer' => $event['organizer']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($events),
        'events' => $transformedEvents,
        'raw_events' => $events
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function formatTime($startTime, $endTime) {
    $formatTimeString = function($time) {
        $parts = explode(':', $time);
        $hour = (int)$parts[0];
        $ampm = $hour >= 12 ? 'PM' : 'AM';
        $hour12 = $hour % 12 ?: 12;
        return $hour12 . ':' . $parts[1] . ' ' . $ampm;
    };
    
    return $formatTimeString($startTime) . ' - ' . $formatTimeString($endTime);
}
?>
