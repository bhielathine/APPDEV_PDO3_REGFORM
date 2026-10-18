<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$errors = [];
$notice = '';
$editing = false;
$form = [
    'id' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'position_applied' => 'Software Engineer',
    'education' => '',
    'years_experience' => '0',
    'portfolio_url' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Invalid request token. Refresh the page and try again.');
    }

    $action = (string)($_POST['action'] ?? '');
    $editing = ($action === 'update');

    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $stmt = $pdo->prepare('DELETE FROM applications WHERE id = :id');
            $stmt->execute(['id' => $id]);
        }
        header('Location: index.php?status=deleted');
        exit;
    }

    $form['id'] = trim((string)($_POST['id'] ?? ''));
    foreach (['first_name', 'last_name', 'email', 'phone', 'position_applied', 'education', 'years_experience', 'portfolio_url'] as $field) {
        $form[$field] = trim((string)($_POST[$field] ?? ''));
    }

    if ($form['first_name'] === '') $errors[] = 'First name is required.';
    if ($form['last_name'] === '') $errors[] = 'Last name is required.';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if ($form['phone'] === '') $errors[] = 'Phone number is required.';
    if ($form['position_applied'] === '') $errors[] = 'Position applied for is required.';
    if ($form['education'] === '') $errors[] = 'Education is required.';
    if (!ctype_digit($form['years_experience']) || (int)$form['years_experience'] > 50) {
        $errors[] = 'Years of experience must be a number from 0 to 50.';
    }
    if ($form['portfolio_url'] !== '' && !filter_var($form['portfolio_url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Portfolio URL must be a valid URL or left blank.';
    }

    if (!$errors && in_array($action, ['create', 'update'], true)) {
        $data = [
            'first_name' => $form['first_name'],
            'last_name' => $form['last_name'],
            'email' => $form['email'],
            'phone' => $form['phone'],
            'position_applied' => $form['position_applied'],
            'education' => $form['education'],
            'years_experience' => (int)$form['years_experience'],
            'portfolio_url' => $form['portfolio_url'] !== '' ? $form['portfolio_url'] : null,
        ];

        if ($action === 'create') {
            $sql = 'INSERT INTO applications
                (first_name, last_name, email, phone, position_applied, education, years_experience, portfolio_url)
                VALUES
                (:first_name, :last_name, :email, :phone, :position_applied, :education, :years_experience, :portfolio_url)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            header('Location: index.php?status=created');
            exit;
        }

        $id = filter_var($form['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            $errors[] = 'Invalid application ID.';
        } else {
            $data['id'] = $id;
            $sql = 'UPDATE applications SET
                first_name = :first_name,
                last_name = :last_name,
                email = :email,
                phone = :phone,
                position_applied = :position_applied,
                education = :education,
                years_experience = :years_experience,
                portfolio_url = :portfolio_url
                WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            header('Location: index.php?status=updated');
            exit;
        }
    }
}

if (isset($_GET['edit']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM applications WHERE id = :id');
    $stmt->execute(['id' => filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT)]);
    $record = $stmt->fetch();
    if ($record) {
        $form = array_merge($form, array_map(static fn($v) => (string)($v ?? ''), $record));
        $editing = true;
    }
}

$statusMessages = [
    'created' => 'Application added successfully.',
    'updated' => 'Application updated successfully.',
    'deleted' => 'Application deleted successfully.',
];
if (isset($_GET['status'], $statusMessages[$_GET['status']])) {
    $notice = $statusMessages[$_GET['status']];
}

$applications = $pdo->query('SELECT * FROM applications ORDER BY date_added DESC, id DESC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software Engineer Job Applications</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <p class="eyebrow">JOB APPLICATION TRACKER</p>
        <h1>Software Engineer Applications</h1>
        <p>Manage submitted applications with Create, Read, Update, and Delete features using PHP PDO.</p>
    </div>
</header>
<main class="container">
    <?php if ($notice): ?><div class="alert success"><?= e($notice) ?></div><?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert error"><strong>Please correct the following:</strong><ul>
            <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <section class="panel">
        <div class="section-heading">
            <div><p class="eyebrow">APPLICATION FORM</p><h2><?= $editing ? 'Update application' : 'Add a new application' ?></h2></div>
            <?php if ($editing): ?><a class="button secondary" href="index.php">Cancel edit</a><?php endif; ?>
        </div>
        <form method="post" action="index.php" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id" value="<?= e($form['id']) ?>">
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">

            <label>First name *
                <input name="first_name" maxlength="80" required value="<?= e($form['first_name']) ?>">
            </label>
            <label>Last name *
                <input name="last_name" maxlength="80" required value="<?= e($form['last_name']) ?>">
            </label>
            <label>Email address *
                <input type="email" name="email" maxlength="150" required value="<?= e($form['email']) ?>">
            </label>
            <label>Phone number *
                <input name="phone" maxlength="30" required value="<?= e($form['phone']) ?>">
            </label>
            <label>Position applied for *
                <select name="position_applied" required>
                    <?php foreach (['Software Engineer', 'Junior Software Engineer', 'Backend Developer', 'Frontend Developer', 'Full-Stack Developer'] as $position): ?>
                        <option value="<?= e($position) ?>" <?= $form['position_applied'] === $position ? 'selected' : '' ?>><?= e($position) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Highest education *
                <input name="education" maxlength="150" required placeholder="e.g. BS Information Technology" value="<?= e($form['education']) ?>">
            </label>
            <label>Years of experience *
                <input type="number" name="years_experience" min="0" max="50" required value="<?= e($form['years_experience']) ?>">
            </label>
            <label>Portfolio / GitHub URL
                <input type="url" name="portfolio_url" maxlength="255" placeholder="https://..." value="<?= e($form['portfolio_url']) ?>">
            </label>
            <div class="form-actions">
                <button type="submit"><?= $editing ? 'Save changes' : 'Submit application' ?></button>
                <?php if (!$editing): ?><button class="secondary" type="reset">Clear form</button><?php endif; ?>
            </div>
        </form>
        <p class="hint">Fields marked with * are required. The record ID and date added are generated by the database.</p>
    </section>

    <section class="panel">
        <div class="section-heading">
            <div><p class="eyebrow">DATABASE RECORDS</p><h2>All applications</h2></div>
            <span class="count"><?= count($applications) ?> record(s)</span>
        </div>
        <?php if (!$applications): ?>
            <div class="empty-state">No applications yet. Use the form above to create the first record.</div>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>ID</th><th>Applicant</th><th>Email</th><th>Phone</th><th>Position</th>
                    <th>Education</th><th>Experience</th><th>Portfolio</th><th>Date added</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= (int)$app['id'] ?></td>
                        <td><?= e($app['first_name'] . ' ' . $app['last_name']) ?></td>
                        <td><?= e($app['email']) ?></td>
                        <td><?= e($app['phone']) ?></td>
                        <td><?= e($app['position_applied']) ?></td>
                        <td><?= e($app['education']) ?></td>
                        <td><?= (int)$app['years_experience'] ?> yr(s)</td>
                        <td><?php if ($app['portfolio_url']): ?><a href="<?= e($app['portfolio_url']) ?>" target="_blank" rel="noopener noreferrer">Open link</a><?php else: ?>—<?php endif; ?></td>
                        <td><?= e($app['date_added']) ?></td>
                        <td class="actions">
                            <a class="button small secondary" href="index.php?edit=<?= (int)$app['id'] ?>">Edit</a>
                            <form method="post" action="index.php" onsubmit="return confirm('Delete this application permanently?');">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$app['id'] ?>">
                                <button class="small danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
    <footer>PHP • MySQL • PDO prepared statements</footer>
</main>
</body>
</html>
