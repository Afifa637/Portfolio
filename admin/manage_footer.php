<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "portfolio_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create
if (isset($_POST['add'])) {
    $name = $_POST['social_name'];
    $icon = $_POST['social_icon'];
    $link = $_POST['social_link'];
    $text = $_POST['footer_text'];
    $conn->query("INSERT INTO footer (social_name, social_icon, social_link, footer_text) VALUES ('$name','$icon','$link','$text')");
}

// Update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['social_name'];
    $icon = $_POST['social_icon'];
    $link = $_POST['social_link'];
    $text = $_POST['footer_text'];
    $conn->query("UPDATE footer SET social_name='$name', social_icon='$icon', social_link='$link', footer_text='$text' WHERE id=$id");
}

// Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM footer WHERE id=$id");
}

// Fetch all
$result = $conn->query("SELECT * FROM footer");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Footer</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f9; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
    form { margin-top: 20px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    input, button { padding: 8px; margin: 5px; }
    button { cursor: pointer; }
  </style>
</head>
<body>
  <h2>Manage Footer</h2>

  <!-- Add / Update Form -->
  <form method="post">
    <input type="hidden" name="id" id="id">
    <input type="text" name="social_name" id="social_name" placeholder="Social Name" required>
    <input type="text" name="social_icon" id="social_icon" placeholder="FontAwesome Icon (e.g., fab fa-facebook)">
    <input type="url" name="social_link" id="social_link" placeholder="Social Link">
    <input type="text" name="footer_text" id="footer_text" placeholder="Footer Text">
    <button type="submit" name="add">Add</button>
    <button type="submit" name="update">Update</button>
  </form>

  <!-- Footer Table -->
  <table>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Icon</th>
      <th>Link</th>
      <th>Footer Text</th>
      <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo $row['social_name']; ?></td>
        <td><i class="<?php echo $row['social_icon']; ?>"></i></td>
        <td><a href="<?php echo $row['social_link']; ?>" target="_blank"><?php echo $row['social_link']; ?></a></td>
        <td><?php echo $row['footer_text']; ?></td>
        <td>
          <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this item?')">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
