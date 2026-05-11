-- shoppy database дотор ажиллуулна (зүүн талд shoppy сонгосон байх ёстой)

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255),
    price INT NOT NULL,
    image_url VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    receiver_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    total_price INT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Туршилтын өгөгдөл
INSERT INTO categories (name) VALUES
  ('Эмэгтэй'), ('Эрэгтэй'), ('Технологи'),
  ('Гэр ахуй'), ('Спорт'), ('Хүүхэд');

INSERT INTO products (category_id, name, description, price, image_url) VALUES
  (1, 'Nike Air Max 270', 'Эмэгтэй гутал, цагаан/ягаан өнгө', 320000, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400'),
  (1, 'Adidas Ultraboost 22', 'Хөнгөн, уян харимхай подошво', 280000, 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400'),
  (1, 'Зуны цамц', 'Хөнгөн, амьсгалдаг материал', 89000, 'https://images.unsplash.com/photo-1571945153237-4929e783af4a?w=400'),
  (2, 'Levi''s 501 Джинс', 'Классик джинс, цэнхэр өнгө', 195000, 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=400'),
  (2, 'Nike Дри-Фит цамц', 'Спорт цамц, хар өнгө', 89000, 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=400'),
  (3, 'Apple AirPods Pro 2', 'ANC дуу зогсоогч, USB-C', 580000, 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=400'),
  (3, 'Anker PowerCore 20000', 'Утасны цэнэглэгч, 20000mAh', 125000, 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=400'),
  (3, 'Logitech MX Master 3', 'Утасгүй хулгана', 210000, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400'),
  (5, 'Спортын шорт', 'Хурдан хуурайшдаг материал', 89000, 'https://images.unsplash.com/photo-1539185441755-769473a23570?w=400'),
  (5, 'Гүйлтийн кроссовк', 'Хөнгөн, бүх улирал', 290000, 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=400');
