<?php
$conn = new mysqli("localhost", "root", "", "portfolio_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['add'])) {
    $name = $_POST['social_name'];
    $icon = $_POST['social_icon'];
    $link = $_POST['social_link'];
    $text = $_POST['footer_text'];
    $conn->query("INSERT INTO footer (social_name, social_icon, social_link, footer_text) 
                  VALUES ('$name','$icon','$link','$text')");
}

if (isset($_POST['update'])) {
    $id   = $_POST['id'];
    $name = $_POST['social_name'];
    $icon = $_POST['social_icon'];
    $link = $_POST['social_link'];
    $text = $_POST['footer_text'];
    $conn->query("UPDATE footer 
                  SET social_name='$name', social_icon='$icon', social_link='$link', footer_text='$text' 
                  WHERE id=$id");
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM footer WHERE id=$id");
}

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
    .btn { 
      display: inline-block; 
      background: #007bff; 
      color: #fff; 
      padding: 8px 15px; 
      text-decoration: none; 
      border-radius: 5px; 
    }
    .btn:hover { background: #0056b3; }
  </style>
</head>
<body>
  <div style="margin-bottom:20px;">
    <a href="index.php" class="btn">Go Back</a>
  </div>

  <h2>Manage Footer</h2>

  <form method="post">
    <input type="hidden" name="id" id="id">
    <input type="text" name="social_name" placeholder="Social Name" required>
    <input type="text" name="social_icon" placeholder="FontAwesome Icon (e.g., fab fa-facebook)">
    <input type="url" name="social_link" placeholder="Social Link">
    <input type="text" name="footer_text" placeholder="Footer Text">
    <button type="submit" name="add">Add</button>
    <button type="submit" name="update">Update</button>
  </form>

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
        <td><?= $row['id']; ?></td>
        <td><?= htmlspecialchars($row['social_name']); ?></td>
        <td><i class="<?= htmlspecialchars($row['social_icon']); ?>"></i></td>
        <td><a href="<?= htmlspecialchars($row['social_link']); ?>" target="_blank"><?= htmlspecialchars($row['social_link']); ?></a></td>
        <td><?= htmlspecialchars($row['footer_text']); ?></td>
        <td>
          <a href="?delete=<?= $row['id']; ?>" onclick="return confirm('Delete this item?')">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
