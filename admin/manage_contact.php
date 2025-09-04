<?php
require "config.php";
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contact'])) {
    $stmt = $conn->prepare("INSERT INTO contact_info (type, icon, data, input_type, label, value, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $_POST['type'], $_POST['icon'], $_POST['data'], $_POST['input_type'], $_POST['label'], $_POST['value'], $_POST['description']);
    $stmt->execute();
    header("Location: manage_contact.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contact'])) {
    $stmt = $conn->prepare("UPDATE contact_info SET type=?, icon=?, data=?, input_type=?, label=?, value=?, description=? WHERE id=?");
    $stmt->bind_param("sssssssi", $_POST['type'], $_POST['icon'], $_POST['data'], $_POST['input_type'], $_POST['label'], $_POST['value'], $_POST['description'], $_POST['id']);
    $stmt->execute();
    header("Location: manage_contact.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM contact_info WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_contact.php");
    exit;
}

$result = $conn->query("SELECT * FROM contact_info ORDER BY id ASC");

$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM contact_info WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Contact Info</title>
  <link rel="stylesheet" href="css/admin_contact.css">
</head>
<body>

<div class="admin-container">
  <a href="index.php" class="btn back-btn">Go Back</a>
  <h2>Manage Contact Info</h2>

  <form method="POST" class="contact-form">
    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
    
    <div class="form-row">
      <input type="text" name="type" value="<?= htmlspecialchars($editData['type'] ?? '') ?>" placeholder="Type (e.g. Email, Phone, Address)" required>
      <input type="text" name="icon" value="<?= htmlspecialchars($editData['icon'] ?? '') ?>" placeholder="Icon class (fa-solid fa-envelope)">
      <input type="text" name="data" value="<?= htmlspecialchars($editData['data'] ?? '') ?>" placeholder="Data (e.g. email@example.com)">
      <input type="text" name="input_type" value="<?= htmlspecialchars($editData['input_type'] ?? '') ?>" placeholder="Input Type (e.g. card, radio, terms)">
    </div>
    
    <div class="form-row">
      <input type="text" name="label" value="<?= htmlspecialchars($editData['label'] ?? '') ?>" placeholder="Label">
      <input type="text" name="value" value="<?= htmlspecialchars($editData['value'] ?? '') ?>" placeholder="Value">
      <input type="text" name="description" value="<?= htmlspecialchars($editData['description'] ?? '') ?>" placeholder="Description">
    </div>
    
    <div class="form-row">
      <?php if ($editData): ?>
        <button type="submit" name="update_contact" class="btn update-btn">Update Contact</button>
        <a href="manage_contact.php" class="btn cancel-btn">Cancel</a>
      <?php else: ?>
        <button type="submit" name="add_contact" class="btn add-btn">Add Contact</button>
      <?php endif; ?>
    </div>
  </form>

  <table class="contact-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Type</th>
        <th>Icon</th>
        <th>Data</th>
        <th>Input Type</th>
        <th>Label</th>
        <th>Value</th>
        <th>Description</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['type']) ?></td>
          <td><?php if ($row['icon']): ?><i class="<?= htmlspecialchars($row['icon']) ?>"></i><?php endif; ?><?= htmlspecialchars($row['icon']) ?></td>
          <td><?= htmlspecialchars($row['data']) ?></td>
          <td><?= htmlspecialchars($row['input_type']) ?></td>
          <td><?= htmlspecialchars($row['label']) ?></td>
          <td><?= htmlspecialchars($row['value']) ?></td>
          <td><?= htmlspecialchars($row['description']) ?></td>
          <td>
            <a href="?edit=<?= $row['id'] ?>" class="btn edit-btn">Edit</a>
            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this contact?');" class="btn delete-btn">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</body>
</html>
