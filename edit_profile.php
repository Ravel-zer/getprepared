<?php
session_start();
include __DIR__ . '/config/db.php';

// CSRF Token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user profile data
$stmt = $conn->prepare("SELECT username, email, profile_picture, password FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $profile_picture = $_FILES['profile_picture'] ?? null;

    // Username and Email validation
    if (empty($new_username) || empty($new_email)) {
        $error .= "Username and email cannot be empty.<br>";
    }

    // Password validation
    if (!empty($new_password) && !empty($current_password)) {
        if ($new_password !== $confirm_password) {
            $error .= "New password and confirmation do not match.<br>";
        } elseif (!password_verify($current_password, $user['password'])) {
            $error .= "Current password is incorrect.<br>";
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $hashed_password, $user_id);
            $stmt->execute();
        }
    }

    // Profile picture upload validation
    if ($profile_picture && $profile_picture['size'] > 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($profile_picture['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            $error .= "Only JPG, JPEG, PNG, and GIF files are allowed.<br>";
        } else {
            $target_dir = "uploads/";
            $new_profile_picture = $target_dir . $user_id . "_" . time() . "." . $file_extension;
            if (!move_uploaded_file($profile_picture['tmp_name'], $new_profile_picture)) {
                $error .= "Failed to upload the profile picture.<br>";
            } else {
                // Update profile picture in database
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->bind_param('si', $new_profile_picture, $user_id);
                $stmt->execute();
            }
        }
    }

    // Update username and email
    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param('ssi', $new_username, $new_email, $user_id);
        $stmt->execute();
        $success = "Profile updated successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="bg-gray-900 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-black text-yellow-500 px-6 py-4 shadow-lg">
        <div class="flex justify-between items-center">
            <a href="dashboard.php" class="text-2xl font-extrabold tracking-wider">GetPrepared</a>
            
            <!-- Hamburger Menu for Small Screens -->
            <button id="menu-toggle" class="sm:hidden text-yellow-400 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Navigation Links -->
            <div id="menu" class="hidden sm:flex space-x-6">
                <a href="profile.php" class="hover:underline font-semibold text-yellow-400">Profile</a> 
                <a href="logout.php" class="hover:underline font-semibold text-yellow-400">Logout</a> 
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="sm:hidden flex flex-col space-y-2 mt-4 hidden">
            <a href="profile.php" class="hover:underline font-semibold text-yellow-400">Profile</a>
            <a href="logout.php" class="hover:underline font-semibold text-yellow-400">Logout</a>
        </div>
    </nav>

    <div class="flex-grow flex items-center justify-center">
        <div class="bg-gray-800 rounded-lg shadow-lg p-10 max-w-lg w-full"> 
            <h1 class="text-3xl font-bold mb-8 text-center text-yellow-500">Edit Profile</h1> 

            <?php if ($error): ?>
                <div class="bg-red-100 text-red-600 p-4 mb-6 rounded">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 text-green-600 p-4 mb-6 rounded">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Username -->
                <div class="mb-6">
                    <label for="username" class="block text-lg font-medium text-yellow-400 mb-2">Username</label> 
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" 
                           class="w-full p-4 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" 
                           required>
                </div>

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-lg font-medium text-yellow-400 mb-2">Email</label> 
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                           class="w-full p-4 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" 
                           required>
                </div>

                <!-- Profile Picture -->
                <div class="mb-6">
                    <label for="profile_picture" class="block text-lg font-medium text-yellow-400 mb-2">Profile Picture</label> 
                    <input type="file" id="profile_picture" name="profile_picture" 
                           class="w-full p-4 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                </div>

                <!-- Current Password (for verification) -->
                <div class="mb-6">
                    <label for="current_password" class="block text-lg font-medium text-yellow-400 mb-2">Current Password (required to change password)</label>
                    <input type="password" id="current_password" name="current_password" 
                           class="w-full p-4 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                </div>

                <!-- New Password -->
                <div class="mb-6">
                    <label for="new_password" class="block text-lg font-medium text-yellow-400 mb-2">New Password</label> 
                    <input type="password" id="new_password" name="new_password" 
                           class="w-full p-4 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                </div>

                <!-- Confirm New Password -->
                <div class="mb-6">
                    <label for="confirm_password" class="block text-lg font-medium text-yellow-400 mb-2">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           class="w-full p-4 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                </div>

                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-all w-full">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-6 text-gray-500"> 
        <p>&copy; 2024 GetPrepared. All Rights Reserved.</p>
    </footer>

    <script>
        // Toggle Menu on Mobile
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        feather.replace();
    </script>
</body>
</html>
