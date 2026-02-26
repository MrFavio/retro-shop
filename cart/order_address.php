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

$stmt = $db->prepare("SELECT * FROM carts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows <= 0) {
    $_SESSION['badAlert'] = "Your cart is empty!";
    header("Location: ..\cart\index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['country']) && isset($_POST['city']) && isset($_POST['postal_code']) && isset($_POST['street']) && isset($_POST['building_num'])) {
    $country = trim($_POST['country']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $street = trim($_POST['street']);
    $building_num = trim($_POST['building_num']);
    $apartment_num = isset($_POST['apartment_num']) ? trim($_POST['apartment_num']) : '';

    $_SESSION['order_address'] = [
        'country' => $country,
        'city' => $city,
        'postal_code' => $postal_code,
        'street' => $street,
        'building_num' => $building_num,
        'apartment_num' => $apartment_num
    ];

    header("Location: ..\cart\summary.php");
    exit();
    
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retro Shop</title>
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
        <h2>Podaj adres dostawy</h2>
        
        <form method="POST" class="quantity_control column">
            <div class="cart_item grid_auto">
    
                <label for="country">Kraj:</label>
                <input type="text" id="country" name="country" placeholder="Wprowadź kraj" required>
    
                <label for="city">Miasto:</label>
                <input type="text" id="city" name="city" placeholder="Wprowadź miasto" required>

                <label for="postal_code">Kod pocztowy:</label>
                <input type="text" id="postal_code" name="postal_code" placeholder="Wprowadź kod pocztowy" required>
    
                <label for="street">Ulica</label>
                <input type="text" id="street" name="street" placeholder="Wprowadź ulicę" required>
    
                <label for="building_num">Numer budynku</label>
                <input type="text" id="building_num" name="building_num" placeholder="Wprowadź numer budynku" required>
    
                <label for="apartment_num">Numer mieszkania</label>
                <input type="text" id="apartment_num" name="apartment_num" placeholder="Wprowadź numer mieszkania (opcjonalne)"><br>
                
            </div>
            <div class="cart_footer">
                <button type="submit">Przejdź do podsumowania</button>
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
