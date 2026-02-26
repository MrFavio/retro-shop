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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($user_id)) {
        if (isset($_POST['product_to_cart']) && isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
            $product_id = intval($_POST['product_id']);

            $stmt = $db->prepare("SELECT * FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                $_SESSION['badAlert'] = "Product does not exist!";
                header("Location: ..\products\index.php");
                exit();
            }

            $stmt = $db->prepare("SELECT * FROM carts WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $stmt = $db->prepare("SELECT 1 FROM cart_items JOIN carts ON cart_items.cart_id = carts.cart_id WHERE carts.user_id = ? AND cart_items.product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
        
                $result = $stmt->get_result();
        
                if ($result->num_rows > 0) {
                    $_SESSION['badAlert'] = "You have this item in your cart!";
                    header("Location: ..\products\page.php?product=" . $product_id);
                    exit();
                }
            } else {
                $stmt = $db->prepare("INSERT INTO carts (user_id) VALUES (?)");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            }

            $stmt = $db->prepare("SELECT cart_id FROM carts WHERE carts.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_row();
            $cart_id = $row[0];

            $stmt = $db->prepare("INSERT INTO cart_items VALUES (NULL, ?, ?, 1)");
            $stmt->bind_param("ii", $cart_id, $product_id);
            $stmt->execute();
            
            $_SESSION['goodAlert'] = "Product successfully added to cart!";
            header("Location: ..\products\page.php?product=" . $product_id);
            exit();
        } elseif (isset($_POST['submit_button']) && isset($_POST['product']) && is_numeric($_POST['product']) && isset($_POST['main_product_id']) && is_numeric($_POST['main_product_id'])) {
            $main_product_id = intval($_POST['main_product_id']);
            $product_id = intval($_POST['product']);

            $stmt = $db->prepare("SELECT * FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                $_SESSION['badAlert'] = "Product does not exist!";
                header("Location: ..\products\index.php");
                exit();
            }

            $stmt = $db->prepare("SELECT * FROM carts WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $stmt = $db->prepare("SELECT 1 FROM cart_items JOIN carts ON cart_items.cart_id = carts.cart_id WHERE carts.user_id = ? AND cart_items.product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
        
                $result = $stmt->get_result();
        
                if ($result->num_rows > 0) {
                    $_SESSION['badAlert'] = "You have this item in your cart!";
                    header("Location: ..\products\page.php?product=" . $main_product_id);
                    exit();
                }
            } else {
                $stmt = $db->prepare("INSERT INTO carts (user_id) VALUES (?)");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            }

            $stmt = $db->prepare("SELECT cart_id FROM carts WHERE carts.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_row();
            $cart_id = $row[0];

            $stmt = $db->prepare("INSERT INTO cart_items VALUES (NULL, ?, ?, 1)");
            $stmt->bind_param("ii", $cart_id, $product_id);
            $stmt->execute();
            
            $_SESSION['goodAlert'] = "Product successfully added to cart!";
            header("Location: ..\products\page.php?product=" . $main_product_id);
            exit();
        }
    } else {
        $_SESSION['badAlert'] = "You have to be logged in first!";
        header("Location: ..\login\index.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['product']) && is_numeric($_GET['product']) && !empty($_GET['product'])) {
    $product_id = intval($_GET['product']);

    $stmt = $db->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['badAlert'] = "Product does not exist!";
        header("Location: ..\products\index.php");
        exit();
    }

    $stmt = $db->prepare("SELECT products.product_id, products.title, products.description, products.price, img1.file_name AS first_image_name, img1.alt_name AS first_image_alt, img2.file_name AS second_image_name, img2.alt_name AS second_image_alt FROM products JOIN img img1 ON products.first_img = img1.img_id JOIN img img2 ON products.second_img = img2.img_id WHERE products.product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $products_result = $stmt->get_result();
    $product = $products_result->fetch_assoc();
} else {
    $_SESSION['badAlert'] = "Product does not exist!";
    header("Location: ..\products\index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retro Shop - <?php echo htmlspecialchars($product['title']); ?></title>
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

    <section class="product_wrapper">

        <div class="product_images">
            <img src="..\files\product_images\<?php echo htmlspecialchars($product['first_image_name']); ?>" alt="<?php echo htmlspecialchars($product['first_image_alt']); ?>" class="main_image">
            <div class="product_thumbnails">
                <img src="..\files\product_images\<?php echo htmlspecialchars($product['first_image_name']); ?>" alt="<?php echo htmlspecialchars($product['first_image_alt']); ?>" onclick="changeMainImg('<?php echo htmlspecialchars($product['first_image_name']); ?>', '<?php echo htmlspecialchars($product['first_image_alt']); ?>')">
                <img src="..\files\product_images\<?php echo htmlspecialchars($product['second_image_name']); ?>" alt="<?php echo htmlspecialchars($product['second_image_alt']); ?>" onclick="changeMainImg('<?php echo htmlspecialchars($product['second_image_name']); ?>', '<?php echo htmlspecialchars($product['second_image_alt']); ?>')">
            </div>
        </div>

        <div class="product_details">
            <h1><?php echo htmlspecialchars($product['title']); ?></h1>
            <p class="price"><?php echo htmlspecialchars($product['price']); ?> zł</p>
            <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>

            <form method="post">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                <button type="submit" name="product_to_cart">Dodaj do koszyka</button>
            </form>
        </div>

    </section>

    <section class="container">
        <h2>Popularne produkty</h2>
        <div class="component">
            <?php
                $stmt = $db->prepare("SELECT products.product_id, products.title, products.description, products.price, img1.file_name AS first_image_name, img1.alt_name AS first_image_alt, img2.file_name AS second_image_name, img2.alt_name AS second_image_alt FROM products JOIN best_products ON products.product_id = best_products.product_id JOIN img img1 ON products.first_img = img1.img_id JOIN img img2 ON products.second_img = img2.img_id LIMIT 5;");
                $stmt->execute();
                $result = $stmt->get_result();
                
                while($row = $result->fetch_assoc()){
                    ?>
                    <div class="product">
                        <a href="..\products\page.php?product=<?php echo htmlspecialchars($row['product_id']); ?>">
                            <img src="..\files\product_images\<?php echo htmlspecialchars($row['first_image_name']); ?>" alt="<?php echo htmlspecialchars($row['first_image_alt']); ?>" class="top_image">
                            <img src="..\files\product_images\<?php echo htmlspecialchars($row['second_image_name']); ?>" alt="<?php echo htmlspecialchars($row['second_image_alt']); ?>" class="bottom_image">
                        </a>
                        <a href="..\products\page.php?product=<?php echo htmlspecialchars($row['product_id']); ?>"><h3><?php echo htmlspecialchars($row['title']); ?></h3></a>
                        <p><?php echo htmlspecialchars($row['price']); ?> zł</p>
                        
                        <form method="post">
                            <input type="hidden" name="product" value="<?php echo htmlspecialchars($row['product_id']); ?>">
                            <input type="hidden" name="main_product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                            <button type="submit" name="submit_button">Dodaj do koszyka</button>
                        </form>
                    </div>
                    <?php
                }
            ?>
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

        function changeMainImg(src, alt) {
            const mainImg = document.querySelector('.main_image');

            mainImg.src = `..\\files\\product_images\\${src}`;
            mainImg.alt = alt;
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
