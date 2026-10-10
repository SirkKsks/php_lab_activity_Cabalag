<?php
require_once 'dbcon.php';

$error = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a name and valid email address.';
    } else {
        $stmt = $conn->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
        $stmt->bind_param('ss', $name, $email);
        $stmt->execute();
        header('Location: index.php?msg=added');
        exit;
    }
}

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') {
        $message = 'added successfully.';
    } elseif ($_GET['msg'] === 'updated') {
        $message = 'updated successfully.';
    } elseif ($_GET['msg'] === 'deleted') {
        $message = 'deleted successfully.';
    }
}

$users = $conn->query('SELECT * FROM users ORDER BY id DESC');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h1>User Management</h1>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" name="name" id="name" class="form-control" required
                   value="<?= htmlspecialchars($name) ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" id="email" class="form-control" required
                   value="<?= htmlspecialchars($email) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email Address</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users->num_rows === 0): ?>
                <tr><td colspan="4">No users yet.</td></tr>
            <?php endif; ?>

            <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= (int) $user['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete.php?id=<?= (int) $user['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this user?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
