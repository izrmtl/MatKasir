// ==========================================
// APLIKASI KASIR UMKM - MAIN APP
// ==========================================

// API Configuration
const API_URL = 'api.php';

// Global State
const AppState = {
    products: [],
    customers: [],
    cart: [],
    currentPage: 'dashboard'
};

// ==========================================
// INITIALIZATION
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

function initializeApp() {
    setupNavigation();
    // Load initial data first, then show the page
    loadInitialData().then(() => {
        showPage('dashboard');
    });
    setupEventListeners();
}

// ==========================================
// NAVIGATION
// ==========================================
function setupNavigation() {
    const navLinks = document.querySelectorAll('.sidebar-menu-item');
    
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = link.getAttribute('data-page');
            showPage(page);
        });
    });
}

function showPage(pageName) {
    // Hide all pages
    document.querySelectorAll('.page').forEach(page => {
        page.classList.remove('active');
    });
    
    // Remove active from nav links
    document.querySelectorAll('.sidebar-menu-item').forEach(link => {
        link.classList.remove('active');
    });
    
    // Show selected page
    const selectedPage = document.getElementById(`${pageName}Page`);
    if (selectedPage) {
        selectedPage.classList.add('active');
    }
    
    // Add active to nav link
    const selectedLink = document.querySelector(`[data-page="${pageName}"]`);
    if (selectedLink) {
        selectedLink.classList.add('active');
    }
    
    AppState.currentPage = pageName;
    
    // Load data for specific pages
    if (pageName === 'dashboard') {
        loadDashboardData();
    } else if (pageName === 'kasir') {
        renderProducts(AppState.products);
        renderCustomerSelect(AppState.customers);
    } else if (pageName === 'stok') {
        loadProductsForManagement();
    } else if (pageName === 'pelanggan') {
        loadCustomersForManagement();
    } else if (pageName === 'riwayat') {
        loadSalesHistory();
    } else if (pageName === 'detail-penjualan') {
        // PERBAIKAN: Ambil ID dari variabel global, bukan URL
        const saleId = window.saleIdToView;
        if (saleId) {
            loadSaleDetails(saleId);
        } else {
            const contentContainer = document.getElementById('saleDetailContent');
            if(contentContainer) {
                contentContainer.innerHTML = '<p class="text-center">ID Penjualan tidak ditemukan.</p>';
            }
        }
    }
}

// ==========================================
// DATA LOADING
// ==========================================
async function loadInitialData() {
    await Promise.all([
        loadProducts(),
        loadCustomers()
    ]);
}

async function loadProducts() {
    try {
        const response = await fetch(`${API_URL}?action=get_products`);
        const result = await response.json();
        
        if (result.status === 200) {
            AppState.products = result.data;
        } else {
            console.error('Failed to load products:', result.message);
            AppState.products = [];
        }
    } catch (error) {
        console.error('Error loading products:', error);
        AppState.products = [];
    }
}

async function loadCustomers() {
    try {
        const response = await fetch(`${API_URL}?action=get_customers`);
        const result = await response.json();
        
        if (result.status === 200) {
            AppState.customers = result.data;
        } else {
            console.error('Failed to load customers:', result.message);
            AppState.customers = [];
        }
    } catch (error) {
        console.error('Error loading customers:', error);
        AppState.customers = [];
    }
}

// ==========================================
// EVENT LISTENERS
// ==========================================
function setupEventListeners() {
    // Kasir Page
    const searchProduct = document.getElementById('searchProduct');
    if (searchProduct) {
        searchProduct.addEventListener('input', handleProductSearch);
    }
    
    const customerSelect = document.getElementById('customerSelect');
    if (customerSelect) {
        customerSelect.addEventListener('change', updateSummary);
    }
    
    const paymentAmount = document.getElementById('paymentAmount');
    if (paymentAmount) {
        paymentAmount.addEventListener('input', updateChangeDisplay);
    }
    
    const btnCheckout = document.getElementById('btnCheckout');
    if (btnCheckout) {
        btnCheckout.addEventListener('click', handleCheckout);
    }

    const clearCartBtn = document.getElementById('clearCart');
    if(clearCartBtn) {
        clearCartBtn.addEventListener('click', () => {
            if(confirm('Kosongkan keranjang?')) {
                AppState.cart = [];
                renderCart();
                updateSummary();
            }
        });
    }
}

// ==========================================
// KASIR FUNCTIONS
// ==========================================
function renderProducts(products) {
    const grid = document.getElementById('productsGrid');
    
    if (!grid) return;

    if (!products || products.length === 0) {
        grid.innerHTML = '<p style="text-align: center; color: #999; padding: 20px; grid-column: 1/-1;">Tidak ada produk tersedia</p>';
        return;
    }
    
    grid.innerHTML = products.map(product => `
        <div class="product-card" onclick="addToCart(${product.id_produk})">
            <div class="product-image"><i class="bi bi-box"></i></div>
            <div class="product-name">${product.nama_produk}</div>
            <div class="product-price">Rp ${formatNumber(product.harga)}</div>
            <div class="product-stock">Stok: ${product.stok}</div>
        </div>
    `).join('');
}

function renderCustomerSelect(customers) {
    const select = document.getElementById('customerSelect');
    if (!select) return;
    
    let optionsHtml = customers.map(customer => `
        <option value="${customer.id_pelanggan}">${customer.nama_pelanggan}</option>
    `).join('');

    if (!customers.some(c => c.id_pelanggan == 1)) {
        optionsHtml = `<option value="1">Pelanggan Umum</option>` + optionsHtml;
    }

    select.innerHTML = optionsHtml;
    if (Array.from(select.options).some(o => o.value == '1')) {
        select.value = '1';
    }
}

function addToCart(productId) {
    const product = AppState.products.find(p => p.id_produk == productId);
    
    if (!product || product.stok <= 0) {
        showNotification('Produk tidak tersedia atau stok habis!', 'warning');
        return;
    }
    
    const existingItem = AppState.cart.find(item => item.id_produk == productId);
    
    if (existingItem) {
        if (existingItem.jumlah >= product.stok) {
            showNotification('Stok tidak mencukupi!', 'warning');
            return;
        }
        existingItem.jumlah++;
    } else {
        AppState.cart.push({
            id_produk: product.id_produk,
            nama_produk: product.nama_produk,
            harga_satuan: parseFloat(product.harga),
            jumlah: 1,
            stok: parseInt(product.stok)
        });
    }
    
    renderCart();
    updateSummary();
}

function renderCart() {
    const cartContainer = document.getElementById('cartItems');
    if (!cartContainer) return;
    
    if (AppState.cart.length === 0) {
        cartContainer.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-cart2"></i></div>
                <div class="empty-text">Keranjang Kosong</div>
            </div>
        `;
        return;
    }
    
    cartContainer.innerHTML = AppState.cart.map((item, index) => `
        <div class="cart-item">
            <div class="cart-item-info">
                <div class="cart-item-name">${item.nama_produk}</div>
                <div class="cart-item-price">Rp ${formatNumber(item.harga_satuan)} x ${item.jumlah}</div>
            </div>
            <div class="cart-item-quantity">
                <button class="quantity-btn" onclick="decreaseQuantity(${index})">−</button>
                <span class="quantity-value">${item.jumlah}</span>
                <button class="quantity-btn" onclick="increaseQuantity(${index})">+</button>
            </div>
            <button class="cart-item-remove" onclick="removeFromCart(${index})">×</button>
        </div>
    `).join('');
}

function removeFromCart(index) {
    AppState.cart.splice(index, 1);
    renderCart();
    updateSummary();
}

function increaseQuantity(index) {
    if (AppState.cart[index].jumlah >= AppState.cart[index].stok) {
        showNotification('Stok tidak mencukupi!', 'warning');
        return;
    }
    AppState.cart[index].jumlah++;
    renderCart();
    updateSummary();
}

function decreaseQuantity(index) {
    if (AppState.cart[index].jumlah > 1) {
        AppState.cart[index].jumlah--;
    } else {
        removeFromCart(index);
        return;
    }
    renderCart();
    updateSummary();
}

function updateSummary() {
    const totalItems = AppState.cart.reduce((sum, item) => sum + item.jumlah, 0);
    const totalPrice = AppState.cart.reduce((sum, item) => sum + (item.harga_satuan * item.jumlah), 0);
    
    const totalItemsEl = document.getElementById('totalItems');
    const totalPriceEl = document.getElementById('totalPrice');
    const btnCheckout = document.getElementById('btnCheckout');
    
    if(totalItemsEl) totalItemsEl.textContent = totalItems;
    if(totalPriceEl) totalPriceEl.textContent = 'Rp ' + formatNumber(totalPrice);
    
    if (btnCheckout) {
        const customerId = document.getElementById('customerSelect')?.value;
        btnCheckout.disabled = AppState.cart.length === 0 || !customerId;
    }
    
    updateChangeDisplay();
}

function updateChangeDisplay() {
    const paymentInput = document.getElementById('paymentAmount');
    const changeDisplay = document.getElementById('changeDisplay');
    const changeAmount = document.getElementById('changeAmount');
    
    if (!paymentInput || !changeDisplay || !changeAmount) return;

    const payment = parseFloat(paymentInput.value) || 0;
    const total = AppState.cart.reduce((sum, item) => sum + (item.harga_satuan * item.jumlah), 0);
    
    if (payment > 0 && payment >= total) {
        const change = payment - total;
        changeAmount.textContent = 'Rp ' + formatNumber(change);
        changeDisplay.style.display = 'block';
    } else {
        changeDisplay.style.display = 'none';
    }
}

async function handleCheckout() {
    const customerId = document.getElementById('customerSelect')?.value;
    const payment = parseFloat(document.getElementById('paymentAmount')?.value) || 0;
    const total = AppState.cart.reduce((sum, item) => sum + (item.harga_satuan * item.jumlah), 0);
    
    if (!customerId) {
        showNotification('Pilih pelanggan terlebih dahulu!', 'warning');
        return;
    }
    
    if (AppState.cart.length === 0) {
        showNotification('Keranjang masih kosong!', 'warning');
        return;
    }
    
    if (payment < total) {
        showNotification('Jumlah bayar kurang dari total!', 'warning');
        return;
    }
    
    const transactionData = {
        id_pelanggan: customerId,
        items: AppState.cart,
        total_harga: total,
        bayar: payment
    };
    
    try {
        const response = await fetch(`${API_URL}?action=checkout`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(transactionData)
        });
        
        const result = await response.json();
        
        if (result.status === 200) {
            showNotification('Transaksi berhasil! Kembalian: Rp ' + formatNumber(result.data.kembalian), 'success');
            
            // Auto print receipt
            setTimeout(() => {
                if (confirm('Cetak nota sekarang?')) {
                    printReceipt(result.data.id_penjualan);
                }
            }, 500);
            
            AppState.cart = [];
            const customerSelect = document.getElementById('customerSelect');
            if(customerSelect) customerSelect.value = '1';
            const paymentInput = document.getElementById('paymentAmount');
            if(paymentInput) paymentInput.value = '';
            
            renderCart();
            updateSummary();
            await loadProducts(); // Refresh stock
        } else {
            showNotification('Transaksi gagal: ' + result.message, 'error');
        }
    } catch (error) {
        showNotification('Error: ' + error.message, 'error');
    }
}

function handleProductSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const filtered = AppState.products.filter(p => 
        p.nama_produk.toLowerCase().includes(searchTerm)
    );
    renderProducts(filtered);
}

// ==========================================
// UTILITY FUNCTIONS
// ==========================================
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

function formatNumber(num) {
    return parseFloat(num).toLocaleString('id-ID');
}

// Make functions globally accessible
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.increaseQuantity = increaseQuantity;
window.decreaseQuantity = decreaseQuantity;
window.filterByDate = filterByDate; // from dashboard.js
window.printReceipt = printReceipt; // from receipt.js