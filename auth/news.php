<?php
include "header.php";
include "database_connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    
    if ($id > 0) {
        // Update existing news
        $sql = "UPDATE news SET content = '$content' WHERE id = $id";
        $message = "News Updated";
    } else {
        // Add new news
        $sql = "INSERT INTO news (content) VALUES ('$content')";
        $message = "News Added";
    }

    if (mysqli_query($conn, $sql)) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: '$message',
                text: 'The news has been processed successfully!'
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Could not process the news.'
            });
        </script>";
    }
}

// Check if a delete request has been made
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM news WHERE id = $delete_id";
    
    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'News Deleted',
                text: 'The news has been deleted successfully!'
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Could not delete the news.'
            });
        </script>";
    }
}
?>

<div class="container mt-5">
    <h1 class="text-center">Insert News</h1>
    <form method="post" class="mt-4">
        <input type="hidden" name="id" id="news-id" value="0">
        <div class="form-group">
            <label for="content">Content:</label>
            <textarea class="form-control" id="content" name="content" rows="6"></textarea>
        </div>
        <button type="submit" class="btn btn-success" id="submit-btn">Add News</button>
    </form>

    <!-- Display existing news entries -->
    <h2 class="mt-5">News List</h2>
    <table class="table table-bordered mt-3">
        <thead class="thead-light">
            <tr>
                <th>ID</th>
                <th>Content</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch existing news entries from the database
            $result = mysqli_query($conn, "SELECT * FROM news ORDER BY id DESC");
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['content']) . "</td>";
                echo "<td>
                    <button class='btn btn-primary btn-sm edit-btn' data-id='" . $row['id'] . "' data-content='" . htmlspecialchars($row['content'], ENT_QUOTES) . "'>Edit</button>
                    <a href='?delete_id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this news item?\")'>Delete</a>
                </td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
    let editor;

    ClassicEditor
        .create(document.querySelector('#content'))
        .then(newEditor => {
            editor = newEditor;
        })
        .catch(error => {
            console.error(error);
        });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const content = this.getAttribute('data-content');
            
            document.querySelector('#news-id').value = id;
            editor.setData(content);

            document.querySelector('#submit-btn').textContent = 'Update News';
            
            // Scroll to the form
            document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Add this part to update the textarea before form submission
    document.querySelector('form').addEventListener('submit', function() {
        document.querySelector('#content').value = editor.getData();
    });
</script>
</body>
</html>
</body>
</html>