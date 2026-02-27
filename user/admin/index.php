<?php

session_start();
require_once '..\php\db.php';

$user = null;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }
} else {
    $_SESSION['badAlert'] = "You have to be logged in!";
    header("Location: ..\login\index.php");
    exit();
}

if ($user['is_admin'] != true) {
    $_SESSION['badAlert'] = "Something went wrong!";
    header("Location: ..\home\index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_button'])) {
    
    $argument_array = [];
    $param_array = [];

    if ((isset($_POST['password']) && !isset($_POST['password_repeat'])) || (!isset($_POST['password']) && isset($_POST['password_repeat']))) {
        $_SESSION['badAlert'] = "Both fields must be filled in!";
        header("Location: ..\user\settings.php");
        exit();
    }

    if (isset($_POST['name'])) {
        $name = $_POST['name'];

        if (empty($name)) {
            $_SESSION['badAlert'] = "Fields cannot be empty!";
            header("Location: ..\user\settings.php");
            exit();
        }

        $argument_array[] = "first_name";
        $param_array[] = $name;
    }

    if (isset($_POST['surname'])) {
        $surname = $_POST['surname'];

        if (empty($surname)) {
            $_SESSION['badAlert'] = "Fields cannot be empty!";
            header("Location: ..\user\settings.php");
            exit();
        }

        $argument_array[] = "last_name";
        $param_array[] = $surname;
    }

    if (isset($_POST['password']) && isset($_POST['password_repeat'])) {
        $password = $_POST['password'];
        $password_repeat = $_POST['password_repeat'];

        if (empty($password) || empty($password_repeat)) {
            $_SESSION['badAlert'] = "Fields cannot be empty!";
            header("Location: ..\user\settings.php");
            exit();
        }

        if (strlen($password) < 4) {
            $_SESSION['badAlert'] = "Password is too short!";
            header("Location: ..\user\settings.php");
            exit();
        }

        if ($password !== $password_repeat) {
            $_SESSION['badAlert'] = "Passwords do not match!";
            header("Location: ..\user\settings.php");
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $argument_array[] = "password";
        $param_array[] = $hashed_password;
    }

    $allowed_columns = ['first_name', 'last_name', 'password'];

    for ($i = 0; $i < count($argument_array); $i++) {

        if (!in_array($argument_array[$i], $allowed_columns)) {
            continue;
        }

        $column = $argument_array[$i];
        $value = $param_array[$i];

        $stmt = $db->prepare("UPDATE users SET $column = ? WHERE user_id = ?");
        $stmt->bind_param("si", $value, $user['user_id']);
        $stmt->execute();
    }
    
    $_SESSION['goodAlert'] = "Changes saved successfully!";
    header("Location: ..\user\settings.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retro Shop - Settings</title>
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

    <section class="cart_container">
        <h2>Ustawienia</h2>
        <form method="post" class="quantity_control column">
            <div class="cart_item grid_auto">
    
                <div id="bigger_user_profile" class="grid_user_row_span">
                    <?php
                        $firstname = htmlspecialchars($user['first_name']);
                        $surname = htmlspecialchars($user['last_name']);
                        $initials = strtoupper($firstname[0] . $surname[0]);
                        echo $initials;
                    ?>
                </div>

                <div>
                    <label for="name">First Name</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                </div>

                <div>
                    <label for="surname">Last Name</label>
                    <input type="text" name="surname" id="surname" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                </div><br>

                <h2 class="grid_user_col_span text_center">Zmień hasło</h2><br>

                <div>
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" placeholder="password123!" minlength="4">
                </div>

                <div>
                    <label for="password_repeat">Password Repeat</label>
                    <input type="password" name="password_repeat" id="password_repeat" placeholder="password123!" minlength="4">
                </div>
            </div>
            <div class="cart_footer">
                <button type="submit" name="submit_button">Zapisz zmiany</button>
            </div>
        </form>
    </section>


    <footer class="footer_bottom">
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
