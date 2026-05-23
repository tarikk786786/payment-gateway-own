<?php
include "header.php";
include "database_connection.php";
$conn->set_charset("utf8mb4");

// Add Offer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $validity = $_POST['validity'];
    $status = $_POST['status'];
    $conn->query("INSERT INTO offers (title, description, validity, status) VALUES ('$title', '$desc', '$validity', '$status')");
    // header("Location: admin.php");
}

// Delete Offer
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM offers WHERE id=$id");
    // header("Location: admin.php");
}

// Edit Offer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $validity = $_POST['validity'];
    $status = $_POST['status'];
    $conn->query("UPDATE offers SET title='$title', description='$desc', validity='$validity', status='$status' WHERE id=$id");
    // header("Location: admin.php");
}

$result = $conn->query("SELECT * FROM offers ORDER BY id DESC");

?>

<h2 class="mb-4">Manage Offers</h2>

<div class="card p-3 mb-4">
    <h4>Add/Edit Offer</h4>
    <form method="POST" id="offerForm">
        <input type="hidden" name="id">
        <input type="hidden" name="edit" value="edit">
        <div class="form-group">
            <input type="text" class="form-control" name="title" placeholder="Offer Title" required>
        </div>
        <div class="form-group">
            <textarea class="form-control" name="description" placeholder="Offer Description" required></textarea>
        </div>
        <div class="form-group">
            <input type="date" class="form-control" name="validity" required>
        </div>
        <div class="form-group">
            <select class="form-control" name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success" name="add">Add Offer</button>
        <button type="submit" class="btn btn-primary" name="edit">Update Offer</button>
    </form>
</div>

<h2>All Offers</h2>
<ul class="list-group">
    <?php while ($row = $result->fetch_assoc()): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><?php echo $row['title'] . " - " . $row['validity'] . " (" . $row['status'] . ")"; ?></span>
            <div>
                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $row['id']; ?>" data-title="<?php echo $row['title']; ?>" data-description="<?php echo htmlspecialchars($row['description'], ENT_QUOTES); ?>" data-validity="<?php echo $row['validity']; ?>" data-status="<?php echo $row['status']; ?>">Edit</button>
            </div>
        </li>
    <?php endwhile; ?>
</ul>

<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('description');

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            const description = this.getAttribute('data-description');
            const validity = this.getAttribute('data-validity');
            const status = this.getAttribute('data-status');

            document.querySelector('[name=id]').value = id;
            document.querySelector('[name=title]').value = title;
            CKEDITOR.instances.description.setData(description);
            document.querySelector('[name=validity]').value = validity;
            document.querySelector('[name=status]').value = status;

            document.querySelector('[name=add]').style.display = 'none';
            document.querySelector('[name=edit]').style.display = 'block';
        });
    });
</script>

<?php
// frontend.php (Display Active Offers)
// include 'db.php';
$result = $conn->query("SELECT * FROM offers WHERE status='active' AND validity >= CURDATE() ORDER BY validity ASC");
while ($row = $result->fetch_assoc()) {
    echo "<h3>" . $row['title'] . "</h3>";
    echo "<p>" . $row['description'] . "</p>";
    echo "<small>Valid till: " . $row['validity'] . "</small><hr>";
}
?>
