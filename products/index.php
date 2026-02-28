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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_button']) && isset($_POST['product']) && is_numeric($_POST['product'])) {
    if (isset($user_id)) {
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
                header("Location: ..\products\index.php");
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
        header("Location: ..\products\index.php");
        exit();
    } else {
        $_SESSION['badAlert'] = "You have to be logged in first!";
        header("Location: ..\login\index.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $query = "SELECT products.product_id, products.title, products.description, products.price, img1.file_name AS first_image_name, img1.alt_name AS first_image_alt, img2.file_name AS second_image_name, img2.alt_name AS second_image_alt FROM products JOIN img img1 ON products.first_img = img1.img_id JOIN img img2 ON products.second_img = img2.img_id";

    $conditions = [];
    $params = [];
    $types = "";

    if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
        $conditions[] = "products.price >= ?";
        $min_price = intval($_GET['min_price']);
        $params[] = $min_price;
        $types .= "i";
    }

    if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
        $conditions[] = "products.price <= ?";
        $max_price = intval($_GET['max_price']);
        $params[] = $max_price;
        $types .= "i";
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $order_by_elements = [];

    if (isset($_GET['val_sort']) && in_array($_GET['val_sort'], ['asc', 'desc'])) {
        $val_sort = $_GET['val_sort'];
        $order_by_elements[] = "products.price " . strtoupper($val_sort);
        $order_by_value_choosen = "$val_sort";
    }

    if (isset($_GET['name_sort']) && in_array($_GET['name_sort'], ['asc', 'desc'])) {
        $name_sort = $_GET['name_sort'];
        $order_by_elements[] = "products.title " . strtoupper($name_sort);
        $order_by_name_choosen = "$name_sort";
    }
        
    if (!empty($order_by_elements)) {
        $query .= " ORDER BY " . implode(", ", $order_by_elements);
    }

    $stmt = $db->prepare($query);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $products_result = $stmt->get_result();
} else {
    $stmt = $db->prepare("SELECT products.product_id, products.title, products.description, products.price, img1.file_name AS first_image_name, img1.alt_name AS first_image_alt, img2.file_name AS second_image_name, img2.alt_name AS second_image_alt FROM products JOIN img img1 ON products.first_img = img1.img_id JOIN img img2 ON products.second_img = img2.img_id;");
    $stmt->execute();
    $products_result = $stmt->get_result();
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retro Shop - Products</title>
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

    <main>
        <aside>
            <form method="get" class="filter-form">
                <h3>Filtruj</h3>

                <div class="filter-group">
                    <label>Cena</label>
                    <div class="price-inputs">
                        <input type="number" name="min_price" placeholder="od" <?php if(isset($_GET['min_price']) && is_numeric($_GET['min_price'])) echo 'value="' . htmlspecialchars($_GET['min_price']) . '"'; ?>>
                        <input type="number" name="max_price" placeholder="do" <?php if(isset($_GET['max_price']) && is_numeric($_GET['max_price'])) echo 'value="' . htmlspecialchars($_GET['max_price']) . '"'; ?>>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Sortowanie ceny</label>

                    <label class="radio">
                        <input type="radio" name="val_sort" value="asc" <?php if(isset($order_by_value_choosen) && $order_by_value_choosen === 'asc') echo 'checked'; ?>>
                        <span>Cena rosnąco</span>
                    </label>

                    <label class="radio">
                        <input type="radio" name="val_sort" value="desc" <?php if(isset($order_by_value_choosen) && $order_by_value_choosen === 'desc') echo 'checked'; ?>>
                        <span>Cena malejąco</span>
                    </label>
                </div>

                <div class="filter-group">
                    <label>Sortowanie alfabetyczne</label>

                    <label class="radio">
                        <input type="radio" name="name_sort" value="asc" <?php if(isset($order_by_name_choosen) && $order_by_name_choosen === 'asc') echo 'checked'; ?>>
                        <span>Od A-Z</span>
                    </label>

                    <label class="radio">
                        <input type="radio" name="name_sort" value="desc" <?php if(isset($order_by_name_choosen) && $order_by_name_choosen === 'desc') echo 'checked'; ?>>
                        <span>Od Z-A</span>
                    </label>
                </div>

                <button type="submit" class="filter-btn">Zastosuj</button>
            </form>
        </aside>
        <section class="container minh">
            <?php if ($products_result->num_rows > 0): ?>
                <h2>Produkty</h2>
                <div class="component">
                    <?php
                        
                        while($row = $products_result->fetch_assoc()){
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
                                    <button type="submit" name="submit_button">Dodaj do koszyka</button>
                                </form>
                            </div>
                            <?php
                        }
                    ?>
                </div>
            <?php else: ?>
                <h2>Brak produktów do wyświetlenia.</h2>
            <?php endif; ?>
        </section>
    </main>

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
