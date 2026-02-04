-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2026-02-04 07:19:56
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `furniture_db`
--

-- --------------------------------------------------------

--
-- 表的结构 `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `product_name`, `price`, `quantity`, `image_url`) VALUES
(8, 3, 6, '', 0.00, 2, NULL),
(9, 3, 5, '', 0.00, 1, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `category_image` varchar(255) DEFAULT 'default_category.png',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `category_image`, `status`, `created_at`) VALUES
(1, 'Bedroom', 'Bedroom.png', 'Active', '2026-02-03 09:52:52'),
(2, 'Living Room', 'LivingRoom.png', 'Active', '2026-02-03 09:52:52'),
(3, 'Study Room', 'studyroom.png', 'Active', '2026-02-03 09:52:52'),
(4, 'Home Living', 'HomeLiving.png', 'Active', '2026-02-03 09:52:52'),
(5, 'Office', 'office.png', 'Active', '2026-02-03 09:52:52'),
(6, 'Dining Room', 'cat_1770170541.png', 'Active', '2026-02-04 02:02:21');

-- --------------------------------------------------------

--
-- 表的结构 `custom_inquiries`
--

CREATE TABLE `custom_inquiries` (
  `inquiry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `reference_image` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Replied','Closed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `custom_inquiries`
--

INSERT INTO `custom_inquiries` (`inquiry_id`, `user_id`, `name`, `email`, `phone`, `subject`, `message`, `reference_image`, `status`, `created_at`) VALUES
(1, 3, 'teoruize044', 'teoruize044@gmail.com', '01120900533', 'Minimalist Solid Wood Office Desk', 'I would like to customize a desk with dimensions 140cm (L) x 60cm (W) x 75cm (H). I prefer North American Black Walnut with a wood wax oil finish. The design should be minimalist, featuring a hidden drawer and a built-in cable management slot.', '', 'Pending', '2026-02-04 03:22:59'),
(2, 4, 'junhengtoh', 'junhengtoh@gmail.com', '0123456789a', 'Minimalist Solid Wood Office Desk', 'yellow', 'custom_1770176751_6982c0efac682.png', 'Pending', '2026-02-04 03:45:51');

-- --------------------------------------------------------

--
-- 表的结构 `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `grand_total` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Processing','Shipped','Delivered','Completed','Cancelled') DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_image` varchar(255) DEFAULT NULL,
  `product_variant` varchar(50) DEFAULT 'Standard',
  `quantity` int(11) DEFAULT 1,
  `payment_method` varchar(50) DEFAULT 'Credit Card',
  `transaction_id` varchar(50) DEFAULT NULL,
  `payment_status` enum('Paid','Unpaid') DEFAULT 'Unpaid',
  `tracking_number` varchar(50) DEFAULT NULL,
  `courier` varchar(50) DEFAULT NULL,
  `order_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `orders`
--

INSERT INTO `orders` (`id`, `order_id`, `user_id`, `customer_name`, `email`, `phone`, `address`, `product_name`, `price`, `grand_total`, `status`, `order_date`, `product_image`, `product_variant`, `quantity`, `payment_method`, `transaction_id`, `payment_status`, `tracking_number`, `courier`, `order_notes`) VALUES
(11, 'ORD-6981D302B9567', 3, 'teoruize044', 'teoruize044@gmail.com', '01120900533', '1212, 1212, 1212, 12121, Sarawak', '', 0.00, 4012.80, 'Pending', '2026-02-01 10:50:42', NULL, 'Standard', 1, 'E-Wallet (Touch \'n Go)', NULL, 'Unpaid', NULL, NULL, NULL),
(12, 'ORD-6982B7A35F80F', 3, 'teoruize044', 'teoruize044@gmail.com', '01120900533', 'Jalan Ayer Keroh Lama, Bukit Beruang, Melaka., Bukit Beruang, 75450, Melaka', '', 0.00, 5610.00, 'Pending', '2026-02-02 03:06:11', NULL, 'Standard', 1, 'E-Wallet (Touch \'n Go)', NULL, 'Unpaid', NULL, NULL, NULL),
(13, 'ORD-6982B7BD67790', 3, 'teoruize044', 'teoruize044@gmail.com', '01120900533', 'Jalan Ayer Keroh Lama, Bukit Beruang, Melaka., Bukit Beruang, 75450, Melaka', '', 0.00, 7249.00, 'Pending', '2026-02-02 03:06:37', NULL, 'Standard', 1, 'Bank Transfer (CIMB)', NULL, 'Unpaid', NULL, NULL, NULL),
(14, 'ORD-6982B8F38DFB2', 3, 'teoruize044', 'teoruize044@gmail.com', '01120900533', 'Jalan Ayer Keroh Lama, Bukit Beruang, Melaka., Bukit Beruang, 75450, Melaka', '', 0.00, 15268.00, 'Pending', '2026-02-04 03:11:47', NULL, 'Standard', 1, 'E-Wallet (Touch \'n Go)', NULL, 'Unpaid', NULL, NULL, NULL),
(15, 'ORD-6982BE86B4617', 4, 'junhengtoh', 'junhengtoh@gmail.com', '0123456789', '79,Jalan Ayer Keroh Lama, Bukit Beruang, Melaka., Bukit Beruang, 75450, Sarawak', '', 0.00, 1190.00, 'Pending', '2026-02-04 03:35:34', NULL, 'Standard', 1, 'E-Wallet (Touch \'n Go)', NULL, 'Unpaid', NULL, NULL, NULL),
(16, 'ORD-6982C045E38DC', 3, 'teoruize044', 'teoruize044@gmail.com', '01120900533', 'Jalan Ayer Keroh Lama, Bukit Beruang, Melaka., Bukit Beruang, 75450, Melaka', '', 0.00, 10120.00, 'Completed', '2026-02-04 03:43:01', NULL, 'Standard', 1, 'Bank Transfer (CIMB)', NULL, 'Unpaid', '', '', NULL),
(17, 'ORD-6982C06632F48', 4, 'junhengtoh', 'junhengtoh@gmail.com', '0123456789', '79,Jalan Ayer Keroh Lama, Bukit Beruang, Melaka., Bukit Beruang, 75450, Sarawak', '', 0.00, 12650.00, 'Pending', '2026-02-04 03:43:34', NULL, 'Standard', 1, 'E-Wallet (Touch \'n Go)', NULL, 'Unpaid', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(8, 11, 7, 2, 899.00),
(9, 11, 8, 2, 550.00),
(10, 11, 9, 1, 750.00),
(11, 12, 33, 1, 550.00),
(12, 12, 32, 1, 1150.00),
(13, 12, 31, 1, 1250.00),
(14, 12, 30, 1, 125.00),
(15, 12, 29, 1, 245.00),
(16, 12, 28, 1, 580.00),
(17, 12, 27, 1, 850.00),
(18, 12, 26, 1, 350.00),
(19, 13, 27, 1, 850.00),
(20, 13, 26, 3, 350.00),
(21, 13, 25, 5, 195.00),
(22, 13, 22, 2, 145.00),
(23, 13, 23, 1, 245.00),
(24, 13, 20, 8, 95.00),
(25, 13, 19, 2, 180.00),
(26, 13, 18, 1, 220.00),
(27, 13, 17, 2, 680.00),
(28, 13, 16, 1, 480.00),
(29, 14, 33, 2, 550.00),
(30, 14, 32, 3, 1150.00),
(31, 14, 31, 2, 1250.00),
(32, 14, 27, 3, 850.00),
(33, 14, 26, 2, 350.00),
(34, 14, 25, 2, 195.00),
(35, 14, 23, 5, 245.00),
(36, 14, 24, 4, 380.00),
(37, 14, 20, 1, 95.00),
(38, 14, 21, 1, 350.00),
(39, 15, 18, 1, 220.00),
(40, 15, 17, 1, 680.00),
(41, 16, 32, 8, 1150.00),
(42, 17, 32, 10, 1150.00);

-- --------------------------------------------------------

--
-- 表的结构 `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_quantity` int(11) DEFAULT 0,
  `product_image` varchar(255) DEFAULT 'default_product.png',
  `image_gallery_1` varchar(255) DEFAULT NULL,
  `image_gallery_2` varchar(255) DEFAULT NULL,
  `image_gallery_3` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `brand` varchar(100) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `compare_at_price` decimal(10,2) DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `sku` varchar(50) DEFAULT NULL,
  `low_stock_alert` int(11) DEFAULT 5,
  `variants_color` varchar(100) DEFAULT NULL,
  `variants_size` varchar(100) DEFAULT NULL,
  `variants_material` varchar(100) DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT 0.00,
  `dimensions` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `stock_quantity`, `product_image`, `image_gallery_1`, `image_gallery_2`, `image_gallery_3`, `status`, `created_at`, `brand`, `tags`, `compare_at_price`, `cost_price`, `sku`, `low_stock_alert`, `variants_color`, `variants_size`, `variants_material`, `weight`, `dimensions`) VALUES
(7, 1, 'Minimalist European White Metal Daybed', 'Features an elegant curved backrest design with European-style floral patterns on the sides. The sturdy metal slat support system eliminates the need for a box spring. It functions perfectly as a comfortable single bed or a stylish sofa.', 899.00, 20, 'prod_6981bc36ba79f.png', '', '', '', 'Active', '2026-02-03 09:13:26', 'Modern Minimalist Series', 'European Design', 1450.00, 450.00, 'KL-DB-WHT-001', 5, 'Pearl White', '', 'Powder-coated Metal Frame', 22.50, 'L200 × D92 × H94 cm'),
(8, 1, 'Industrial Red Metal Twin-over-Full Bunk Bed', 'Classic twin-over-full bunk bed. Made of reinforced metal tubing with a durable red matte finish. Features a slanted side ladder for added safety.', 550.00, 30, 'prod_6981bde37fcad.png', '', '', '', 'Active', '2026-02-03 09:20:35', 'Modern Industrial Series', 'Bunk Bed，Industrial Style', 960.00, 280.00, 'KL-IND-BK-RED-02', 5, 'Retro Red', 'single', 'Powder-coated Metal Frame', 48.00, 'L200 × W140 × H165 cm'),
(9, 1, 'Dreamy Series White Metal Kids\' Bunk Bed', 'Equal-width bunk bed design with full-length safety guardrails. Accented with delicate star-shaped hollow-out patterns. Eco-friendly powder-coated metal with ample under-bed space.', 750.00, 50, 'prod_6981bf4ee6369.png', '', '', '', 'Active', '2026-02-03 09:26:38', 'Cozy Home Series', 'Children\'s Bed，Safety Guardrails', 1200.00, 380.00, 'KL-KID-BK-WHT-03', 5, 'Pure White', 'Twin-over-Twin', 'Rust-resistant Powder-coated Metal', 42.00, 'L200 × W100 × H165 cm'),
(10, 6, 'Nordic Minimalist Black Leather Dining Chair', 'Minimalist Nordic design with an ergonomic curved backrest. Upholstered in easy-to-clean black leather that is both smooth and wear-resistant. Sturdy metal legs provide stable support.', 120.00, 50, 'prod_6982a9a6bbd92.png', '', '', '', 'Active', '2026-02-04 02:06:30', 'Modern Minimalist Series', 'Dining Chair', 220.00, 55.00, 'KL-CH-BLK-04', 5, 'Matte Black', 'Standard Size', 'Synthetic Leather + Metal Legs', 6.50, 'L85cm × W45cm ×  H50cm'),
(11, 6, 'Nordic Minimalist Round Coffee Table', 'Simple and modern round design with a smooth, easy-to-clean tabletop. Features a stable pedestal metal base to maximize legroom. Suitable for cafes, discussion areas, or small dining spaces.', 150.00, 40, 'prod_6982aaddab8cc.png', '', '', '', 'Active', '2026-02-04 02:11:41', 'Modern Dining Series', 'Coffee Table', 280.00, 70.00, 'KL-TAB-WHT-05', 5, 'Simple White', 'Standard Size', 'MDF Tabletop + Metal Support Stand', 8.50, 'L60cm × W60cm ×  H75cm'),
(12, 6, 'Nordic Style Curved Backrest Chair', 'Unique curved one-piece design that provides an expansive armrest feel. Stylish yet ergonomic, offering excellent back support. Lightweight and waterproof, suitable for both indoor and outdoor use.', 85.00, 102, 'prod_6982ab8ebdd81.png', '', '', '', 'Active', '2026-02-04 02:14:38', 'Modern Leisure Series', 'Leisure Chair', 160.00, 40.00, 'KL-CH-WHT-06', 20, 'Simple White', 'Standard Size', 'Eco-friendly Polypropylene (PP)', 4.50, 'L78cm × W52cm ×  H48cm'),
(13, 6, 'Nordic Minimalist Rectangular Dining Table', 'KL-DT-WHT-07', 299.00, 30, 'prod_6982abfee0d96.png', '', '', '', 'Active', '2026-02-04 02:16:30', 'Modern Dining Series', 'Dining Table', 450.00, 140.00, 'KL-DT-WHT-07', 5, 'Simple White + Natural Wood', 'Standard Size', 'MDF Tabletop + Solid Wood Frame Support', 18.50, 'L120cm × W60cm ×  H75cm'),
(14, 6, 'Modern Minimalist Black Round Stool', 'A classic round stool design featuring a perforated surface for breathability. Sturdy four-legged metal frame provides stable support. Lightweight and stackable for easy storage, making it an ideal choice for small dining areas or event venues.', 35.00, 120, 'prod_6982acba245c0.png', '', '', '', 'Active', '2026-02-04 02:19:38', 'Modern Minimalist Series', 'Round Stool', 65.00, 15.00, 'KL-ST-BLK-08', 25, 'Matte Black', 'Standard Size', 'Eco-friendly Plastic Surface + Powder-coated Metal Legs', 2.10, 'L30cm × W30cm ×  H45cm'),
(15, 6, 'Modern Minimalist Stainless Steel Round Bistro Table', 'Durable full stainless steel construction with excellent water and corrosion resistance. The smooth surface is easy to clean and maintain. Features a stable four-star base design, perfect for outdoor dining, cafes, or break areas.', 199.00, 25, 'prod_6982ad100ab53.png', '', '', '', 'Active', '2026-02-04 02:21:04', 'Modern Leisure Series', 'Outdoor Table', 350.00, 90.00, 'KL-TAB-SS-09', 5, 'Polished Silver', 'Standard Size', 'Stainless Steel Tabletop + Stainless Steel Pedestal Stand', 9.50, 'L60cm × W60cm ×  H72cm'),
(16, 2, 'Nordic Solid Wood Two-Seater Sofa Chair', 'A classic Nordic solid wood frame design, built for durability and premium texture. Features thickened, removable, and washable linen cushions for excellent breathability and comfort. The rounded, ergonomic armrests make it an ideal choice for apartments, study rooms, or small living areas.', 480.00, 14, 'prod_6982adb3b21e0.png', '', '', '', 'Active', '2026-02-04 02:23:47', 'Modern Minimalist Series', 'Solid Wood', 750.00, 220.00, 'KL-SF-BRW-10', 3, 'Dark Wood Frame + Coffee Brown Cushions', 'Standard Two-Seater Size', 'Solid Wood Frame + High-Density Foam + Linen Fabric', 28.50, 'L120cm × W75cm ×  H80cm'),
(17, 2, 'Nordic Solid Wood Three-Seater Sofa', 'A classic Nordic solid wood structure, the spacious three-seater design provides ample space for relaxation. It features thickened linen cushions that are removable and washable, balancing breathability with comfort. The sturdy solid wood frame is durable with a sophisticated finish, making it an ideal piece to elevate any living room.', 680.00, 7, 'prod_6982ae1668ff0.png', '', '', '', 'Active', '2026-02-04 02:25:26', 'Modern Minimalist Series', 'Solid Wood', 950.00, 350.00, 'KL-SF-BRW-11', 2, 'KL-SF-BRW-11', 'Standard Three-Seater Size', 'Solid Wood Frame + High-Density Foam + Linen Fabric', 38.00, 'L180cm × W75cm ×  H80cm'),
(18, 2, 'Nordic Solid Wood Rectangular Coffee Table', 'A classic Nordic rectangular coffee table crafted from full solid wood, offering a stable structure and natural wood grain texture. Its minimalist design blends perfectly with various living room styles. The spacious tabletop is ideal for books, tea sets, or daily essentials.', 220.00, 23, 'prod_6982ae7b85dea.png', '', '', '', 'Active', '2026-02-04 02:27:07', 'Modern Minimalist Series', 'Solid Wood', 350.00, 95.00, 'KL-CT-BRW-12', 5, 'Walnut Brown', 'Standard Size', 'Full Solid Wood Structure (Rubberwood)', 15.00, 'L120cm × W60cm ×  H45cm'),
(19, 2, 'Modern Minimalist Armless Leather Sofa Chair', 'Features a minimalist armless modular design that saves space and supports multi-unit combinations. The premium orange leather finish is easy to maintain, providing a delicate touch and a modern aesthetic. Full foam padding ensures excellent seating support.', 180.00, 23, 'prod_6982aee55b388.png', '', '', '', 'Active', '2026-02-04 02:28:53', 'Modern Leisure Series', 'Sofa Chair', 320.00, 85.00, 'KL-LC-ORG-13', 5, 'Vibrant Orange', 'Standard Single Seat Size', 'High-quality Synthetic Leather + High-density Resilience Foam', 12.50, 'L60cm × W70cm ×  H75cm'),
(20, 2, 'Modern Minimalist Blue Cube Ottoman', 'A simple and stable cube design upholstered in durable, easy-to-clean deep blue leather. Filled with high-resilience foam, it can be used as an extra seat or a footstool, flexibly meeting various space requirements.', 95.00, 71, 'prod_6982af7da33ae.png', '', '', '', 'Active', '2026-02-04 02:31:04', 'Modern Leisure Series', 'Cube Stool', 150.00, 40.00, 'KL-OTT-BLU-14', 15, 'Deep Blue', 'Standard Size', 'High-quality Synthetic Leather + Thickened Foam Padding', 5.80, 'L45cm × W45cm ×  H45cm'),
(21, 6, 'Modern Minimalist High Bar Table', 'A simple and stable rectangular bar table design featuring a white wear-resistant tabletop paired with a black matte metal frame. The lower crossbar provides extra structural stability and functions as a footrest. Ideal for break areas, bars, or open-plan office spaces.', 350.00, 19, 'prod_6982afe70458f.png', '', '', '', 'Active', '2026-02-04 02:33:11', 'Modern Office & Leisure Series', 'Bar Table', 580.00, 160.00, 'KL-BT-WHT-15', 5, 'Simple White Top + Black Metal Frame', 'Standard Size', 'Thickened MDF Tabletop + Reinforced Metal Support Frame', 24.50, 'L120cm × W60cm ×  H105cm'),
(22, 6, 'Modern Ergonomic Leisure Bar Stool', 'Featuring a minimalist one-piece molded back with a cut-out design, it is not only stylish but also enhances breathability and ease of movement. The streamlined ergonomic seat provides comfortable support, while the slender metal frame with a footrest ring ensures stability. Ideal for bars, discussion areas, or modern home dining spaces.', 145.00, 48, 'prod_6982b053cd1c4.png', '', '', '', 'Active', '2026-02-04 02:34:59', 'Modern Office & Leisure Series', 'Bar Stool', 260.00, 65.00, 'KL-BS-BLU-16', 5, 'Modern Blue + Light Grey Frame', 'Standard Size', 'Reinforced Polypropylene (PP) Seat + Powder-coated Slim Metal Legs', 5.20, 'L45cm × W48cm ×  H105cm'),
(23, 5, 'Modern Minimalist Office Desk', 'This desk features a minimalist design with a natural wood-finish tabletop for a warm, organic feel, paired with stable black panel-style supports. The spacious surface is ideal for laptops, monitors, and office supplies, with a built-in cable management hole at the back to help keep your workspace tidy.', 245.00, 39, 'prod_6982b0d29cab6.png', '', '', '', 'Active', '2026-02-04 02:37:06', 'Modern Office Series', 'Office Desk', 380.00, 110.00, 'KL-OD-BLK-17', 5, 'Natural Wood Top + Black Legs', 'Standard Office Size', 'High-quality MDF + Reinforced Support Structure', 21.50, 'L120cm × W60cm ×  H75cm'),
(24, 5, 'Modern Minimalist Office Long Table', 'This desk perfectly combines minimalist design with functionality. The light wood-grain surface is spacious and flat, paired with a stable T-shaped metal frame that provides ample legroom. It features a perforated modesty panel at the front, offering privacy while maintaining an airy feel, making it an ideal choice for open office environments or personal studios.', 380.00, 11, 'prod_6982b167d87a7.png', '', '', '', 'Active', '2026-02-04 02:39:35', 'Modern Office Series', 'Long Office Desk', 520.00, 180.00, 'KL-OD-WHT-18', 5, 'Light Wood Top + Simple White Frame', 'Extended Office Size', 'Premium MDF Tabletop + Powder-coated Metal T-base Frame', 26.00, 'L160cm × W70cm ×  H75cm'),
(25, 5, 'Modern Minimalist Ergonomic Office Chair', 'This office chair features a minimalist one-piece molded shell design with an ergonomic curve to provide excellent lumbar support. The seat is upholstered in thickened fabric for a comfortable and breathable touch. The base is equipped with a stable five-star nylon frame and silent casters, along with a pneumatic adjustment handle to flexibly adapt the seat height to various desk requirements.', 195.00, 43, 'prod_6982b26955c0f.png', '', '', '', 'Active', '2026-02-04 02:43:53', 'Modern Office Series', 'Office Chair', 320.00, 80.00, 'KL-OC-WHT-19', 5, 'Simple White Frame + Classic Black Seat', 'Standard Office Size with Adjustable Height', 'High-quality Plastic Shell + Comfortable Fabric Cushion + Metal Support Frame', 8.50, 'L40cm × W40cm ×  H75cm'),
(26, 5, 'Modern Ergonomic Mesh Office Chair', 'Designed for long hours of professional use, this chair features a full mesh backrest and headrest for maximum breathability and flexible support. Equipped with an independently adjustable headrest, ergonomic lumbar support, and multifunctional armrests to effectively reduce fatigue. The thickened explosion-proof gas lift supports smooth height adjustment, while the five-star base and silent casters ensure flexible mobility.', 350.00, 29, 'prod_6982b38d9439a.png', '', '', '', 'Active', '2026-02-04 02:48:45', 'Modern Office Series', 'Ergonomic Chair', 580.00, 165.00, 'KL-OC-MSH-20', 5, '14.5', 'Standard Office Size with Full Adjustability', 'High-density Breathable Mesh + Reinforced Nylon Frame + Metal Support Stand', 14.50, 'L50cm × W50cm ×  H125cm'),
(27, 3, 'Nordic Multi-functional L-Shaped Office Desk Set', 'This all-in-one study workstation features a sophisticated deep walnut grain, seamlessly integrating a long desk, a three-drawer cabinet, glass-door display cabinets, and a top-tier open bookshelf. The L-shaped design significantly optimizes space utilization, providing massive categorized storage to keep your work and study area perfectly organized.', 850.00, 7, 'prod_6982b4143bdf6.png', '', '', '', 'Active', '2026-02-04 02:51:00', 'Modern Office Series', 'L-Shaped Desk', 1280.00, 450.00, 'KL-SD-BRW-22', 2, 'Deep Walnut', 'L-Shaped Configuration', 'Premium Eco-friendly MDF + Tempered Glass Doors + Metal Slide Rails', 45.00, 'L180cm × W120cm ×  H75cm'),
(28, 3, 'Nordic Multi-functional Integrated Study Desk & Bookshelf Set', 'This desk features an aesthetically pleasing Nordic integrated design, cleverly combining a spacious work surface with multi-tiered bookshelves, storage drawers, and a side cabinet. The natural wood grain paired with a pure white finish creates a fresh and peaceful study environment. The solid wood tapered legs not only enhance overall stability but also give the product a unique artistic touch.', 580.00, 19, 'prod_6982b479d82b2.png', '', '', '', 'Active', '2026-02-04 02:52:41', 'Modern Office Series', 'Integrated Desk', 850.00, 260.00, 'KL-SD-WHT-23', 5, 'Oak + Simple White', 'Standard Integrated Size', 'Premium Eco-friendly MDF + Solid Wood Tapered Legs + Metal Handles', 35.00, 'L120cm × W60cm ×  H160cm'),
(29, 3, 'Nordic Minimalist Fabric Leisure Armchair', 'This armchair blends Nordic minimalism with ultimate comfort. Upholstered in soft-touch creamy fabric, the integrated rounded armrest design is not only aesthetic but also ergonomic, providing a cozy, wrapped seating experience. The stable star-shaped metal base ensures strong support, making it perfect for use as a reading chair in a study or a discussion chair in a modern office space.', 245.00, 19, 'prod_6982b4f0ae5bf.png', '', '', '', 'Active', '2026-02-04 02:54:40', 'Modern Leisure Series', 'Leisure Armchair', 380.00, 115.00, 'KL-LC-WHT-24', 5, 'Creamy White', 'Standard Single Seat Size', 'Premium Brushed Fabric + Polished Metal Base + High-density Resilience Foam', 10.50, 'L65cm × W60cm ×  H82cm'),
(30, 4, 'Nordic Multi-functional Metal Coat Rack', 'This coat rack features a minimalist Nordic line design with multi-directional top hooks for coats, hats, scarves, or handbags. It cleverly integrates two circular shelves for keys, small plants, or daily essentials. The stable tripod base and eco-friendly rust-resistant finish ensure both aesthetics and durability, making it an ideal storage solution for study rooms or entryways.', 125.00, 44, 'prod_6982b55c0b333.png', '', '', '', 'Active', '2026-02-04 02:56:28', 'Modern Home Series', 'Coat Rack', 210.00, 45.00, 'KL-HR-MW-25', 5, 'Classic Black, Simple White', 'Standard Size', 'Rust-resistant Powder-coated Metal + Wood-textured Shelves', 5.50, 'L40cm × W40cm ×  H175cm'),
(31, 1, 'Modern Minimalist Upholstered Queen Bed', 'This bed features a modern minimalist vertical tufted design. The tall upholstered headboard provides excellent back support and comfort while adding an elegant visual layer to the bedroom. Upholstered in premium cream beige velvet, it is soft to the touch and easy to maintain. The sturdy internal solid wood structure ensures long-lasting durability, making it an ideal choice for a cozy sleeping environment.', 1250.00, 12, 'prod_6982b5f4c0777.png', '', '', '', 'Active', '2026-02-04 02:59:00', 'Cozy Home Series', 'Upholstered Bed', 1850.00, 680.00, 'KL-BD-CRM-27', 3, 'Cream Beige', 'Queen Size', 'Premium Velvet Fabric + Solid Wood Frame + High-density Resilience Foam', 45.00, 'L210cm × W160cm ×  H110cm'),
(32, 1, 'Modern Minimalist Dark Grey Upholstered Bed', 'This bed features a minimalist horizontal tufted design, with a dark grey tone that brings a steady and modern atmosphere to the bedroom. The fully upholstered headboard provides comfortable support, while the breathable fabric is both durable and easy to maintain. Its stable structural design ensures long-lasting sleep quality, making it an ideal piece for any modern minimalist bedroom.', 1150.00, -10, 'prod_6982b66382029.png', '', '', '', 'Active', '2026-02-04 03:00:51', 'Cozy Home Series', 'Upholstered Bed', 1680.00, 520.00, 'KL-BD-DGY-29', 2, 'Dark Grey', 'Queen Size', 'Premium Wear-resistant Fabric + Solid Wood Internal Frame + High-density Foam Padding', 98.00, 'L210cm × W160cm ×  H115cm'),
(33, 2, 'Modern Minimalist Multi-functional Folding Sofa Bed', 'This sofa bed features a unique square-tufted stitching process, with a matcha green tone that adds a touch of freshness to any home space. The multi-angle adjustable design allows it to easily switch between a sofa and a bed, perfect for naps or overnight guests. Equipped with stable metal legs, it offers both aesthetics and excellent load-bearing capacity, making it an ideal choice for modern small apartments.', 550.00, 15, 'prod_6982b706c16f6.png', '', '', '', 'Active', '2026-02-04 03:03:34', 'Cozy Home Series', 'Sofa Bed', 880.00, 240.00, 'KL-SB-GRN-30', 5, 'Matcha Green', 'Standard Three-Seater Size', 'Premium Wear-resistant Faux Leather + Polished Metal Legs + High-density Resilience Foam', 32.00, 'L180cm × W90cm ×  H35cm'),
(34, 5, 'test', 'test', 100.00, 0, 'default_product.png', '', '', '', '', '2026-02-04 03:56:11', '', '', 500.00, 60.00, '', 5, '', '', '', 0.00, '');

-- --------------------------------------------------------

--
-- 表的结构 `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `comment` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `product_id`, `rating`, `comment`, `image_url`, `is_visible`, `created_at`) VALUES
(3, 3, 7, 5, 'Very good', 'img/uploads/1770174295_Screenshot 2026-02-01 033048.png', 1, '2026-02-04 03:04:55'),
(4, 3, 33, 5, 'OMG so Good!!!!\\r\\n', 'img/uploads/1770174501_Screenshot 2026-02-04 025453.png', 1, '2026-02-04 03:08:21'),
(5, 4, 18, 5, '1', NULL, 1, '2026-02-04 03:37:44');

-- --------------------------------------------------------

--
-- 表的结构 `review_likes`
--

CREATE TABLE `review_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `review_likes`
--

INSERT INTO `review_likes` (`id`, `user_id`, `review_id`, `created_at`) VALUES
(2, 3, 4, '2026-02-04 03:08:25');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default_user.png',
  `role` enum('customer','admin') DEFAULT 'customer',
  `account_status` enum('Active','Banned') DEFAULT 'Active',
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `street_1` varchar(255) DEFAULT NULL,
  `street_2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `phone`, `address`, `profile_image`, `role`, `account_status`, `is_deleted`, `created_at`, `street_1`, `street_2`, `city`, `state`, `postcode`) VALUES
(1, 'Junheng', 'admin@test.com', '$2y$10$exZumY9QknaEdfPiSsAUxeJCHpGEbdohNsiA96dE/wNlpYDnwCjeC', NULL, NULL, 'default_user.png', 'admin', 'Active', 0, '2026-02-01 15:27:47', NULL, NULL, NULL, NULL, NULL),
(2, 'TestUser', 'user@test.com', '$2y$10$abcdefg1234567890HASH', NULL, NULL, 'default_user.png', 'customer', 'Active', 0, '2026-02-01 15:27:47', NULL, NULL, NULL, NULL, NULL),
(3, 'teoruize044', 'teoruize044@gmail.com', '$2y$10$K/TSeEb4Opgk0BjtmA2LreRCiiz/dHnJh8quEuEEQgNXfUQJBy6eK', '01120900533', 'Jalan Ayer Keroh Lama, Bukit Beruang, Melaka., Bukit Beruang, 75450, Melaka', 'default_user.png', 'customer', 'Active', 1, '2026-02-01 18:48:57', 'Jalan Ayer Keroh Lama', 'Bukit Beruang, Melaka.', 'Bukit Beruang', 'Melaka', '75450'),
(4, 'junhengtoh', 'junhengtoh@gmail.com', '$2y$10$3WX3/vq.USpwdsBL3q5zq.AGQ38oew1jJVgcNGd3uJCjHAyYq/uJm', '0123456789', '79,Jalan Ayer Keroh Lama, Bukit Beruang, Melaka., Bukit Beruang, 75450, Sarawak', 'default_user.png', 'customer', 'Active', 0, '2026-02-04 03:31:21', '79,Jalan Ayer Keroh Lama', 'Bukit Beruang, Melaka.', 'Bukit Beruang', 'Sarawak', '75450');

-- --------------------------------------------------------

--
-- 表的结构 `user_addresses`
--

CREATE TABLE `user_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line` text NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `postcode` varchar(10) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转储表的索引
--

--
-- 表的索引 `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`);

--
-- 表的索引 `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- 表的索引 `custom_inquiries`
--
ALTER TABLE `custom_inquiries`
  ADD PRIMARY KEY (`inquiry_id`);

--
-- 表的索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`);

--
-- 表的索引 `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- 表的索引 `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`);

--
-- 表的索引 `review_likes`
--
ALTER TABLE `review_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`review_id`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- 表的索引 `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`address_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- 使用表AUTO_INCREMENT `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `custom_inquiries`
--
ALTER TABLE `custom_inquiries`
  MODIFY `inquiry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- 使用表AUTO_INCREMENT `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- 使用表AUTO_INCREMENT `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- 使用表AUTO_INCREMENT `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `review_likes`
--
ALTER TABLE `review_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 限制导出的表
--

--
-- 限制表 `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
