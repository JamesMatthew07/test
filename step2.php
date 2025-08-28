<?php
require __DIR__.'/db.php';
if (empty($_SESSION['customer'])) { header('Location: step1.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // very light checks; keep simple
  $type = strtolower(trim($_POST['card_type'] ?? ''));
  $num  = preg_replace('/\s+|-/', '', $_POST['card_number'] ?? '');
  $exp  = trim($_POST['card_exp'] ?? '');
  $cvv  = preg_replace('/\D+/', '', $_POST['cvv'] ?? '');

  if (!in_array($type, ['visa','mastercard','amex','discover'])) $errors['card_type']='Choose a card type';
  if ($num==='')  $errors['card_number']='Enter a card number';
  if (!preg_match('#^\d{1,2}/\d{2}$#', $exp)) $errors['card_exp']='Use MM/YY';
  if ($cvv==='' || strlen($cvv)<3 || strlen($cvv)>4) $errors['cvv']='3–4 digits';

  if (!$errors) {
    // Save to session for review.php to read
    $_SESSION['payment'] = [
      'card_type'   => $type,
      'card_number' => $num,
      'card_exp_mm' => (int)explode('/', $exp)[0],
      'card_exp_yy' => 2000 + (int)explode('/', $exp)[1],
      'cvv'         => $cvv,
    ];
    header('Location: review.php');
    exit;
  }
}
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><title>Checkout — Step 2</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="styles.css" rel="stylesheet">
</head><body>
<nav class="navbar"><div class="container"><span class="navbar-brand">Mass Garage Doors Expert</span></div></nav>
<main class="container container-narrow py-5">
<p class="step mb-3">Step 2 of 3 — Payment</p>

<form method="post" action="step2.php">
  <select name="card_type" class="form-select mb-2 <?php echo isset($errors['card_type'])?'is-invalid':''; ?>">
    <option value="visa">Visa</option>
    <option value="mastercard">MasterCard</option>
    <option value="amex">Amex</option>
    <option value="discover">Discover</option>
  </select>
  <?php if(isset($errors['card_type'])) echo '<div class="invalid-feedback d-block">'.$errors['card_type'].'</div>'; ?>

  <input name="card_number" placeholder="Card Number" class="form-control mb-2 <?php echo isset($errors['card_number'])?'is-invalid':''; ?>">
  <?php if(isset($errors['card_number'])) echo '<div class="invalid-feedback d-block">'.$errors['card_number'].'</div>'; ?>

  <input name="card_exp" placeholder="MM/YY" class="form-control mb-2 <?php echo isset($errors['card_exp'])?'is-invalid':''; ?>">
  <?php if(isset($errors['card_exp'])) echo '<div class="invalid-feedback d-block">'.$errors['card_exp'].'</div>'; ?>

  <input name="cvv" placeholder="CVV" class="form-control mb-2 <?php echo isset($errors['cvv'])?'is-invalid':''; ?>">
  <?php if(isset($errors['cvv'])) echo '<div class="invalid-feedback d-block">'.$errors['cvv'].'</div>'; ?>

  <button class="btn btn-primary mt-3">Review</button>
</form>
</main></body></html>
