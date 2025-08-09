<?php
$pdo = new PDO("mysql:host=localhost;dbname=ncnhsdb;charset=utf8", "root", "root");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Add August 2025 events
$augustEvents = [
    [
        'title' => 'Faculty Meeting',
        'description' => 'Monthly faculty meeting to discuss curriculum updates',
        'event_date' => '2025-08-15',
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
        'location' => 'Conference Room',
        'event_type' => 'meeting',
        'organizer' => 'Principal Office'
    ],
    [
        'title' => 'Science Fair',
        'description' => 'Annual school science fair exhibition',
        'event_date' => '2025-08-20',
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
        'location' => 'Main Gymnasium', 
        'event_type' => 'academic',
        'organizer' => 'Science Department'
    ],
    [
        'title' => 'School Holiday',
        'description' => 'National Heroes Day',
        'event_date' => '2025-08-26',
        'start_time' => '00:00:00',
        'end_time' => '23:59:59',
        'location' => 'School Wide',
        'event_type' => 'holiday',
        'organizer' => 'Administration'
    ]
];

$stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, start_time, end_time, location, event_type, organizer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($augustEvents as $event) {
    $stmt->execute([
        $event['title'],
        $event['description'], 
        $event['event_date'],
        $event['start_time'],
        $event['end_time'],
        $event['location'],
        $event['event_type'],
        $event['organizer']
    ]);
}

echo "✅ Added " . count($augustEvents) . " August 2025 events!\n";
echo "Events added for: Aug 15, Aug 20, Aug 26\n";
?>
