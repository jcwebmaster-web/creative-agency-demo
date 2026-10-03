<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/config.php';

function respond(int $status, string $message): void
{
    http_response_code($status);

    echo json_encode([
        'success' => $status >= 200 && $status < 300,
        'message' => $message,
    ]);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, 'Method not allowed.');
}

require_once __DIR__ . '/config.php';

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$service = trim((string) ($_POST['service'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

// Basic input validation
if (
    $name === '' ||
    strlen($name) < 2 ||
    strlen($name) > 100 ||
    preg_match('/[\r\n]/', $name)
) {
    respond(422, 'Please enter a valid name.');
}

if (
    strlen($email) > 254 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    preg_match('/[\r\n]/', $email)
) {
    respond(422, 'Please enter a valid email address.');
}

$allowedServices = [
    'Web Development',
    'UI & Design',
    'Website Maintenance',
    'Other',
];

if (!in_array($service, $allowedServices, true)) {
    respond(422, 'Please select a valid service.');
}

if ($message === '' || strlen($message) < 10 || strlen($message) > 5000) {
    respond(422, 'Your message must be between 10 and 5000 characters.');
}

// Build the email
$subject = 'New JC Studio Contact Inquiry';

$body = "You received a new contact form inquiry.\n\n";
$body .= "Name: {$name}\n";
$body .= "Email: {$email}\n";
$body .= "Service: {$service}\n\n";
$body .= "Message:\n{$message}\n";

$headers = [
    'From' => SITE_EMAIL,
    'Reply-To' => $email,
    'Content-Type' => 'text/plain; charset=UTF-8',
    'X-Mailer' => 'PHP/' . phpversion(),
];

/*try {
    $sent = mail(
        CONTACT_EMAIL,
        $subject,
        $body,
        $headers
    );
} catch (Throwable $exception) {
    error_log('Contact form mail error: ' . $exception->getMessage());
    respond(500, 'Sorry, your message could not be sent. Please try again later.');
}

if (!$sent) {
    error_log('Contact form: PHP mail() returned false.');
    respond(500, 'Sorry, your message could not be sent. Please try again later.');
}

respond(200, 'Thanks! Your message has been sent successfully.');*/

/* Local development: validate and process the form without sending email. */
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    respond(200, 'Form submitted successfully! Local test only — no email was sent.');
}

try {
    $sent = mail(CONTACT_EMAIL, $subject, $body, $headers);
} catch (Throwable $exception) {
    error_log('Contact form mail error: ' . $exception->getMessage());
    respond(500, 'Sorry, your message could not be sent. Please try again later.');
}

if (!$sent) {
    error_log('Contact form: PHP mail() returned false.');
    respond(500, 'Sorry, your message could not be sent. Please try again later.');
}

respond(200, 'Thanks! Your message has been sent successfully.');