<?php
session_start();
include __DIR__ . '/config/db.php';

$message = "";
$alertType = "";

// Function to ensure input is not empty or just spaces
function isEmpty($input) {
    return trim($input) === '';
}

// Registration Process
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (isEmpty($username) || isEmpty($password)) {
        $message = "Username dan Password tidak boleh kosong.";
        $alertType = "error";
    } else {
        // Check if username is already taken
        $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $checkUser->bind_param('s', $username);
        $checkUser->execute();
        $result = $checkUser->get_result();

        if ($result->num_rows > 0) {
            $message = "Username sudah digunakan. Silakan pilih username lain.";
            $alertType = "error";
        } else {
            // Save new user
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param('ss', $username, $hashedPassword);

            if ($stmt->execute()) {
                $message = "Registrasi berhasil! Silakan login.";
                $alertType = "success";
            } else {
                $message = "Terjadi kesalahan saat registrasi.";
                $alertType = "error";
            }
        }
    }
}

// Login Process
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (isEmpty($username) || isEmpty($password)) {
        $message = "Username dan Password tidak boleh kosong.";
        $alertType = "error";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit();
        } else {
            $message = "Login gagal! Username atau password salah.";
            $alertType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="./css/styles.css">
    <style>
        /* Custom styles for enhanced design */
        .input-field {
            color: #f97316;
        }
        .input-field::placeholder {
            color: #f97316;
            opacity: 0.7;
        }
        .input-field:focus {
            border-color: #f97316;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 0 5px #f97316;
        }
        .btn-orange {
            background-color: #f97316;
            color: #fff;
            transition: background-color 0.3s ease;
        }
        .btn-orange:hover {
            background-color: #ea580c;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }
        .btn-black {
            background-color: #1f2937;
            color: #f97316;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .btn-black:hover {
            color: #ea580c;
            background-color: #111827;
        }
        /* Alert box styling for better visibility */
        .alert-success {
            background-color: #22c55e;
            color: #000;
        }
        .alert-error {
            background-color: #ef4444;
            color: #fff;
        }
    </style>
</head>
<body class="bg-gray-900 h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-gray-800 p-8 rounded-lg border border-gray-700 shadow-lg">
        <h2 class="text-2xl font-bold text-center mb-6 text-white py-2 rounded">Login To-Do List</h2>

        <!-- Alert Box -->
        <?php if (!empty($message)): ?>
            <div class="mb-4 p-4 rounded-lg text-center 
                <?php echo $alertType === 'success' ? 'alert-success' : 'alert-error'; ?>">
                <p><?php echo $message; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <input type="text" name="username" placeholder="Username" required class="input-field w-full p-3 border border-orange-500 rounded bg-gray-900 text-orange-500">
            <input type="password" name="password" placeholder="Password" required class="input-field w-full p-3 border border-orange-500 rounded bg-gray-900 text-orange-500">
            <div class="flex space-x-4">
                <button name="login" class="btn-black w-1/2 p-2 rounded flex items-center justify-center space-x-1">
                    <i data-feather="log-in" class="h-5 w-5"></i><span>Login</span>
                </button>
                <button name="register" class="btn-orange w-1/2 p-2 rounded flex items-center justify-center space-x-1">
                    <i data-feather="user-plus" class="h-5 w-5"></i><span>Register</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>
