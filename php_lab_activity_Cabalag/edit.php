<?php
require_once __DIR__ . '/dbcon.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) {
    http_response_code(400);
    exit('A valid user ID is required.');
}

$stmt = $conn->prepare('SELECT name, email FROM users WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    http_response_code(404);
    exit('User not found.');
}

$formError = '';
$name = $user['name'];
$email = $user['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || $email === '') {
        $formError = 'Please enter both your full name and email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->bind_param('ssi', $name, $email, $id);
        $stmt->execute();

        header('Location: index.php?msg=updated');
        exit;
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <main class="container py-5">
        <h1 class="mb-4">Edit User</h1>
        <?php if ($formError !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= e($formError) ?></div>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?= (int) $id ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required
                       value="<?= e($name) ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required
                       value="<?= e($email) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </main>
</body>
</html>
