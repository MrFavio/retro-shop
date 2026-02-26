<?php

session_start();
require_once '..\php\db.php';

$user = null;
unset($_SESSION['order_address']);

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_button']) && isset($_POST['password']) && isset($_POST['email']) && isset($_POST['name']) && isset($_POST['surname']) && isset($_POST['password_repeat'])) {
    $password = $_POST['password'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $password_repeat = $_POST['password_repeat'];

    if (empty($email) || empty($password) || empty($name) || empty($surname) || empty($password_repeat)) {
        $_SESSION['badAlert'] = "Fields cannot be empty!";
        header("Location: ..\signup\index.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['badAlert'] = "Invalid Email!";
        header("Location: ..\signup\index.php");
        exit();
    }

    if (strlen($password) < 4) {
        $_SESSION['badAlert'] = "Password is too short!";
        header("Location: ..\signup\index.php");
        exit();
    }

    if ($password !== $password_repeat) {
        $_SESSION['badAlert'] = "Passwords do not match!";
        header("Location: ..\signup\index.php");
        exit();
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['badAlert'] = "Email already in use!";
        header("Location: ..\signup\index.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password, is_admin) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $name, $surname, $email, $hashed_password);
    $stmt->execute();

    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $_SESSION['user_id'] = $result->fetch_assoc()['user_id'];

    $_SESSION['goodAlert'] = "Account created successfully!";
    header("Location: ..\home\index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retro Shop - Signup</title>
    <link rel="stylesheet" href="..\php\styles.css">
</head>
<body>
    <?php if (isset($_SESSION['goodAlert'])): ?>
        <div class="good_alert" id="alert-box"><?php echo $_SESSION['goodAlert']; unset($_SESSION['goodAlert']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['badAlert'])): ?>
        <div class="bad_alert" id="alert-box"><?php echo $_SESSION['badAlert']; unset($_SESSION['badAlert']); ?></div>
    <?php endif; ?>
    <header>
        <a href="..\home\index.php">
            <div class="logo">Retro Shop 🛒</div>
        </a>
        <nav>
            <ul>
                <li><a href="..\home\index.php">Strona główna</a></li>
                <li><a href="..\products\index.php">Produkty</a></li>
                <li><a href="..\contact\index.php">Kontakt</a></li>
                <li><a href="..\cart\index.php">Koszyk</a></li>
                <?php if ($user) : ?>
                    <li>
                        <button id="user_profile" onclick="toggleProfileOverlay()">
                            <?php
                                $firstname = htmlspecialchars($user['first_name']);
                                $surname = htmlspecialchars($user['last_name']);
                                $initials = strtoupper($firstname[0] . $surname[0]);
                                echo $initials;
                            ?>
                        </button>

                        <div class="hidden" id="profile_overlay">
                            <a href="../php/logout.php" class="logout_link">Logout</a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <h1>Największe community retro w Polsce!</h1>
    </section>

    <section class="container">
        <h2>Zaloguj się aby kupować produkty.</h2>
        <div class="component">
            <form action="" method="post" class="input_box">
                <div class="input_container">
                    <div>
                        <label for="name">Name:</label><br>
                        <input type="text" name="name" id="name" placeholder="Jan" required><br><br>
                    </div>
                    <div>
                        <label for="surname">Surname:</label><br>
                        <input type="text" name="surname" id="surname" placeholder="Kowalski" required><br><br>
                    </div>
                </div>
                <label for="email">E-mail:</label><br>
                <input type="email" name="email" id="email" placeholder="example@gmail.com" required><br><br>
                <label for="password">Password:</label><br>
                <input type="password" name="password" id="password" placeholder="password123!" minlength="4" required><br><br>
                <label for="password_repeat">Password Repeat:</label><br>
                <input type="password" name="password_repeat" id="password_repeat" placeholder="password123!" minlength="4" required><br><br>
                <a href="..\login\index.php" class="mb">➤ Logowanie</a><br><br>
                <button type="submit" name="submit_button">Login</button>
            </form>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 Retro Shop. Wszelkie prawa zastrzeżone.</p>
    </footer>

    <script>
        function toggleProfileOverlay() {
            const menu = document.getElementById('profile_overlay');
            menu.classList.toggle('hidden');
        }

        document.addEventListener("DOMContentLoaded", function () {
            let alertBox = document.getElementById("alert-box");

            if (alertBox && alertBox.innerText.trim() !== "") {
                alertBox.style.display = "block";

                setTimeout(function () {
                    alertBox.style.opacity = "1";
                    alertBox.style.transition = "opacity 0.5s";
                    alertBox.style.opacity = "0";
                    setTimeout(() => alertBox.style.display = "none", 500);
                }, 2000);
            }
        });
    </script>
</body>
</html>
