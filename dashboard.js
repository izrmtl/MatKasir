// ==========================================
// DASHBOARD FUNCTIONS
// ==========================================

let dashboardData = {
  sales: [],
  products: [],
  customers: [],
};

// ==========================================
// LOAD DASHBOARD DATA
// ==========================================
async function loadDashboardData() {
  try {
    // Show loading state on cards
    document.getElementById("revenueToday").textContent = "Loading...";
    document.getElementById("transactionCount").textContent = "Loading...";
    document.getElementById("productCount").textContent = "Loading...";
    document.getElementById("customerCount").textContent = "Loading...";

    await Promise.all([
      loadDashboardStats(),
      loadRecentTransactions(),
      loadSalesChart(),
    ]);
  } catch (error) {
    console.error("Error loading dashboard:", error);
    showNotification("Gagal memuat data dashboard", "error");
  }
}

// ==========================================
// LOAD STATS
// ==========================================
async function loadDashboardStats() {
  try {
    const response = await fetch(`${API_URL}?action=get_dashboard_stats`);
    const result = await response.json();

    if (result.status === 200) {
      const stats = result.data;
      document.getElementById("revenueToday").textContent =
        "Rp " + formatNumber(stats.revenue_today);
      document.getElementById("transactionCount").textContent =
        stats.transactions_today;
      document.getElementById("productCount").textContent =
        stats.total_products;
      document.getElementById("customerCount").textContent =
        stats.total_customers;
    } else {
      throw new Error(result.message || "Gagal memuat statistik");
    }
  } catch (error) {
    console.error("Error loading stats:", error);
    // Set to 0 on error
    document.getElementById("revenueToday").textContent = "Rp 0";
    document.getElementById("transactionCount").textContent = "0";
    document.getElementById("productCount").textContent = "0";
    document.getElementById("customerCount").textContent = "0";
  }
}

// ==========================================
// LOAD RECENT TRANSACTIONS
// ==========================================
async function loadRecentTransactions() {
  try {
    const response = await fetch(`${API_URL}?action=get_sales`);
    const result = await response.json();

    if (result.status === 200) {
      const today = new Date().toISOString().split("T")[0];
      const todayTransactions = result.data
        .filter((sale) => {
          const saleDate = new Date(sale.tanggal_penjualan)
            .toISOString()
            .split("T")[0];
          return saleDate === today;
        })
        .slice(0, 5); // Get last 5 transactions

      renderRecentTransactions(todayTransactions);
    } else {
      throw new Error(result.message || "Gagal memuat transaksi");
    }
  } catch (error) {
    console.error("Error loading recent transactions:", error);
    renderRecentTransactions([]); // Render empty state
  }
}

function renderRecentTransactions(transactions) {
  const container = document.getElementById("recentTransactionsList");
  if (!container) return;

  if (!transactions || transactions.length === 0) {
    container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-graph-up"></i></div>
                <p class="empty-text">Belum ada transaksi hari ini</p>
            </div>
        `;
    return;
  }

  container.innerHTML = transactions
    .map(
      (sale) => `
        <div class="transaction-item">
            <div class="transaction-icon">
                <i class="bi bi-receipt"></i>
            </div>
            <div class="transaction-details">
                <div class="transaction-header">
                    <span class="transaction-id">#${sale.id_penjualan}</span>
                    <span class="transaction-time">${formatTime(
                      sale.tanggal_penjualan
                    )}</span>
                </div>
                <div class="transaction-customer">${
                  sale.nama_pelanggan || "Pelanggan Umum"
                }</div>
                <div class="transaction-amount">Rp ${formatNumber(
                  sale.total_harga
                )}</div>
            </div>
            <button class="transaction-action" onclick="printReceipt(${
              sale.id_penjualan
            })" title="Cetak Nota">
                <i class="bi bi-printer"></i>
            </button>
        </div>
    `
    )
    .join("");
}

// ==========================================
// LOAD SALES CHART
// ==========================================
async function loadSalesChart() {
  try {
    const response = await fetch(`${API_URL}?action=get_sales`);
    const result = await response.json();

    if (result.status === 200) {
      const salesData = result.data;

      // Get last 7 days
      const last7Days = [];
      for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        last7Days.push(date.toISOString().split("T")[0]);
      }

      // Calculate daily sales
      const dailySales = last7Days.map((date) => {
        const daySales = salesData.filter((sale) => {
          const saleDate = new Date(sale.tanggal_penjualan)
            .toISOString()
            .split("T")[0];
          return saleDate === date && sale.status === "selesai";
        });

        return daySales.reduce(
          (sum, sale) => sum + parseFloat(sale.total_harga),
          0
        );
      });

      renderChart(last7Days, dailySales);
    }
  } catch (error) {
    console.error("Error loading chart:", error);
  }
}

function renderChart(labels, data) {
  const canvas = document.getElementById("salesChart");
  if (!canvas) return;
  const ctx = canvas.getContext("2d");

  // Set canvas size
  const container = canvas.parentElement;
  canvas.width = container.offsetWidth;
  canvas.height = 300;

  const maxValue = Math.max(...data, 1);
  const chartHeight = canvas.height - 60;
  const chartWidth = canvas.width - 60;
  const barWidth = chartWidth / labels.length;
  const padding = 40;

  // Clear canvas
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  // Draw grid lines
  ctx.strokeStyle = "#e0e0e0";
  ctx.lineWidth = 1;
  for (let i = 0; i <= 5; i++) {
    const y = padding + (chartHeight / 5) * i;
    ctx.beginPath();
    ctx.moveTo(padding, y);
    ctx.lineTo(canvas.width - 20, y);
    ctx.stroke();
  }

  // Draw bars with gradient
  data.forEach((value, index) => {
    const barHeight = (value / maxValue) * chartHeight;
    const x = padding + index * barWidth + barWidth * 0.1;
    const y = padding + chartHeight - barHeight;
    const width = barWidth * 0.8;

    // Create gradient
    const gradient = ctx.createLinearGradient(x, y, x, y + barHeight);
    gradient.addColorStop(0, "#667eea");
    gradient.addColorStop(1, "#764ba2");

    // Draw bar with rounded top
    ctx.fillStyle = gradient;
    ctx.beginPath();
    ctx.roundRect(x, y, width, barHeight, [8, 8, 0, 0]);
    ctx.fill();

    // Draw value on top of bar if there's space
    if (value > 0) {
      ctx.fillStyle = "#333";
      ctx.font = "bold 11px Arial";
      ctx.textAlign = "center";
      const valueText =
        "Rp " + (value >= 1000 ? (value / 1000).toFixed(0) + "k" : value);
      ctx.fillText(valueText, x + width / 2, y - 5);
    }

    // Draw date label
    ctx.fillStyle = "#666";
    ctx.font = "11px Arial";
    ctx.textAlign = "center";
    const date = new Date(labels[index]);
    const dateLabel = date.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "short",
    });
    ctx.fillText(dateLabel, x + width / 2, padding + chartHeight + 20);
  });

  // Draw Y-axis labels
  ctx.fillStyle = "#666";
  ctx.font = "10px Arial";
  ctx.textAlign = "right";
  for (let i = 0; i <= 5; i++) {
    const value = maxValue - (maxValue / 5) * i;
    const y = padding + (chartHeight / 5) * i;
    ctx.fillText("Rp " + formatNumber(Math.round(value)), padding - 5, y + 4);
  }
}

// ==========================================
// SALES HISTORY
// ==========================================
// ==========================================
// SALES HISTORY
// ==========================================
async function loadSalesHistory() {
    try {
        const response = await fetch(`${API_URL}?action=get_sales`);
        const result = await response.json();
        
        if (result.status === 200) {
            renderSalesHistory(result.data);
        } else {
            throw new Error(result.message || 'Gagal memuat riwayat');
        }
    } catch (error) {
        console.error('Error loading sales history:', error);
        const tbody = document.getElementById('salesHistoryBody');
        if(tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center">Gagal memuat data</td></tr>';
    }
}

function renderSalesHistory(sales) {
    const tbody = document.getElementById('salesHistoryBody');
    if (!tbody) return;
    
    if (!sales || sales.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Belum ada transaksi</td></tr>';
        return;
    }
    
    tbody.innerHTML = sales.map(sale => `
        <tr>
            <td>#${sale.id_penjualan}</td>
            <td>${formatDateTime(sale.tanggal_penjualan)}</td>
            <td>${sale.nama_pelanggan || 'Pelanggan Umum'}</td>
            <td>Rp ${formatNumber(sale.total_harga)}</td>
            <td>
                <!-- PERBAIKAN: Panggil fungsi showViewSalePage alih-alih mengubah URL -->
                <button class="btn btn-sm btn-primary" onclick="showViewSalePage(${sale.id_penjualan})">
                    <i class="bi bi-eye"></i> Detail
                </button>
            </td>
        </tr>
    `).join('');
}

// Fungsi baru untuk menyiapkan dan menampilkan halaman detail
function showViewSalePage(saleId) {
    // Simpan ID yang akan dilihat ke variabel global
    window.saleIdToView = saleId;
    // Tampilkan halaman detail
    showPage('detail-penjualan');
}

function filterByDate() {
    const filterDate = document.getElementById('filterDate')?.value;
    if (!filterDate) {
        loadSalesHistory(); // Load all if no date
        return;
    }
    
    // Here you would typically send filterDate to the API
    // For now, we'll just reload all data
    loadSalesHistory();
}

// ==========================================
// UTILITY FUNCTIONS
// ==========================================
function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Make functions globally accessible
window.loadDashboardData = loadDashboardData;
window.loadSalesHistory = loadSalesHistory;
window.filterByDate = filterByDate;
window.showViewSalePage = showViewSalePage; // Penting: buat fungsi ini global