<?php
// ============================================
// FILE: setup_database.php
// Jalankan sekali untuk membuat tabel dan view yang diperlukan
// ============================================

require_once 'config.php';

 $database = new Database();
 $db = $database->getConnection();

echo "<h3>Setting up database...</h3>";

// Buat tabel jika belum ada
 $tables = [
    "CREATE TABLE IF NOT EXISTS produk (
        id_produk INT AUTO_INCREMENT PRIMARY KEY,
        nama_produk VARCHAR(255) NOT NULL,
        harga DECIMAL(10,2) NOT NULL,
        stok INT NOT NULL DEFAULT 0,
        kategori VARCHAR(100),
        deskripsi TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS pelanggan (
        id_pelanggan INT AUTO_INCREMENT PRIMARY KEY,
        nama_pelanggan VARCHAR(255) NOT NULL,
        no_telepon VARCHAR(20),
        alamat TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS penjualan (
        id_penjualan INT AUTO_INCREMENT PRIMARY KEY,
        id_pelanggan INT NOT NULL,
        tanggal_penjualan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total_harga DECIMAL(10,2) NOT NULL,
        bayar DECIMAL(10,2) NOT NULL,
        kembalian DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'selesai') DEFAULT 'selesai',
        FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
    )",
    
    "CREATE TABLE IF NOT EXISTS detail_penjualan (
        id_detail INT AUTO_INCREMENT PRIMARY KEY,
        id_penjualan INT NOT NULL,
        id_produk INT NOT NULL,
        jumlah INT NOT NULL,
        harga_satuan DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (id_penjualan) REFERENCES penjualan(id_penjualan),
        FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
    )"
];

// Buat view laporan
 $view = "CREATE OR REPLACE VIEW view_laporan_penjualan AS
        SELECT p.id_penjualan, p.tanggal_penjualan, p.total_harga, p.bayar, 
               p.kembalian, p.status, pl.nama_pelanggan
        FROM penjualan p
        JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
        ORDER BY p.tanggal_penjualan DESC";

try {
    // Buat tabel
    foreach ($tables as $sql) {
        $db->exec($sql);
        echo "Table created successfully...<br>";
    }
    
    // Buat view
    $db->exec($view);
    echo "View 'view_laporan_penjualan' created successfully...<br>";
    
    // Insert data default
    $checkPelanggan = $db->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn();
    if ($checkPelanggan == 0) {
        $db->exec("INSERT INTO pelanggan (id_pelanggan, nama_pelanggan) VALUES (1, 'Pelanggan Umum')");
        echo "Default customer 'Pelanggan Umum' added...<br>";
    }
    
    $checkProduk = $db->query("SELECT COUNT(*) FROM produk")->fetchColumn();
    if ($checkProduk == 0) {
        $db->exec("INSERT INTO produk (nama_produk, harga, stok, kategori) VALUES 
                  ('Kopi Hitam', 8000, 50, 'Minuman'),
                  ('Teh Manis', 5000, 50, 'Minuman'),
                  ('Nasi Goreng', 15000, 30, 'Makanan'),
                  ('Mie Ayam', 12000, 30, 'Makanan'),
                  ('Air Mineral', 3000, 100, 'Minuman')");
        echo "Default products added...<br>";
    }
    
    echo "<h3>Database setup completed successfully!</h3>";
    echo '<p><a href="login.php">Go to Login Page</a></p>';
    
} catch (PDOException $e) {
    echo "<h3>Error setting up database:</h3> " . $e->getMessage();
}
?>