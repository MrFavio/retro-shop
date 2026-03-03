<?php

session_start();
require_once '..\..\php\db.php';

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
    header("Location: ..\..\login\index.php");
    exit();
}

if (!$user['is_admin']) {
    $_SESSION['badAlert'] = "Something went wrong!";
    header("Location: ..\..\home\index.php");
    exit();
}

$stmt = $db->prepare("SELECT products.*, best_products.product_id as b_product_id FROM products left JOIN best_products on products.product_id = best_products.product_id;");
$stmt->execute();
$product_result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_products'])) {

    if (isset($_POST['products'])) {

        foreach ($_POST['products'] as $product_id => $data) {

            $price = $data['price'];
            $title = $data['title'];
            $description = $data['description'];
            $in_stock = isset($data['in_stock']) ? 1 : 0;
            $best = isset($data['best']) ? 1 : 0;

            $stmt = $db->prepare("UPDATE products SET price=?, title=?, description=?, in_stock=? WHERE product_id=?");
            $stmt->bind_param("dssii", $price, $title, $description, $in_stock, $product_id);
            $stmt->execute();

            if ($best) {
                $stmt = $db->prepare("INSERT IGNORE INTO best_products (product_id) VALUES (?)");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
            } else {
                $stmt = $db->prepare("DELETE FROM best_products WHERE product_id=?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
            }
        }
    }

    $_SESSION['goodAlert'] = "Produkty zaktualizowane!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retro Shop - Admin Panel</title>
    <link rel="stylesheet" href="..\..\php\styles.css">
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
                <li><a href="..\..\home\index.php">Strona główna</a></li>
                <li><a href="..\..\products\index.php">Produkty</a></li>
                <li><a href="..\..\contact\index.php">Kontakt</a></li>
                <li><a href="..\..\cart\index.php">Koszyk</a></li>
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
                            <a href="..\..\php\logout.php" class="logout_link">Logout</a>
                            <?php if ($user['is_admin']): ?>
                                <br><a href="..\..\user\admin\index.php" class="admin_link">Admin Panel</a>
                            <?php endif; ?>
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
    
                <table>
                    <tr>
                        <th>ID Produktu</th><th>Cena</th><th>Nazwa</th><th>Opis</th><th>Popularne produkty</th><th>Dostępność</th>
                    </tr>
                    <?php
                    while ($product = $product_result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><span><?php echo htmlspecialchars($product['product_id']); ?></span><input type="hidden" name="products[<?php echo htmlspecialchars($product['product_id']); ?>][id]"value="<?php echo htmlspecialchars($product['product_id']); ?>"></td>
                            <td><input type="text" name="products[<?php echo htmlspecialchars($product['product_id']); ?>][price]" value="<?php echo htmlspecialchars($product['price']); ?>" required></td>
                            <td><input type="text" name="products[<?php echo htmlspecialchars($product['product_id']); ?>][title]" value="<?php echo htmlspecialchars($product['title']); ?>" required></td>
                            <td><input type="text" name="products[<?php echo htmlspecialchars($product['product_id']); ?>][description]" value="<?php echo htmlspecialchars($product['description']); ?>" required></td>
                            <td><input type="checkbox" name="products[<?php echo htmlspecialchars($product['product_id']); ?>][best]" value="1" <?php echo $product['b_product_id'] ? "checked" : ""; ?>></td>
                            <td><input type="checkbox" name="products[<?php echo htmlspecialchars($product['product_id']); ?>][in_stock]" value="1" <?php echo $product['in_stock'] ? "checked" : ""; ?>></td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
                <button type="submit" name="update_products">Zapisz zmiany</button>
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

