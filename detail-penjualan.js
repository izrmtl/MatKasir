// ==========================================
// DETAIL PENJUALAN PAGE
// ==========================================

let currentSaleId = null;

// ==========================================
// LOAD SALE DETAILS
// ==========================================
// ==========================================
// LOAD SALE DETAILS
// ==========================================
async function loadSaleDetails(saleId) {
    if (!saleId) {
        document.getElementById('saleDetailContent').innerHTML = '<p class="text-center">ID Penjualan tidak valid.</p>';
        return;
    }

    currentSaleId = saleId;
    const contentContainer = document.getElementById('saleDetailContent');
    contentContainer.innerHTML = `
        <div class="loading-state">
            <div class="loading-spinner"></div>
            <p>Memuat data...</p>
        </div>
    `;

    console.log(`[DetailPenjualan] Memulai proses untuk Sale ID: ${saleId}`);

    try {
        // Langkah 1: Ambil data penjualan utama dengan endpoint baru yang lebih efisien
        console.log(`[DetailPenjualan] Mengambil data penjualan utama dari API...`);
        const saleResponse = await fetch(`${API_URL}?action=get_sale&id=${saleId}`);
        
        // Periksa jika respons dari server tidak berhasil (misalnya error 404, 500)
        if (!saleResponse.ok) {
            throw new Error(`HTTP error! status: ${saleResponse.status} ${saleResponse.statusText}`);
        }

        const saleResult = await saleResponse.json();
        console.log('[DetailPenjualan] Data penjualan utama diterima:', saleResult);
        
        if (saleResult.status !== 200) {
            throw new Error(saleResult.message || 'Gagal memuat data penjualan dari server');
        }
        
        const sale = saleResult.data;
        if (!sale) {
            throw new Error('Data penjualan tidak ditemukan di respons server');
        }
        
        // Langkah 2: Ambil detail item dari penjualan tersebut
        console.log(`[DetailPenjualan] Mengambil data detail item...`);
        const detailResponse = await fetch(`${API_URL}?action=get_sale_detail&id=${saleId}`);
        
        if (!detailResponse.ok) {
            throw new Error(`HTTP error! status: ${detailResponse.status} ${detailResponse.statusText}`);
        }

        const detailResult = await detailResponse.json();
        console.log('[DetailPenjualan] Data detail item diterima:', detailResult);
        
        if (detailResult.status !== 200) {
            throw new Error(detailResult.message || 'Gagal memuat detail item dari server');
        }
        
        const details = detailResult.data;
        
        // Langkah 3: Render data ke halaman
        console.log('[DetailPenjualan] Semua data berhasil diambil. Merender ke halaman...');
        renderSaleDetail(sale, details);

    } catch (error) {
        console.error('[DetailPenjualan] TERJADI ERROR:', error);
        showNotification('Gagal memuat detail: ' + error.message, 'error');
        contentContainer.innerHTML = `<p class="text-center text-danger">Gagal memuat detail: ${error.message}</p>`;
    }
}

// ==========================================
// RENDER SALE DETAIL
// ==========================================
function renderSaleDetail(sale, details) {
    const contentContainer = document.getElementById('saleDetailContent');
    const saleDate = new Date(sale.tanggal_penjualan);

    contentContainer.innerHTML = `
        <div class="sale-info-grid">
            <div class="info-card">
                <h4>Informasi Transaksi</h4>
                <p><strong>No. Nota:</strong> #${sale.id_penjualan}</p>
                <p><strong>Tanggal:</strong> ${saleDate.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                <p><strong>Waktu:</strong> ${saleDate.toLocaleTimeString('id-ID')}</p>
                <p><strong>Status:</strong> <span class="badge badge-success">${sale.status}</span></p>
            </div>
            <div class="info-card">
                <h4>Informasi Pelanggan</h4>
                <p><strong>Nama:</strong> ${sale.nama_pelanggan || 'Pelanggan Umum'}</p>
            </div>
        </div>

        <h4 style="margin-top: 25px; margin-bottom: 15px;">Detail Produk</h4>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ${details.map(item => `
                        <tr>
                            <td>${item.nama_produk}</td>
                            <td>Rp ${formatNumber(item.harga_satuan)}</td>
                            <td>${item.jumlah}</td>
                            <td>Rp ${formatNumber(item.subtotal)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        
        <div class="summary-section" style="margin-top: 25px;">
            <div class="summary-card">
                <h4>Ringkasan Pembayaran</h4>
                <div class="summary-row">
                    <span>Total Harga:</span>
                    <span>Rp ${formatNumber(sale.total_harga)}</span>
                </div>
                <div class="summary-row">
                    <span>Jumlah Bayar:</span>
                    <span>Rp ${formatNumber(sale.bayar)}</span>
                </div>
                <div class="summary-row grand-total">
                    <span>Kembalian:</span>
                    <span>Rp ${formatNumber(sale.kembalian)}</span>
                </div>
            </div>
        </div>

        <div class="action-buttons" style="margin-top: 25px; text-align: right;">
            <button class="btn btn-primary" onclick="printReceipt(${sale.id_penjualan})">
                <i class="bi bi-printer"></i> Cetak Nota
            </button>
            <button class="btn btn-danger" onclick="deleteSale(${sale.id_penjualan})">
                <i class="bi bi-trash"></i> Hapus Transaksi
            </button>
        </div>
    `;
}

// ==========================================
// DELETE SALE
// ==========================================
async function deleteSale(id) {
    if (!confirm('PERHATIAN: Apakah Anda yakin ingin menghapus transaksi ini? Stok produk yang telah terjual akan dikembalikan.')) {
        return;
    }

    try {
        const response = await fetch(`${API_URL}?action=delete_sale`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_penjualan: id })
        });

        const result = await response.json();

        if (result.status === 200) {
            showNotification('Transaksi berhasil dihapus.', 'success');
            // Redirect back to history page
            showPage('riwayat');
        } else {
            showNotification('Gagal menghapus: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error deleting sale:', error);
        showNotification('Terjadi kesalahan saat menghapus data.', 'error');
    }
}

// Make functions globally accessible
window.loadSaleDetails = loadSaleDetails;
window.deleteSale = deleteSale;