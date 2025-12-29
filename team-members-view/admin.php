<?php
include 'db.php';

$upload_dir = 'members-images/';
$msg = "";
$edit_member = null;

// 1. FETCH MEMBER DATA FOR EDITING
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM team_members WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_member = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 2. HANDLE ADD OR UPDATE
if (isset($_POST['save_member'])) {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $id = $_POST['id']; // Hidden field for updates
    $image_url = $_POST['old_image']; // Default to existing image

    // Image Upload Logic (Only if a new file is chosen)
    if (!empty($_FILES["image"]["name"])) {
        $file_name = time() . '_' . basename($_FILES["image"]["name"]);
        $target_path = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));
        $allow_types = array('jpg', 'png', 'jpeg', 'gif');

        if (in_array($file_type, $allow_types)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_path)) {
                // Delete old image file if it exists and we are updating
                if (!empty($_POST['old_image']) && file_exists($_POST['old_image'])) {
                    unlink($_POST['old_image']);
                }
                $image_url = $target_path;
            }
        }
    }

    if ($id) {
        // UPDATE EXISTING
        $stmt = $db->prepare("UPDATE team_members SET name = ?, role = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$name, $role, $image_url, $id]);
        $msg = "Member updated successfully!";
    } else {
        // ADD NEW
        $stmt = $db->prepare("INSERT INTO team_members (name, role, image_url) VALUES (?, ?, ?)");
        $stmt->execute([$name, $role, $image_url]);
        $msg = "Member added successfully!";
    }
    // Refresh to clear GET parameters
    header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode($msg));
    exit();
}

// 3. HANDLE DELETE
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("SELECT image_url FROM team_members WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $member = $stmt->fetch();

    if ($member) {
        if (file_exists($member['image_url'])) {
            unlink($member['image_url']);
        }
        $stmt = $db->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=Member deleted");
        exit();
    }
}

if(isset($_GET['msg'])) $msg = $_GET['msg'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Admin</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .member-row img { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
        form { margin-bottom: 30px; border: 1px solid #ccc; padding: 15px; width: 320px; background: #f9f9f9; }
        input { display: block; margin-bottom: 10px; width: 100%; box-sizing: border-box; }
        .btn-edit { color: blue; margin-right: 10px; }
        .btn-delete { color: red; }
    </style>
</head>
<body>

    <h2><?php echo $edit_member ? 'Edit' : 'Add New'; ?> Team Member</h2>
    <?php if($msg) echo "<p><strong>$msg</strong></p>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $edit_member['id'] ?? ''; ?>">
        <input type="hidden" name="old_image" value="<?php echo $edit_member['image_url'] ?? ''; ?>">

        <input type="text" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($edit_member['name'] ?? ''); ?>" required>
        <input type="text" name="role" placeholder="Role" value="<?php echo htmlspecialchars($edit_member['role'] ?? ''); ?>" required>

        <label>Avatar:</label>
        <?php if ($edit_member): ?>
            <div style="margin-bottom: 10px;">
                <img src="<?php echo $edit_member['image_url']; ?>" width="40"><br>
                <small>Leave blank to keep current photo</small>
            </div>
        <?php endif; ?>
        <input type="file" name="image" accept="image/*" <?php echo $edit_member ? '' : 'required'; ?>>

        <button type="submit" name="save_member">
            <?php echo $edit_member ? 'Update Member' : 'Upload & Save'; ?>
        </button>
        <?php if($edit_member): ?>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>">Cancel</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Current Members</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Photo</th>
            <th>Name</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php
        $stmt = $db->query("SELECT * FROM team_members ORDER BY id DESC");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <tr class="member-row">
            <td><img src="<?php echo $row['image_url']; ?>" alt="img"></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['role']); ?></td>
            <td>
                <a class="btn-edit" href="?edit=<?php echo $row['id']; ?>">Edit</a>
                <a class="btn-delete" href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this member?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
