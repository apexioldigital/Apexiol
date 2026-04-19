<?php
// ─────────────────────────────────────────
//  Apexiol — Contact Form Handler
//  Uses PHP native mail() — no setup needed
//  Works on Hostinger, cPanel, SiteGround, etc.
// ─────────────────────────────────────────

define('TO_EMAIL',     'apexioldigital@gmail.com');
define('THANK_YOU',    'thank-you.html');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.html');
    exit;
}

// ── Sanitise ──────────────────────────────
function clean($val) {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

$name    = clean($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$phone   = clean($_POST['phone']   ?? '');
$service = clean($_POST['service'] ?? '');
$message = clean($_POST['message'] ?? '');

// ── Validation ────────────────────────────
$errors = [];

// Name
if (strlen($name) < 2) {
    $errors[] = 'Full name is required.';
}

// Email — must have chars@chars.tld
if (empty($email)) {
    $errors[] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid email (e.g. you@example.com).';
} else {
    [$local, $domain] = explode('@', $email, 2);
    if (strlen($local) < 1 || strpos($domain, '.') === false) {
        $errors[] = 'Email format is invalid.';
    }
}
$emailSafe = clean($email);

// Phone — required, max 16 digits
if (empty($phone)) {
    $errors[] = 'Phone number is required.';
} else {
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) < 7)  $errors[] = 'Phone number is too short.';
    if (strlen($digits) > 16) $errors[] = 'Phone number must not exceed 16 digits.';
    if (!preg_match('/^[\d\+\-\s\(\)]+$/', $phone)) $errors[] = 'Phone contains invalid characters.';
}

// Service
if (empty($service)) {
    $errors[] = 'Please select a service.';
}

// Message
if (strlen($message) < 10) {
    $errors[] = 'Message must be at least 10 characters.';
}

// If errors → go back
if (!empty($errors)) {
    $ref = $_SERVER['HTTP_REFERER'] ?? 'home.html';
    header('Location: ' . $ref . '?form_error=' . urlencode(implode(' | ', $errors)));
    exit;
}

// ── Build Email ───────────────────────────
$time    = date('D, d M Y \a\t H:i:s T');
$subject = "=?UTF-8?B?" . base64_encode("New Inquiry from {$name} — {$service} | Apexiol") . "?=";

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f5f6f8;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f6f8;padding:40px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

  <!-- Header -->
  <tr>
    <td style="background:#08162b;padding:30px 40px;border-radius:16px 16px 0 0;text-align:center;">
      <p style="margin:0;font-size:24px;font-weight:800;color:#f0b429;letter-spacing:1px;">Apexiol</p>
      <p style="margin:8px 0 0;font-size:12px;color:rgba(255,255,255,.5);letter-spacing:1px;text-transform:uppercase;">New Contact Form Submission</p>
    </td>
  </tr>

  <!-- Body -->
  <tr>
    <td style="background:#ffffff;padding:36px 40px;border-left:1px solid #e8eaf0;border-right:1px solid #e8eaf0;">
      <p style="margin:0 0 24px;font-size:14px;color:#6b7a94;line-height:1.6;">
        A new inquiry has been submitted through your website. Details are below.
      </p>

      <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e8eaf0;border-radius:12px;overflow:hidden;">

        <tr>
          <td style="padding:14px 20px;width:36%;background:#f5f6f8;font-size:11px;font-weight:700;color:#6b7a94;letter-spacing:1px;text-transform:uppercase;border-bottom:1px solid #e8eaf0;">Full Name</td>
          <td style="padding:14px 20px;font-size:15px;color:#1a2640;font-weight:600;border-bottom:1px solid #e8eaf0;">{$name}</td>
        </tr>

        <tr>
          <td style="padding:14px 20px;background:#f5f6f8;font-size:11px;font-weight:700;color:#6b7a94;letter-spacing:1px;text-transform:uppercase;border-bottom:1px solid #e8eaf0;">Email</td>
          <td style="padding:14px 20px;font-size:15px;border-bottom:1px solid #e8eaf0;">
            <a href="mailto:{$emailSafe}" style="color:#f0b429;text-decoration:none;">{$emailSafe}</a>
          </td>
        </tr>

        <tr>
          <td style="padding:14px 20px;background:#f5f6f8;font-size:11px;font-weight:700;color:#6b7a94;letter-spacing:1px;text-transform:uppercase;border-bottom:1px solid #e8eaf0;">Phone</td>
          <td style="padding:14px 20px;font-size:15px;color:#1a2640;border-bottom:1px solid #e8eaf0;">{$phone}</td>
        </tr>

        <tr>
          <td style="padding:14px 20px;background:#f5f6f8;font-size:11px;font-weight:700;color:#6b7a94;letter-spacing:1px;text-transform:uppercase;border-bottom:1px solid #e8eaf0;">Service</td>
          <td style="padding:14px 20px;border-bottom:1px solid #e8eaf0;">
            <span style="display:inline-block;background:rgba(240,180,41,.12);border:1px solid rgba(240,180,41,.3);color:#8a6200;font-size:13px;font-weight:700;padding:4px 12px;border-radius:99px;">{$service}</span>
          </td>
        </tr>

        <tr>
          <td colspan="2" style="padding:18px 20px;background:#f5f6f8;">
            <p style="margin:0 0 8px;font-size:11px;font-weight:700;color:#6b7a94;letter-spacing:1px;text-transform:uppercase;">Message</p>
            <p style="margin:0;font-size:15px;color:#1a2640;line-height:1.75;white-space:pre-wrap;">{$message}</p>
          </td>
        </tr>

      </table>

      <!-- Reply button -->
      <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:28px;">
        <tr>
          <td align="center">
            <a href="mailto:{$emailSafe}?subject=Re: Your Inquiry — Apexiol"
               style="display:inline-block;background:#08162b;color:#ffffff;font-size:14px;font-weight:700;padding:13px 28px;border-radius:10px;text-decoration:none;">
              Reply to {$name} →
            </a>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#f5f6f8;padding:18px 40px;border-radius:0 0 16px 16px;border:1px solid #e8eaf0;border-top:none;text-align:center;">
      <p style="margin:0;font-size:11px;color:#b0b8c8;line-height:1.6;">
        Received on {$time}<br>
        Sent automatically via Apexiol website contact form.
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;

// ── Send ──────────────────────────────────
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: Apexiol Website <noreply@apexiol.com>\r\n";
$headers .= "Reply-To: {$name} <{$emailSafe}>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$sent = mail(TO_EMAIL, $subject, $html, $headers);

if ($sent) {
    header('Location: ' . THANK_YOU);
} else {
    // mail() failed (rare on real hosting) — redirect back with message
    $ref = $_SERVER['HTTP_REFERER'] ?? 'home.html';
    header('Location: ' . $ref . '?form_error=' . urlencode('Message could not be sent. Please email us directly at apexioldigital@gmail.com'));
}
exit;