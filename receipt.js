// ==========================================
// RECEIPT / NOTA SYSTEM
// ==========================================

/**
 * Mencetak nota/struk penjualan berdasarkan ID penjualan.
 * Fungsi ini mengambil data penjualan dan detailnya, lalu membuka jendela baru
 * untuk mencetak HTML yang telah diformat untuk printer thermal.
 * @param {number} saleId - ID penjualan yang akan dicetak
 */
async function printReceipt(saleId) {
    try {
        // Langkah 1: Ambil data penjualan utama
        const saleResponse = await fetch(`${API_URL}?action=get_sale&id=${saleId}`);
        
        if (!saleResponse.ok) {
            throw new Error(`Gagal mengambil data penjualan: ${saleResponse.statusText} (${saleResponse.status})`);
        }

        const saleResult = await saleResponse.json();
        
        if (saleResult.status !== 200) {
            throw new Error(saleResult.message || 'Gagal memuat data penjualan dari server');
        }
        
        const sale = saleResult.data;
        if (!sale) {
            throw new Error('Data penjualan tidak ditemukan di respons server');
        }
        
        // Langkah 2: Ambil detail item dari penjualan tersebut
        const detailResponse = await fetch(`${API_URL}?action=get_sale_detail&id=${saleId}`);
        
        if (!detailResponse.ok) {
            throw new Error(`Gagal mengambil detail penjualan: ${detailResponse.statusText}`);
        }

        const detailResult = await detailResponse.json();
        
        if (detailResult.status !== 200) {
            throw new Error(detailResult.message || 'Gagal memuat detail item dari server');
        }
        
        const details = detailResult.data;
        
        // Langkah 3: Buat HTML untuk nota thermal
        const receiptHTML = generateThermalReceiptHTML(sale, details);
        
        // Langkah 4: Buka jendela cetak dan tulis HTML
        const printWindow = window.open('', '_blank', 'width=400,height=600');
        printWindow.document.write(receiptHTML);
        printWindow.document.close();
        
        // Tunggu sebentar agar konten termuat sebelum memunculkan dialog cetak
        setTimeout(() => {
            printWindow.print();
            printWindow.onafterprint = () => printWindow.close();
        }, 500);
        
    } catch (error) {
        console.error('Error printing receipt:', error);
        showNotification('Terjadi kesalahan saat mencetak: ' + error.message, 'error');
    }
}

/**
 * Menghasilkan string HTML untuk nota/struk yang dioptimalkan untuk printer thermal.
 * @param {object} sale - Objek data penjualan.
 * @param {Array} details - Array detail item yang dibeli.
 * @returns {string} String HTML yang lengkap untuk nota.
 */
function generateThermalReceiptHTML(sale, details) {
    const now = new Date();
    const saleDate = new Date(sale.tanggal_penjualan);
    
    return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota #${sale.id_penjualan}</title>
    <style>
        /* Gaya untuk tampilan di layar (preview) */
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.3;
            width: 350px; /* Lebar preview di layar */
            margin: 0 auto;
            padding: 15px;
            background-color: #f5f5f5;
            color: #333;
        }

        /* Gaya khusus untuk media cetak (printer thermal) */
        @media print {
            @page {
                size: 80mm auto; /* Lebar 80mm, tinggi otomatis */
                margin: 0mm 5mm; /* Margin kecil di kiri/kanan */
            }

            body {
                width: auto; /* Biarkan mengikuti lebar kertas */
                margin: 0;
                padding: 5px; /* Padding minimal */
                font-size: 10pt; /* Ukuran font lebih kecil untuk cetakan */
                -webkit-print-color-adjust: exact; /* Pertahankan warna jika perlu */
                color-adjust: exact;
            }
        }

        .receipt-container {
            text-align: center;
        }

        .header {
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0;
            font-weight: bold;
        }

        .header p {
            margin: 3px 0;
            font-size: 11px;
        }

        .separator {
            border-top: 1px dashed #333;
            margin: 10px 0;
        }

        .info-section, .totals-section {
            text-align: left;
            margin-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 11px;
        }

        .items-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        
        .item-name {
            width: 55%;
        }

        .item-qty {
            width: 15%;
            text-align: center;
        }

        .item-price {
            width: 30%;
            text-align: right;
        }

        .totals-section {
            font-size: 11px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .total-row.grand-total {
            font-weight: bold;
            font-size: 12px;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px solid #333;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>TOKO UMKM</h1>
            <p>Jl. Contoh No. 123</p>
            <p>Telp: 021-12345678</p>
        </div>
        
        <div class="separator"></div>
        
        <div class="info-section">
            <div class="info-row">
                <span>No. Nota:</span>
                <span><strong>#${sale.id_penjualan}</strong></span>
            </div>
            <div class="info-row">
                <span>Tanggal:</span>
                <span>${saleDate.toLocaleDateString('id-ID')}</span>
            </div>
            <div class="info-row">
                <span>Kasir:</span>
                <span>${sale.kasir || 'Admin'}</span>
            </div>
        </div>

        <div class="separator"></div>
        
        <table class="items-table">
            <tbody>
                ${details.map(item => `
                    <tr>
                        <td class="item-name">${item.nama_produk}</td>
                        <td class="item-qty">${item.jumlah}x</td>
                        <td class="item-price">${formatNumber(item.subtotal)}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        
        <div class="separator"></div>
        
        <div class="totals-section">
            <div class="total-row">
                <span>Total:</span>
                <span>${formatNumber(sale.total_harga)}</span>
            </div>
            <div class="total-row">
                <span>Bayar:</span>
                <span>${formatNumber(sale.bayar)}</span>
            </div>
            <div class="total-row grand-total">
                <span>Kembalian:</span>
                <span>${formatNumber(sale.kembalian)}</span>
            </div>
        </div>
        
        <div class="separator"></div>
        
        <div class="footer">
            <p>*** TERIMA KASIH ***</p>
            <p>Barang yang sudah dibeli</p>
            <p>tidak dapat ditukar/kembali</p>
        </div>
    </div>
</body>
</html>
    `;
}

/**
 * Fungsi pembantu untuk mencetak otomatis setelah checkout berhasil.
 * @param {number} saleId - ID penjualan yang baru saja selesai.
 */
function autoPrintReceipt(saleId) {
    if (confirm('Transaksi berhasil! Cetak nota sekarang?')) {
        printReceipt(saleId);
    }
}

// Pastikan fungsi-fungsi ini dapat diakses secara global
window.printReceipt = printReceipt;
window.autoPrintReceipt = autoPrintReceipt;