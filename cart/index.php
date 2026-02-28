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
} else {
    $_SESSION['badAlert'] = "You have to be logged in!";
    header("Location: ..\login\index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_item_id = isset($_POST['cart_item_id']) ? (int)$_POST['cart_item_id'] : 0;
    $action = $_POST['action'] ?? '';

    if ($cart_item_id > 0) {
        if ($action === 'update') {
            $change = isset($_POST['change']) ? (int)$_POST['change'] : 0;
            $stmt = $db->prepare("UPDATE cart_items SET quantity = GREATEST(quantity + ?, 1) WHERE cart_item_id = ?");
            $stmt->bind_param("ii", $change, $cart_item_id);
            $stmt->execute();
        } elseif ($action === 'remove') {
            $stmt = $db->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
            $stmt->bind_param("i", $cart_item_id);
            $stmt->execute();
        }
    }

    header("Location: index.php");
    exit();
}

$stmt = $db->prepare("SELECT cart_items.cart_item_id, cart_items.quantity, products.title, products.price, img.file_name as first_img FROM cart_items JOIN carts ON cart_items.cart_id = carts.cart_id JOIN products ON cart_items.product_id = products.product_id join img on products.first_img = img.img_id WHERE carts.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retro Shop - Cart</title>
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
                            <?php if ($user['is_admin']): ?>
                                <br><a href="../user/admin/index.php" class="admin_link">Admin Panel</a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <?php if ($result->num_rows === 0): ?>
        <section class="hero">
            <h1>Twój koszyk jest pusty!</h1>
            <p>Dodaj produkty do koszyka, aby je kupić.</p>
        </section>
        
    <?php else: ?>

    <section class="cart_container">
        <h2>Twój koszyk</h2>
        
        <?php
        $total = 0;
        while ($row = $result->fetch_assoc()):
            $lineTotal = $row['price'] * $row['quantity'];
            $total += $lineTotal;
        ?>
            <div class="cart_item">

                <img src="..\files\product_images\<?php echo htmlspecialchars($row['first_img']); ?>" alt="">

                <div class="cart_info">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="price"><?php echo htmlspecialchars(number_format($row['price'],2)); ?> zł</p>
                </div>

                <form method="post" class="quantity_control">
                    <input type="hidden" name="cart_item_id" value="<?php echo htmlspecialchars($row['cart_item_id']); ?>">
                    <input type="hidden" name="action" value="update">
                    <button type="submit" name="change" value="-1" class="qty_btn">&#8592;</button>
                    <span class="qty_value"><?php echo htmlspecialchars($row['quantity']); ?></span>
                    <button type="submit" name="change" value="1" class="qty_btn">&#8594;</button>
                </form>

                <form method="post">
                    <input type="hidden" name="cart_item_id" value="<?php echo htmlspecialchars($row['cart_item_id']); ?>">
                    <input type="hidden" name="action" value="remove">
                    <button type="submit" class="remove_btn">✖</button>
                </form>

            </div>
        <?php endwhile; ?>

        <div class="cart_footer">
            <div class="cart_summary">
                <span>Suma:</span>
                <strong><?php echo htmlspecialchars(number_format($total, 2)); ?> zł</strong>
            </div>
            <a href="../cart/order_address.php">Przejdź dalej</a>
        </div>

        <?php endif; ?>
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
