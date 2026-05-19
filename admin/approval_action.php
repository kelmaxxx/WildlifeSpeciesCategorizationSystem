<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/activity.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_approvals.php');
    exit;
}
csrf_check();

$id       = Mongo::oid($_POST['id'] ?? null);
$decision = $_POST['decision'] ?? '';

if (!$id || !in_array($decision, ['approve', 'reject'], true)) {
    header('Location: manage_approvals.php');
    exit;
}

$species = $db->findById('species', $id);
if (!$species) {
    header('Location: manage_approvals.php');
    exit;
}

$newStatus = $decision === 'approve' ? 'approved' : 'rejected';
$db->update('species', ['_id' => $id], ['approval_status' => $newStatus]);
log_activity($db, $decision, 'species', $species->name ?? '(unnamed)');

header('Location: manage_approvals.php');
