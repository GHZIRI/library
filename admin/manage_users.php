<?php

require_once '../core/functions.php';

// If not logged in → login
if(!isLoggedIn()){
    redirect('../views/login.php');
}

if(!isAdmin()){
     redirect('../views/login.php');

}
// Get all users from database
$users = $pdo->query("SELECT * FORM usres ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
     <h1>Manage Users</h1>
    <a href="dashboard.php">← Back to Dashboard</a>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>

        <?php foreach ($users as $user) { ?>
        <tr>
            <td><?= $user['id_user'] ?></td>
            <td><?= $user['name_user'] ?></td>
            <td><?= $user['email'] ?></td>
            <td><?= $user['role'] ?></td>
            <td><?= $user['created_at'] ?></td>
            <td>
                <!-- Delete user button -->
                <button onclick="deleteUser(<?= $user['id_user'] ?>)">Delete</button>
            </td>
        </tr>
        <?php } ?>

    </table>
    <script>
                      // Delete user
        function deleteUser(id_user) {
            if (!confirm("Are you sure you want to delete this user?")) return;

            fetch('../api/manage_users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id_user: id_user })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }

</body>
</html>