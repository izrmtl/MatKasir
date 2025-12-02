// ==========================================
// MANAJEMEN PELANGGAN - CUSTOMER MANAGEMENT
// ==========================================

let customersData = [];

// ==========================================
// LOAD CUSTOMERS FOR MANAGEMENT
// ==========================================
async function loadCustomersForManagement() {
    const tbody = document.getElementById('customerTableBody');
    if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
    
    try {
        const response = await fetch(`${API_URL}?action=get_customers`);
        
        // Cek jika response tidak ok (misal 404, 500)
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.status === 200) {
            customersData = result.data;
            renderCustomerTable(customersData);
        } else {
            throw new Error(result.message || 'Gagal memuat pelanggan');
        }
    } catch (error) {
        console.error('Error loading customers:', error);
        showNotification('Gagal memuat data pelanggan', 'error');
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center">Gagal memuat data</td></tr>';
    }
}

// ==========================================
// RENDER CUSTOMER TABLE
// ==========================================
function renderCustomerTable(customers) {
    const tbody = document.getElementById('customerTableBody');
    if (!tbody) return;
    
    if (!customers || customers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Tidak ada data pelanggan</td></tr>';
        return;
    }
    
    tbody.innerHTML = customers.map(customer => `
        <tr>
            <td>${customer.nama_pelanggan}</td>
            <td>${customer.no_telepon || '-'}</td>
            <td>${customer.alamat || '-'}</td>
            <td>${formatDate(customer.created_at)}</td>
            <td class="actions">
                <button class="btn btn-sm btn-warning" onclick="editCustomer(${customer.id_pelanggan})">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteCustomer(${customer.id_pelanggan})" ${customer.id_pelanggan === 1 ? 'disabled' : ''}>Hapus</button>
            </td>
        </tr>
    `).join('');
}

// ==========================================
// ADD CUSTOMER
// ==========================================
async function handleAddCustomer() {
    const formData = {
        nama_pelanggan: document.getElementById('namaPelanggan')?.value.trim(),
        no_telepon: document.getElementById('noTelepon')?.value.trim(),
        alamat: document.getElementById('alamatPelanggan')?.value.trim()
    };
    
    // Validasi
    if (!formData.nama_pelanggan) {
        showNotification('Nama pelanggan harus diisi!', 'warning');
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}?action=add_customer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.status === 200) {
            showNotification('Pelanggan berhasil ditambahkan!', 'success');
            clearCustomerForm();
            await loadCustomersForManagement();
            // Refresh customers for kasir page
            await loadCustomers(); 
        } else {
            showNotification('Gagal menambahkan pelanggan: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Error adding customer:', error);
        showNotification('Error: ' + error.message, 'error');
    }
}

// ==========================================
// EDIT CUSTOMER
// ==========================================
async function editCustomer(id) {
    const customer = customersData.find(c => c.id_pelanggan == id);
    if (!customer) {
        showNotification('Pelanggan tidak ditemukan', 'error');
        return;
    }
    
    // Isi form dengan data pelanggan
    document.getElementById('namaPelanggan').value = customer.nama_pelanggan;
    document.getElementById('noTelepon').value = customer.no_telepon || '';
    document.getElementById('alamatPelanggan').value = customer.alamat || '';
    
    // Ubah tombol ke mode update
    const btnAdd = document.getElementById('btnAddCustomer');
    if(btnAdd) {
        btnAdd.textContent = 'Update Pelanggan';
        btnAdd.onclick = () => handleUpdateCustomer(id);
    }
    
    // Scroll ke form
    document.querySelector('.form-container')?.scrollIntoView({ behavior: 'smooth' });
}

async function handleUpdateCustomer(id) {
    const formData = {
        id_pelanggan: id,
        nama_pelanggan: document.getElementById('namaPelanggan')?.value.trim(),
        no_telepon: document.getElementById('noTelepon')?.value.trim(),
        alamat: document.getElementById('alamatPelanggan')?.value.trim()
    };
    
    // Validasi
    if (!formData.nama_pelanggan) {
        showNotification('Nama pelanggan harus diisi!', 'warning');
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}?action=update_customer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.status === 200) {
            showNotification('Pelanggan berhasil diupdate!', 'success');
            clearCustomerForm();
            resetAddCustomerButton();
            await loadCustomersForManagement();
            await loadCustomers();
        } else {
            showNotification('Gagal mengupdate pelanggan: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Error updating customer:', error);
        showNotification('Error: ' + error.message, 'error');
    }
}

// ==========================================
// DELETE CUSTOMER
// ==========================================
async function deleteCustomer(id) {
    // Cegah menghapus pelanggan default "Umum"
    if (id === 1) {
        showNotification('Pelanggan "Umum" tidak dapat dihapus!', 'warning');
        return;
    }
    
    const customer = customersData.find(c => c.id_pelanggan == id);
    if (!customer) {
        showNotification('Pelanggan tidak ditemukan', 'error');
        return;
    }
    
    if (!confirm(`Yakin ingin menghapus pelanggan "${customer.nama_pelanggan}"?`)) {
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}?action=delete_customer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_pelanggan: id })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.status === 200) {
            showNotification('Pelanggan berhasil dihapus!', 'success');
            await loadCustomersForManagement();
            await loadCustomers();
        } else {
            showNotification('Gagal menghapus pelanggan: ' + (result.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Error deleting customer:', error);
        showNotification('Error: ' + error.message, 'error');
    }
}

// ==========================================
// SEARCH CUSTOMER TABLE
// ==========================================
function handleCustomerTableSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const filtered = customersData.filter(c => 
        c.nama_pelanggan.toLowerCase().includes(searchTerm) ||
        (c.no_telepon && c.no_telepon.toLowerCase().includes(searchTerm)) ||
        (c.alamat && c.alamat.toLowerCase().includes(searchTerm))
    );
    renderCustomerTable(filtered);
}

// ==========================================
// UTILITY FUNCTIONS
// ==========================================
function clearCustomerForm() {
    document.getElementById('namaPelanggan').value = '';
    document.getElementById('noTelepon').value = '';
    document.getElementById('alamatPelanggan').value = '';
}

function resetAddCustomerButton() {
    const btnAdd = document.getElementById('btnAddCustomer');
    if(btnAdd) {
        btnAdd.textContent = 'Tambah Pelanggan';
        btnAdd.onclick = handleAddCustomer;
    }
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric' // <-- PERBAIKAN: Menghapus typo
    });
}

// Make functions globally accessible
window.loadCustomersForManagement = loadCustomersForManagement;
window.editCustomer = editCustomer;
window.deleteCustomer = deleteCustomer;
window.handleAddCustomer = handleAddCustomer;
window.handleCustomerTableSearch = handleCustomerTableSearch;