<?php
session_start();

// Check if logout is requested
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();

    // Redirect to login page with success message
    header('Location: login.php?logout=success');
    exit;
}

// Check authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Kasir UMKM</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-color: #4a6bdf;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --sidebar-width: 260px;
            --navbar-height: 70px;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            --hover-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f0f2f5;
            color: #333;
            overflow-x: hidden;
        }

        /* Sidebar Navigation */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 25px 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .sidebar-logo i {
            font-size: 2rem;
            margin-right: 10px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu-item {
            display: block;
            padding: 15px 25px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            font-weight: 500;
        }

        .sidebar-menu-item i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .sidebar-menu-item:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu-item.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .sidebar-menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 5px;
            background-color: white;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            color: white;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .logout-btn {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            font-size: 1.2rem;
            padding: 5px;
            transition: color 0.3s ease;
        }

        .logout-btn:hover {
            color: white;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Top Navigation */
        .top-navbar {
            height: var(--navbar-height);
            background-color: white;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-color);
            cursor: pointer;
            margin-right: 15px;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .navbar-right {
            display: flex;
            align-items: center;
        }

        .navbar-item {
            margin-left: 20px;
            position: relative;
        }

        .notification-icon {
            font-size: 1.2rem;
            color: var(--secondary-color);
            cursor: pointer;
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 10px;
            height: 10px;
            background-color: var(--danger-color);
            border-radius: 50%;
        }

        /* Page Content */
        .page-content {
            padding: 25px;
        }

        .page {
            display: none;
        }

        .page.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Dashboard Cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
        }

        .stat-card.primary::before {
            background: var(--primary-gradient);
        }

        .stat-card.success::before {
            background: var(--success-gradient);
        }

        .stat-card.warning::before {
            background: var(--secondary-gradient);
        }

        .stat-card.info::before {
            background: var(--info-gradient);
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .stat-card-title {
            font-size: 0.9rem;
            color: var(--secondary-color);
            font-weight: 500;
        }

        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .stat-card.primary .stat-card-icon {
            background: var(--primary-gradient);
        }

        .stat-card.success .stat-card-icon {
            background: var(--success-gradient);
        }

        .stat-card.warning .stat-card-icon {
            background: var(--secondary-gradient);
        }

        .stat-card.info .stat-card-icon {
            background: var(--info-gradient);
        }

        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 10px;
        }

        .stat-card-footer {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
        }

        .stat-card-change {
            display: flex;
            align-items: center;
            margin-right: 10px;
        }

        .stat-card-change.positive {
            color: var(--success-color);
        }

        .stat-card-change.negative {
            color: var(--danger-color);
        }

        .stat-card-change.neutral {
            color: var(--secondary-color);
        }

        /* Charts */
        .chart-container {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .chart-options {
            display: flex;
        }

        .chart-option {
            padding: 8px 15px;
            border-radius: 20px;
            background-color: var(--light-color);
            color: var(--secondary-color);
            border: none;
            font-size: 0.85rem;
            cursor: pointer;
            margin-left: 10px;
            transition: all 0.3s ease;
        }

        .chart-option.active {
            background-color: var(--primary-color);
            color: white;
        }

        /* Tables */
        .table-container {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .table-controls {
            display: flex;
            align-items: center;
        }

        .search-input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            width: 250px;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 107, 223, 0.1);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 15px;
            background-color: var(--light-color);
            color: var(--dark-color);
            font-weight: 600;
            font-size: 0.9rem;
            border-bottom: 1px solid #ddd;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background-color: rgba(74, 107, 223, 0.05);
        }

        /* Forms */
        .form-container {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
        }

        .form-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 107, 223, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
            background-color: white;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 107, 223, 0.1);
        }

        .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
            resize: vertical;
            min-height: 100px;
            transition: all 0.3s ease;
        }

        .form-textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 107, 223, 0.1);
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #3a5bd9;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(74, 107, 223, 0.2);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(40, 167, 69, 0.2);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(220, 53, 69, 0.2);
        }

        .btn-warning {
            background-color: var(--warning-color);
            color: var(--dark-color);
        }

        .btn-warning:hover {
            background-color: #e0a800;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(255, 193, 7, 0.2);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        /* Product Grid for POS */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .product-card {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--light-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 2rem;
            color: var(--primary-color);
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .product-price {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 5px;
        }

        .product-stock {
            font-size: 0.8rem;
            color: var(--secondary-color);
        }

        /* Cart */
        .cart-container {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--card-shadow);
        }

        .cart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .cart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .cart-items {
            margin-bottom: 20px;
            max-height: 300px;
            overflow-y: auto;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .cart-item-price {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            margin-right: 15px;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: var(--light-color);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .quantity-value {
            width: 40px;
            text-align: center;
            font-weight: 500;
        }

        .cart-item-remove {
            background: none;
            border: none;
            color: var(--danger-color);
            cursor: pointer;
            font-size: 1.2rem;
        }

        .cart-summary {
            padding: 15px 0;
            border-top: 1px solid #eee;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .summary-row.total {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--dark-color);
            padding-top: 10px;
            border-top: 1px dashed #ddd;
        }

        .payment-section {
            margin-bottom: 20px;
        }

        .payment-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            outline: none;
            transition: all 0.3s ease;
        }

        .payment-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 107, 223, 0.1);
        }

        .change-display {
            padding: 15px;
            background-color: var(--light-color);
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .change-label {
            font-size: 0.9rem;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }

        .change-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success-color);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--secondary-color);
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-text {
            font-size: 1.1rem;
        }

        /* Loading State */
        .loading-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--secondary-color);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--light-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Notifikasi */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            transform: translateX(120%);
            transition: transform 0.3s ease;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background-color: var(--success-color);
        }

        .notification.error {
            background-color: var(--danger-color);
        }

        .notification.warning {
            background-color: var(--warning-color);
        }

        /* Transaction Item Style */
        .transaction-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s;
        }

        .transaction-item:hover {
            background-color: #f8f9fa;
        }

        .transaction-icon {
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .transaction-details {
            flex: 1;
        }

        .transaction-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .transaction-id {
            font-weight: 600;
            color: var(--dark-color);
        }

        .transaction-time {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .transaction-customer {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .transaction-amount {
            font-weight: 600;
            color: var(--primary-color);
        }

        .transaction-action {
            background: none;
            border: none;
            color: var(--secondary-color);
            cursor: pointer;
            padding: 5px;
            transition: color 0.3s;
        }

        .transaction-action:hover {
            color: var(--primary-color);
        }

        /* Badge for stock status */
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* ... di dalam <style> di index.php ... */

        /* Detail Penjualan Page Styles */
        .sale-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-card,
        .summary-card {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
        }

        .info-card h4,
        .summary-card h4 {
            margin-bottom: 15px;
            color: var(--dark-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 5px;
        }

        .info-card p {
            margin-bottom: 8px;
        }

        .summary-section {
            display: flex;
            justify-content: flex-end;
        }

        .summary-card {
            width: 100%;
            max-width: 400px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .summary-row.grand-total {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 15px;
        }

        @media (max-width: 768px) {
            .sale-info-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .dashboard-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }

            .table-container {
                overflow-x: auto;
            }

            .data-table {
                min-width: 600px;
            }
        }

        @media (max-width: 576px) {
            .page-content {
                padding: 15px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="bi bi-cart3"></i>
                <span>Kasir UMKM</span>
            </div>
        </div>
        <!-- ... di dalam index.php ... -->
        <div class="sidebar-menu">
            <a href="#" class="sidebar-menu-item active" data-page="dashboard">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
            <a href="#" class="sidebar-menu-item" data-page="kasir">
                <i class="bi bi-cash-stack"></i>
                Kasir
            </a>
            <a href="#" class="sidebar-menu-item" data-page="stok">
                <i class="bi bi-box-seam"></i>
                Stok Barang
            </a>
            <a href="#" class="sidebar-menu-item" data-page="pelanggan">
                <i class="bi bi-people"></i>
                Pelanggan
            </a>
            <a href="#" class="sidebar-menu-item" data-page="riwayat">
                <i class="bi bi-clock-history"></i>
                Riwayat
            </a>
        </div>
        <!-- ... -->
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <div class="user-role">Kasir</div>
                </div>
                <button class="logout-btn" id="logoutBtn">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-navbar">
            <div class="navbar-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="page-title" id="pageTitle">Dashboard</h1>
            </div>
            <div class="navbar-right">
                <div class="navbar-item">
                    <div class="notification-icon">
                        <i class="bi bi-bell"></i>
                        <span class="notification-badge"></span>
                    </div>
                </div>
                <div class="navbar-item">
                    <div id="currentDate" style="font-weight: 500;"></div>
                </div>
                <div class="navbar-item">
                    <div id="currentTime" style="font-weight: 500;"></div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="page-content">

            <!-- ==========================================
                 HALAMAN DASHBOARD
                 ========================================== -->
            <div id="dashboardPage" class="page active">
                <!-- Stats Cards -->
                <div class="dashboard-grid">
                    <div class="stat-card primary">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Pendapatan Hari Ini</div>
                            <div class="stat-card-icon">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                        <div class="stat-card-value" id="revenueToday">Rp 0</div>
                        <div class="stat-card-footer">
                            <div class="stat-card-change positive">
                                <i class="bi bi-arrow-up"></i>
                                <span>12% dari kemarin</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card success">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Transaksi Hari Ini</div>
                            <div class="stat-card-icon">
                                <i class="bi bi-receipt"></i>
                            </div>
                        </div>
                        <div class="stat-card-value" id="transactionCount">0</div>
                        <div class="stat-card-footer">
                            <div class="stat-card-change neutral">
                                <i class="bi bi-dash"></i>
                                <span>Total transaksi selesai</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card warning">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Total Produk</div>
                            <div class="stat-card-icon">
                                <i class="bi bi-box"></i>
                            </div>
                        </div>
                        <div class="stat-card-value" id="productCount">0</div>
                        <div class="stat-card-footer">
                            <div class="stat-card-change neutral">
                                <i class="bi bi-dash"></i>
                                <span>Produk dalam database</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card info">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Total Pelanggan</div>
                            <div class="stat-card-icon">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                        <div class="stat-card-value" id="customerCount">0</div>
                        <div class="stat-card-footer">
                            <div class="stat-card-change neutral">
                                <i class="bi bi-dash"></i>
                                <span>Pelanggan terdaftar</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">Penjualan 7 Hari Terakhir</h3>
                        <div class="chart-options">
                            <button class="chart-option active">Harian</button>
                            <button class="chart-option">Mingguan</button>
                            <button class="chart-option">Bulanan</button>
                        </div>
                    </div>
                    <div style="height: 300px; position: relative;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Transaksi Terbaru</h3>
                        <div class="table-controls">
                            <input type="text" class="search-input" placeholder="Cari transaksi...">
                        </div>
                    </div>
                    <div id="recentTransactionsList">
                        <div class="loading-state">
                            <div class="loading-spinner"></div>
                            <p>Memuat data...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 HALAMAN KASIR
                 ========================================== -->
            <div id="kasirPage" class="page">
                <div class="form-container">
                    <div class="form-title">Point of Sale</div>

                    <div class="form-group">
                        <label class="form-label">Pilih Pelanggan:</label>
                        <select class="form-select" id="customerSelect">
                            <option value="1">Pelanggan Umum</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
                    <!-- Products Section -->
                    <div class="form-container">
                        <div class="table-header">
                            <h3 class="table-title">Produk</h3>
                            <div class="table-controls">
                                <input type="text" class="search-input" id="searchProduct" placeholder="Cari produk...">
                            </div>
                        </div>
                        <div class="product-grid" id="productsGrid">
                            <!-- Products akan dimuat di sini -->
                        </div>
                    </div>

                    <!-- Cart Section -->
                    <div class="cart-container">
                        <div class="cart-header">
                            <h3 class="cart-title">Keranjang</h3>
                            <button class="btn btn-sm btn-danger" id="clearCart">
                                <i class="bi bi-trash"></i>
                                Kosongkan
                            </button>
                        </div>
                        <div class="cart-items" id="cartItems">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="bi bi-cart2"></i>
                                </div>
                                <div class="empty-text">Keranjang Kosong</div>
                            </div>
                        </div>
                        <div class="cart-summary">
                            <div class="summary-row">
                                <span>Total Items:</span>
                                <span id="totalItems">0</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span id="totalPrice">Rp 0</span>
                            </div>
                        </div>
                        <div class="payment-section">
                            <label class="form-label">Jumlah Bayar:</label>
                            <input type="number" class="payment-input" id="paymentAmount" placeholder="0" min="0">
                        </div>
                        <div class="change-display" id="changeDisplay" style="display: none;">
                            <div class="change-label">Kembalian:</div>
                            <div class="change-amount" id="changeAmount">Rp 0</div>
                        </div>
                        <button class="btn btn-primary btn-block" id="btnCheckout" disabled>
                            <i class="bi bi-credit-card"></i>
                            Bayar
                        </button>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 HALAMAN MANAJEMEN STOK
                 ========================================== -->
            <div id="stokPage" class="page">
                <div class="form-container">
                    <h3 class="form-title">Tambah Produk Baru</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nama Produk *</label>
                            <input type="text" class="form-input" id="namaProduk" placeholder="Masukkan nama produk">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga *</label>
                            <input type="number" class="form-input" id="hargaProduk" placeholder="0" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stok Awal *</label>
                            <input type="number" class="form-input" id="stokProduk" placeholder="0" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kategori</label>
                            <input type="text" class="form-input" id="kategoriProduk" placeholder="Contoh: Minuman, Makanan">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-textarea" id="deskripsiProduk" placeholder="Deskripsi produk (opsional)"></textarea>
                    </div>
                    <button class="btn btn-primary" id="btnAddProduct">
                        <i class="bi bi-plus-circle"></i>
                        Tambah Produk
                    </button>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Daftar Produk</h3>
                        <div class="table-controls">
                            <input type="text" class="search-input" id="searchProductTable" placeholder="Cari produk...">
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th>Stok Saat Ini</th>
                                <th>Status Stok</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="productTableBody">
                            <tr>
                                <td colspan="6" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ==========================================
                 HALAMAN MANAJEMEN PELANGGAN
                 ========================================== -->
            <div id="pelangganPage" class="page">
                <div class="form-container">
                    <h3 class="form-title">Tambah Pelanggan Baru</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nama Pelanggan *</label>
                            <input type="text" class="form-input" id="namaPelanggan" placeholder="Masukkan nama pelanggan">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-input" id="noTelepon" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-textarea" id="alamatPelanggan" placeholder="Alamat lengkap (opsional)"></textarea>
                    </div>
                    <button class="btn btn-primary" id="btnAddCustomer">
                        <i class="bi bi-plus-circle"></i>
                        Tambah Pelanggan
                    </button>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Daftar Pelanggan</h3>
                        <div class="table-controls">
                            <input type="text" class="search-input" id="searchCustomerTable" placeholder="Cari pelanggan...">
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama Pelanggan</th>
                                <th>Nomor Telepon</th>
                                <th>Alamat</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="customerTableBody">
                            <tr>
                                <td colspan="5" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ==========================================
                 HALAMAN RIWAYAT TRANSAKSI
                 ========================================== -->
            <div id="riwayatPage" class="page">
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Riwayat Transaksi Penjualan</h3>
                        <div class="table-controls">
                            <input type="date" class="form-input" id="filterDate" style="width: auto; margin-right: 10px;">
                            <button class="btn btn-primary" onclick="filterByDate()">
                                <i class="bi bi-funnel"></i>
                                Filter
                            </button>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No. Nota</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="salesHistoryBody">
                            <tr>
                                <td colspan="5" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- ==========================================
     HALAMAN DETAIL PENJUALAN
     ========================================== -->
            <div id="detailPenjualanPage" class="page">
                <div class="form-container">
                    <button class="btn btn-secondary" onclick="showPage('riwayat')">
                        <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
                    </button>
                </div>
                <div class="form-container">
                    <h3 class="form-title">Detail Transaksi</h3>

                    <!-- DEBUGGING: Tampilkan ID yang diterima -->
                    <div id="debugInfo" style="background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-family: monospace;">
                        <strong>Debug Info:</strong> Menunggu ID penjualan...
                    </div>

                    <div id="saleDetailContent">
                        <!-- Detail transaksi akan dimuat di sini -->
                        <div class="loading-state">
                            <div class="loading-spinner"></div>
                            <p>Memuat data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Print Template (Hidden) -->
    <div id="receiptTemplate" style="display: none;">
        <!-- Template will be populated by JavaScript -->
    </div>

    <!-- JavaScript Files -->
    <script>
        // Update date and time
        function updateDateTime() {
            const now = new Date();

            // Format date
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const dateStr = now.toLocaleDateString('id-ID', options);
            document.getElementById('currentDate').textContent = dateStr;

            // Format time
            const timeStr = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById('currentTime').textContent = timeStr;
        }

        // Initial update and then update every second
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Menu toggle for mobile
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Logout functionality
        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();

            if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                // Redirect to logout URL
                window.location.href = 'index.php?logout=1';
            }
        });

        // Chart options
        document.querySelectorAll('.chart-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.chart-option').forEach(opt => {
                    opt.classList.remove('active');
                });
                this.classList.add('active');

                // Here you would typically update the chart based on the selected option
                // This is just a placeholder for the actual chart update logic
            });
        });
    </script>
<!-- ... sebelum </body> di index.php ... -->

<!-- Pastikan file JS dipanggil di sini -->
<script src="app.js"></script>
<script src="stok-manager.js"></script>
<script src="customer-manager.js"></script>
<script src="dashboard.js"></script>
<script src="receipt.js"></script>
<script src="detail-penjualan.js"></script>

<script>
    // Fungsi untuk memperbarui info debug
    function updateDebugInfo() {
        const debugDiv = document.getElementById('debugInfo');
        if (debugDiv) {
            const saleId = window.saleIdToView;
            if (saleId) {
                debugDiv.innerHTML = `<strong>Debug Info:</strong> ID Penjualan diterima = <strong>${saleId}</strong>. Sekarang mencoba memuat data...`;
            } else {
                debugDiv.innerHTML = `<strong>Debug Info:</strong> ID Penjualan <strong>TIDAK</strong> ditemukan di <code>window.saleIdToView</code>.`;
            }
        }
    }
    
    // Perbarui info debug setiap kali halaman detail ditampilkan
    const originalShowPage = showPage;
    window.showPage = function(pageName) {
        originalShowPage(pageName); // Jalankan fungsi aslinya
        
        if (pageName === 'detail-penjualan') {
            // Beri sedikit jeda agar DOM siap, lalu perbarui debug
            setTimeout(updateDebugInfo, 100);
        }
    };
</script>

</body>
</html>
</body>

</html>