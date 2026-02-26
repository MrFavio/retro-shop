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

if (isset($_SESSION['order_address']) && isset($_SESSION['order_address']['country']) && isset($_SESSION['order_address']['city']) && isset($_SESSION['order_address']['postal_code']) && isset($_SESSION['order_address']['street']) && isset($_SESSION['order_address']['building_num'])) {
    $order_address = $_SESSION['order_address'];
} else {
    $_SESSION['badAlert'] = "You have to provide an address first!";
    header("Location: ..\cart\order_address.php");
    exit();
}

$country = htmlspecialchars($order_address['country']);
$city = htmlspecialchars($order_address['city']);
$postal_code = htmlspecialchars($order_address['postal_code']);
$street = htmlspecialchars($order_address['street']);
$building_num = htmlspecialchars($order_address['building_num']);
$apartment_num = isset($order_address['apartment_num']) && $order_address['apartment_num'] !== '' ? $order_address['apartment_num'] : null;

$stmt = $db->prepare("SELECT cart_items.cart_item_id, cart_items.quantity, products.price FROM cart_items JOIN carts ON cart_items.cart_id = carts.cart_id JOIN products ON cart_items.product_id = products.product_id WHERE carts.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_price = 0;
while ($row = $result->fetch_assoc()) {
    $total_price += $row['price'] * $row['quantity'];
}
if (isset($_SESSION['order_address']['promo_code'])) {
    $discount = $_SESSION['order_address']['promo_code'];
    $total_price = $total_price - ($total_price * ($discount / 100));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {

    $stmt = $db->prepare("INSERT INTO order_addresses (order_address_id, country, city, street, building_number, apartment_number, postal_code, user_id) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $country, $city, $street, $building_num, $apartment_num, $postal_code, $user_id);
    $stmt->execute();
    $order_address_id = $db->insert_id;

    $stmt = $db->prepare("INSERT INTO orders (order_id, user_id, order_address_id, delivered, order_date, delivery_date) VALUES (NULL, ?, ?, 0, NOW(), NULL)");
    $stmt->bind_param("ii", $user_id, $order_address_id);
    $stmt->execute();
    $order_id = $db->insert_id;

    $stmt = $db->prepare("SELECT cart_items.cart_item_id, cart_items.product_id, cart_items.quantity FROM cart_items JOIN carts ON cart_items.cart_id = carts.cart_id WHERE carts.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $stmt_insert = $db->prepare("INSERT INTO order_items (order_item_id, order_id, product_id, quantity) VALUES (NULL, ?, ?, ?)");
        $stmt_insert->bind_param("iii", $order_id, $row['product_id'], $row['quantity']);
        $stmt_insert->execute();
    }

    $stmt = $db->prepare("DELETE cart_items FROM cart_items JOIN carts ON cart_items.cart_id = carts.cart_id WHERE carts.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    unset($_SESSION['order_address']);

    $_SESSION['goodAlert'] = "Payment successful! Your order is being processed.";
    header("Location: ..\home\index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retro Shop - Payment</title>
    <link rel="stylesheet" href="..\php\styles.css">
    <style>
        body {
        margin: 0;
        padding: 0;
        background-color: #1a1c2c;
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        }
    </style>
</head>
<body>
    <form method="post" id="payment-form">
        <input type="hidden" name="payment_method" id="payment-method">
        <div class="payment-box">
            <div class="payment-option" data-method="card">
                <div>
                💳 <span>Karta</span>
                </div>
            </div>
            <div class="details" id="card-details">
                <input type="text" placeholder="Card number" maxlength="19" id="card-number" name="card_number">
                <input type="text" placeholder="Expire date (MM/RR)" maxlength="5" id="card-exp" name="card_exp">
                <input type="text" placeholder="CVV" maxlength="3" id="card-cvv" name="card_cvv">
            </div>
        
            <div class="payment-option" data-method="bank">
                <div>
                🏦
                <span>Przelew bankowy</span>
                </div>
            </div>
            <div class="details" id="bank-details">
                <input type="text" placeholder="Account number" maxlength="32" id="bank-number" name="bank_number">
                <input type="text" placeholder="Name" id="name" name="name">
                <input type="text" placeholder="Surname" id="surname" name="surname">
            </div>
        
            <button class="pay-button" disabled id="pay-btn">Pay <?php echo htmlspecialchars($total_price); ?> zł now</button>
        </div>
    </form>

  <script>
    const options = document.querySelectorAll(".payment-option");
    const payBtn = document.getElementById("pay-btn");

    let currentMethod = null;

    options.forEach(option => {
        option.addEventListener("click", () => {
            const method = option.getAttribute("data-method");

            document.querySelectorAll(".details").forEach(d => d.style.display = "none");

            const details = document.getElementById(`${method}-details`);
            if (currentMethod === method) {
            details.style.display = "none";
            currentMethod = null;
            document.getElementById("payment-method").value = "";
            } else {
            details.style.display = "flex";
            currentMethod = method;
            document.getElementById("payment-method").value = method;
            }

            validateForm();
        });
    });

    document.getElementById("bank-number").addEventListener("input", function (e) {
        let value = e.target.value.replace(/\D/g, "");
        value = value.substring(0, 28);

        let formatted = "";

        if (value.length > 0) {
            formatted += value.substring(0, 2);
        }
        if (value.length > 2) {
            formatted += " " + value.substring(2, 6);
        }
        if (value.length > 6) {
            formatted += " " + value.substring(6, 10);
        }
        if (value.length > 10) {
            formatted += " " + value.substring(10, 14);
        }
        if (value.length > 14) {
            formatted += " " + value.substring(14, 18);
        }
        if (value.length > 18) {
            formatted += " " + value.substring(18, 22);
        }
        if (value.length > 22) {
            formatted += " " + value.substring(22, 26);
        }
        if (value.length > 26) {
            formatted += " " + value.substring(26, 28);
        }

        e.target.value = formatted.trim();
        validateForm();
    });

    document.getElementById("card-exp").addEventListener("input", function (e) {
        let value = e.target.value.replace(/\D/g, "");
        value = value.substring(0, 4);

        let formatted = "";

        if (value.length > 0) {
            formatted += value.substring(0, 2);
        }
        if (value.length > 2) {
            formatted += "/" + value.substring(2, 4);
        }

        e.target.value = formatted.trim();
        validateForm();
    });

    document.getElementById("card-cvv").addEventListener("input", function (e) {
        let value = e.target.value.replace(/\D/g, "");

        e.target.value = value.trim();
        validateForm();
    });

    document.getElementById("card-number").addEventListener("input", function (e) {
        let value = e.target.value.replace(/\D/g, "");
        value = value.substring(0, 16);

        let formatted = value.match(/.{1,4}/g);
        if (formatted) {
            e.target.value = formatted.join("-");
        } else {
            e.target.value = "";
        }

        validateForm();
    });

    function validateForm() {
    let valid = false;

    if (currentMethod === "card") {
        let number = document.getElementById("card-number").value.trim();
        let exp = document.getElementById("card-exp").value.trim();
        let cvv = document.getElementById("card-cvv").value.trim();

        if (number) {
            if (number.length == 19) {
                
            } else {
                number = null;
            }
        } else {
            number = null;
        }

        if (exp) {
            if (exp.length == 5) {
                
            } else {
                exp = null;
            }
        } else {
            exp = null;
        }

        if (cvv) {
            if (cvv.length == 3) {
                
            } else {
                cvv = null;
            }
        } else {
            cvv = null;
        }

        if(number && exp && cvv){
            valid = true;
        } else {
            valid = false;
        }
    } else if (currentMethod === "bank") {
        let bankNumber = document.getElementById("bank-number").value.trim();
        const name = document.getElementById("name").value.trim();
        const surname = document.getElementById("surname").value.trim();

        if (bankNumber) {
            if (bankNumber.length == 32) {
                
            } else {
                bankNumber = null;
            }
        } else {
            bankNumber = null;
        }

        if(bankNumber && name && surname){
            valid = true;
        } else {
            valid = false;
        }
    } else {
        valid = false;
    }

    payBtn.disabled = !valid;
    }

    document.querySelectorAll("input").forEach(input => {
    input.addEventListener("input", validateForm);
    });
    </script>
</body>
</html>