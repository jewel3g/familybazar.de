<?php
// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start debugging output
echo "<!-- DEBUG MODE ACTIVE -->\n";
echo "<!-- Database Connection Test -->\n";

// Simple database connection test
try {
    $host = 'localhost';
    $dbname = 'u848712226_familybazar';
    $username = 'u848712226_admin';
    $password = 'JewelAdmin@#102109';
    
    echo "<!-- Connecting to: host=$host, dbname=$dbname, username=$username -->\n";
    
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5,
        ]
    );
    
    echo "<!-- Database connection successful! -->\n";
    
    // Test query: Count tables
    $testQuery = $conn->query("SHOW TABLES");
    $tables = $testQuery->fetchAll(PDO::FETCH_COLUMN);
    echo "<!-- Found " . count($tables) . " tables: " . implode(', ', $tables) . " -->\n";
    
    // Fetch ALL data for debugging
    echo "\n<!-- === DEBUG: FETCHING ALL DATA === -->\n";
    
    // 1. Fetch all categories
    echo "<!-- 1. CATEGORIES -->\n";
    $categoryQuery = $conn->prepare("SELECT * FROM categories ORDER BY category_id ASC");
    $categoryQuery->execute();
    $allCategories = $categoryQuery->fetchAll();
    echo "<!-- Found " . count($allCategories) . " categories -->\n";
    
    // 2. Fetch all products
    echo "\n<!-- 2. PRODUCTS -->\n";
    $productsQuery = $conn->prepare("SELECT * FROM products ORDER BY product_id ASC");
    $productsQuery->execute();
    $allProductsData = $productsQuery->fetchAll();
    echo "<!-- Found " . count($allProductsData) . " products -->\n";
    
    // 3. Fetch product images
    echo "\n<!-- 3. PRODUCT IMAGES -->\n";
    $imagesQuery = $conn->prepare("SELECT * FROM product_images ORDER BY product_id, display_order");
    $imagesQuery->execute();
    $allImages = $imagesQuery->fetchAll();
    echo "<!-- Found " . count($allImages) . " product images -->\n";
    
    // Now fetch data for the website display
    echo "\n<!-- === FETCHING DATA FOR WEBSITE DISPLAY === -->\n";
    
    // Fetch categories for website (only active ones)
    $categoryDisplayQuery = $conn->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC LIMIT 8");
    $categoryDisplayQuery->execute();
    $categories = $categoryDisplayQuery->fetchAll();
    echo "<!-- Active categories for display: " . count($categories) . " -->\n";
    
    // Fetch featured products - SIMPLIFIED VERSION FIRST
    echo "<!-- === FETCHING FEATURED PRODUCTS === -->\n";
    
    // First, check if there are any featured products at all
    $featuredCountQuery = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND is_featured = 1");
    $featuredCountQuery->execute();
    $featuredCount = $featuredCountQuery->fetch()['count'];
    echo "<!-- Total featured products in database: " . $featuredCount . " -->\n";
    
    if ($featuredCount > 0) {
        // Fetch with images
        $featuredQuery = $conn->prepare("SELECT p.*, pi.image_url 
                                        FROM products p 
                                        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                                        WHERE p.is_active = 1 AND p.is_featured = 1 
                                        ORDER BY p.created_at DESC 
                                        LIMIT 8");
        $featuredQuery->execute();
        $featuredProducts = $featuredQuery->fetchAll();
        echo "<!-- Featured products with images: " . count($featuredProducts) . " -->\n";
    } else {
        // If no featured products, get any active products
        echo "<!-- No featured products found, getting any active products instead -->\n";
        $featuredQuery = $conn->prepare("SELECT p.*, pi.image_url 
                                        FROM products p 
                                        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                                        WHERE p.is_active = 1 
                                        ORDER BY p.created_at DESC 
                                        LIMIT 8");
        $featuredQuery->execute();
        $featuredProducts = $featuredQuery->fetchAll();
        echo "<!-- Active products (fallback): " . count($featuredProducts) . " -->\n";
    }
    
    // Fetch new arrivals - Get products from last 30 days or newest products
    $newArrivalsQuery = $conn->prepare("SELECT p.*, pi.image_url 
                                       FROM products p 
                                       LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                                       WHERE p.is_active = 1 
                                       ORDER BY p.created_at DESC 
                                       LIMIT 8");
    $newArrivalsQuery->execute();
    $newArrivals = $newArrivalsQuery->fetchAll();
    echo "<!-- New arrivals: " . count($newArrivals) . " -->\n";
    
    // Fetch best sellers - Use view count or get random products
    $bestSellersQuery = $conn->prepare("SELECT p.*, pi.image_url 
                                       FROM products p 
                                       LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                                       WHERE p.is_active = 1 
                                       ORDER BY p.view_count DESC, RAND() 
                                       LIMIT 8");
    $bestSellersQuery->execute();
    $bestSellers = $bestSellersQuery->fetchAll();
    echo "<!-- Best sellers: " . count($bestSellers) . " -->\n";
    
    // Fetch all active products
    $allProductsQuery = $conn->prepare("SELECT p.*, pi.image_url 
                                       FROM products p 
                                       LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                                       WHERE p.is_active = 1 
                                       ORDER BY p.created_at DESC 
                                       LIMIT 12");
    $allProductsQuery->execute();
    $allProducts = $allProductsQuery->fetchAll();
    echo "<!-- All active products: " . count($allProducts) . " -->\n";
    
    // If connection is good
    $connection_success = true;
    echo "<!-- All data fetched successfully! -->\n";
    
    // Check if we have any products at all
    $totalActiveProducts = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1")->fetch()['count'];
    echo "<!-- Total active products in database: " . $totalActiveProducts . " -->\n";
    
} catch(PDOException $e) {
    $connection_error = $e->getMessage();
    $connection_success = false;
    $categories = [];
    $featuredProducts = [];
    $newArrivals = [];
    $bestSellers = [];
    $allProducts = [];
    
    echo "<!-- DATABASE ERROR: " . htmlspecialchars($e->getMessage()) . " -->\n";
    echo "<!-- Error occurred in file: " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . " -->\n";
    
    // Show detailed error information
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3>Database Connection Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database credentials or contact support.</p>";
    echo "</div>";
}

// If no products found, create fallback data
$hasProducts = !empty($featuredProducts) || !empty($newArrivals) || !empty($bestSellers) || !empty($allProducts);
echo "<!-- Has products to display: " . ($hasProducts ? 'YES' : 'NO') . " -->\n";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FamilyBazar - Premium Grocery Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <style>
        * {
            margin: 0; 
            padding: 0; 
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary-red: #d32f2f;
            --dark-red: #b71c1c;
            --light-red: #ff6659;
            --light-bg: #f9f9f9;
            --dark-text: #333;
            --light-text: #777;
            --white: #ffffff;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        body { 
            background-color: var(--light-bg); 
            color: var(--dark-text);
            line-height: 1.6;
        }

        /* Header & Navigation */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: var(--shadow);
            background-color: var(--primary-red);
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        header.hidden {
            transform: translateY(-100%);
        }

        .top-nav {
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 15px 5%; 
            background: var(--primary-red);
            color: var(--white);
            flex-wrap: wrap;
        }
        
        .logo { 
            font-size: 28px; 
            font-weight: bold; 
            color: var(--white); 
            display: flex;
            align-items: center;
        }
        
        .logo i {
            margin-right: 8px;
            font-size: 22px;
        }
        
        .logo span { 
            color: #ffeb3b; 
        }
        
        .search-bar {
            position: relative;
            flex-grow: 1;
            max-width: 500px;
            margin: 0 20px;
        }
        
        .search-bar input {
            width: 100%; 
            padding: 12px 20px 12px 45px; 
            border-radius: 25px;
            border: none; 
            outline: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .search-bar input:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }
        
        .search-bar i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-red);
        }
        
        .user-actions {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        /* User links styling */
        .user-link {
            text-decoration: none;
            color: inherit;
        }

        .user-link:hover span {
            color: #ffeb3b;
        }

        /* Logo link */
        .logo a {
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .user-actions span {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 12px;
            color: var(--white);
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .user-actions span:hover {
            color: #ffeb3b;
        }
        
        .user-actions i {
            font-size: 20px;
            margin-bottom: 4px;
        }
        
        .cart-count {
            background: var(--white);
            color: var(--primary-red);
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: -5px;
            right: -5px;
            font-weight: bold;
        }
        
        .cart-wrapper {
            position: relative;
        }

        .main-nav {
            background: var(--dark-red); 
            color: var(--white);
            display: flex; 
            align-items: center; 
            padding: 0 5%;
            transition: all 0.3s ease;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }

        .logo-image {
            height: 40px;
            width: auto;
            max-width: 200px;
        }
        
        .cat-btn { 
            background: #9a0007; 
            color: var(--white); 
            border: none; 
            padding: 15px 30px; 
            cursor: pointer; 
            font-weight: 600;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
        }
        
        .cat-btn:hover {
            background: #7a0000;
        }
        
        .cat-btn i {
            margin-right: 8px;
        }

        .main-nav ul { 
            display: flex; 
            list-style: none; 
        }
        
        .main-nav ul li a { 
            color: var(--white); 
            text-decoration: none; 
            padding: 15px 20px; 
            display: block;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .main-nav ul li a:hover,
        .main-nav ul li a.active { 
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Hero Section - SLIDING IMAGE ONLY */
        .hero {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            padding: 140px 5% 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Debug styles */
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
        }

        .debug-success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .debug-error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .debug-warning {
            background: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }

        /* LEFT SIDE - Sliding Image Section */
        .hero-main {
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            height: 400px;
        }

        .hero-slider {
            width: 100%;
            height: 100%;
        }

        .swiper {
            width: 100%;
            height: 100%;
        }

        .swiper-slide {
            position: relative;
        }

        .slide-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slide-content {
            position: absolute;
            bottom: 40px;
            left: 40px;
            color: white;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 25px;
            border-radius: 10px;
            max-width: 500px;
        }

        .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
            color: white;
        }

        .slide-content h1 {
            font-size: 32px;
            margin: 10px 0;
            line-height: 1.2;
            color: white;
        }

        .slide-content p {
            font-size: 16px;
            max-width: 80%;
            line-height: 1.6;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.9);
        }

        .shop-now {
            background: var(--primary-red);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }

        .shop-now:hover {
            background: var(--dark-red);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .shop-now i {
            margin-left: 8px;
            transition: transform 0.3s;
        }

        .shop-now:hover i {
            transform: translateX(5px);
        }

        /* RIGHT SIDE - Original Cards */
        .hero-side {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .side-card {
            border-radius: 20px;
            padding: 30px;
            color: white;
            position: relative;
            overflow: hidden;
            min-height: 190px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: transform 0.3s;
        }

        .side-card:hover {
            transform: translateY(-5px);
        }

        .side-card p {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .side-card h3 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .red {
            background: linear-gradient(135deg, #e63946, #d00000);
        }

        .green {
            background: linear-gradient(135deg, #40916c, #2d6a4f);
        }

        .order-btn {
            background: white;
            border: none;
            padding: 10px 25px;
            border-radius: 20px;
            margin-top: 10px;
            font-weight: 600;
            color: #333;
            cursor: pointer;
            align-self: flex-start;
            transition: all 0.3s;
        }

        .order-btn:hover {
            background: #f8f8f8;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        /* Categories Sidebar */
        .categories-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100%;
            background-color: var(--white);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1001;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            padding: 20px;
            overflow-y: auto;
        }

        .categories-sidebar.active {
            transform: translateX(0);
        }

        .categories-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-bg);
        }

        .categories-header h2 {
            color: var(--primary-red);
        }

        .close-categories {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--dark-text);
            cursor: pointer;
        }

        .category-list {
            list-style: none;
        }

        .category-list li {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .category-list li:hover {
            color: var(--primary-red);
            padding-left: 10px;
        }

        .category-list i {
            width: 20px;
            color: var(--primary-red);
        }

        /* Categories Section */
        .categories { 
            text-align: center; 
            padding: 60px 5%; 
            max-width: 1400px;
            margin: 30px auto;
        }
        
        .categories h2 { 
            font-size: 36px; 
            color: var(--primary-red);
            margin-bottom: 10px;
        }
        
        .categories > p {
            color: var(--light-text);
            font-size: 16px;
            margin-bottom: 40px;
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .cat-card {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s; 
            cursor: pointer;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow);
            background: var(--white);
            height: 220px;
        }
        
        .cat-card:hover { 
            transform: translateY(-10px); 
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .cat-image {
            height: 140px;
            width: 100%;
            object-fit: cover;
        }
        
        .cat-info {
            padding: 15px;
            text-align: center;
        }
        
        .cat-info p {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 5px;
            color: var(--dark-text);
        }
        
        .cat-info small {
            color: var(--light-text);
            font-size: 14px;
        }

        /* Featured Products & Sections */
        .featured-products, .new-arrivals, .best-sellers, .all-products {
            padding: 60px 5%;
            background-color: var(--white);
            max-width: 1400px;
            margin: 30px auto;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }
        
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-red);
        }
        
        .section-title h2 {
            font-size: 32px;
            color: var(--primary-red);
        }
        
        .view-all {
            color: var(--primary-red);
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .view-all i {
            margin-left: 5px;
            transition: transform 0.3s;
        }
        
        .view-all:hover i {
            transform: translateX(5px);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .product-card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: var(--primary-red);
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1;
        }
        
        .product-img {
            height: 180px;
            width: 100%;
            object-fit: cover;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-title {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 8px;
            color: var(--dark-text);
            height: 50px;
            overflow: hidden;
        }
        
        .product-description {
            color: var(--light-text);
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
            height: 40px;
            overflow: hidden;
        }
        
        .product-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .price {
            font-weight: 700;
            font-size: 20px;
            color: var(--primary-red);
        }
        
        .old-price {
            text-decoration: line-through;
            color: var(--light-text);
            font-size: 14px;
            margin-left: 5px;
        }
        
        .add-to-cart {
            background: var(--primary-red);
            color: var(--white);
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .add-to-cart:hover {
            background: var(--dark-red);
        }

        /* Deals Section */
        .deals-section {
            background-color: var(--white);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 30px;
            max-width: 1400px;
            margin: 40px auto;
        }

        .deal-content {
            flex: 1;
        }

        .deal-content h3 {
            color: var(--primary-red);
            font-size: 28px;
            margin-bottom: 15px;
        }

        .deal-timer {
            display: flex;
            gap: 15px;
            margin: 20px 0;
        }

        .timer-box {
            background-color: var(--light-bg);
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            min-width: 70px;
        }

        .timer-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-red);
        }

        .timer-label {
            font-size: 12px;
            color: var(--light-text);
            margin-top: 5px;
        }

        .deal-image {
            flex: 1;
            border-radius: 10px;
            overflow: hidden;
        }

        .deal-image img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        /* Floating WhatsApp & Messenger */
        .floating-buttons {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 999;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .floating-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
            position: relative;
        }

        .floating-btn:hover {
            transform: scale(1.1);
        }

        .floating-btn.whatsapp {
            background-color: #25D366;
        }

        .floating-btn.messenger {
            background-color: #006AFF;
        }

        .floating-btn .tooltip {
            position: absolute;
            right: 70px;
            top: 50%;
            transform: translateY(-50%);
            background-color: var(--dark-text);
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .floating-btn:hover .tooltip {
            opacity: 1;
            visibility: visible;
        }

        /* Google Map Section */
        .map-section {
            padding: 60px 5%;
            background-color: var(--white);
            max-width: 1400px;
            margin: 30px auto;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .map-container {
            width: 100%;
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 20px;
            position: relative;
        }

        .map-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--dark-text);
        }

        .map-placeholder i {
            font-size: 60px;
            color: var(--primary-red);
            margin-bottom: 20px;
        }

        .map-placeholder h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .map-placeholder p {
            color: var(--light-text);
            text-align: center;
            max-width: 500px;
        }

        /* Footer */
        footer {
            background: #222;
            color: var(--white);
            padding: 60px 5% 30px;
            margin-top: 50px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .footer-logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color: var(--white);
        }
        
        .footer-logo span {
            color: #ffeb3b;
        }
        
        .footer-about p {
            line-height: 1.6;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .footer-links h3, .footer-contact h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: var(--primary-red);
        }
        
        .footer-links ul {
            list-style: none;
        }
        
        .footer-links ul li {
            margin-bottom: 12px;
        }
        
        .footer-links ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links ul li a:hover {
            color: var(--primary-red);
        }
        
        .contact-info {
            color: #ccc;
            line-height: 1.8;
        }
        
        .contact-info i {
            margin-right: 10px;
            width: 20px;
        }
        
        .copyright {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #aaa;
            font-size: 14px;
        }

        /* No products message */
        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            color: var(--light-text);
            font-size: 18px;
            background: #f9f9f9;
            border-radius: 10px;
            border: 2px dashed #ddd;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .deals-section {
                flex-direction: column;
            }
            
            .hero {
                grid-template-columns: 1fr;
                padding: 120px 5% 40px;
            }

            .hero-main {
                height: 350px;
            }
        }

        @media (max-width: 768px) {
            .top-nav {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .search-bar {
                order: 3;
                width: 100%;
                max-width: 100%;
                margin: 10px 0 0;
            }
            
            .main-nav {
                flex-direction: column;
                padding: 0;
            }
            
            .cat-btn {
                width: 100%;
                justify-content: center;
                border-radius: 0;
            }
            
            .main-nav ul {
                flex-direction: column;
                width: 100%;
            }
            
            .main-nav ul li a {
                text-align: center;
                padding: 15px;
            }
            
            .hero {
                padding: 140px 5% 40px;
            }
            
            .hero-main {
                height: 300px;
            }
            
            .slide-content {
                left: 20px;
                bottom: 20px;
                padding: 15px;
            }
            
            .slide-content h1 {
                font-size: 28px;
            }
            
            .slide-content p {
                font-size: 14px;
                max-width: 100%;
            }
            
            .category-grid {
                grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
                gap: 15px;
            }
            
            .cat-card {
                height: 200px;
            }
            
            .cat-image {
                height: 120px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .floating-buttons {
                bottom: 20px;
                right: 20px;
            }
            
            .floating-btn {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            .category-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .user-actions {
                gap: 15px;
            }
            
            .hero {
                padding: 160px 5% 40px;
            }
            
            .hero-main {
                height: 250px;
            }
            
            .slide-content {
                left: 15px;
                bottom: 15px;
                padding: 12px;
            }
            
            .slide-content h1 {
                font-size: 24px;
            }
            
            .slide-content p {
                font-size: 12px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            }
            
            .floating-buttons {
                bottom: 15px;
                right: 15px;
            }
            
            .floating-btn {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
            
            .floating-btn .tooltip {
                display: none;
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="top-nav">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/image/logo.png" alt="FamilyBazar" class="logo-image">
                </a>
            </div>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search for fresh vegetables, fruits, meat, dairy..." id="searchInput">
            </div>
            <div class="user-actions">
                <a href="account.php" class="user-link">
                    <span>
                        <i class="far fa-user"></i>
                        Account
                    </span>
                </a>
                <a href="wishlist.php" class="user-link">
                    <span>
                        <i class="far fa-heart"></i>
                        Wishlist
                    </span>
                </a>
                <a href="cart.php" class="user-link">
                    <div class="cart-wrapper">
                        <span>
                            <i class="fas fa-shopping-cart"></i>
                            Cart
                        </span>
                        <div class="cart-count">0</div>
                    </div>
                </a>
            </div>
        </div>
        <nav class="main-nav">
            <button class="cat-btn" id="categoryToggle"><i class="fas fa-bars"></i>All Categories</button>
            <ul>
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="deals.php">Hot Deals</a></li>
                <li><a href="discount.php">Discount</a></li>
                <li><a href="offers.php">Special Offers</a></li>
                <li><a href="best-sellers.php">Best Sellers</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <!-- Categories Sidebar -->
    <div class="categories-sidebar" id="categoriesSidebar">
        <div class="categories-header">
            <h2>All Categories</h2>
            <button class="close-categories" id="closeCategories">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="category-list">
            <?php
            // Display categories from database
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    echo '<li><i class="fas fa-tag"></i> ' . htmlspecialchars($category['name']) . '</li>';
                }
            } else {
                // Fallback categories
                $fallbackCats = [
                    "Fresh Vegetables", "Fresh Fruits", "Meat & Poultry", "Fish & Seafood",
                    "Dairy & Eggs", "Beverages", "Bakery", "Ready to Eat",
                    "Frozen Foods", "Spices & Masala", "Snacks", "Household"
                ];
                foreach ($fallbackCats as $cat) {
                    echo '<li><i class="fas fa-tag"></i> ' . $cat . '</li>';
                }
            }
            ?>
        </ul>
    </div>

    <!-- Hero Section - ONLY LEFT SIDE IS SLIDING IMAGE -->
    <section class="hero">
        <!-- LEFT SIDE - Sliding Images -->
        <div class="hero-main">
            <div class="hero-slider">
                <div class="swiper" id="heroSlider">
                    <div class="swiper-wrapper">
                        <!-- Slide 1 -->
                        <div class="swiper-slide">
                            <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80" class="slide-image" alt="Fresh Vegetables">
                            <div class="slide-content">
                                <span class="badge">Fresh Vegetables</span>
                                <h1>Weekend Special Offer</h1>
                                <p>Get farm-fresh vegetables delivered to your doorstep every morning. Enjoy up to 40% off on organic produce this weekend only!</p>
                                <button class="shop-now">Shop Now <i class="fas fa-arrow-right"></i></button>
                            </div>
                        </div>
                        <!-- Slide 2 -->
                        <div class="swiper-slide">
                            <img src="https://images.unsplash.com/photo-1621996346565-e3dbc353d2c5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80" class="slide-image" alt="Premium Meat">
                            <div class="slide-content">
                                <span class="badge">Premium Meat</span>
                                <h1>Premium Quality Meat</h1>
                                <p>Grass-fed beef, free-range chicken, and more. All our meat is sourced from trusted farms and delivered fresh.</p>
                                <button class="shop-now">Order Now <i class="fas fa-arrow-right"></i></button>
                            </div>
                        </div>
                        <!-- Slide 3 -->
                        <div class="swiper-slide">
                            <img src="https://images.unsplash.com/photo-1610832958506-aa56368176cf?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80" class="slide-image" alt="Dairy Products">
                            <div class="slide-content">
                                <span class="badge">Fresh Dairy</span>
                                <h1>Fresh Dairy & Eggs</h1>
                                <p>Fresh milk, cheese, butter and other dairy items delivered daily from local farms.</p>
                                <button class="shop-now">Shop Now <i class="fas fa-arrow-right"></i></button>
                            </div>
                        </div>
                        <!-- Slide 4 -->
                        <div class="swiper-slide">
                            <img src="https://images.unsplash.com/photo-1598170845058-78131a90f4bf?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80" class="slide-image" alt="Organic Vegetables">
                            <div class="slide-content">
                                <span class="badge">Organic</span>
                                <h1>100% Organic Vegetables</h1>
                                <p>Certified organic vegetables grown without pesticides or chemicals. Fresh from farm to your table.</p>
                                <button class="shop-now">Shop Organic <i class="fas fa-arrow-right"></i></button>
                            </div>
                        </div>
                    </div>
                    <!-- Add pagination -->
                    <div class="swiper-pagination"></div>
                    <!-- Add navigation buttons -->
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE - Original Cards -->
        <div class="hero-side">
            <div class="side-card red">
                <p>Premium Meat</p>
                <h3>Premium Quality Meat</h3>
                <p>Grass-fed beef, free-range chicken, and more.</p>
                <button class="order-btn">Order Now</button>
            </div>
            <div class="side-card green">
                <p>Fresh Vegetables</p>
                <h3>Fresh Vegetables Daily</h3>
                <p>Locally sourced, pesticide-free vegetables.</p>
                <button class="order-btn">Shop Now</button>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <h2>Shop By Categories</h2>
        <p>Browse our wide range of fresh products</p>
        <div class="category-grid" id="categoryGrid">
            <!-- Categories loaded by JavaScript -->
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="section-title">
            <h2>Featured Products</h2>
            <a href="products.php?filter=featured" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="products-grid" id="featuredProducts">
            <!-- Featured products loaded by JS -->
        </div>
    </section>

    <!-- New Arrivals -->
    <section class="new-arrivals">
        <div class="section-title">
            <h2>New Arrivals</h2>
            <a href="products.php?filter=new" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="products-grid" id="newArrivals">
            <!-- New arrivals loaded by JS -->
        </div>
    </section>

    <!-- Best Sellers -->
    <section class="best-sellers">
        <div class="section-title">
            <h2>Best Sellers</h2>
            <a href="products.php?filter=bestsellers" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="products-grid" id="bestSellers">
            <!-- Best sellers loaded by JS -->
        </div>
    </section>

    <!-- Deals of the Day -->
    <section class="container">
        <div class="deals-section">
            <div class="deal-content">
                <h3>Weekend Special Offer</h3>
                <p>Get up to 40% off on all fresh vegetables and fruits. This offer is valid only for this weekend.</p>
                <div class="deal-timer">
                    <div class="timer-box">
                        <div class="timer-value" id="hours">12</div>
                        <div class="timer-label">Hours</div>
                    </div>
                    <div class="timer-box">
                        <div class="timer-value" id="minutes">45</div>
                        <div class="timer-label">Minutes</div>
                    </div>
                    <div class="timer-box">
                        <div class="timer-value" id="seconds">30</div>
                        <div class="timer-label">Seconds</div>
                    </div>
                </div>
                <button class="shop-now" onclick="window.location.href='deals.php'">Grab This Deal <i class="fas fa-arrow-right"></i></button>
            </div>
            <div class="deal-image">
                <img src="https://images.unsplash.com/photo-1579113800032-c38bd7635818?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Weekend Deal">
            </div>
        </div>
    </section>

    <!-- All Products -->
    <section class="all-products">
        <div class="section-title">
            <h2>All Products</h2>
            <a href="products.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="products-grid" id="allProducts">
            <!-- All products loaded by JS -->
        </div>
    </section>

    <!-- Google Map Section -->
    <section class="map-section">
        <div class="section-title">
            <h2>Our Location</h2>
        </div>
        <p>Visit our store in Offenbach, Germany or order online for delivery</p>
        <div class="map-container">
            <div class="map-placeholder">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Offenbach am Main, Germany</h3>
                <p>Our store is located at Große Marktstraße 15, 63065 Offenbach am Main. We serve fresh groceries to the entire Rhine-Main region.</p>
                <button class="shop-now" onclick="window.open('https://maps.google.com/?q=Große+Marktstraße+15,+63065+Offenbach+am+Main+Germany', '_blank')" style="margin-top: 20px;">Get Directions <i class="fas fa-directions"></i></button>
            </div>
        </div>
    </section>

    <!-- Floating WhatsApp & Messenger -->
    <div class="floating-buttons">
        <div class="floating-btn whatsapp" id="whatsappBtn">
            <i class="fab fa-whatsapp"></i>
            <div class="tooltip">Chat on WhatsApp</div>
        </div>
        <div class="floating-btn messenger" id="messengerBtn">
            <i class="fab fa-facebook-messenger"></i>
            <div class="tooltip">Message on Messenger</div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-about">
                <div class="footer-logo">Family<span>Bazar</span></div>
                <p>Your premium grocery shop in Offenbach, Germany. We deliver fresh vegetables, meat, dairy and all household items to your doorstep.</p>
                <p>Quality, freshness, and customer satisfaction are our top priorities.</p>
                <div style="margin-top: 15px; display: flex; gap: 15px;">
                    <i class="fab fa-facebook fa-2x" style="cursor: pointer; color: #ccc;"></i>
                    <i class="fab fa-instagram fa-2x" style="cursor: pointer; color: #ccc;"></i>
                    <i class="fab fa-twitter fa-2x" style="cursor: pointer; color: #ccc;"></i>
                    <i class="fab fa-youtube fa-2x" style="cursor: pointer; color: #ccc;"></i>
                </div>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="products.php">Our Products</a></li>
                    <li><a href="deals.php">Hot Deals</a></li>
                    <li><a href="discount.php">Discount</a></li>
                    <li><a href="offers.php">Special Offers</a></li>
                    <li><a href="best-sellers.php">Best Sellers</a></li>
                    <li><a href="delivery.php">Delivery Information</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms & Conditions</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <div class="contact-info">
                    <p><i class="fas fa-map-marker-alt"></i> Große Marktstraße 15, 63065 Offenbach am Main, Germany</p>
                    <p><i class="fas fa-phone"></i> +49 (0) 69 12345678</p>
                    <p><i class="fas fa-envelope"></i> info@familybazar.de</p>
                    <p><i class="fas fa-clock"></i> Mon-Sat: 7:00 AM - 10:00 PM<br>Sun: 8:00 AM - 8:00 PM</p>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> FamilyBazar - Offenbach, Germany. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>
        // === DATABASE DATA ===
        const dbCategories = <?php echo json_encode($categories); ?>;
        const dbFeaturedProducts = <?php echo json_encode($featuredProducts); ?>;
        const dbNewArrivals = <?php echo json_encode($newArrivals); ?>;
        const dbBestSellers = <?php echo json_encode($bestSellers); ?>;
        const dbAllProducts = <?php echo json_encode($allProducts); ?>;

        // Default images
        const defaultCategoryImages = [
            'https://images.unsplash.com/photo-1598170845058-78131a90f4bf?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
            'https://images.unsplash.com/photo-1610832958506-aa56368176cf?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
            'https://images.unsplash.com/photo-1604503468506-1e8e7c5f7524?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
            'https://images.unsplash.com/photo-1563636619-e9143da7973b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
            'https://images.unsplash.com/photo-1509440159596-0249088772ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
            'https://images.unsplash.com/photo-1586201375761-83865001e31c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
            'https://images.unsplash.com/photo-1551024709-8f23befc6f87?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
            'https://images.unsplash.com/photo-1578916046942-9dcb3c2c4c9b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'
        ];

        const defaultProductImage = 'https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80';

        // Fallback data (in case database is empty)
        const fallbackCategories = [
            { name: "Fresh Vegetables", count: "45 Items", image: "https://images.unsplash.com/photo-1598170845058-78131a90f4bf?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
            { name: "Seasonal Fruits", count: "58 Items", image: "https://images.unsplash.com/photo-1610832958506-aa56368176cf?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
            { name: "Meat & Poultry", count: "52 Items", image: "https://images.unsplash.com/photo-1604503468506-1e8e7c5f7524?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
            { name: "Dairy & Eggs", count: "67 Items", image: "https://images.unsplash.com/photo-1563636619-e9143da7973b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
            { name: "Bakery & Breads", count: "20 Items", image: "https://images.unsplash.com/photo-1509440159596-0249088772ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
            { name: "Pantry Staples", count: "85 Items", image: "https://images.unsplash.com/photo-1586201375761-83865001e31c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
            { name: "Beverages", count: "42 Items", image: "https://images.unsplash.com/photo-1551024709-8f23befc6f87?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
            { name: "Frozen Foods", count: "35 Items", image: "https://images.unsplash.com/photo-1578916046942-9dcb3c2c4c9b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
        ];

        const fallbackProducts = [
            { id: 1, name: "Fresh Organic Tomatoes", category: "Vegetables", price: 2.99, originalPrice: 3.99, badge: "Sale", image: "https://images.unsplash.com/photo-1592924357228-91a4daadcfea?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
            { id: 2, name: "Farm Fresh Chicken Breast", category: "Meat", price: 8.99, originalPrice: null, badge: "Popular", image: "https://images.unsplash.com/photo-1604503468506-1e8e7c5f7524?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
            { id: 3, name: "Organic Broccoli", category: "Vegetables", price: 3.49, originalPrice: 4.49, badge: "Sale", image: "https://images.unsplash.com/photo-1584270354949-c26b0d5b4a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
            { id: 4, name: "Fresh Cow Milk 1L", category: "Dairy", price: 1.99, originalPrice: null, badge: null, image: "https://images.unsplash.com/photo-1563636619-e9143da7973b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
            { id: 5, name: "Organic Brown Eggs (12 pcs)", category: "Dairy", price: 4.99, originalPrice: 5.99, badge: "Sale", image: "https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
            { id: 6, name: "Fresh Salmon Fillet", category: "Fish", price: 12.99, originalPrice: null, badge: "Fresh", image: "https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" },
        ];

        // Initialize Hero Slider
        const heroSlider = new Swiper('#heroSlider', {
            direction: 'horizontal',
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '#heroSlider .swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '#heroSlider .swiper-button-next',
                prevEl: '#heroSlider .swiper-button-prev',
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
        });

        // Auto-hide header on scroll
        let lastScrollTop = 0;
        const header = document.querySelector('header');

        window.addEventListener('scroll', function () {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down
                header.classList.add('hidden');
            } else {
                // Scrolling up
                header.classList.remove('hidden');
            }

            lastScrollTop = scrollTop;
        });

        // Category sidebar toggle
        const categoryToggle = document.getElementById('categoryToggle');
        const categoriesSidebar = document.getElementById('categoriesSidebar');
        const closeCategories = document.getElementById('closeCategories');

        categoryToggle.addEventListener('click', () => {
            categoriesSidebar.classList.add('active');
        });

        closeCategories.addEventListener('click', () => {
            categoriesSidebar.classList.remove('active');
        });

        // Floating buttons functionality
        const whatsappBtn = document.getElementById('whatsappBtn');
        const messengerBtn = document.getElementById('messengerBtn');

        whatsappBtn.addEventListener('click', () => {
            window.open('https://wa.me/4915123456789?text=Hello%20FamilyBazar%20Offenbach%20team!%20I%20have%20a%20question%20about%20your%20products.', '_blank');
        });

        messengerBtn.addEventListener('click', () => {
            window.open('https://m.me/familybazar.offenbach', '_blank');
        });

        // Deal timer
        function updateTimer() {
            const hoursElement = document.getElementById('hours');
            const minutesElement = document.getElementById('minutes');
            const secondsElement = document.getElementById('seconds');

            let hours = parseInt(hoursElement.textContent);
            let minutes = parseInt(minutesElement.textContent);
            let seconds = parseInt(secondsElement.textContent);

            seconds--;

            if (seconds < 0) {
                seconds = 59;
                minutes--;

                if (minutes < 0) {
                    minutes = 59;
                    hours--;

                    if (hours < 0) {
                        hours = 23;
                    }
                }
            }

            hoursElement.textContent = hours.toString().padStart(2, '0');
            minutesElement.textContent = minutes.toString().padStart(2, '0');
            secondsElement.textContent = seconds.toString().padStart(2, '0');
        }

        setInterval(updateTimer, 1000);

        // === FUNCTIONS ===
        
        // Function to create category card HTML (for database categories)
        function createDBCategoryCard(category, index) {
            const imageUrl = category.icon ? category.icon : defaultCategoryImages[index] || defaultCategoryImages[0];
            const itemCount = Math.floor(Math.random() * 50) + 20;
            
            return `
                <div class="cat-card" data-category-id="${category.category_id || index}">
                    <img src="${imageUrl}" alt="${category.name}" class="cat-image" onerror="this.src='${defaultCategoryImages[0]}'">
                    <div class="cat-info">
                        <p>${category.name || 'Category ' + (index + 1)}</p>
                        <small>${itemCount} Items</small>
                    </div>
                </div>
            `;
        }

        // Function to create product card HTML (for database products)
        function createDBProductCard(product) {
            // Get image URL
            let imageUrl = defaultProductImage;
            
            if (product.image_url && product.image_url !== '' && product.image_url !== null) {
                imageUrl = product.image_url;
            }
            
            // Determine badge
            let badge = '';
            if (product.compare_price && parseFloat(product.compare_price) > parseFloat(product.price || 0)) {
                badge = 'Sale';
            } else if (product.is_featured == 1) {
                badge = 'Featured';
            }
            
            const originalPrice = product.compare_price && parseFloat(product.compare_price) > parseFloat(product.price || 0) ? product.compare_price : null;
            const price = product.price ? parseFloat(product.price).toFixed(2) : '0.00';
            const productName = product.name || 'Product';
            const description = product.short_description || product.description || 'Fresh, high-quality product from trusted suppliers.';
            
            return `
                <div class="product-card" data-product-id="${product.product_id}">
                    ${badge ? `<div class="product-badge">${badge}</div>` : ''}
                    <img src="${imageUrl}" alt="${productName}" class="product-img" onerror="this.src='${defaultProductImage}'">
                    <div class="product-info">
                        <h3 class="product-title">${productName}</h3>
                        <p class="product-description">${description.substring(0, 60)}${description.length > 60 ? '...' : ''}</p>
                        <div class="product-price">
                            <div>
                                <span class="price">€${price}</span>
                                ${originalPrice ? `<span class="old-price">€${parseFloat(originalPrice).toFixed(2)}</span>` : ''}
                            </div>
                            <button class="add-to-cart" data-product-id="${product.product_id}">
                                <i class="fas fa-shopping-cart"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // Function to create fallback category card HTML
        function createFallbackCategoryCard(category) {
            return `
                <div class="cat-card">
                    <img src="${category.image}" alt="${category.name}" class="cat-image">
                    <div class="cat-info">
                        <p>${category.name}</p>
                        <small>${category.count}</small>
                    </div>
                </div>
            `;
        }

        // Function to create fallback product card HTML
        function createFallbackProductCard(product) {
            return `
                <div class="product-card">
                    ${product.badge ? `<div class="product-badge">${product.badge}</div>` : ''}
                    <img src="${product.image}" alt="${product.name}" class="product-img">
                    <div class="product-info">
                        <h3 class="product-title">${product.name}</h3>
                        <p class="product-description">Fresh, high-quality ${product.category.toLowerCase()} from trusted suppliers.</p>
                        <div class="product-price">
                            <div>
                                <span class="price">€${product.price.toFixed(2)}</span>
                                ${product.originalPrice ? `<span class="old-price">€${product.originalPrice.toFixed(2)}</span>` : ''}
                            </div>
                            <button class="add-to-cart" data-product-id="${product.id}">
                                <i class="fas fa-shopping-cart"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // === MAIN POPULATE CONTENT FUNCTION ===
        function populateContent() {
            const categoryGrid = document.getElementById('categoryGrid');
            const featuredProductsDiv = document.getElementById('featuredProducts');
            const newArrivalsDiv = document.getElementById('newArrivals');
            const bestSellersDiv = document.getElementById('bestSellers');
            const allProductsDiv = document.getElementById('allProducts');

            // Clear any existing content
            categoryGrid.innerHTML = '';
            featuredProductsDiv.innerHTML = '';
            newArrivalsDiv.innerHTML = '';
            bestSellersDiv.innerHTML = '';
            allProductsDiv.innerHTML = '';

            // === CATEGORIES ===
            if (dbCategories && dbCategories.length > 0) {
                // Use database categories
                categoryGrid.innerHTML = dbCategories.map((cat, index) => createDBCategoryCard(cat, index)).join('');
            } else {
                // Use fallback categories
                categoryGrid.innerHTML = fallbackCategories.map(createFallbackCategoryCard).join('');
            }

            // === FEATURED PRODUCTS ===
            if (dbFeaturedProducts && dbFeaturedProducts.length > 0) {
                featuredProductsDiv.innerHTML = dbFeaturedProducts.map(createDBProductCard).join('');
            } else {
                // Use fallback products
                featuredProductsDiv.innerHTML = fallbackProducts.slice(0, 4).map(createFallbackProductCard).join('');
            }

            // === NEW ARRIVALS ===
            if (dbNewArrivals && dbNewArrivals.length > 0) {
                newArrivalsDiv.innerHTML = dbNewArrivals.map(createDBProductCard).join('');
            } else {
                // Use fallback products
                newArrivalsDiv.innerHTML = fallbackProducts.slice(2, 6).map(createFallbackProductCard).join('');
            }

            // === BEST SELLERS ===
            if (dbBestSellers && dbBestSellers.length > 0) {
                bestSellersDiv.innerHTML = dbBestSellers.map(createDBProductCard).join('');
            } else {
                // Use fallback products
                bestSellersDiv.innerHTML = fallbackProducts.slice(1, 5).map(createFallbackProductCard).join('');
            }

            // === ALL PRODUCTS ===
            if (dbAllProducts && dbAllProducts.length > 0) {
                allProductsDiv.innerHTML = dbAllProducts.map(createDBProductCard).join('');
            } else {
                // Use fallback products
                allProductsDiv.innerHTML = fallbackProducts.map(createFallbackProductCard).join('');
            }
        }

        // Add to cart functionality
        function addToCart(productId) {
            // Update cart count
            const cartCount = document.querySelector('.cart-count');
            let currentCount = parseInt(cartCount.textContent);
            cartCount.textContent = currentCount + 1;
            
            // Show notification
            showNotification('Product added to cart!');
        }

        // Show notification
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#28a745' : '#dc3545'};
                color: white;
                padding: 15px 20px;
                border-radius: 5px;
                z-index: 9999;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideIn 0.3s ease;
            `;
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
            
            // Add CSS for animations
            if (!document.querySelector('#notification-styles')) {
                const style = document.createElement('style');
                style.id = 'notification-styles';
                style.textContent = `
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                    @keyframes slideOut {
                        from { transform: translateX(0); opacity: 1; }
                        to { transform: translateX(100%); opacity: 0; }
                    }
                `;
                document.head.appendChild(style);
            }
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = this.value.trim();
                    if (searchTerm) {
                        window.location.href = `products.php?search=${encodeURIComponent(searchTerm)}`;
                    }
                }
            });
        }

        // === INITIALIZE WHEN PAGE LOADS ===
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Page loaded with database data:');
            console.log('Categories:', dbCategories);
            console.log('Featured Products:', dbFeaturedProducts);
            console.log('New Arrivals:', dbNewArrivals);
            console.log('Best Sellers:', dbBestSellers);
            
            // Populate content
            populateContent();
            
            // Add to cart functionality
            document.addEventListener('click', function(e) {
                if (e.target.closest('.add-to-cart')) {
                    const button = e.target.closest('.add-to-cart');
                    const productId = button.getAttribute('data-product-id');
                    
                    // Visual feedback
                    const originalHTML = button.innerHTML;
                    const originalBackground = button.style.background;
                    
                    button.innerHTML = '<i class="fas fa-check"></i> Added';
                    button.style.background = '#52b788';
                    button.disabled = true;
                    
                    // Call add to cart function
                    addToCart(productId);
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.style.background = originalBackground;
                        button.disabled = false;
                    }, 2000);
                }
            });

            // Category cards click functionality
            document.querySelectorAll('.cat-card').forEach(card => {
                card.addEventListener('click', function () {
                    const categoryName = this.querySelector('p').textContent;
                    showNotification(`Browsing: ${categoryName}`);
                });
            });

            // Shop now buttons
            document.querySelectorAll('.shop-now, .order-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!e.target.closest('.shop-now[onclick]')) {
                        showNotification('Redirecting to shop...');
                        setTimeout(() => {
                            window.location.href = 'products.php';
                        }, 1000);
                    }
                });
            });

            // Search bar focus effect
            if (searchInput) {
                searchInput.addEventListener('focus', function () {
                    this.parentElement.style.transform = 'scale(1.02)';
                    this.parentElement.style.transition = 'transform 0.3s';
                });

                searchInput.addEventListener('blur', function () {
                    this.parentElement.style.transform = 'scale(1)';
                });
            }
        });
    </script>
</body>
</html>
