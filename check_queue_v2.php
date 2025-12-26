<?php
require_once __DIR__ . '/modelo/ExpedienteQueue.php';

try {
    $queue = new ExpedienteQueue();
    $jobs = $queue->getAll(10); // Get last 10 jobs

    foreach ($jobs as $job) {
        echo "ID: " . $job['id'] . "\n";
        echo "Status: " . $job['status'] . "\n";
        $data = json_decode($job['data'] ?? '{}', true);
        echo "Filename: " . ($data['filename'] ?? 'N/A') . "\n";
        echo "Error: " . ($job['error_message'] ?? 'N/A') . "\n";
        echo "Created: " . $job['created_at'] . "\n";
        echo "--------------------------------\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
