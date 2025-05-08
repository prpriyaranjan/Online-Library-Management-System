<?php
include '../database/db_connect.php';

if (!isset($_GET['user_id'])) {
    echo "No user selected.";
    exit();
}

$user_id = intval($_GET['user_id']);

$query = "SELECT id, payment_date, status, screenshot FROM fee_payments WHERE user_id = $user_id ORDER BY payment_date DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo "Failed to fetch payment history.";
    exit();
}

if (mysqli_num_rows($result) == 0) {
    echo "<p>No payment history found.</p>";
} else {
    echo "<table>
            <thead>
                <tr>
                    <th>Payment Date</th>
                    <th>Status</th>
                    <th>Screenshot</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>" . date('d M Y', strtotime($row['payment_date'])) . "</td>
                <td class='status " . $row['status'] . "'>" . ucfirst($row['status']) . "</td>
                <td>";
        if ($row['screenshot']) {
            echo "<a href='../" . htmlspecialchars($row['screenshot']) . "' target='_blank' class='view-screenshot'>View</a>";
        } else {
            echo "N/A";
        }
        echo "</td>
              <td>
                <form method='POST' action='verify_fee.php' style='display:inline;'>
                    <input type='hidden' name='user_id' value='$user_id'>
                    <input type='hidden' name='payment_id' value='" . $row['id'] . "'>";

        if (strtolower($row['status']) !== 'confirmed') {
            echo "<button type='submit' name='confirm_fee' class='btn-approve'>Confirm</button>
                  <button type='submit' name='decline_fee' class='btn-reject'>Decline</button>";
        }

        echo "  <button type='submit' name='delete_payment' class='btn-reject' onclick=\"return confirm('Delete this payment history?');\">Delete</button>
                </form>
              </td>
            </tr>";
    }

    echo "</tbody></table>";
}
?>
