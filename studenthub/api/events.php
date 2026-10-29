<?php
// Practical 13: PHP JSON API for events (GET list/search, POST create/update/delete)
$IS_API = true;
require_once __DIR__ . '/../includes/functions.php'; require_once __DIR__ . '/../includes/db.php';
set_exception_handler(function (Throwable $t) { error_log($t->getMessage()); json_out(['success' => false, 'message' => 'Server error.'], 500); });
try_remember_login($pdo);
if (!is_logged_in()) json_out(['success' => false, 'message' => 'Authentication required.'], 401);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $q = clean($_GET['q'] ?? ''); $sql = 'SELECT id,title,category,event_date,venue,status FROM events'; $par = [];
    if ($q !== '') { $sql .= ' WHERE title LIKE ? OR venue LIKE ? OR category LIKE ?'; $par = ["%$q%", "%$q%", "%$q%"]; }
    $st = $pdo->prepare($sql . ' ORDER BY event_date DESC LIMIT 100'); $st->execute($par);
    json_out(['success' => true, 'data' => $st->fetchAll()]);
}
if ($method !== 'POST') json_out(['success' => false, 'message' => 'Method not allowed.'], 405);
if (!is_admin()) json_out(['success' => false, 'message' => 'Admin access required.'], 403);
$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) json_out(['success' => false, 'message' => 'Invalid JSON body.'], 400);
if (!csrf_check($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) json_out(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
$act = $in['action'] ?? '';

if ($act === 'delete') {
    $id = (int)($in['id'] ?? 0); if (!$id) json_out(['success' => false, 'message' => 'Event id required.'], 422);
    $st = $pdo->prepare('DELETE FROM events WHERE id=?'); $st->execute([$id]);
    if (!$st->rowCount()) json_out(['success' => false, 'message' => 'Event not found.'], 404);
    audit($pdo, 'api_event_delete', "id $id"); json_out(['success' => true, 'message' => 'Event deleted.']);
}
if (in_array($act, ['create', 'update'], true)) {
    $title = clean($in['title'] ?? ''); $cat = clean($in['category'] ?? 'General'); $date = $in['event_date'] ?? ''; $venue = clean($in['venue'] ?? ''); $status = $in['status'] ?? 'open';
    $err = [];
    if (mb_strlen($title) < 3 || mb_strlen($title) > 120) $err['title'] = 'Title must be 3-120 characters.';
    $d = DateTime::createFromFormat('Y-m-d', $date); if (!$d || $d->format('Y-m-d') !== $date) $err['event_date'] = 'Valid date (YYYY-MM-DD) required.';
    if ($venue === '') $err['venue'] = 'Venue required.';
    if (!in_array($status, ['open', 'closed'], true)) $err['status'] = 'Status must be open or closed.';
    if ($err) json_out(['success' => false, 'message' => 'Validation failed.', 'errors' => $err], 422);
    if ($act === 'create') {
        $pdo->prepare('INSERT INTO events (title,category,event_date,venue,status,created_by) VALUES (?,?,?,?,?,?)')->execute([$title, $cat, $date, $venue, $status, current_user()['id']]);
        audit($pdo, 'api_event_create', $title); json_out(['success' => true, 'message' => 'Event created.', 'id' => (int)$pdo->lastInsertId()], 201);
    }
    $id = (int)($in['id'] ?? 0);
    $st = $pdo->prepare('UPDATE events SET title=?,category=?,event_date=?,venue=?,status=? WHERE id=?'); $st->execute([$title, $cat, $date, $venue, $status, $id]);
    audit($pdo, 'api_event_update', "id $id"); json_out(['success' => true, 'message' => 'Event updated.']);
}
json_out(['success' => false, 'message' => 'Unknown action.'], 400);
