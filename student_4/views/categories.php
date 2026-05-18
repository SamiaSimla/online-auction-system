<?php
// views/categories.php — Admin: create/edit/delete categories
session_start();
require_once __DIR__ . '/../config/database.php';
$pdo = getDBConnection();

$errors = [];
$editCat = null;

// ---- CREATE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $errors[] = 'Category name cannot be empty.';
    } else {
        $pdo->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$name]);
        header('Location: categories.php?msg=created'); exit;
    }
}

// ---- UPDATE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id   = (int)$_POST['edit_id'];
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $errors[] = 'Category name cannot be empty.';
    } else {
        $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?")->execute([$name, $id]);
        header('Location: categories.php?msg=updated'); exit;
    }
}

// ---- DELETE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['delete_id'];
    // Check if used by listings
    $used = $pdo->prepare("SELECT COUNT(*) FROM listings WHERE category_id = ?");
    $used->execute([$id]);
    if ($used->fetchColumn() > 0) {
        $errors[] = 'Cannot delete — this category has listings.';
    } else {
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        header('Location: categories.php?msg=deleted'); exit;
    }
}

// ---- Load edit form ----
if (isset($_GET['edit'])) {
    $es = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $es->execute([(int)$_GET['edit']]);
    $editCat = $es->fetch();
}

// ---- All categories with listing counts ----
$categories = $pdo->query("
    SELECT c.*, COUNT(l.id) AS listing_count
    FROM categories c
    LEFT JOIN listings l ON l.category_id = c.id
    GROUP BY c.id
    ORDER BY c.name ASC
")->fetchAll();

$msgMap = ['created'=>'Category created.','updated'=>'Category updated.','deleted'=>'Category deleted.'];
$msg    = $msgMap[$_GET['msg'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categories — AuctionHub Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Categories</h1>
            <p class="page-sub">Manage auction categories</p>
        </div>
    </header>

    <?php if ($msg): ?>
    <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>
    <?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><?= $e ?></div>
    <?php endforeach; ?>

    <div class="two-col-layout">

        <!-- Left: table -->
        <div class="table-card">
            <div class="table-header" style="margin-bottom:16px">
                <h2 class="chart-title">All Categories (<?= count($categories) ?>)</h2>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr><th>#</th><th>Name</th><th>Listings</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td class="row-num"><?= $cat['id'] ?></td>
                        <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                        <td>
                            <span class="badge badge-active"><?= $cat['listing_count'] ?></span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="?edit=<?= $cat['id'] ?>" class="btn-sm btn-view">Edit</a>
                                <form method="POST" style="display:inline"
                                      onsubmit="return confirm('Delete category?')">
                                    <input type="hidden" name="action"    value="delete">
                                    <input type="hidden" name="delete_id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn-sm btn-danger"
                                            <?= $cat['listing_count'] > 0 ? 'disabled title="Has listings"' : '' ?>>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: form -->
        <div class="table-card form-card">
            <h2 class="chart-title" style="margin-bottom:20px">
                <?= $editCat ? 'Edit Category' : 'Add New Category' ?>
            </h2>
            <form method="POST">
                <input type="hidden" name="action"  value="<?= $editCat ? 'update' : 'create' ?>">
                <?php if ($editCat): ?>
                <input type="hidden" name="edit_id" value="<?= $editCat['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" class="form-input"
                           value="<?= htmlspecialchars($editCat['name'] ?? '') ?>"
                           placeholder="e.g. Electronics" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <?= $editCat ? 'Update Category' : 'Create Category' ?>
                    </button>
                    <?php if ($editCat): ?>
                    <a href="categories.php" class="btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

    </div>

</main>
</body>
</html>