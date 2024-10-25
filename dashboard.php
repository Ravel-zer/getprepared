<?php
session_start();
include __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fungsi untuk cek apakah semua task di list selesai
function isCompleted($list_id, $conn) {
    $tasks = $conn->query("SELECT status FROM tasks WHERE list_id = $list_id");
    while ($task = $tasks->fetch_assoc()) {
        if ($task['status'] === 'incomplete') {
            return false;
        }
    }
    return true;
}

// Tambah To-Do List
if (isset($_POST['add_list'])) {
    $title = trim($_POST['title']);
    if (!empty($title)) {
        $stmt = $conn->prepare("INSERT INTO todo_lists (user_id, title) VALUES (?, ?)");
        $stmt->bind_param('is', $user_id, $title);
        $stmt->execute();
    }
}

// Hapus To-Do List
if (isset($_GET['delete_list'])) {
    $list_id = $_GET['delete_list'];

    // Hapus semua task yang terkait dengan list ini
    $conn->query("DELETE FROM tasks WHERE list_id = $list_id");

    // Kemudian hapus list itu sendiri
    $conn->query("DELETE FROM todo_lists WHERE id = $list_id");
}

// Proses pencarian dan filter status
$searchTerm = "";
$statusFilter = "all"; // Default filter untuk menampilkan semua list

if (isset($_POST['search'])) {
    $searchTerm = trim($_POST['search_term']);
    $statusFilter = $_POST['status_filter'] ?? 'all'; // Filter status (completed, incomplete, all)
}

// Ambil semua list dengan filter pencarian dan status
$query = "SELECT tl.id, tl.title, 
    (SELECT COUNT(*) FROM tasks t WHERE t.list_id = tl.id AND t.status = 'incomplete') AS incomplete_tasks
    FROM todo_lists tl 
    WHERE tl.user_id = $user_id";

if (!empty($searchTerm)) {
    $query .= " AND tl.title LIKE '%" . $conn->real_escape_string($searchTerm) . "%'";
}

if ($statusFilter === 'completed') {
    $query .= " HAVING incomplete_tasks = 0"; // List yang semua task-nya sudah selesai
} elseif ($statusFilter === 'incomplete') {
    $query .= " HAVING incomplete_tasks > 0"; // List yang masih ada task belum selesai
}

$lists = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleUp {
            from {
                transform: scale(0.95);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        body {
            background-color: #1E293B;
            color: #F59E0B;
        }

        .bg-dark-navy {
            background-color: #1E293B;
        }

        .btn-orange {
            background-color: #F59E0B;
            color: #1E293B;
        }

        .btn-orange:hover {
            background-color: #F97316;
        }

        .border-orange {
            border-color: #F59E0B;
        }

        .text-orange {
            color: #F59E0B;
        }

        .navbar-black-orange {
            background-color: #000000;
            color: #F59E0B;
        }

        .navbar-black-orange a {
            color: #F59E0B;
        }

        .navbar-black-orange a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="bg-dark-navy min-h-screen">
    <!-- Navbar -->
    <nav class="navbar-black-orange px-6 py-4 flex justify-between items-center shadow-lg">
        <a href="dashboard.php" class="text-2xl font-extrabold tracking-wider">GetPrepared</a>
        <!-- Hamburger icon for mobile -->
        <div class="block md:hidden">
            <button id="menu-toggle" class="focus:outline-none">
                <svg data-feather="menu" class="w-8 h-8 text-orange"></svg>
            </button>
        </div>
        <!-- Navbar links for desktop and tablet screens -->
        <div id="menu" class="hidden md:flex space-x-6">
            <a href="profile.php" class="hover:underline font-semibold">Profile</a>
            <a href="logout.php" class="hover:underline font-semibold">Logout</a>
        </div>
    </nav>

    <!-- Dropdown Menu for Mobile -->
    <div id="mobile-menu" class="md:hidden hidden flex flex-col space-y-4 px-6 py-4 bg-black text-orange">
        <a href="profile.php" class="hover:underline font-semibold">Profile</a>
        <a href="logout.php" class="hover:underline font-semibold">Logout</a>
    </div>

    <div class="container mx-auto p-4 sm:p-8">
        <h1 class="text-4xl font-extrabold text-center mb-8 text-orange">Manage Your Tasks</h1>

        <!-- Search and Filter Form -->
        <form method="POST" class="mb-6 flex flex-col sm:flex-row items-center bg-dark-navy p-4 rounded-lg shadow-lg">
            <input type="text" name="search_term" placeholder="Search Lists" value="<?php echo htmlspecialchars($searchTerm); ?>" class="p-3 border border-orange-500 rounded w-full bg-dark-navy text-orange">
            <select name="status_filter" class="ml-0 sm:ml-4 mt-4 sm:mt-0 p-3 border border-orange-500 rounded bg-dark-navy text-orange w-full sm:w-auto">
                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All</option>
                <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="incomplete" <?php echo $statusFilter === 'incomplete' ? 'selected' : ''; ?>>Incomplete</option>
            </select>
            <button name="search" class="btn-orange px-6 py-3 rounded mt-4 sm:mt-0 ml-0 sm:ml-4 flex items-center font-bold hover:bg-orange-600 transition-all w-full sm:w-auto">
                <svg data-feather="search" class="w-5 h-5 mr-2"></svg> Search
            </button>
        </form>

        <!-- Add New List Form -->
        <form method="POST" class="mb-6 flex flex-col sm:flex-row bg-dark-navy p-4 rounded-lg shadow-lg">
            <input type="text" name="title" placeholder="Add New Task" required class="p-3 border border-orange-500 rounded w-full bg-dark-navy text-orange">
            <button name="add_list" class="btn-orange px-6 py-3 rounded mt-4 sm:mt-0 ml-0 sm:ml-4 font-bold hover:bg-orange-600 transition-all w-full sm:w-auto">Add Task</button>
        </form>

        <!-- To-Do Lists Display -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($list = $lists->fetch_assoc()): ?>
                <?php $completed = $list['incomplete_tasks'] == 0; ?>
                <div class="p-6 bg-dark-navy rounded-lg shadow-lg border <?php echo $completed ? 'border-green-500' : 'border-red-500'; ?> animate-fade-in transition-transform duration-300 hover:scale-105">
                    <div class="flex justify-between items-center">
                        <h2 class="<?php echo $completed ? 'text-green-500' : 'text-red-500'; ?> text-xl font-bold">
                            <?php echo htmlspecialchars($list['title']); ?>
                        </h2>
                        <div>
                            <a href="tasks.php?list_id=<?php echo $list['id']; ?>" class="text-orange font-semibold hover:underline">View</a>
                            <button onclick="showDeleteModal(<?php echo $list['id']; ?>)" class="text-red-500 ml-2 font-semibold hover:underline">Delete</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
        <div class="bg-dark-navy p-6 rounded-lg shadow-lg animate-scaleUp max-w-sm w-full mx-4">
            <h2 class="text-lg font-bold mb-4">Are you sure you want to delete this list?</h2>
            <div class="flex justify-end">
                <button onclick="hideDeleteModal()" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancel</button>
                <a id="confirmDelete" href="#" class="btn-orange px-4 py-2 rounded">Delete</a>
            </div>
        </div>
    </div>

    <script>
        feather.replace();

        // Toggle Mobile Menu
        document.getElementById('menu-toggle').addEventListener('click', () => {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        function showDeleteModal(listId) {
            document.getElementById('confirmDelete').setAttribute('href', '?delete_list=' + listId);
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function hideDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</body>
</html>




