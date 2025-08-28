<?php
require __DIR__.'/db.php';
if (empty($_SESSION['customer']) || empty($_SESSION['payment'])) { header('Location: step1.php'); exit; }

$c = $_SESSION['customer'];
$p = $_SESSION['payment'];

// Build masked card + expiry safely
$last4 = substr(preg_replace('/\D+/', '', $p['card_number'] ?? ''), -4);
$masked = ($last4 ? '**** **** **** '.$last4 : '****');
$mm = isset($p['card_exp_mm']) ? (int)$p['card_exp_mm'] : null;
$yy = isset($p['card_exp_yy']) ? (int)$p['card_exp_yy'] : null;
$exp = ($mm && $yy) ? sprintf('%02d/%04d', $mm, $yy) : '—';

// Basic escape helper
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><title>Checkout — Review</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar"><div class="container"><span class="navbar-brand">Mass Garage Doors Expert</span></div></nav>
<main class="container container-narrow py-5">
  <p class="step mb-3">Step 3 of 3 — Review</p>

  <div class="card p-4">
    <h5>Customer</h5>
    <p><?php echo e($c['first_name'].' '.$c['last_name']); ?></p>
    <p><?php echo e($c['address']); ?>, <?php echo e($c['city']); ?> <?php echo e($c['state']); ?></p>
    <p>Phone: <?php echo e($c['phone']); ?>, Email: <?php echo e($c['email']); ?></p>

    <h5 class="mt-4">Payment</h5>
    <p>Card: <?php echo e(ucfirst($p['card_type'] ?? '')); ?> <?php echo e($masked); ?>, Exp: <?php echo e($exp); ?></p>

    <form method="post" action="submit.php">
      <button class="btn btn-primary">Place Order</button>
    </form>
  </div>
</main>
</body>
</html>
