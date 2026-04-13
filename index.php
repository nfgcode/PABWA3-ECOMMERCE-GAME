<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PC & AI Agent E-Commerce Store</title>
  <style>
    :root {
      --primary: #ffffff;
      --secondary: #f9fafb;
      --accent: #03ac0e;
      --accent-alt: #029a0b;
      --success: #2ecc71;
      --danger: #e74c3c;
      --warning: #f39c12;
      --bg: #f3f4f5;
      --paper: #ffffff;
      --border: #e5e7eb;
      --text: #1f2937;
      --muted: #6b7280;
      --shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
      --shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.06);
      --radius: 12px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      color: var(--text);
      background: var(--bg);
      min-height: 100vh;
      overflow-x: hidden;
    }

    .navbar {
      background: #ffffff;
      border-bottom: 1px solid var(--border);
      padding: 16px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: var(--shadow-sm);
      position: sticky;
      top: 0;
      z-index: 100;
      gap: 16px;
    }

    .navbar-brand {
      font-size: 1.3rem;
      font-weight: 800;
      color: var(--accent);
      display: flex;
      align-items: center;
      gap: 8px;
      white-space: nowrap;
    }

    .navbar-center {
      flex: 1;
      max-width: 700px;
      position: relative;
    }

    .navbar-center input {
      width: 100%;
      margin: 0;
      border-radius: 999px;
      padding: 11px 16px;
      background: #f8fafc;
      border: 1px solid #d1d5db;
      color: #111827;
    }

    .navbar-menu {
      display: flex;
      gap: 16px;
      align-items: center;
    }

    .nav-link {
      color: var(--muted);
      text-decoration: none;
      font-weight: 600;
      padding: 8px 14px;
      border-radius: 8px;
      transition: 0.3s;
      cursor: pointer;
      border: none;
      background: none;
      font-size: 0.95rem;
    }

    .nav-link:hover,
    .nav-link.active {
      color: var(--accent);
      background: rgba(3, 172, 14, 0.1);
    }

    .hero {
      background: linear-gradient(180deg, #ffffff 0%, #f4fbf4 100%);
      border-bottom: 1px solid var(--border);
      padding: 34px 20px;
      text-align: center;
      margin-bottom: 24px;
    }

    .hero h1 {
      margin: 0 0 12px;
      font-size: 2.2rem;
      color: var(--accent);
      font-weight: 900;
    }

    .hero p {
      margin: 0;
      font-size: 1.1rem;
      color: var(--muted);
      max-width: 800px;
      margin: 0 auto;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 20px 40px;
    }

    .page {
      display: none;
    }

    .page.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 20px;
    }

    .col-12 { grid-column: span 12; }
    .col-9 { grid-column: span 9; }
    .col-8 { grid-column: span 8; }
    .col-6 { grid-column: span 6; }
    .col-4 { grid-column: span 4; }
    .col-3 { grid-column: span 3; }

    .card {
      background: var(--paper);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px;
      box-shadow: var(--shadow-sm);
      transition: 0.3s;
      animation: slideUp 0.4s ease;
    }

    .card:hover {
      border-color: #bbf7d0;
      box-shadow: 0 10px 24px rgba(3, 172, 14, 0.14);
    }

    .card-title {
      margin: 0 0 16px;
      font-size: 1.2rem;
      color: var(--accent);
      font-weight: 700;
    }

    .product-card {
      position: relative;
      overflow: hidden;
    }

    .product-card::before {
      content: "";
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(3, 172, 14, 0.08) 0%, transparent 70%);
      z-index: -1;
    }

    .product-image {
      width: 100%;
      height: 180px;
      background: linear-gradient(180deg, #f9fafb, #f3f4f6);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--muted);
      font-size: 3rem;
      margin-bottom: 12px;
    }

    .product-name {
      font-size: 1rem;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 6px;
    }

    .product-spec {
      font-size: 0.85rem;
      color: var(--muted);
      margin-bottom: 4px;
    }

    .product-price {
      font-size: 1.3rem;
      font-weight: 900;
      color: var(--accent);
      margin: 12px 0;
    }

    .product-stock {
      font-size: 0.9rem;
      color: var(--success);
      margin-bottom: 12px;
    }

    .product-stock.out-of-stock {
      color: var(--danger);
    }

    .btn {
      border: none;
      border-radius: 8px;
      padding: 10px 16px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
      font-size: 0.95rem;
    }

    .btn:hover {
      transform: translateY(-2px);
    }

    .btn-primary {
      background: var(--accent);
      color: #ffffff;
    }

    .btn-secondary {
      background: #ffffff;
      color: var(--accent);
      border: 1px solid var(--accent);
    }

    .btn-success {
      background: var(--success);
      color: white;
    }

    .btn-danger {
      background: var(--danger);
      color: white;
    }

    .btn-warning {
      background: var(--warning);
      color: white;
    }

    .btn-small {
      padding: 6px 12px;
      font-size: 0.85rem;
    }

    .btn-block {
      width: 100%;
    }

    input, textarea, select {
      display: block;
      width: 100%;
      border: 1px solid var(--border);
      background: var(--secondary);
      color: var(--text);
      border-radius: 8px;
      padding: 10px 12px;
      font-family: inherit;
      font-size: 0.95rem;
      margin-bottom: 10px;
      transition: 0.2s;
    }

    input:focus, textarea:focus, select:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 8px rgba(0, 212, 255, 0.3);
    }

    textarea {
      resize: vertical;
      min-height: 80px;
    }

    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-right: 6px;
      margin-bottom: 6px;
    }

    .badge-primary {
      background: rgba(0, 212, 255, 0.2);
      color: var(--accent);
    }

    .badge-success {
      background: rgba(46, 204, 113, 0.2);
      color: var(--success);
    }

    .badge-danger {
      background: rgba(231, 76, 60, 0.2);
      color: var(--danger);
    }

    .status-message {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 16px;
      border-left: 4px solid;
    }

    .status-message.success {
      background: rgba(46, 204, 113, 0.1);
      color: var(--success);
      border-color: var(--success);
    }

    .status-message.error {
      background: rgba(231, 76, 60, 0.1);
      color: var(--danger);
      border-color: var(--danger);
    }

    .status-message.info {
      background: rgba(0, 212, 255, 0.1);
      color: var(--accent);
      border-color: var(--accent);
    }

    .table-responsive {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }

    th {
      background: var(--secondary);
      color: var(--accent);
      font-weight: 700;
    }

    tr:hover {
      background: rgba(0, 212, 255, 0.05);
    }

    .chat-box {
      background: var(--secondary);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 16px;
      max-height: 400px;
      overflow-y: auto;
      margin-bottom: 12px;
    }

    .chat-message {
      margin-bottom: 12px;
      padding: 10px 12px;
      border-radius: 8px;
      word-wrap: break-word;
    }

    .chat-message.user {
      background: rgba(0, 212, 255, 0.2);
      border-left: 3px solid var(--accent);
      margin-left: 20px;
    }

    .chat-message.ai {
      background: rgba(255, 107, 53, 0.2);
      border-left: 3px solid var(--accent-alt);
      margin-right: 20px;
    }

    .cart-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px;
      background: var(--secondary);
      border-radius: 8px;
      margin-bottom: 8px;
      border-left: 3px solid var(--accent);
    }

    .cart-total {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px;
      background: rgba(0, 212, 255, 0.1);
      border: 2px solid var(--accent);
      border-radius: 8px;
      font-weight: 700;
      font-size: 1.1rem;
    }

    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: var(--muted);
    }

    .empty-state-icon {
      font-size: 3rem;
      margin-bottom: 12px;
    }

    .stack {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .row {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .row > * {
      flex: 1;
      min-width: 120px;
    }

    .search-box {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    .search-box input {
      flex: 1;
      margin-bottom: 0;
    }

    .filter-chip {
      background: var(--secondary);
      border: 1px solid var(--border);
      color: var(--text);
      padding: 8px 14px;
      border-radius: 999px;
      cursor: pointer;
      transition: 0.2s;
      font-size: 0.9rem;
    }

    .filter-chip.active {
      background: var(--accent);
      color: var(--primary);
      border-color: var(--accent);
    }

    @media (max-width: 1024px) {
      .col-9 { grid-column: span 12; }
      .col-8, .col-6 { grid-column: span 12; }
      .col-4, .col-3 { grid-column: span 6; }

      .hero h1 {
        font-size: 2rem;
      }

      .grid {
        gap: 16px;
      }
    }

    @media (max-width: 640px) {
      .col-12, .col-9, .col-8, .col-6, .col-4, .col-3 {
        grid-column: span 12;
      }

      .navbar {
        flex-direction: column;
        gap: 12px;
      }

      .navbar-brand {
        font-size: 1.1rem;
      }

      .navbar-center {
        width: 100%;
        max-width: none;
      }

      .navbar-menu {
        flex-direction: column;
        gap: 8px;
        width: 100%;
      }

      .nav-link {
        padding: 10px 12px;
        width: 100%;
        text-align: center;
      }

      .hero {
        padding: 40px 16px;
      }

      .hero h1 {
        font-size: 1.6rem;
      }

      .product-grid {
        grid-template-columns: 1fr;
      }
    }

    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 20px;
    }

    .tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .tab-button {
      background: var(--secondary);
      border: 1px solid var(--border);
      color: var(--text);
      padding: 10px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .tab-button.active {
      background: var(--accent);
      color: #ffffff;
      border-color: var(--accent);
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-brand">💻 PC Store AI</div>
    <div class="navbar-center">
      <input type="text" placeholder="Cari motherboard, GPU, SSD, atau build PC..." />
    </div>
    <div class="navbar-menu">
      <button class="nav-link active" data-page="guest">Guest</button>
      <button class="nav-link" data-page="customer">Customer</button>
      <button class="nav-link" data-page="admin">Admin</button>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <h1>PC & AI Agent E-Commerce Store</h1>
    <p>Temukan komponen PC impian Anda dengan rekomendasi dari AI Assistant. Rakit PC custom sesuai budget dan kebutuhan Anda.</p>
  </section>

  <!-- Main Container -->
  <div class="container">
    <!-- GUEST PAGE -->
    <div class="page active" id="guest">
      <div class="grid">
        <div class="col-9 stack">
          <div class="card">
            <h2 class="card-title">🤖 AI Recommendation Assistant</h2>
            <p style="color: var(--muted); margin-bottom: 16px;">Ceritakan kebutuhan Anda, dan AI akan merekomendasikan PC build yang sempurna untuk Anda.</p>
            <textarea id="guestPrompt" placeholder="Contoh: Saya butuh PC untuk gaming 4K, budget 50 juta, mau RTX 4090 sama Intel Core i9..."></textarea>
            <div class="row">
              <button class="btn btn-primary" id="guestAskAI">💬 Tanya AI</button>
              <button class="btn btn-secondary" id="guestFilterParts">🔍 Cari Parts</button>
            </div>
            <div id="guestStatus" class="status-message info" style="display: none;"></div>
            <div id="guestAiOutput" class="chat-box" style="display: none; margin-top: 12px;"></div>
          </div>

          <div class="card">
            <h2 class="card-title">🖥️ Katalog PC Parts</h2>
            <div class="search-box">
              <input type="text" id="guestSearchParts" placeholder="Cari komponen...">
              <select id="guestCategoryFilter" style="margin-bottom: 0;">
                <option value="">Semua Kategori</option>
                <option value="Processor">Processor</option>
                <option value="Graphics Card">Graphics Card</option>
                <option value="Memory">Memory</option>
                <option value="Storage">Storage</option>
                <option value="Motherboard">Motherboard</option>
                <option value="Power Supply">Power Supply</option>
                <option value="Case">Case</option>
              </select>
            </div>
            <div id="guestPartsList" class="product-grid"></div>
          </div>

          <div class="card">
            <h2 class="card-title">⚙️ PC Build Packages</h2>
            <div id="guestBuildsList" class="product-grid"></div>
          </div>
        </div>

        <div class="col-3 stack">
          <div class="card">
            <h2 class="card-title">📊 Statistik</h2>
            <div style="padding: 12px; background: var(--secondary); border-radius: 8px; margin-bottom: 12px;">
              <div style="font-size: 0.9rem; color: var(--muted);">Total Parts</div>
              <div style="font-size: 1.8rem; color: var(--accent); font-weight: 700;" id="guestTotalParts">0</div>
            </div>
            <div style="padding: 12px; background: var(--secondary); border-radius: 8px;">
              <div style="font-size: 0.9rem; color: var(--muted);">PC Builds</div>
              <div style="font-size: 1.8rem; color: var(--accent-alt); font-weight: 700;" id="guestTotalBuilds">0</div>
            </div>
          </div>

          <div class="card">
            <h2 class="card-title">🚀 Langkah Berikutnya</h2>
            <p style="font-size: 0.95rem; color: var(--muted); margin-bottom: 16px;">Siap untuk membeli? Daftar atau login sebagai customer untuk mulai berbelanja.</p>
            <button class="btn btn-primary btn-block" onclick="switchPage('customer')">Buka Customer Dashboard</button>
          </div>

          <div class="card">
            <h2 class="card-title">💡 Tips</h2>
            <ul style="color: var(--muted); font-size: 0.9rem; margin: 0; padding-left: 20px;">
              <li>Gunakan AI Assistant untuk rekomendasi personal</li>
              <li>Filter parts berdasarkan kategori</li>
              <li>Cek PC Builds siap jadi untuk kemudahan</li>
              <li>Login untuk memulai checkout</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- CUSTOMER PAGE -->
    <div class="page" id="customer">
      <div class="grid">
        <div class="col-9 stack">
          <div class="card">
            <h2 class="card-title">🛒 Shopping Dashboard</h2>

            <div class="tabs">
              <button class="tab-button active" data-tab="shop">Belanja</button>
              <button class="tab-button" data-tab="cart">Keranjang</button>
              <button class="tab-button" data-tab="orders">Pesanan Saya</button>
            </div>

            <!-- Shop Tab -->
            <div id="shop-tab" class="tab-content">
              <div class="search-box">
                <input type="text" id="shopSearch" placeholder="Cari produk..." style="margin-bottom: 0;">
                <select id="shopCategory" style="margin-bottom: 0;">
                  <option value="">Semua Kategori</option>
                  <option value="Processor">Processor</option>
                  <option value="Graphics Card">Graphics Card</option>
                  <option value="Memory">Memory</option>
                  <option value="Storage">Storage</option>
                  <option value="Motherboard">Motherboard</option>
                  <option value="Power Supply">Power Supply</option>
                  <option value="Case">Case</option>
                </select>
              </div>
              <div id="shopPartsList" class="product-grid"></div>

              <h3 style="margin-top: 30px; color: var(--accent);">📦 PC Build Packages</h3>
              <div id="shopBuildsList" class="product-grid"></div>
            </div>

            <!-- Cart Tab -->
            <div id="cart-tab" class="tab-content" style="display: none;">
              <div id="cartItemsList"></div>
              <div id="cartTotalBox" style="margin-top: 20px;"></div>
              <div class="row" style="margin-top: 16px;">
                <button class="btn btn-primary" id="btnCheckout" onclick="proceedCheckout()">💳 Checkout</button>
                <button class="btn btn-secondary" id="btnClearCart" onclick="clearCartCustomer()">Kosongkan</button>
              </div>
            </div>

            <!-- Orders Tab -->
            <div id="orders-tab" class="tab-content" style="display: none;">
              <div id="customerOrdersList"></div>
            </div>
          </div>
        </div>

        <div class="col-3 stack">
          <div class="card">
            <h2 class="card-title">👤 Profil Customer</h2>
            <div style="padding: 12px; background: var(--secondary); border-radius: 8px; margin-bottom: 12px;">
              <div style="font-size: 0.85rem; color: var(--muted);">Nama</div>
              <div style="color: var(--text); font-weight: 600;" id="customerName">Budi Santoso</div>
            </div>
            <div style="padding: 12px; background: var(--secondary); border-radius: 8px;">
              <div style="font-size: 0.85rem; color: var(--muted);">Email</div>
              <div style="color: var(--text); font-weight: 600;" id="customerEmail">budi@email.com</div>
            </div>
          </div>

          <div class="card">
            <h2 class="card-title">📋 Quick Stats</h2>
            <div style="padding: 12px; background: var(--secondary); border-radius: 8px; margin-bottom: 12px;">
              <div style="font-size: 0.85rem; color: var(--muted);">Pesanan Aktif</div>
              <div style="font-size: 1.8rem; color: var(--success); font-weight: 700;" id="activeOrders">0</div>
            </div>
            <div style="padding: 12px; background: var(--secondary); border-radius: 8px;">
              <div style="font-size: 0.85rem; color: var(--muted);">Pesanan Selesai</div>
              <div style="font-size: 1.8rem; color: var(--accent); font-weight: 700;" id="completedOrders">0</div>
            </div>
          </div>

          <div class="card">
            <h2 class="card-title">🎁 Promo</h2>
            <p style="font-size: 0.95rem; color: var(--muted);">Belanja sekarang dan dapatkan konsultasi gratis dari AI Assistant kami untuk konfigurasi PC optimal Anda!</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ADMIN PAGE -->
    <div class="page" id="admin">
      <div class="card">
        <h2 class="card-title">🔧 Admin Dashboard</h2>

        <div class="tabs">
          <button class="tab-button active" data-admin-tab="inventory">Inventory</button>
          <button class="tab-button" data-admin-tab="builds">PC Builds</button>
          <button class="tab-button" data-admin-tab="orders">Pesanan</button>
          <button class="tab-button" data-admin-tab="customers">Customer</button>
        </div>

        <!-- Inventory Tab -->
        <div id="admin-inventory-tab" class="tab-content">
          <div class="grid">
            <div class="col-4">
              <div style="background: var(--secondary); padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
                <h3 class="card-title">Tambah PC Part</h3>
                <input id="adminPartName" placeholder="Nama part" />
                <select id="adminPartCategory">
                  <option value="">Pilih Kategori</option>
                  <option value="Processor">Processor</option>
                  <option value="Graphics Card">Graphics Card</option>
                  <option value="Memory">Memory</option>
                  <option value="Storage">Storage</option>
                  <option value="Motherboard">Motherboard</option>
                  <option value="Power Supply">Power Supply</option>
                  <option value="Case">Case</option>
                </select>
                <input id="adminPartBrand" placeholder="Brand" />
                <input id="adminPartModel" placeholder="Model" />
                <input id="adminPartPrice" type="number" placeholder="Harga (Rp)" />
                <input id="adminPartStock" type="number" placeholder="Stok" />
                <textarea id="adminPartDesc" placeholder="Deskripsi"></textarea>
                <button class="btn btn-primary btn-block" id="btnAddPart">Simpan Part</button>
              </div>
            </div>

            <div class="col-8">
              <div style="background: var(--secondary); padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
                <h3 class="card-title">Daftar PC Parts</h3>
                <div id="adminPartsList"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- PC Builds Tab -->
        <div id="admin-builds-tab" class="tab-content" style="display: none;">
          <div class="grid">
            <div class="col-4">
              <div style="background: var(--secondary); padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
                <h3 class="card-title">Buat PC Build</h3>
                <input id="adminBuildName" placeholder="Nama Build" />
                <textarea id="adminBuildDesc" placeholder="Deskripsi Build"></textarea>
                <button class="btn btn-primary btn-block" id="btnCreateBuild">Buat Build Baru</button>
              </div>
            </div>

            <div class="col-8">
              <div style="background: var(--secondary); padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
                <h3 class="card-title">Daftar PC Builds</h3>
                <div id="adminBuildsList"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Orders Tab -->
        <div id="admin-orders-tab" class="tab-content" style="display: none;">
          <div style="background: var(--secondary); padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
            <h3 class="card-title">Kelola Pesanan</h3>
            <div id="adminOrdersList"></div>
          </div>
        </div>

        <!-- Customers Tab -->
        <div id="admin-customers-tab" class="tab-content" style="display: none;">
          <div style="background: var(--secondary); padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
            <h3 class="card-title">Daftar Customer</h3>
            <div class="table-responsive">
              <table id="adminCustomersTable">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody id="adminCustomersList">
                  <tr>
                    <td colspan="5" style="text-align: center; color: var(--muted);">Memuat...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const API = './api.php';
    const rupiah = (n) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(n || 0));

    const state = {
      currentPage: 'guest',
      currentCustomer: { id: 2, name: 'Budi Santoso', email: 'budi@email.com' },
      parts: [],
      builds: [],
      cart: [],
      orders: [],
      customers: [],
    };

    async function request(action, method = 'GET', body = null, params = null) {
      try {
        const opts = { method, headers: { 'Content-Type': 'application/json' } };
        if (body) opts.body = JSON.stringify(body);
        const query = new URLSearchParams({ action, ...(params || {}) }).toString();
        const res = await fetch(`${API}?${query}`, opts);
        const json = await res.json();
        if (!json.ok) throw new Error(json.message || 'Terjadi kesalahan');
        return json;
      } catch (err) {
        console.error('API Error:', err);
        throw err;
      }
    }

    function switchPage(page) {
      document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
      document.getElementById(page).classList.add('active');
      document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
      document.querySelector(`[data-page="${page}"]`).classList.add('active');
      state.currentPage = page;

      if (page === 'guest') loadGuestData();
      else if (page === 'customer') loadCustomerData();
      else if (page === 'admin') loadAdminData();
    }

    function showStatus(message, type = 'info') {
      const status = document.getElementById('guestStatus');
      if (status) {
        status.textContent = message;
        status.className = `status-message ${type}`;
        status.style.display = 'block';
        setTimeout(() => status.style.display = 'none', 5000);
      }
    }

    async function loadGuestData() {
      try {
        const partsRes = await request('list_parts');
        const buildsRes = await request('list_builds');
        state.parts = partsRes.data || [];
        state.builds = buildsRes.data || [];
        renderGuestParts();
        renderGuestBuilds();
        document.getElementById('guestTotalParts').textContent = state.parts.length;
        document.getElementById('guestTotalBuilds').textContent = state.builds.length;
      } catch (err) {
        showStatus('Error: ' + err.message, 'error');
      }
    }

    function renderGuestParts() {
      const wrap = document.getElementById('guestPartsList');
      if (!state.parts.length) {
        wrap.innerHTML = '<div class="empty-state"><div class="empty-state-icon">📭</div><p>Belum ada parts tersedia</p></div>';
        return;
      }
      wrap.innerHTML = state.parts.map(p => `
        <div class="card product-card">
          <div class="product-image">🔧</div>
          <div class="product-name">${p.name}</div>
          <div class="product-spec">${p.brand} ${p.model}</div>
          <div class="badge badge-primary">${p.category}</div>
          <div class="product-price">${rupiah(p.price)}</div>
          <div class="product-stock ${p.is_stock_empty ? 'out-of-stock' : ''}">
            ${p.is_stock_empty ? '❌ Stok Habis' : '✅ Tersedia (' + p.stock + ')'}
          </div>
          <button class="btn btn-primary btn-block btn-small" ${p.is_stock_empty ? 'disabled' : ''}>Lihat Detail</button>
        </div>
      `).join('');
    }

    function renderGuestBuilds() {
      const wrap = document.getElementById('guestBuildsList');
      if (!state.builds.length) {
        wrap.innerHTML = '<div class="empty-state"><div class="empty-state-icon">📦</div><p>Belum ada PC Builds tersedia</p></div>';
        return;
      }
      wrap.innerHTML = state.builds.map(b => `
        <div class="card product-card">
          <div class="product-image">⚙️</div>
          <div class="product-name">${b.name}</div>
          <div class="product-spec">${b.description || '-'}</div>
          <div class="badge badge-success">Package</div>
          <div class="product-price">${rupiah(b.total_price)}</div>
          <button class="btn btn-primary btn-block btn-small">Lihat Komponen</button>
        </div>
      `).join('');
    }

    function extractBudget(prompt) {
      const p = String(prompt || '').toLowerCase();
      const juta = p.match(/(\d+(?:[\.,]\d+)?)\s*(jt|juta)/i);
      if (juta) {
        return Math.round(parseFloat(juta[1].replace(',', '.')) * 1000000);
      }
      const rupiah = p.match(/(\d{6,12})/);
      if (rupiah) {
        return Number(rupiah[1]);
      }
      return null;
    }

    function scoreItemByPrompt(item, prompt) {
      const text = `${item.name || ''} ${item.brand || ''} ${item.model || ''} ${item.category || ''}`.toLowerCase();
      const tokens = String(prompt || '').toLowerCase().split(/\s+/).filter(Boolean);
      let score = 0;
      tokens.forEach(t => {
        if (t.length > 2 && text.includes(t)) score += 2;
      });

      if (text.includes('rtx') && /(gaming|game|fps|2k|1440|4k)/i.test(prompt)) score += 3;
      if (text.includes('processor') && /(render|editing|stream|produktif|kerja)/i.test(prompt)) score += 2;
      if (text.includes('memory') && /(multitask|editing|3d|desain)/i.test(prompt)) score += 2;
      return score;
    }

    function renderGuestAiOutput(prompt, recParts, recBuilds, budget) {
      const box = document.getElementById('guestAiOutput');
      if (!box) return;

      const budgetText = budget ? `Budget terdeteksi: <strong>${rupiah(budget)}</strong>.` : 'Budget tidak terdeteksi, menampilkan rekomendasi umum terbaik.';
      const partsHtml = recParts.length
        ? `<ul style="margin: 6px 0 0; padding-left: 18px;">${recParts.map(p => `<li>${p.name} (${p.brand}) - <strong>${rupiah(p.price)}</strong></li>`).join('')}</ul>`
        : '<p style="margin: 6px 0 0; color: var(--muted);">Belum ada part yang cocok.</p>';
      const buildsHtml = recBuilds.length
        ? `<ul style="margin: 6px 0 0; padding-left: 18px;">${recBuilds.map(b => `<li>${b.name} - <strong>${rupiah(b.total_price)}</strong></li>`).join('')}</ul>`
        : '<p style="margin: 6px 0 0; color: var(--muted);">Belum ada build yang cocok.</p>';

      box.innerHTML = `
        <div class="chat-message user">${prompt.replace(/</g, '&lt;')}</div>
        <div class="chat-message ai">
          <div><strong>Saran AI:</strong> ${budgetText}</div>
          <div style="margin-top: 8px;"><strong>Produk yang direkomendasikan:</strong>${partsHtml}</div>
          <div style="margin-top: 8px;"><strong>Build alternatif:</strong>${buildsHtml}</div>
          <div style="margin-top: 8px; color: var(--muted);">Tips: kombinasi ideal biasanya Processor + GPU + RAM 32GB + SSD NVMe agar performa stabil.</div>
        </div>
      `;
      box.style.display = 'block';
    }

    function runGuestAiRecommendation() {
      const promptEl = document.getElementById('guestPrompt');
      const prompt = (promptEl?.value || '').trim();
      if (!prompt) {
        showStatus('Isi dulu kebutuhan Anda agar AI bisa memberi saran.', 'error');
        return;
      }

      const budget = extractBudget(prompt);
      let parts = [...state.parts].filter(p => !p.is_stock_empty);
      if (budget) {
        parts = parts.filter(p => Number(p.price) <= budget);
      }

      parts.sort((a, b) => scoreItemByPrompt(b, prompt) - scoreItemByPrompt(a, prompt));
      const recommendedParts = parts.slice(0, 5);

      let builds = [...state.builds];
      if (budget) {
        builds = builds.filter(b => Number(b.total_price) <= budget * 1.1);
      }
      builds.sort((a, b) => Math.abs((budget || a.total_price) - a.total_price) - Math.abs((budget || b.total_price) - b.total_price));
      const recommendedBuilds = builds.slice(0, 2);

      renderGuestAiOutput(prompt, recommendedParts, recommendedBuilds, budget);
      showStatus('Rekomendasi sudah dibuat dari katalog produk kita.', 'success');
    }

    function applyGuestFilter() {
      const keyword = (document.getElementById('guestSearchParts')?.value || '').trim().toLowerCase();
      const category = (document.getElementById('guestCategoryFilter')?.value || '').trim();

      let filtered = [...state.parts];
      if (keyword) {
        filtered = filtered.filter(p => `${p.name} ${p.brand} ${p.model}`.toLowerCase().includes(keyword));
      }
      if (category) {
        filtered = filtered.filter(p => p.category === category);
      }

      const wrap = document.getElementById('guestPartsList');
      if (!filtered.length) {
        wrap.innerHTML = '<div class="empty-state"><div class="empty-state-icon">🔎</div><p>Produk tidak ditemukan</p></div>';
        showStatus('Tidak ada produk sesuai filter.', 'info');
        return;
      }

      wrap.innerHTML = filtered.map(p => `
        <div class="card product-card">
          <div class="product-image">🔧</div>
          <div class="product-name">${p.name}</div>
          <div class="product-spec">${p.brand} ${p.model}</div>
          <div class="badge badge-primary">${p.category}</div>
          <div class="product-price">${rupiah(p.price)}</div>
          <div class="product-stock ${p.is_stock_empty ? 'out-of-stock' : ''}">
            ${p.is_stock_empty ? '❌ Stok Habis' : '✅ Tersedia (' + p.stock + ')'}
          </div>
        </div>
      `).join('');

      showStatus(`Menampilkan ${filtered.length} produk hasil filter.`, 'success');
    }

    function initGuestActions() {
      const askBtn = document.getElementById('guestAskAI');
      const filterBtn = document.getElementById('guestFilterParts');
      const promptEl = document.getElementById('guestPrompt');

      if (askBtn) askBtn.addEventListener('click', runGuestAiRecommendation);
      if (filterBtn) filterBtn.addEventListener('click', applyGuestFilter);
      if (promptEl) {
        promptEl.addEventListener('keydown', (e) => {
          if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            runGuestAiRecommendation();
          }
        });
      }
    }

    async function loadCustomerData() {
      try {
        const partsRes = await request('list_parts');
        const buildsRes = await request('list_builds');
        state.parts = partsRes.data || [];
        state.builds = buildsRes.data || [];
        renderShopParts();
        renderShopBuilds();
        refreshCustomerCart();
        renderCustomerOrders();
      } catch (err) {
        console.error('Error loading customer data:', err);
      }
    }

    function renderShopParts() {
      const wrap = document.getElementById('shopPartsList');
      wrap.innerHTML = state.parts.map(p => `
        <div class="card product-card">
          <div class="product-image">🔧</div>
          <div class="product-name">${p.name}</div>
          <div class="product-spec">${p.brand}</div>
          <div class="product-price">${rupiah(p.price)}</div>
          <div class="product-stock">${p.stock} tersedia</div>
          <button class="btn btn-primary btn-small btn-block" onclick="addToCart('part', ${p.id})">Tambah</button>
        </div>
      `).join('');
    }

    function renderShopBuilds() {
      const wrap = document.getElementById('shopBuildsList');
      wrap.innerHTML = state.builds.map(b => `
        <div class="card product-card">
          <div class="product-image">🖥️</div>
          <div class="product-name">${b.name}</div>
          <div class="product-price">${rupiah(b.total_price)}</div>
          <button class="btn btn-secondary btn-small btn-block" onclick="addToCart('build', ${b.id})">Tambah</button>
        </div>
      `).join('');
    }

    function addToCart(type, id) {
      const item = type === 'part' ? state.parts.find(p => p.id === id) : state.builds.find(b => b.id === id);
      if (!item) return;
      state.cart.push({ type, item, qty: 1 });
      showStatus('✅ Ditambahkan ke keranjang', 'success');
      refreshCustomerCart();
    }

    function refreshCustomerCart() {
      const wrap = document.getElementById('cartItemsList');
      if (!state.cart.length) {
        wrap.innerHTML = '<div class="empty-state"><p>Keranjang kosong</p></div>';
        document.getElementById('cartTotalBox').innerHTML = '';
        return;
      }

      let total = 0;
      wrap.innerHTML = state.cart.map((item, idx) => {
        const price = item.item.price;
        total += price;
        return `
          <div class="cart-item">
            <div>
              <div style="font-weight: 600; color: var(--text);">${item.item.name || item.item.title}</div>
              <div style="font-size: 0.9rem; color: var(--muted);">${rupiah(price)}</div>
            </div>
            <button class="btn btn-danger btn-small" onclick="removeFromCart(${idx})">Hapus</button>
          </div>
        `;
      }).join('');

      document.getElementById('cartTotalBox').innerHTML = `
        <div class="cart-total">
          <span>Total Harga</span>
          <span>${rupiah(total)}</span>
        </div>
      `;
    }

    function removeFromCart(idx) {
      state.cart.splice(idx, 1);
      refreshCustomerCart();
    }

    function clearCartCustomer() {
      state.cart = [];
      refreshCustomerCart();
      showStatus('✅ Keranjang dibersihkan', 'success');
    }

    function proceedCheckout() {
      showStatus('✅ Checkout berhasil! Silakan lanjutkan pembayaran via aplikasi', 'success');
    }

    function renderCustomerOrders() {
      const wrap = document.getElementById('customerOrdersList');
      wrap.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">📦</div>
          <p>Belum ada pesanan</p>
        </div>
      `;
    }

    async function loadAdminData() {
      try {
        const partsRes = await request('list_parts');
        const buildsRes = await request('list_builds');
        const customersRes = await request('list_users');
        state.parts = partsRes.data || [];
        state.builds = buildsRes.data || [];
        state.customers = customersRes.data || [];
        renderAdminParts();
        renderAdminBuilds();
        renderAdminCustomers();
      } catch (err) {
        console.error('Error loading admin data:', err);
      }
    }

    function renderAdminParts() {
      const wrap = document.getElementById('adminPartsList');
      wrap.innerHTML = `
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Brand</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              ${state.parts.map(p => `
                <tr>
                  <td>${p.name}</td>
                  <td><span class="badge badge-primary">${p.category}</span></td>
                  <td>${p.brand}</td>
                  <td>${rupiah(p.price)}</td>
                  <td>${p.stock}</td>
                  <td><button class="btn btn-danger btn-small">Hapus</button></td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      `;
    }

    function renderAdminBuilds() {
      const wrap = document.getElementById('adminBuildsList');
      wrap.innerHTML = `
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Nama</th>
                <th>Total Harga</th>
                <th>Komponen</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              ${state.builds.map(b => `
                <tr>
                  <td>${b.name}</td>
                  <td>${rupiah(b.total_price)}</td>
                  <td><span class="badge badge-success">Lihat</span></td>
                  <td><button class="btn btn-danger btn-small">Hapus</button></td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      `;
    }

    function renderAdminCustomers() {
      const wrap = document.getElementById('adminCustomersList');
      wrap.innerHTML = state.customers.map(c => `
        <tr>
          <td>${c.id}</td>
          <td>${c.name}</td>
          <td>${c.email || '-'}</td>
          <td><span class="badge badge-success">Aktif</span></td>
          <td><button class="btn btn-secondary btn-small">Edit</button></td>
        </tr>
      `).join('');
    }

    function initTabs() {
      // Shop tabs
      document.querySelectorAll('.tab-button[data-tab]').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const tabName = e.target.getAttribute('data-tab');
          document.querySelectorAll('.tab-button[data-tab]').forEach(b => b.classList.remove('active'));
          document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
          e.target.classList.add('active');
          document.getElementById(tabName + '-tab').style.display = 'block';
        });
      });

      // Admin tabs
      document.querySelectorAll('.tab-button[data-admin-tab]').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const tabName = e.target.getAttribute('data-admin-tab');
          document.querySelectorAll('.tab-button[data-admin-tab]').forEach(b => b.classList.remove('active'));
          document.querySelectorAll('[id^="admin-"][id$="-tab"]').forEach(t => t.style.display = 'none');
          e.target.classList.add('active');
          document.getElementById('admin-' + tabName + '-tab').style.display = 'block';
        });
      });
    }

    function initNavigation() {
      document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
          const page = e.target.getAttribute('data-page');
          switchPage(page);
        });
      });
    }

    window.switchPage = switchPage;
    window.addToCart = addToCart;
    window.removeFromCart = removeFromCart;
    window.clearCartCustomer = clearCartCustomer;
    window.proceedCheckout = proceedCheckout;

    document.addEventListener('DOMContentLoaded', () => {
      initTabs();
      initNavigation();
      initGuestActions();
      loadGuestData();
    });
  </script>
</body>
</html>
