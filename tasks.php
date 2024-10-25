<?php
session_start();
include __DIR__ . '/config/db.php';

$list_id = $_GET['list_id'] ?? null;

// Check if list_id is available
if (!$list_id) {
    header('Location: dashboard.php');
    exit();
}

// Add Task
if (isset($_POST['add_task'])) {
    $task_name = trim($_POST['task_name']);
    if (!empty($task_name)) {
        $stmt = $conn->prepare("INSERT INTO tasks (list_id, task_name, status) VALUES (?, ?, 'incomplete')");
        $stmt->bind_param('is', $list_id, $task_name);
        $stmt->execute();
    }
}

// Update Task Status
if (isset($_POST['task_id'])) {
    $task_id = $_POST['task_id'];
    $status = ($_POST['current_status'] === 'completed') ? 'incomplete' : 'completed';

    $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $task_id);
    $stmt->execute();

    header("Location: tasks.php?list_id=" . $list_id);
    exit();
}

// Delete Task
if (isset($_POST['delete_task'])) {
    $task_id = $_POST['delete_task'];

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param('i', $task_id);
    $stmt->execute();

    header("Location: tasks.php?list_id=" . $list_id);
    exit();
}

// Retrieve All Tasks
$tasks = $conn->query("SELECT * FROM tasks WHERE list_id = $list_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #111827, #1f2937);
        }
        .task-item {
            transition: transform 0.2s ease-in-out, background-color 0.3s ease;
        }
        .task-item:hover {
            transform: translateY(-3px);
            background-color: #374151;
        }
    </style>
</head>
<body class="bg-gray-900 min-h-screen text-white">
    <!-- Navbar -->
    <nav class="bg-black text-yellow-500 px-6 py-4 shadow-lg">
        <div class="flex justify-between items-center">
            <a href="dashboard.php" class="text-3xl font-extrabold tracking-wider hover:text-yellow-300">GetPrepared</a>
            
            <!-- Hamburger Menu for Small Screens -->
            <button id="menu-toggle" class="sm:hidden text-yellow-400 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Navigation Links -->
            <div id="menu" class="hidden sm:flex space-x-6">
                <a href="dashboard.php" class="hover:text-yellow-300 text-xl font-semibold">Home</a>
                <a href="logout.php" class="hover:text-yellow-300 text-xl font-semibold">Logout</a>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="sm:hidden flex flex-col space-y-2 mt-4 hidden">
            <a href="dashboard.php" class="hover:text-yellow-300 text-xl font-semibold">Home</a>
            <a href="logout.php" class="hover:text-yellow-300 text-xl font-semibold">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto p-8">
        <h1 class="text-4xl font-extrabold mb-8 text-center text-yellow-500">Manage Your Tasks</h1>

        <!-- Add Task Form -->
        <form method="POST" class="mb-6 flex items-center space-x-3 bg-gray-800 p-6 rounded-lg shadow-xl">
            <input type="text" name="task_name" placeholder="Add New Task" required 
                   class="w-full p-4 border border-yellow-500 bg-gray-900 text-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
            <button name="add_task" 
                    class="bg-yellow-500 hover:bg-yellow-600 text-black px-6 py-3 rounded-lg shadow-xl font-bold transition-all">
                Add Task
            </button>
        </form>

        <!-- Task List -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <ul class="space-y-2">
                <?php while ($task = $tasks->fetch_assoc()): ?>
                    <li class="flex items-center justify-between bg-gray-900 rounded-lg p-4 shadow-md task-item">
                        <form method="POST" action="" class="flex items-center w-full">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                            <input type="hidden" name="current_status" value="<?php echo $task['status']; ?>">
                            <input type="checkbox" name="status" 
                                   class="w-6 h-6 text-yellow-500 focus:ring-yellow-400 border-gray-700 rounded"
                                   <?php if ($task['status'] == 'completed') echo 'checked'; ?> 
                                   onchange="this.form.submit()">
                            <span class="ml-4 flex-grow text-xl font-semibold <?php echo $task['status'] == 'completed' ? 'line-through text-gray-500' : ''; ?>">
                                <?php echo htmlspecialchars($task['task_name']); ?>
                            </span>
                        </form>

                        <!-- Delete Button -->
                        <form method="POST" action="" class="ml-4">
                            <input type="hidden" name="delete_task" value="<?php echo $task['id']; ?>">
                            <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-800 text-white px-4 py-2 rounded-lg shadow-md font-bold transition-all">
                                Delete
                            </button>
                        </form>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <!-- JavaScript for Hamburger Menu Toggle -->
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        feather.replace();
    </script>
</body>
</html>
