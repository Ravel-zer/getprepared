<?php
session_start();
include __DIR__ . '/config/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user profile information
$stmt = $conn->prepare("SELECT username, email, profile_picture FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        /* Fade-in animation */
        .fade-in {
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.5s ease-out, transform 0.5s ease-out;
        }
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Hover effect */
        .hover-grow:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }

        /* Color scheme */
        body {
            background-color: #1F2937;
            color: #F59E0B;
        }
        .profile-content {
            background-color: #1A1A1A;
            color: #F59E0B;
        }
        .profile-content h2, .profile-content p {
            color: #F97316;
        }
        .profile-content a {
            background-color: #F59E0B;
            color: #1F2937;
        }
        .profile-content a:hover {
            background-color: #F97316;
        }
        nav {
            background-color: #000000;
            color: #F59E0B;
        }
        nav a {
            color: #F59E0B;
        }
        nav a:hover {
            color: #F97316;
        }
        footer {
            background-color: #111827;
            color: #F59E0B;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="px-6 py-4 shadow-lg">
        <div class="flex justify-between items-center">
            <a href="dashboard.php" class="text-2xl font-extrabold tracking-wider hover-grow">GetPrepared</a>

            <!-- Hamburger Menu for Small Screens -->
            <button id="menu-toggle" class="sm:hidden text-yellow-400 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Navigation Links -->
            <div id="menu" class="hidden sm:flex space-x-6">
                <a href="dashboard.php" class="hover:underline font-semibold hover-grow">Home</a>
                <a href="profile.php" class="hover:underline font-semibold hover-grow">Profile</a>
                <a href="logout.php" class="hover:underline font-semibold hover-grow">Logout</a>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="sm:hidden flex flex-col space-y-2 mt-4 hidden">
            <a href="dashboard.php" class="hover:underline font-semibold">Home</a>
            <a href="profile.php" class="hover:underline font-semibold">Profile</a>
            <a href="logout.php" class="hover:underline font-semibold">Logout</a>
        </div>
    </nav>

    <!-- Profile Section -->
    <div class="flex-grow flex items-center justify-center py-12">
        <div class="profile-content rounded-lg shadow-lg p-8 md:p-16 max-w-4xl text-center fade-in">
            <!-- Profile Picture -->
            <div class="w-32 h-32 md:w-48 md:h-48 rounded-full mx-auto overflow-hidden mb-6 md:mb-8">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="w-full h-full object-cover">
                <?php else: ?>
                    <img src="image/default-avatar.png" alt="Default Avatar" class="w-full h-full object-cover">
                <?php endif; ?>
            </div>

            <!-- Username -->
            <h2 class="text-3xl md:text-5xl font-bold mb-2 md:mb-4"><?php echo htmlspecialchars($user['username']); ?></h2>

            <!-- Email -->
            <p class="text-lg md:text-xl mb-6 md:mb-8"><?php echo htmlspecialchars($user['email']); ?></p>

            <!-- Edit Profile Button -->
            <a href="edit_profile.php" class="px-8 py-3 md:px-10 md:py-4 rounded-lg font-bold shadow-md transition-all transform hover:scale-105 hover:shadow-lg">
                Edit Profile
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-6">
        <p>&copy; 2024 To-Do List App. All Rights Reserved.</p>
    </footer>

    <script>
        feather.replace();

        // Menu toggle for small screens
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Fade-in animation on load
        window.onload = function() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach(el => {
                el.classList.add('visible');
            });
        };
    </script>
</body>
</html>
