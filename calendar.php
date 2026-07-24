<?php
session_start();
include "includes/db.php";

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

// fetch bookings that overlap this month (any status)
$q = "SELECT id, check_in, check_out, status, full_name, email FROM bookings WHERE house_id=$house_id AND NOT (check_out < '$firstOfMonth' OR check_in > '$lastOfMonth')";
$res = mysqli_query($conn, $q);

$booked = [];
$pending = [];
$info = [];
while ($row = mysqli_fetch_assoc($res)) {
    $startRaw = $row['check_in'];
    $endRaw = $row['check_out'];
    $start = max($startRaw, $firstOfMonth);
    $end = min($endRaw, $lastOfMonth);
    $d = strtotime($start);
    $endTs = strtotime($end);
    $statusLower = strtolower($row['status'] ?? '');
    $bookingId = isset($row['id']) ? (int)$row['id'] : 0;
    $owner = trim($row['full_name'] ?? ($row['email'] ?? ''));
    while ($d <= $endTs) {
        $dayKey = date('Y-m-d', $d);
        if ($statusLower === 'approved') {
            $booked[$dayKey] = true;
            $info[$dayKey] = 'Approved (ID#' . $bookingId . ', ' . $owner . '): ' . $startRaw . ' → ' . $endRaw;
        } else {
            // treat everything else as pending/blocked
            if (!isset($booked[$dayKey])) {
                $pending[$dayKey] = true;
                $info[$dayKey] = ucfirst($statusLower ?: 'Pending') . ' (ID#' . $bookingId . ', ' . $owner . '): ' . $startRaw . ' → ' . $endRaw;
            }
        }
        $d = strtotime('+1 day', $d);
    }
}

// calendar calculations
$firstTs = strtotime($firstOfMonth);
$startWeekday = (int)date('w', $firstTs); // 0 (Sun) - 6 (Sat)

function monthLink($house_id, $month, $year) {
    return 'calendar.php?house_id=' . $house_id . '&month=' . $month . '&year=' . $year;
}

$prevMonthTs = strtotime('-1 month', $firstTs);
$nextMonthTs = strtotime('+1 month', $firstTs);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Availability Calendar</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .calendar { max-width:900px; margin:30px auto; background:#fff; padding:18px; border-radius:12px; border:1px solid #e2e8f0; }
        .calendar table { width:100%; border-collapse:collapse; }
        .calendar th, .calendar td { padding:10px; text-align:center; border:1px solid #f1f5f9; }
        .calendar td.booked { background:#fee2e2; color:#b91c1c; font-weight:700; }
        .calendar td.pending { background:#fff7ed; color:#92400e; font-weight:600; }
        .calendar td.available { background:#e6fffa; color:#0f766e; font-weight:600; }
        .calendar .nav { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">House Rental</div>
    <input type="checkbox" id="nav-toggle" class="nav-toggle-checkbox" hidden>
    <label for="nav-toggle" class="nav-toggle" aria-label="Toggle navigation menu">☰</label>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="houses.php">Houses</a>
        <a href="index.php#contact">Contact</a>
        <?php if (isset($_SESSION['user'])) { ?>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php } else { ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php } ?>
    </div>
</nav>
<div class="calendar">
    <div class="nav">
        <div><a href="<?php echo monthLink($house_id, date('n', $prevMonthTs), date('Y', $prevMonthTs)); ?>">&larr; Prev</a></div>
        <div><strong><?php echo htmlspecialchars($house['title'] ?? 'House'); ?> — <?php echo date('F Y', $firstTs); ?></strong></div>
        <div><a href="<?php echo monthLink($house_id, date('n', $nextMonthTs), date('Y', $nextMonthTs)); ?>">Next &rarr;</a></div>
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
            // first week
            echo "<tr>";
            for ($i = 0; $i < $startWeekday; $i++) { echo '<td></td>'; $printed++; }
            while ($day <= $daysInMonth) {
                $cur = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $cls = '';
                $title = '';
                if (isset($booked[$cur])) {
                    $cls = 'booked';
                    $title = $info[$cur] ?? '';
                } elseif (isset($pending[$cur])) {
                    $cls = 'pending';
                    $title = $info[$cur] ?? '';
                } else {
                    $cls = 'available';
                    $title = 'Available';
                }
                echo '<td class="' . $cls . '" title="' . htmlspecialchars($title) . '">' . $day . '</td>';
                $day++; $printed++;
                if ($printed % 7 == 0) { echo "</tr>"; if ($day <= $daysInMonth) echo "<tr>"; }
            }
            while ($printed % 7 != 0) { echo '<td></td>'; $printed++; }
            if ($printed % 7 == 0) echo "</tr>";
            ?>
        </tbody>
    </table>

    <p style="margin-top:12px;">
        <span style="display:inline-block;width:12px;height:12px;background:#fee2e2;margin-right:6px;border-radius:3px;"></span>Booked (approved)
        &nbsp;&nbsp;
        <span style="display:inline-block;width:12px;height:12px;background:#fff7ed;margin-right:6px;border-radius:3px;border:1px solid #f1e0cc;"></span>Pending
        &nbsp;&nbsp;
        <span style="display:inline-block;width:12px;height:12px;background:#e6fffa;margin-right:6px;border-radius:3px;border:1px solid #a7f3d0;"></span>Available
    </p>

    <p style="margin-top:8px;"><a href="details.php?id=<?php echo $house_id; ?>">Back to details</a></p>
</div>
</body>
</html>
