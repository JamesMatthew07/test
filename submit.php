<?php
require __DIR__.'/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: review.php'); exit; }
if (empty($_SESSION['customer']) || empty($_SESSION['payment'])) { header('Location: step1.php'); exit; }

$c = $_SESSION['customer'];
$p = $_SESSION['payment'];

// --- helpers ---
function card_type_code($t){
  $t = strtolower((string)$t);
  return $t === 'visa' ? 1
       : ($t === 'mastercard' ? 2
       : ($t === 'amex' ? 3
       : ($t === 'discover' ? 4 : 0)));
}
function only_digits($s){ return preg_replace('/\D+/', '', (string)$s); }

// --- build safe values ---
$last4  = substr(only_digits($p['card_number'] ?? ''), -4);
$masked = $last4 ? ('**** **** **** '.$last4) : '****';

// prefer mm/yy pieces from step2; fallback to a raw card_exp if you had it earlier
$mm = isset($p['card_exp_mm']) ? (int)$p['card_exp_mm'] : null;
$yy = isset($p['card_exp_yy']) ? (int)$p['card_exp_yy'] : null;
if (!$mm || !$yy) {
  // fallback parse if someone stored card_exp = "MM/YY"
  if (!empty($p['card_exp']) && preg_match('#^\s*(\d{1,2})/(\d{2})\s*$#', $p['card_exp'], $m)) {
    $mm = (int)$m[1]; $yy = 2000 + (int)$m[2];
  }
}
$exp_str = ($mm && $yy) ? sprintf('%02d/%04d', $mm, $yy) : '';

// schema wants tinyint(1)
$ctype = card_type_code($p['card_type'] ?? '');
// schema has phone as INT(10) — cast (may drop leading zeros; OK for test)
$phone_int = (int)only_digits($c['phone'] ?? '0');

try {
  $pdo->beginTransaction();

  // Insert customer
  $stmt1 = $pdo->prepare(
    'INSERT INTO customer_details (first_name,last_name,address,city,state,phone,email)
     VALUES (?,?,?,?,?,?,?)'
  );
  $stmt1->execute([
    (string)$c['first_name'],
    (string)$c['last_name'],
    (string)$c['address'],
    (string)$c['city'],
    strtoupper((string)$c['state']),
    $phone_int,
    (string)$c['email'],
  ]);

  // Insert payment (masked PAN + expiry only; never store CVV)
  $stmt2 = $pdo->prepare(
    'INSERT INTO payment_details (card_type, card_number, card_exp_date)
     VALUES (?,?,?)'
  );
  $stmt2->execute([$ctype, $masked, $exp_str]);

  $pdo->commit();

  // clear sensitive session values
  unset($_SESSION['payment']['cvv']);
  header('Location: success.php'); exit;

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  // Show the real reason during dev:
  http_response_code(500);
  echo 'DB Error: '.$e->getMessage();
}
