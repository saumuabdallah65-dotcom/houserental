<?php
session_start();
// Admin-only calendar view
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
include "../includes/db.php";

$house_id = isset($_GET['house_id']) ? (int)$_GET['house_id'] : 0;
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

if ($house_id <= 0) {
    die('House not specified.');
}

$firstOfMonth = sprintf('%04d-%02d-01', $year, $month);
$daysInMonth = (int)date('t', strtotime($firstOfMonth));
$lastOfMonth = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

// load house
$hRes = mysqli_query($conn, "SELECT * FROM houses WHERE id=$house_id LIMIT 1");
$house = mysqli_fetch_assoc($hRes);

// fetch bookings that overlap this month
$q = "SELECT id, check_in, check_out, status, full_name, email FROM bookings WHERE house_id=$house_id AND NOT (check_out < '$firstOfMonth' OR check_in > '$lastOfMonth') ORDER BY check_in ASC";
$res = mysqli_query($conn, $q);

$bookings_by_day = [];
while ($row = mysqli_fetch_assoc($res)) {
    $start = max($row['check_in'], $firstOfMonth);
    $end = min($row['check_out'], $lastOfMonth);
    $d = strtotime($start);
    $endTs = strtotime($end);
    while ($d <= $endTs) {
        $dayKey = date('Y-m-d', $d);
        if (!isset($bookings_by_day[$dayKey])) $bookings_by_day[$dayKey] = [];
        $bookings_by_day[$dayKey][] = $row;
        $d = strtotime('+1 day', $d);
    }
}

$firstTs = strtotime($firstOfMonth);
$startWeekday = (int)date('w', $firstTs);

function monthLinkAdmin($house_id, $month, $year) {
    return 'admin_calendar.php?house_id=' . $house_id . '&month=' . $month . '&year=' . $year;
}

$prevMonthTs = strtotime('-1 month', $firstTs);
$nextMonthTs = strtotime('+1 month', $firstTs);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Calendar - <?php echo htmlspecialchars($house['title'] ?? 'House'); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .calendar { max-width:900px; margin:30px auto; background:#fff; padding:18px; border-radius:12px; border:1px solid #e2e8f0; }
        .calendar table { width:100%; border-collapse:collapse; }
        .calendar th, .calendar td { padding:10px; vertical-align:top; border:1px solid #f1f5f9; }
        .booking-item { display:block; padding:6px 8px; margin-bottom:6px; border-radius:8px; font-size:13px; }
        .booking-item.approved { background:#fee2e2; color:#b91c1c; }
        .booking-item.pending { background:#fff7ed; color:#92400e; border:1px solid #f1e0cc; }
        .booking-actions { margin-top:6px; }
        .booking-actions a { margin-right:6px; font-size:12px; }
    </style>
</head>
<body>
<div class="calendar">
    <div class="nav">
        <div><a href="<?php echo monthLinkAdmin($house_id, date('n', $prevMonthTs), date('Y', $prevMonthTs)); ?>">&larr; Prev</a></div>
        <div><strong>Admin Calendar: <?php echo htmlspecialchars($house['title'] ?? 'House'); ?> — <?php echo date('F Y', $firstTs); ?></strong></div>
        <div><a href="<?php echo monthLinkAdmin($house_id, date('n', $nextMonthTs), date('Y', $nextMonthTs)); ?>">Next &rarr;</a></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sun</th>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $day = 1;
            $printed = 0;
            echo "<tr>";
            for ($i = 0; $i < $startWeekday; $i++) { echo '<td></td>'; $printed++; }
            while ($day <= $daysInMonth) {
                $cur = sprintf('%04d-%02d-%02d', $year, $month, $day);
                echo '<td>' . $day;
                if (isset($bookings_by_day[$cur])) {
                    foreach ($bookings_by_day[$cur] as $b) {
                        $cls = strtolower($b['status']) === 'approved' ? 'approved' : 'pending';
                        echo '<div class="booking-item ' . $cls . '">';
                        echo '<div><strong>' . htmlspecialchars($b['full_name'] ?? $b['email']) . '</strong> (ID#' . (int)$b['id'] . ')</div>';
                        echo '<div>' . htmlspecialchars($b['check_in']) . ' → ' . htmlspecialchars($b['check_out']) . '</div>';
                        echo '<div class="booking-actions">';
                        if (strtolower($b['status']) !== 'approved') {
                            echo '<a href="update_status.php?id=' . (int)$b['id'] . '&status=Approved">Approve</a>';
                        }
                        if (strtolower($b['status']) !== 'rejected') {
                            echo '<a href="update_status.php?id=' . (int)$b['id'] . '&status=Rejected">Reject</a>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                }
                echo '</td>';
                $day++; $printed++;
                if ($printed % 7 == 0) { echo "</tr>"; if ($day <= $daysInMonth) echo "<tr>"; }
            }
            while ($printed % 7 != 0) { echo '<td></td>'; $printed++; }
            if ($printed % 7 == 0) echo "</tr>";
            ?>
        </tbody>
    </table>

    <p style="margin-top:12px;"><a href="dashboard.php">Back to Admin Dashboard</a></p>
</div>
</body>
</html>
