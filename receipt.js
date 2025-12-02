// ==========================================
// RECEIPT / NOTA SYSTEM
// ==========================================

/**
 * Mencetak nota/struk penjualan berdasarkan ID penjualan.
 * Fungsi ini mengambil data penjualan dan detailnya, lalu membuka jendela baru
 * untuk mencetak HTML yang telah diformat.
 * @param {number} saleId - ID penjualan yang akan dicetak
 */
async function printReceipt(saleId) {
    try {
        // Langkah 1: Ambil data penjualan umum dari API
        // Idealnya, ada endpoint API spesifik seperti `?action=get_sale&id=`
        // Saat ini, kita mengambil semua data dan mencari yang relevan di klien.
        const saleResponse = await fetch(`${API_URL}?action=get_sales`);
        
        // Periksa jika respons dari server tidak berhasil (misalnya error 500)
        if (!saleResponse.ok) {
            throw new Error(`Gagal mengambil data penjualan: ${saleResponse.statusText}`);
        }

        const saleResult = await saleResponse.json();
        
        if (saleResult.status !== 200) {
            showNotification('Gagal memuat data penjualan', 'error');
            return;
        }
        
        // Cari penjualan spesifik berdasarkan ID
        const sale = saleResult.data.find(s => s.id_penjualan == saleId);
        if (!sale) {
            showNotification('Data penjualan tidak ditemukan', 'error');
            return;
        }
        
        // Langkah 2: Ambil detail item dari penjualan tersebut
        const detailResponse = await fetch(`${API_URL}?action=get_sale_detail&id=${saleId}`);
        
        if (!detailResponse.ok) {
            throw new Error(`Gagal mengambil detail penjualan: ${detailResponse.statusText}`);
        }

        const detailResult = await detailResponse.json();
        
        if (detailResult.status !== 200) {
            showNotification('Gagal memuat detail penjualan', 'error');
            return;
        }
        
        const details = detailResult.data;
        
        // Langkah 3: Buat HTML untuk nota
        const receiptHTML = generateReceiptHTML(sale, details);
        
        // Langkah 4: Buka jendela cetak dan tulis HTML
        const printWindow = window.open('', '_blank', 'width=800,height=600');
        printWindow.document.write(receiptHTML);
        printWindow.document.close(); // Penting untuk memastikan rendering selesai
        
        // Tunggu sebentar agar konten termuat sebelum memunculkan dialog cetak
        setTimeout(() => {
            printWindow.print();
            // Opsional: Tutup jendela setelah dialog cetak ditutup
            printWindow.onafterprint = () => printWindow.close();
        }, 500); // Sedikit diperlambat untuk memastikan semua aset (font, dll) dimuat
        
    } catch (error) {
        console.error('Error printing receipt:', error);
        showNotification('Terjadi kesalahan saat mencetak: ' + error.message, 'error');
    }
}

/**
 * Menghasilkan string HTML untuk nota/struk.
 * @param {object} sale - Objek data penjualan.
 * @param {Array} details - Array detail item yang dibeli.
 * @returns {string} String HTML yang lengkap untuk nota.
 */
function generateReceiptHTML(sale, details) {
    const now = new Date();
    const saleDate = new Date(sale.tanggal_penjualan);
    
    // Template literal untuk HTML nota
    // CSS disematkan langsung agar tidak tergantung pada file eksternal saat dicetak
    return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota #${sale.id_penjualan}</title>
    <style>
        /* Reset dan gaya dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace; /* Font standar untuk struk */
            padding: 20px;
            max-width: 80mm; /* Lebar kertas struk tipikal */
            margin: 0 auto;
        }
        
        .receipt {
            border: 2px dashed #000;
            padding: 10px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 11px;
            margin: 2px 0;
        }
        
        .info-section {
            margin-bottom: 15px;
            font-size: 12px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .items-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 11px;
        }
        
        .items-table th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            text-align: left;
        }
        
        .items-table td {
            padding: 5px 0;
            border-bottom: 1px dashed #ccc;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-details {
            font-size: 10px;
            color: #666;
        }
        
        .totals {
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-bottom: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 12px;
        }
        
        .total-row.grand-total {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px double #000;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 2px solid #000;
            padding-top: 10px;
            font-size: 11px;
        }
        
        .footer p {
            margin: 3px 0;
        }
        
        /* Gaya khusus untuk media cetak */
        @media print {
            body {
                padding: 0;
            }
            
            .receipt {
                border: none; /* Hilangkan border saat cetak */
            }
            
            @page {
                margin: 0;
                size: 80mm auto; /* Atur ukuran halaman */
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>TOKO UMKM</h1>
            <p>Jl. Contoh No. 123</p>
            <p>Telp: 021-12345678</p>
        </div>
        
        <div class="info-section">
            <div class="info-row">
                <span>No. Nota:</span>
                <span><strong>#${sale.id_penjualan}</strong></span>
            </div>
            <div class="info-row">
                <span>Tanggal:</span>
                <span>${saleDate.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                })}</span>
            </div>
            <div class="info-row">
                <span>Waktu:</span>
                <span>${saleDate.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                })}</span>
            </div>
            <div class="info-row">
                <span>Kasir:</span>
                <span>Admin</span>
            </div>
            <div class="info-row">
                <span>Pelanggan:</span>
                <span>${sale.nama_pelanggan || 'Pelanggan Umum'}</span>
            </div>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 60%;">Item</th>
                    <th style="width: 20%; text-align: center;">Qty</th>
                    <th style="width: 20%; text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                ${details.map(item => `
                    <tr>
                        <td>
                            <div class="item-name">${item.nama_produk}</div>
                            <div class="item-details">
                                @ Rp ${formatNumber(item.harga_satuan)}
                            </div>
                        </td>
                        <td style="text-align: center;">${item.jumlah}</td>
                        <td style="text-align: right;">Rp ${formatNumber(item.subtotal)}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp ${formatNumber(sale.total_harga)}</span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>Rp ${formatNumber(sale.total_harga)}</span>
            </div>
            <div class="total-row" style="margin-top: 10px;">
                <span>Bayar:</span>
                <span>Rp ${formatNumber(sale.bayar)}</span>
            </div>
            <div class="total-row">
                <span>Kembalian:</span>
                <span>Rp ${formatNumber(sale.kembalian)}</span>
            </div>
        </div>
        
        <div class="footer">
            <p>*** TERIMA KASIH ***</p>
            <p>Barang yang sudah dibeli</p>
            <p>tidak dapat ditukar/dikembalikan</p>
            <p style="margin-top: 10px;">Dicetak: ${now.toLocaleString('id-ID')}</p>
        </div>
    </div>
</body>
</html>
    `;
}

/**
 * Fungsi pembantu untuk mencetak otomatis setelah checkout berhasil.
 * Ini akan menampilkan dialog konfirmasi sebelum mencetak.
 * @param {number} saleId - ID penjualan yang baru saja selesai.
 */
function autoPrintReceipt(saleId) {
    if (confirm('Transaksi berhasil! Cetak nota sekarang?')) {
        printReceipt(saleId);
    }
}

// Pastikan fungsi-fungsi ini dapat diakses secara global
// sehingga dapat dipanggil dari atribut onclick di HTML.
window.printReceipt = printReceipt;
window.autoPrintReceipt = autoPrintReceipt;