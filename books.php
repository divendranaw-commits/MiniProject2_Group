<?php
require_once 'config.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
$isAdmin = ($_SESSION['role'] === 'admin');

// CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $stmt = $pdo->prepare("INSERT INTO books (book_title, author, isbn) VALUES (:title, :author, :isbn)");
    $stmt->execute([
        'title' => $_POST['book_title'],
        'author' => $_POST['author'],
        'isbn' => $_POST['isbn']
    ]);
    header("Location: books.php");
    exit;
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $stmt = $pdo->prepare("UPDATE books SET book_title = :title, author = :author, isbn = :isbn, status = :status WHERE id = :id");
    $stmt->execute([
        'title' => $_POST['book_title'],
        'author' => $_POST['author'],
        'isbn' => $_POST['isbn'],
        'status' => $_POST['status'],
        'id' => (int)$_POST['id']
    ]);
    header("Location: books.php");
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = :id");
    $stmt->execute(['id' => (int)$_GET['delete']]);
    header("Location: books.php");
    exit;
}

// BORROW
if (isset($_GET['borrow'])) {
    $stmt = $pdo->prepare("UPDATE books SET status = 'borrowed' WHERE id = :id");
    $stmt->execute(['id' => (int)$_GET['borrow']]);
    header("Location: books.php");
    exit;
}

// READ
$books = $pdo->query("SELECT * FROM books ORDER BY book_title")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="container mt-4">

<div class="d-flex justify-content-between align-items-center">
    <h3>Library Books (<?= htmlspecialchars($_SESSION['username']) ?> - <?= htmlspecialchars($_SESSION['role']) ?>)</h3>
    <a href="logout.php" class="btn btn-outline-dark btn-sm">Logout</a>
</div>

<?php if ($isAdmin): ?>
<h5 class="mt-4">Add Book</h5>
<form method="POST" class="row g-2 mb-4">
    <input type="hidden" name="action" value="add">
    <div class="col"><input name="book_title" class="form-control" placeholder="Book title" required></div>
    <div class="col"><input name="author" class="form-control" placeholder="Author" required></div>
    <div class="col"><input name="isbn" class="form-control" placeholder="ISBN" required></div>
    <div class="col-auto"><button class="btn btn-success">Add</button></div>
</form>
<?php endif; ?>

<input type="text" id="searchBox" class="form-control mb-3" placeholder="Search by title or author...">

<table class="table table-bordered mt-3">
<tr><th>Title</th><th>Author</th><th>ISBN</th><th>Status</th><th></th></tr>
<tbody id="bookTableBody">
<?php foreach ($books as $b): ?>
<tr>
    <td><?= htmlspecialchars($b['book_title']) ?></td>
    <td><?= htmlspecialchars($b['author']) ?></td>
    <td><?= htmlspecialchars($b['isbn']) ?></td>
    <td><?= htmlspecialchars($b['status']) ?></td>
    <td>
        <?php if ($isAdmin): ?>
            <button class="btn btn-sm btn-warning"
                onclick="document.getElementById('editForm<?= $b['id'] ?>').classList.toggle('d-none')">Edit</button>
            <a href="books.php?delete=<?= $b['id'] ?>" class="btn btn-sm btn-danger"
                onclick="return confirm('Delete?')">Delete</a>

            <form id="editForm<?= $b['id'] ?>" method="POST" class="d-none mt-2">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                <input name="book_title" value="<?= htmlspecialchars($b['book_title']) ?>" class="form-control mb-1" required>
                <input name="author" value="<?= htmlspecialchars($b['author']) ?>" class="form-control mb-1" required>
                <input name="isbn" value="<?= htmlspecialchars($b['isbn']) ?>" class="form-control mb-1" required>
                <select name="status" class="form-control mb-1">
                    <option value="available" <?= $b['status']==='available'?'selected':'' ?>>Available</option>
                    <option value="borrowed" <?= $b['status']==='borrowed'?'selected':'' ?>>Borrowed</option>
                </select>
                <button class="btn btn-sm btn-primary">Save</button>
            </form>
        <?php else: ?>
            <?php if ($b['status'] === 'available'): ?>
                <a href="books.php?borrow=<?= $b['id'] ?>" class="btn btn-sm btn-primary">Borrow</a>
            <?php else: ?>
                <span class="text-muted">Borrowed</span>
            <?php endif; ?>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<script>
const IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;

document.getElementById('searchBox').addEventListener('input', function () {
    const term = this.value;
    fetch('search_books.php?term=' + encodeURIComponent(term))
        .then(res => res.json())
        .then(books => {
            const body = document.getElementById('bookTableBody');
            body.innerHTML = '';

            if (books.length === 0) {
                body.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No books found.</td></tr>';
                return;
            }

            books.forEach(b => {
                let actionHtml = '';
                if (IS_ADMIN) {
                    actionHtml = `<a href="books.php?delete=${b.id}" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>`;
                } else if (b.status === 'available') {
                    actionHtml = `<a href="books.php?borrow=${b.id}" class="btn btn-sm btn-primary">Borrow</a>`;
                } else {
                    actionHtml = `<span class="text-muted">Borrowed</span>`;
                }

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${b.book_title}</td>
                    <td>${b.author}</td>
                    <td>${b.isbn}</td>
                    <td>${b.status}</td>
                    <td>${actionHtml}</td>
                `;
                body.appendChild(row);
            });
        });
});
</script>

</body>
</html>