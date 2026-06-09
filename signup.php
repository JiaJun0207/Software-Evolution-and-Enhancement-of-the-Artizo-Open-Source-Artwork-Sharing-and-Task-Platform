<?php
session_start(); // to start a session
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container-fluid">
        <div class="row vh-100">
            <div class="col-md-6 d-flex justify-content-center align-items-center bg-white">
                <div class="text-center" style="width: 100%; max-width: 400px;">

                    <img src="assets/logo/onboarding_logo.png" alt="Logo" class="img-fluid mb-4">

                    <div class="d-flex gap-2 mb-3">
                        <a href="login.php" class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black<?php if(basename($_SERVER['PHP_SELF'])=='login.php'){echo ' active';} ?>">Login</a>
                        <a href="signup.php" class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black<?php if(basename($_SERVER['PHP_SELF'])=='signup.php'){echo ' active';} ?>">Sign Up</a>
                    </div>

                    <form action="signup_form.php" method="post">
                        <input type="text" name="user_name" id="user_name" class="form-control mb-3 inter-medium-25 border_black" placeholder="Username">
                        <input type="email" id="email" name="email" class="form-control mb-3 inter-medium-25 border_black" placeholder="Email">
                        <div class="input-group gap-2 mb-3">
                            <input type="password" id="password" name="password" class="form-control inter-medium-25 border_black" placeholder="Password" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" title="Password must be at least 8 characters and include both letters and numbers.">
                            <button type="button" class="btn btn-outline-black border_black password-toggle" data-target="password" aria-label="Show password" aria-pressed="false">
                                <svg class="eye-icon eye-icon-hidden" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M2.3 2.3 21.7 21.7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M10.6 5.1c.5-.1.9-.1 1.4-.1 5.1 0 9.3 4.4 10.5 6.9-.4.9-1.4 2.2-2.8 3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6.4 6.7C4 8.2 2.4 10.5 1.5 11.9 2.7 14.4 6.9 18.8 12 18.8c1.7 0 3.2-.5 4.5-1.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M14.1 9.9a3 3 0 0 0-4.2 4.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <svg class="eye-icon eye-icon-visible d-none" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </button>
                        </div>
                        <div class="input-group gap-2 mb-3">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control inter-medium-25 border_black" placeholder="Confirm Password">
                            <button type="button" class="btn btn-outline-black border_black password-toggle" data-target="confirm_password" aria-label="Show confirm password" aria-pressed="false">
                                <svg class="eye-icon eye-icon-hidden" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M2.3 2.3 21.7 21.7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M10.6 5.1c.5-.1.9-.1 1.4-.1 5.1 0 9.3 4.4 10.5 6.9-.4.9-1.4 2.2-2.8 3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6.4 6.7C4 8.2 2.4 10.5 1.5 11.9 2.7 14.4 6.9 18.8 12 18.8c1.7 0 3.2-.5 4.5-1.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M14.1 9.9a3 3 0 0 0-4.2 4.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <svg class="eye-icon eye-icon-visible d-none" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-outline-black w-100 mb-3 inter-medium-25 border_black">Register</button>
                    </form>

                    <div id="feedback-message" class="feedback-error">
                    <?php
                    if (isset($_SESSION["feedback"])) {
                        echo $_SESSION["feedback"];
                        unset($_SESSION["feedback"]);
                    }
                    ?>
                    </div>
                    <script>
                      const feedback = document.getElementById('feedback-message');
                      if (feedback && feedback.textContent.trim() !== "") {
                        setTimeout(() => {
                          feedback.style.display = 'none';
                        }, 3000); // 3 seconds
                      }

                      document.querySelectorAll('.password-toggle').forEach((button) => {
                        button.addEventListener('click', () => {
                          const input = document.getElementById(button.dataset.target);
                          if (!input) {
                            return;
                          }

                          const isHidden = input.type === 'password';
                          input.type = isHidden ? 'text' : 'password';
                          button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                          button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                          button.querySelector('.eye-icon-hidden').classList.toggle('d-none', isHidden);
                          button.querySelector('.eye-icon-visible').classList.toggle('d-none', !isHidden);
                        });
                      });
                    </script>

                </div>

            </div>

            <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center p-0"
                style="background: #f8f9fa;">
                <img src="assets/onboarding/image.jpg" alt="Image description"
                    style="width: 100%; height: 100vh; object-fit: cover;">
            </div>
        </div>
    </div>

</body>

</html>
