CREATE TABLE users (
  user_id       INT          PRIMARY KEY AUTO_INCREMENT,
  first_name    VARCHAR(20)  NOT NULL,
  last_name     VARCHAR(30)  NOT NULL,
  email         VARCHAR(255) NOT NULL,
  password      VARCHAR(255) NOT NULL,
  is_admin      BOOLEAN      NOT NULL    DEFAULT FALSE
);

CREATE TABLE img (
  img_id       INT          PRIMARY KEY AUTO_INCREMENT,
  file_name    VARCHAR(50)  NOT NULL,
  alt_name     VARCHAR(50)  NOT NULL
);

CREATE TABLE products (
  product_id  INT            PRIMARY KEY AUTO_INCREMENT,
  price       DECIMAL(10, 2) NOT NULL,
  title       VARCHAR(50)    NOT NULL,
  description TEXT           NOT NULL,
  first_img   INT            NOT NULL,
  second_img  INT            NOT NULL,
  in_stock    BOOLEAN        NOT NULL    DEFAULT TRUE,

  FOREIGN KEY (first_img)  REFERENCES img(img_id) ON DELETE CASCADE,
  FOREIGN KEY (second_img) REFERENCES img(img_id) ON DELETE CASCADE
);

CREATE TABLE best_products (
  best_product_id  INT  PRIMARY KEY AUTO_INCREMENT,
  product_id       INT  NOT NULL,

  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

CREATE TABLE carts (
  cart_id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,

  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE cart_items (
  cart_item_id INT PRIMARY KEY AUTO_INCREMENT,
  cart_id      INT NOT NULL,
  product_id   INT NOT NULL,
  quantity     INT NOT NULL,

  FOREIGN KEY (cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

CREATE TABLE order_addresses (
  order_address_id INT          PRIMARY KEY AUTO_INCREMENT,
  country          VARCHAR(100) NOT NULL,
  city             VARCHAR(100) NOT NULL,
  street           VARCHAR(100) NOT NULL,
  building_number  VARCHAR(10)  NOT NULL,
  apartment_number VARCHAR(10)      NULL,
  postal_code      CHAR(6)      NOT NULL,
  user_id          INT          NOT NULL,

  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE orders (
  order_id         INT     PRIMARY KEY AUTO_INCREMENT,
  user_id          INT     NOT NULL,
  order_address_id INT     NOT NULL,
  delivered        BOOLEAN NOT NULL    DEFAULT FALSE,
  order_date       DATE    NOT NULL,
  delivery_date    DATE        NULL,

  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (order_address_id) REFERENCES order_addresses(order_address_id) ON DELETE CASCADE
);

CREATE TABLE order_items (
  order_item_id INT PRIMARY KEY AUTO_INCREMENT,
  order_id      INT NOT NULL,
  product_id    INT NOT NULL,
  quantity      INT NOT NULL,

  FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE  
);

CREATE TABLE promo_codes (
  code_id     INT          PRIMARY KEY AUTO_INCREMENT,
  code        VARCHAR(100) NOT NULL,
  discount    INT          NOT NULL
);