INSERT INTO users (first_name, last_name, email, password, is_admin) VALUES
('Admin', 'Admin', 'admin@adm.com', '$2y$10$69n7O0JnaqJo.fkMbVL3SuNnN4Ky0m329zmGE4b8SVdrmtgJ6Mjai', TRUE),
('Jan', 'Kowalski', 'jan.kowalski@example.com', '$2y$10$69n7O0JnaqJo.fkMbVL3SuNnN4Ky0m329zmGE4b8SVdrmtgJ6Mjai', FALSE),
('Anna', 'Nowak', 'anna.nowak@example.com', '$2y$10$69n7O0JnaqJo.fkMbVL3SuNnN4Ky0m329zmGE4b8SVdrmtgJ6Mjai', TRUE);

-- Password: qwerty

INSERT INTO img (file_name, alt_name) VALUES
('a500_1.jpg', 'Atari 500 Front View'),
('a500_2.jpg', 'Atari 500 Side View'),
('asus_gpu_1.jpg', 'Asus GPU Front View'),
('asus_gpu_2.jpg', 'Asus GPU Back View'),
('atari2600_1.jpg', 'Atari 2600 Front View'),
('atari2600_2.jpg', 'Atari 2600 Side View'),
('commodore_64_1.jpg', 'Commodore 64 Front View'),
('commodore_64_2.jpg', 'Commodore 64 Back View'),
('joystick_1.jpg', 'Joystick Front View'),
('joystick_2.jpg', 'Joystick Side View'),
('kamera_video_1.jpg', 'Video Camera Front View'),
('kamera_video_2.jpg', 'Video Camera Back View'),
('kaseta_1.jpg', 'Cassette Tape Front View'),
('kaseta_2.jpg', 'Cassette Tape Back View'),
('kaseta_vhs_1.jpg', 'VHS Tape Front View'),
('kaseta_vhs_2.jpg', 'VHS Tape Back View'),
('nintendo_classic_1.jpg', 'Nintendo Classic Console Front View'),
('nintendo_classic_2.jpg', 'Nintendo Classic Console Back View'),
('nintendo_gameboy.jpg', 'Nintendo Game Boy Front View'),
('odtwarzacz_cd_1.jpg', 'CD Player Front View'),
('odtwarzacz_cd_2.jpg', 'CD Player Back View'),
('odtwarzacz_kaset_1.jpg', 'Cassette Player Front View'),
('odtwarzacz_kaset_2.jpg', 'Cassette Player Side View'),
('pacman_1.jpg', 'Pac-Man Arcade Front View'),
('pacman_2.jpg', 'Pac-Man Arcade Side View'),
('radio_1.jpg', 'Radio Front View'),
('radio_2.jpg', 'Radio Back View'),
('telefon_tarcza_1.jpg', 'Rotary Phone Front View'),
('telefon_tarcza_2.jpg', 'Rotary Phone Side View');

INSERT INTO products (price, title, description, first_img, second_img, in_stock) VALUES
(1299.00, 'Amiga 500', 'Commodore Amiga 500 to kultowy komputer domowy z początku lat 90. Urządzenie było wykorzystywane zarówno do gier, jak i do pracy z grafiką oraz muzyką. Charakteryzuje się wyjątkowym dźwiękiem i dużą biblioteką klasycznych tytułów. Idealny wybór dla fanów retro informatyki.', 1, 2, TRUE),
(499.00, 'Karta graficzna Asus GPU', 'Klasyczna karta graficzna firmy Asus przeznaczona do starszych komputerów PC. Umożliwia uruchamianie gier i aplikacji z lat 90. oraz wczesnych 2000. Często wykorzystywana w komputerach retro składanych do celów kolekcjonerskich.', 3, 4, TRUE),
(599.00, 'Atari 2600', 'Atari 2600 to jedna z pierwszych konsol do gier wideo, która zapoczątkowała rynek gier domowych. Konsola korzysta z wymiennych kartridży i oferuje proste, ale niezwykle grywalne tytuły. Prawdziwa legenda historii gamingu.', 5, 6, TRUE),
(899.00, 'Commodore 64', 'Commodore 64 to jeden z najlepiej sprzedających się komputerów domowych na świecie. Znany z ogromnej liczby gier, programów edukacyjnych i narzędzi użytkowych. Do dziś ceniony przez kolekcjonerów oraz miłośników retro technologii.', 7, 8, TRUE),
(99.00, 'Joystick retro', 'Klasyczny joystick kompatybilny z komputerami Commodore oraz Atari. Solidna konstrukcja i prosty układ przycisków sprawiają, że idealnie nadaje się do gier zręcznościowych i arcade. Niezbędny element każdego zestawu retro.', 9, 10, TRUE),
(399.00, 'Kamera wideo VHS', 'Analogowa kamera wideo nagrywająca obraz na kasety VHS. Popularna w latach 80. i 90., wykorzystywana do nagrywania domowych filmów i wydarzeń rodzinnych. Obecnie ciekawy element kolekcjonerski i przykład dawnej technologii.', 11, 12, TRUE),
(19.99, 'Kaseta magnetofonowa', 'Klasyczna kaseta magnetofonowa przeznaczona do nagrywania i odtwarzania dźwięku. Używana w magnetofonach, walkmanach oraz radiomagnetofonach. Symbol epoki analogowej muzyki.', 13, 14, TRUE),
(29.99, 'Kaseta VHS', 'Kaseta wideo VHS umożliwiająca nagrywanie filmów i programów telewizyjnych. Standardowy nośnik obrazu domowego przed erą DVD i streamingu. Idealna do magnetowidów VHS.', 15, 16, TRUE),
(399.00, 'Nintendo Classic', 'Klasyczna konsola Nintendo w wersji retro, nawiązująca wyglądem do oryginalnych modeli z lat 80. Umożliwia powrót do kultowych gier, które ukształtowały historię branży gier wideo.', 17, 18, TRUE),
(549.00, 'Nintendo Game Boy', 'Pierwsza przenośna konsola Nintendo, która zrewolucjonizowała rynek gier mobilnych. Prosta konstrukcja, długi czas pracy na bateriach oraz ogromna biblioteka gier sprawiły, że Game Boy stał się ikoną popkultury.', 19, 19, TRUE),
(279.00, 'Odtwarzacz CD Discman', 'Przenośny odtwarzacz płyt CD, popularny w latach 90. Pozwalał słuchać muzyki w wysokiej jakości poza domem. Charakterystyczny element epoki przed odtwarzaczami MP3.', 20, 21, TRUE),
(299.00, 'Odtwarzacz kaset Walkman', 'Przenośny odtwarzacz kaset magnetofonowych, który umożliwiał słuchanie muzyki w dowolnym miejscu. Walkman stał się symbolem mobilnej rozrywki końca XX wieku.', 22, 23, TRUE),
(149.00, 'Automat Pac-Man', 'Kultowy automat arcade z grą Pac-Man. Jedna z najbardziej rozpoznawalnych gier w historii, która zdobyła popularność na całym świecie. Idealny element dekoracyjny i kolekcjonerski.', 24, 25, TRUE),
(349.00, 'Radio retro', 'Klasyczne radio inspirowane stylistyką lat 80. Prosta obsługa i charakterystyczny wygląd czynią je atrakcyjnym dodatkiem do wnętrz w stylu retro.', 26, 27, TRUE),
(199.00, 'Telefon z tarczą', 'Analogowy telefon stacjonarny z obrotową tarczą numeryczną. Urządzenie przypomina czasy, gdy połączenia telefoniczne były realizowane wyłącznie przez linie stacjonarne.', 28, 29, TRUE);

INSERT INTO best_products (product_id) VALUES
(1),
(2),
(4),
(5),
(8),
(10),
(14),
(15),
(13);

INSERT INTO carts (user_id) VALUES
(1),
(2);

INSERT INTO cart_items (cart_id, product_id, quantity) VALUES
(1, 1, 2),
(2, 2, 1);

INSERT INTO order_addresses (country, city, street, building_number, apartment_number, postal_code, user_id) VALUES
('Polska', 'Warszawa', 'Kwiatowa', '10A', '5', '00-001', 1),
('Polska', 'Kraków', 'Długa', '7', NULL, '31-002', 2);

INSERT INTO orders (user_id, order_address_id, delivered, order_date, delivery_date) VALUES
(1, 1, TRUE, '2025-10-01', '2025-10-06'),
(2, 2, FALSE, '2025-10-15', '2025-10-21');

INSERT INTO order_items (order_id, product_id, quantity) VALUES
(1, 1, 2),
(2, 2, 1);

INSERT INTO promo_codes (code, discount) VALUES
('WELCOME10', 10),
('FREESHIP', 100);
