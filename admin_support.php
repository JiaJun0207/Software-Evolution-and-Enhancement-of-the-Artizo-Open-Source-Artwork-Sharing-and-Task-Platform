<?php
include("config.php");// Include the database connection file

session_start(); // Start the session
include("admin_auth.php"); // Restrict to authenticated admin sessions

$adminUid = intval($_SESSION['UID']);

// Allowed ticket statuses (kept in this order for the dropdown).
$allowedStatuses = ['Open', 'Pending', 'In Progress', 'Resolved'];

// --- Handle status change / response submission BEFORE any output ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_ticket') {
    $ticketId = intval($_POST['ticket_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $response = trim($_POST['admin_response'] ?? '');

    if ($ticketId <= 0 || !in_array($status, $allowedStatuses, true)) {
        $_SESSION['feedback'] = "Invalid ticket update request.";
        header("Location: admin_support.php?id=" . $ticketId);
        exit();
    }

    if ($response !== '') {
        $updateStmt = $conn->prepare(
            "UPDATE support_tickets
             SET status = ?, admin_response = ?, responded_at = NOW(), responded_by = ?
             WHERE ticket_id = ?"
        );
        $updateStmt->bind_param("ssii", $status, $response, $adminUid, $ticketId);
    } else {
        // Status-only change keeps any previous response intact.
        $updateStmt = $conn->prepare(
            "UPDATE support_tickets SET status = ? WHERE ticket_id = ?"
        );
        $updateStmt->bind_param("si", $status, $ticketId);
    }

    if ($updateStmt->execute()) {
        $_SESSION['feedback'] = "Ticket updated successfully.";
    } else {
        $_SESSION['feedback'] = "Unable to update ticket.";
    }
    header("Location: admin_support.php?id=" . $ticketId);
    exit();
}

// Admin profile image for the navbar.
$profileImg = "assets/profile/user_profile.png";
$adminStmt = $conn->prepare("SELECT profile_image FROM `user` WHERE user_id = ? LIMIT 1");
$adminStmt->bind_param("i", $adminUid);
$adminStmt->execute();
$adminResult = $adminStmt->get_result();
if ($adminRow = $adminResult->fetch_assoc()) {
    if (!empty($adminRow['profile_image'])) {
        $profileImg = "assets/profile/" . $adminRow['profile_image'];
    }
}

// Detail view if a ticket id is provided.
$detailTicket = null;
$detailTicketId = intval($_GET['id'] ?? 0);
if ($detailTicketId > 0) {
    $detailStmt = $conn->prepare(
        "SELECT s.*, u.user_name
         FROM support_tickets s
         JOIN `user` u ON s.user_id = u.user_id
         WHERE s.ticket_id = ?
         LIMIT 1"
    );
    $detailStmt->bind_param("i", $detailTicketId);
    $detailStmt->execute();
    $detailTicket = $detailStmt->get_result()->fetch_assoc();
}

// Ticket list (with optional search on code / subject / email).
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $like = "%" . $search . "%";
    $listStmt = $conn->prepare(
        "SELECT s.*, u.user_name
         FROM support_tickets s
         JOIN `user` u ON s.user_id = u.user_id
         WHERE s.ticket_code LIKE ? OR s.subject LIKE ? OR s.email LIKE ?
         ORDER BY s.created_at DESC"
    );
    $listStmt->bind_param("sss", $like, $like, $like);
} else {
    $listStmt = $conn->prepare(
        "SELECT s.*, u.user_name
         FROM support_tickets s
         JOIN `user` u ON s.user_id = u.user_id
         ORDER BY s.created_at DESC"
    );
}
$listStmt->execute();
$tickets = $listStmt->get_result();

include("admin_navbar.php"); // Include the navigation bar
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid px-3 px-md-5" style="padding-bottom:60px;">
        <h1 class="inter-bold-44 mb-4" style="margin-top:60px;">Support Management</h1>

        <?php if (isset($_SESSION['feedback'])): ?>
            <div class="alert alert-info inter-extralight-24" role="status">
                <?php echo htmlspecialchars($_SESSION['feedback']); ?>
            </div>
            <?php unset($_SESSION['feedback']); ?>
        <?php endif; ?>

        <?php if ($detailTicketId > 0): ?>
            <a href="admin_support.php" class="inter-extralight-24">&larr; Back to all tickets</a>
            <?php if (!$detailTicket): ?>
                <div class="card_border mt-3" style="padding:30px;">
                    <p class="inter-extralight-24 mb-0">Ticket not found.</p>
                </div>
            <?php else: ?>
                <div class="card_border mt-3 mb-4" style="padding:30px;">
                    <div class="d-flex justify-content-between flex-column flex-md-row gap-2 mb-3">
                        <h2 class="inter-bold-32 mb-0"><?php echo htmlspecialchars($detailTicket['subject']); ?></h2>
                        <span class="inter-medium-25"><?php echo htmlspecialchars($detailTicket['ticket_code']); ?></span>
                    </div>
                    <p class="inter-extralight-15 mb-1"><strong>User:</strong> <?php echo htmlspecialchars($detailTicket['user_name']); ?></p>
                    <p class="inter-extralight-15 mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($detailTicket['email']); ?></p>
                    <p class="inter-extralight-15 mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($detailTicket['phone']); ?></p>
                    <p class="inter-extralight-15 mb-1"><strong>Status:</strong> <?php echo htmlspecialchars($detailTicket['status']); ?></p>
                    <p class="inter-extralight-15 mb-3"><strong>Created:</strong> <?php echo htmlspecialchars($detailTicket['created_at']); ?></p>
                    <h5 class="inter-bold-24 mb-2">Message</h5>
                    <p class="inter-extralight-24"><?php echo nl2br(htmlspecialchars($detailTicket['message'])); ?></p>

                    <?php if (!empty($detailTicket['admin_response'])): ?>
                        <h5 class="inter-bold-24 mb-2 mt-4">Current Response</h5>
                        <p class="inter-extralight-24 mb-1"><?php echo nl2br(htmlspecialchars($detailTicket['admin_response'])); ?></p>
                        <p class="inter-extralight-15">Responded at: <?php echo htmlspecialchars($detailTicket['responded_at']); ?></p>
                    <?php endif; ?>
                </div>

                <form method="post" action="admin_support.php" class="card_border" style="padding:30px;">
                    <input type="hidden" name="action" value="update_ticket">
                    <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($detailTicket['ticket_id']); ?>">
                    <div class="mb-4">
                        <label for="status" class="inter-bold-24 mb-2">Status</label>
                        <select id="status" name="status" class="form-control inter-medium-25 border_black">
                            <?php foreach ($allowedStatuses as $statusOption): ?>
                                <option value="<?php echo htmlspecialchars($statusOption); ?>"
                                    <?php echo $detailTicket['status'] === $statusOption ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($statusOption); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="admin_response" class="inter-bold-24 mb-2">Response</label>
                        <textarea id="admin_response" name="admin_response" rows="5"
                            class="form-control inter-medium-25 left-placeholder border_black"
                            placeholder="Write a response to the user"><?php echo htmlspecialchars($detailTicket['admin_response'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-black inter-medium-24 active">Save Changes</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <form action="admin_support.php" method="get" class="mb-4">
                <input type="text" name="search" class="form-control inter-medium-25 left-placeholder border_black"
                    value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by code, subject or email">
            </form>

            <div class="d-none d-md-flex mb-3 px-3">
                <div class="flex-fill inter-bold-24">Code</div>
                <div class="flex-fill inter-bold-24">User</div>
                <div class="flex-fill inter-bold-24">Subject</div>
                <div class="flex-fill inter-bold-24">Status</div>
                <div class="flex-fill inter-bold-24">Response</div>
                <div class="flex-fill inter-bold-24 text-end">Action</div>
            </div>

            <?php if ($tickets->num_rows === 0): ?>
                <div class="card_border" style="padding:30px;">
                    <p class="inter-extralight-24 mb-0">No support tickets found.</p>
                </div>
            <?php endif; ?>

            <?php while ($ticket = $tickets->fetch_assoc()):
                $hasResponse = !empty($ticket['admin_response']);
            ?>
                <div class="card_border mb-3" style="padding:24px 30px;">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                        <div class="flex-fill inter-extralight-15"><?php echo htmlspecialchars($ticket['ticket_code']); ?></div>
                        <div class="flex-fill inter-extralight-15"><?php echo htmlspecialchars($ticket['user_name']); ?><br><?php echo htmlspecialchars($ticket['email']); ?></div>
                        <div class="flex-fill inter-extralight-15"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                        <div class="flex-fill inter-extralight-15"><?php echo htmlspecialchars($ticket['status']); ?></div>
                        <div class="flex-fill inter-extralight-15"><?php echo $hasResponse ? 'Responded' : 'No response'; ?></div>
                        <div class="flex-fill text-md-end">
                            <a href="admin_support.php?id=<?php echo intval($ticket['ticket_id']); ?>"
                                class="btn btn-outline-black inter-medium-25 border_black">View</a>
                        </div>
                    </div>
                    <p class="inter-extralight-15 mb-0 mt-2">Created: <?php echo htmlspecialchars($ticket['created_at']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>

</html>
