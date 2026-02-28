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

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retro Shop - Contact</title>
    <link rel="stylesheet" href="..\php\styles.css">
</head>
<body>
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

    <section class="hero">
        <h1>Skontaktuj się z nami w każdej chwili!</h1>
        <p>Pomoc doświadczonych konsultantów 24/7.</p>
    </section>

    <section class="container">
        <h2>Odwiedź nas w naszych stacjonarnych sklepach!</h2>
        <div class="component">
            <div class="product">
                <h3>Warszawa</h3>
                <p>Stanisława Witkiewicza 9A, 03-305 Warszawa</p>
                <p class="open_status"></p>
                <a href="https://www.google.com/maps/search/Stereofonia+Witkiewicza+9A+Warszawa" target="_blank" class="map_link">Sprawdź nas!</a>
            </div>
            <div class="product">
                <h3>Berlin</h3>
                <p>Düppelstraße 32, 12163 Berlin, Niemcy</p>
                <p class="open_status"></p>
                <a href="https://www.google.com/maps/search/Retro+Audio+D%C3%BCppelstra%C3%9Fe+32+Berlin" target="_blank" class="map_link">Sprawdź nas!</a>
            </div>
            <div class="product">
                <h3>Warszawa</h3>
                <p>Puławska 176, 02-670 Warszawa</p>
                <p class="open_status"></p>
                <a href="https://www.google.com/maps/search/Audio+Laboratorium+Warszawa" target="_blank" class="map_link">Sprawdź nas!</a>
            </div>
        </div>
    </section>

    <footer class="footer_bottom">
        <p>&copy; 2025 Retro Shop. Wszelkie prawa zastrzeżone.</p>
    </footer>

    <script>
        function toggleProfileOverlay() {
            const menu = document.getElementById('profile_overlay');
            menu.classList.toggle('hidden');
        }

        const statuses = document.querySelectorAll('.open_status');

        const d = new Date();
        const day = d.getDay();
        const hour = d.getHours();

        if (day >= 0 && day < 5 && hour >= 8 && hour <= 22) {

        }

        statuses.forEach(element => {
            if (day >= 0 && day < 5 && hour >= 8 && hour <= 22) {
                element.innerHTML = "Open"
                element.classList.add('open')
            } else if (day >= 5 && day <= 6 && hour >= 10 && hour <= 20) {
                element.innerHTML = "Open"
                element.classList.add('open')
            } else {
                element.innerHTML = "Closed"
                element.classList.remove('open')
            }
        });
    </script>
</body>
</html>