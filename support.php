<?php
include("config.php");
session_start();

$isLoggedIn = isset($_SESSION['UID']);
$uid = $isLoggedIn ? intval($_SESSION['UID']) : 0;

$userEmail = "";
if ($isLoggedIn) {
    $userStmt = $conn->prepare("SELECT email FROM `user` WHERE user_id = ? LIMIT 1");
    $userStmt->bind_param("i", $uid);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    if ($userRow = $userResult->fetch_assoc()) {
        $userEmail = $userRow["email"];
    }
}

$trackedTicket = null;
$supportAction = $_GET["action"] ?? "";
$isTrackingAction = $supportAction === "track_ticket";
$trackCode = trim($_GET["ticket_code"] ?? "");
$trackEmail = trim($_GET["ticket_email"] ?? "");
$hasTrackSearch = $isTrackingAction;
if ($isTrackingAction && $trackCode !== "" && $trackEmail !== "" && filter_var($trackEmail, FILTER_VALIDATE_EMAIL)) {
    $trackStmt = $conn->prepare("SELECT ticket_code, email, phone, subject, message, status, admin_response, responded_at, created_at, updated_at FROM support_tickets WHERE ticket_code = ? AND email = ? LIMIT 1");
    $trackStmt->bind_param("ss", $trackCode, $trackEmail);
    $trackStmt->execute();
    $trackedTicket = $trackStmt->get_result()->fetch_assoc();
}

$tickets = [];
if ($isLoggedIn) {
    $ticketStmt = $conn->prepare("SELECT ticket_code, subject, status, created_at, updated_at FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
    $ticketStmt->bind_param("i", $uid);
    $ticketStmt->execute();
    $ticketResult = $ticketStmt->get_result();
    while ($ticketRow = $ticketResult->fetch_assoc()) {
        $tickets[] = $ticketRow;
    }
}

include("navbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid px-3 px-md-5" style="margin-top:60px;">
        <h1 class="inter-bold-44 mb-4 mt-4 mt-md-5">Support</h1>

        <?php if (isset($_SESSION["feedback"])): ?>
            <div class="alert alert-info inter-extralight-24" role="status">
                <?php echo htmlspecialchars($_SESSION["feedback"]); ?>
            </div>
            <?php unset($_SESSION["feedback"]); ?>
        <?php endif; ?>

        <?php if ($isTrackingAction): ?>
            <?php unset($_SESSION["ticket_code"]); ?>
        <?php elseif (isset($_SESSION["ticket_code"])): ?>
            <div class="alert alert-success inter-extralight-24" role="status">
                Ticket submitted. Tracking code: <strong><?php echo htmlspecialchars($_SESSION["ticket_code"]); ?></strong>
            </div>
            <?php unset($_SESSION["ticket_code"]); ?>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <?php if (!$isLoggedIn): ?>
                    <div class="support-panel">
                        <h2 class="inter-bold-32 mb-4">Submit Ticket</h2>
                        <p class="inter-extralight-24">Please log in to submit a new support ticket.</p>
                        <a href="login.php" class="btn btn-outline-black inter-medium-25 border_black">Login</a>
                    </div>
                <?php else: ?>
                <form action="submit_ticket.php" method="post" class="support-panel">
                    <input type="hidden" name="action" value="submit_ticket">
                    <h2 class="inter-bold-32 mb-4">Submit Ticket</h2>
                    <div class="mb-4">
                        <label for="submit_ticket_email" class="inter-bold-24 mb-2">Email</label>
                        <input type="email" id="submit_ticket_email" name="email"
                            class="form-control inter-medium-25 left-placeholder border_black"
                            value="<?php echo htmlspecialchars($userEmail); ?>" placeholder="Add an email" readonly required>
                    </div>
                    <div class="mb-4">
                        <label for="ticket_phone" class="inter-bold-24 mb-2">Phone Number</label>
                        <input type="tel" id="ticket_phone" name="phone"
                            class="form-control inter-medium-25 left-placeholder border_black"
                            placeholder="Add a phone number" required>
                    </div>
                    <div class="mb-4">
                        <label for="ticket_subject" class="inter-bold-24 mb-2">Subject</label>
                        <input type="text" id="ticket_subject" name="subject"
                            class="form-control inter-medium-25 left-placeholder border_black"
                            placeholder="Add a subject" maxlength="255" required>
                    </div>
                    <div class="mb-4">
                        <label for="ticket_message" class="inter-bold-24 mb-2">Message</label>
                        <textarea id="ticket_message" name="message"
                            class="form-control inter-medium-25 left-placeholder border_black" rows="5"
                            placeholder="Add a message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-black inter-medium-24 active">
                        <img src="assets/icons/post.png" alt="post Icon"
                            style="width:20px; height:20px; margin-right:8px; vertical-align:middle;">
                        Submit Ticket
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <div class="col-12 col-lg-6">
                <div class="support-panel">
                    <h2 class="inter-bold-32 mb-4">Track Ticket</h2>
                    <form action="support.php" method="get" class="mb-4">
                        <input type="hidden" name="action" value="track_ticket">
                        <label for="ticket_code" class="inter-bold-24 mb-2">Ticket Code</label>
                        <div class="d-flex gap-2 flex-column mb-3">
                            <input type="text" id="ticket_code" name="ticket_code"
                                class="form-control inter-medium-25 left-placeholder border_black"
                                value="<?php echo htmlspecialchars($trackCode); ?>" placeholder="Enter ticket code">
                            <label for="ticket_email" class="inter-bold-24 mb-2">Ticket Email</label>
                            <input type="email" id="ticket_email" name="ticket_email"
                                class="form-control inter-medium-25 left-placeholder border_black"
                                value="<?php echo htmlspecialchars($trackEmail); ?>" placeholder="Enter ticket email">
                            <button type="submit" class="btn btn-outline-black inter-medium-25 border_black">Track</button>
                        </div>
                    </form>

                    <?php if ($isTrackingAction): ?>
                        <section class="tracking-result-section mb-4">
                            <h3 class="inter-bold-24 mb-3">Tracking Result</h3>

                            <?php if ($trackCode === "" || $trackEmail === ""): ?>
                                <div class="alert alert-warning inter-extralight-24">Enter the email used for this ticket.</div>
                            <?php endif; ?>

                            <?php if ($trackCode !== "" && $trackEmail !== "" && !$trackedTicket): ?>
                                <div class="alert alert-warning inter-extralight-24">No matching ticket found.</div>
                            <?php endif; ?>

                            <?php if ($trackedTicket): ?>
                                <div class="tracking-ticket-detail">
                                    <p class="inter-bold-24 mb-2"><?php echo htmlspecialchars($trackedTicket["subject"]); ?></p>
                                    <p class="inter-extralight-15 mb-2">Code: <?php echo htmlspecialchars($trackedTicket["ticket_code"]); ?></p>
                                    <p class="inter-extralight-15 mb-2">Status: <?php echo htmlspecialchars($trackedTicket["status"]); ?></p>
                                    <p class="inter-extralight-24 mb-3"><?php echo nl2br(htmlspecialchars($trackedTicket["message"])); ?></p>

                                    <div class="mt-3" style="border-top:2px solid var(--artizo-border); padding-top:16px;">
                                        <p class="inter-bold-24 mb-2">Admin Response</p>
                                        <?php if (!empty($trackedTicket["admin_response"])): ?>
                                            <p class="inter-extralight-24 mb-2"><?php echo nl2br(htmlspecialchars($trackedTicket["admin_response"])); ?></p>
                                            <?php if (!empty($trackedTicket["responded_at"])): ?>
                                                <p class="inter-extralight-15 mb-0">Responded at: <?php echo htmlspecialchars($trackedTicket["responded_at"]); ?></p>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="inter-extralight-24 mb-0">No admin response yet.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>

                    <?php if ($isLoggedIn): ?>
                        <section class="my-tickets-section">
                        <h3 class="inter-bold-24 mb-3">My Tickets</h3>
                        <?php if (empty($tickets)): ?>
                            <p class="inter-extralight-24">No support tickets yet.</p>
                        <?php endif; ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <div class="card_border mb-3" style="padding: 18px 22px;">
                                <div class="d-flex justify-content-between gap-3 flex-column flex-sm-row">
                                    <div>
                                        <p class="inter-bold-24 mb-1"><?php echo htmlspecialchars($ticket["subject"]); ?></p>
                                        <p class="inter-extralight-15 mb-0"><?php echo htmlspecialchars($ticket["ticket_code"]); ?></p>
                                    </div>
                                    <div class="text-sm-end">
                                        <p class="inter-extralight-15 mb-1"><?php echo htmlspecialchars($ticket["status"]); ?></p>
                                        <a href="support.php?ticket_code=<?php echo urlencode($ticket["ticket_code"]); ?>&ticket_email=<?php echo urlencode($userEmail); ?>" class="inter-extralight-15">View</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </section>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <?php include("footer.php"); ?>
    </footer>
</body>

</html>
