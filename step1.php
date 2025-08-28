<?php
require __DIR__.'/db.php'; // starts the session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // minimal sanitization
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $addr  = trim($_POST['address'] ?? '');
    $city  = trim($_POST['city'] ?? '');
    $state = strtoupper(trim($_POST['state'] ?? ''));
    $phone = preg_replace('/\D+/', '', $_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // store to session for step2/review
    $_SESSION['customer'] = [
        'first_name' => $first,
        'last_name'  => $last,
        'address'    => $addr,
        'city'       => $city,
        'state'      => $state,
        'phone'      => $phone,
        'email'      => $email,
    ];

    // redirect AFTER setting session
    header('Location: step2.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><title>Checkout — Step 1</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar"><div class="container"><span class="navbar-brand">Mass Garage Doors Expert</span></div></nav>
<main class="container container-narrow py-5">
  <p class="step mb-3">Step 1 of 3 — Customer Details</p>

  <!-- Post back to THIS page so the redirect code runs -->
  <form method="post" action="step1.php">
    <input name="first_name" placeholder="First Name" class="form-control mb-2" required>
    <input name="last_name"  placeholder="Last Name"  class="form-control mb-2" required>
    <input name="address"    placeholder="Address"    class="form-control mb-2" required>
    <input name="city"       placeholder="City"       class="form-control mb-2" required>
    <input name="state"      placeholder="State"      class="form-control mb-2" maxlength="2" required>
    <input name="phone"      placeholder="Phone"      class="form-control mb-2" required>
    <input name="email"      placeholder="Email"      class="form-control mb-2" type="email" required>
    <button class="btn btn-primary mt-3">Continue</button>
  </form>
</main>
</body>
</html>
