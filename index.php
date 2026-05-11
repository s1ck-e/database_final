<?php
// index.php — Үндсэн хуудас (PHP session шалгана)
if (session_status() === PHP_SESSION_NONE) session_start();
$current_user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="mn">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Shoppy.mn - Үндэсний цахим худалдааны платформ</title>
  <link rel="stylesheet" href="style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    /* ── Нэмэлт style ── */
    .toast{position:fixed;bottom:24px;right:24px;background:#333;color:#fff;padding:12px 20px;border-radius:8px;font-size:14px;z-index:9999;opacity:0;transform:translateY(10px);transition:.3s;pointer-events:none}
    .toast.show{opacity:1;transform:translateY(0)}
    .toast.success{background:#9840b1}
    .toast.error{background:#e74c8c}
    .cart-badge{background:#e74c8c;color:#fff;border-radius:50%;width:18px;height:18px;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;position:absolute;top:-4px;right:-4px;display:none}
    .action-icon{position:relative}
    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center}
    .modal-overlay.open{display:flex}
    .modal{background:#fff;border-radius:16px;padding:32px;width:90%;max-width:420px;max-height:90vh;overflow-y:auto}
    .modal h3{font-size:20px;font-weight:700;margin-bottom:20px;color:#2e2034}
    .modal input,.modal textarea{width:100%;border:1px solid #ddd1e2;border-radius:8px;padding:11px 14px;font-size:14px;margin-bottom:14px;outline:none;font-family:inherit}
    .modal input:focus{border-color:#9840b1;box-shadow:0 0 0 3px rgba(152,64,177,.12)}
    .modal-btn{width:100%;background:#9840b1;border:none;border-radius:8px;color:#fff;cursor:pointer;font-size:15px;font-weight:700;padding:13px;transition:.2s}
    .modal-btn:hover{background:#7e3295}
    .modal-close{float:right;background:none;border:none;font-size:22px;cursor:pointer;color:#999;margin-top:-8px}
    .modal-switch{text-align:center;font-size:14px;color:#6d6272;margin-top:14px}
    .modal-switch a{color:#9840b1;font-weight:600;cursor:pointer}
    .cart-panel{position:fixed;right:0;top:0;height:100vh;width:380px;background:#fff;box-shadow:-4px 0 30px rgba(0,0,0,.12);z-index:1001;transform:translateX(100%);transition:.3s;display:flex;flex-direction:column}
    .cart-panel.open{transform:translateX(0)}
    .cart-head{padding:20px 24px;border-bottom:1px solid #eee;display:flex;align-items:center;justify-content:space-between}
    .cart-head h3{font-size:18px;font-weight:700}
    .cart-body{flex:1;overflow-y:auto;padding:16px 24px}
    .cart-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #f0e8f2}
    .cart-item img{width:64px;height:64px;object-fit:cover;border-radius:8px;background:#f5f5f5}
    .cart-item-info{flex:1}
    .cart-item-name{font-size:14px;font-weight:600;color:#2e2034;margin-bottom:4px}
    .cart-item-price{font-size:13px;color:#9840b1;font-weight:600}
    .cart-item-qty{font-size:12px;color:#999}
    .cart-del{background:none;border:none;cursor:pointer;color:#ccc;font-size:18px;padding:4px}
    .cart-del:hover{color:#e74c8c}
    .cart-foot{padding:20px 24px;border-top:1px solid #eee}
    .cart-total{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;font-weight:700;font-size:16px}
    .cart-total span:last-child{color:#9840b1;font-size:20px}
    .cart-checkout-btn{width:100%;background:#9840b1;border:none;border-radius:8px;color:#fff;cursor:pointer;font-size:15px;font-weight:700;padding:14px;transition:.2s}
    .cart-checkout-btn:hover{background:#7e3295}
    .cart-empty{text-align:center;padding:40px 0;color:#999;font-size:14px}
    .products-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:20px}
    .product-card{background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.08);transition:.2s;cursor:pointer}
    .product-card:hover{transform:translateY(-5px);box-shadow:0 8px 25px rgba(0,0,0,.12)}
    .product-image{height:200px;overflow:hidden;background:#f5f5f5;position:relative}
    .product-image img{width:100%;height:100%;object-fit:cover}
    .product-card h3{padding:12px 14px 4px;font-size:14px;font-weight:600;color:#333}
    .product-card .price{display:block;padding:0 14px 14px;font-size:16px;font-weight:700;color:#9840b1}
    .add-to-cart-btn{display:none;position:absolute;bottom:0;left:0;right:0;background:#9840b1;color:#fff;border:none;padding:10px;font-size:13px;font-weight:600;cursor:pointer;transition:.2s}
    .product-card:hover .add-to-cart-btn{display:block}
    .user-menu{position:relative}
    .user-dropdown{display:none;position:absolute;right:0;top:110%;background:#fff;border:1px solid #eee;border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,.12);min-width:180px;z-index:500;padding:8px 0}
    .user-menu:hover .user-dropdown{display:block}
    .user-dropdown a,.user-dropdown button{display:block;width:100%;text-align:left;padding:10px 16px;font-size:14px;color:#333;background:none;border:none;cursor:pointer;transition:.15s}
    .user-dropdown a:hover,.user-dropdown button:hover{background:#f9f2fc;color:#9840b1}
    .checkout-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1002;align-items:center;justify-content:center}
    .checkout-overlay.open{display:flex}
    .checkout-modal{background:#fff;border-radius:16px;width:90%;max-width:700px;max-height:90vh;overflow-y:auto;display:grid;grid-template-columns:1fr 280px;gap:0}
    @media(max-width:640px){.checkout-modal{grid-template-columns:1fr}}
    .co-form{padding:32px}
    .co-form h3{font-size:20px;font-weight:700;color:#2e2034;margin-bottom:20px}
    .co-form label{display:block;font-size:13px;font-weight:600;color:#4c4250;margin-bottom:6px}
    .co-form input,.co-form textarea{width:100%;border:1px solid #ddd1e2;border-radius:8px;padding:11px 14px;font-size:14px;margin-bottom:14px;outline:none;font-family:inherit}
    .co-form input:focus,.co-form textarea:focus{border-color:#9840b1;box-shadow:0 0 0 3px rgba(152,64,177,.12)}
    .pay-opts{display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:16px}
    .pay-opt{border:1px solid #ddd1e2;border-radius:8px;padding:10px;cursor:pointer;text-align:center;font-size:13px;transition:.2s}
    .pay-opt:hover,.pay-opt.active{border-color:#9840b1;background:#f9f2fc;color:#9840b1;font-weight:600}
    .co-summary{background:#f7f4f9;border-left:1px solid #eadfed;padding:28px;border-radius:0 16px 16px 0}
    .co-summary h4{font-size:16px;font-weight:700;color:#2e2034;margin-bottom:16px}
    .co-item{display:flex;justify-content:space-between;font-size:13px;color:#6d6272;padding:6px 0;border-bottom:1px solid #f0e8f2}
    .co-item strong{color:#2e2034}
    .co-grand{display:flex;justify-content:space-between;font-weight:700;font-size:16px;color:#2e2034;padding-top:14px;margin-top:4px}
    .co-grand span:last-child{color:#9840b1}
    .loading-dots::after{content:'...';animation:dots 1s steps(3,end) infinite}
    @keyframes dots{0%{content:'.'}33%{content:'..'}66%{content:'...'}}
    #overlay-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:999}
    #overlay-bg.open{display:block}
    .order-success{text-align:center;padding:40px 20px}
    .order-success .checkmark{font-size:56px;margin-bottom:16px}
    .order-success h3{font-size:24px;font-weight:700;color:#2e2034;margin-bottom:8px}
    .order-success p{color:#6d6272;font-size:15px}
    .orders-list{max-width:700px;margin:40px auto;padding:0 20px}
    .order-row{background:#fff;border:1px solid #eee;border-radius:12px;padding:20px;margin-bottom:14px}
    .order-row-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
    .order-status{font-size:12px;padding:3px 10px;border-radius:20px;font-weight:600}
    .order-status.pending{background:#fff3cd;color:#856404}
    .order-status.confirmed{background:#d4edda;color:#155724}
  </style>
</head>
<body>

<!-- Toast мэдэгдэл -->
<div id="toast" class="toast"></div>
<!-- Cart overlay -->
<div id="overlay-bg" onclick="closeCart()"></div>

<!-- ── Top Banner ────────────────────────── -->
<div class="top-banner">
  <div class="banner-content">
    <a href="#" class="banner-link"><span class="banner-text">ШИНЭ ЗАГВАР ИРЛЭЭ — ХУРДАН АВ</span></a>
  </div>
</div>

<!-- ── Sub Header ────────────────────────── -->
<div class="sub-header">
  <div class="container">
    <span class="welcome-text">Шоппид тавтай морил</span>
    <div class="sub-header-links">
      <a href="#">Хамтран ажиллах</a>
      <a href="#">Апп татах</a>
      <a href="#">Тусламж</a>
    </div>
  </div>
</div>

<!-- ── Main Header ────────────────────────── -->
<header class="main-header">
  <div class="container">
    <a href="index.php" class="logo"><span class="logo-text">shoppy</span></a>
    <div class="search-box">
      <select class="search-category" id="search-category">
        <option value="">Бүгд</option>
      </select>
      <input type="text" id="search-input" placeholder="Хайлт жишээ нь: гутал, цамц..." class="search-input"/>
      <button class="search-btn" onclick="doSearch()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </button>
    </div>
    <div class="header-actions">
      <!-- Сагс -->
      <a href="#" class="action-item" onclick="toggleCart(); return false">
        <div class="action-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
          <span class="cart-badge" id="cart-badge">0</span>
        </div>
        <span>Сагс</span>
      </a>
      <!-- Нэвтрэх / хэрэглэгч -->
      <div id="auth-area"></div>
    </div>
  </div>
</header>

<!-- ── Nav ────────────────────────────────── -->
<nav class="main-nav">
  <div class="container">
    <button class="category-btn">☰ Ангилал</button>
    <div class="nav-links" id="nav-cats"></div>
  </div>
</nav>

<!-- ── Hero ──────────────────────────────── -->
<section class="hero-section">
  <div class="container">
    <div class="hero-slider">
      <div class="hero-slide">
        <img src="https://ext.same-assets.com/3288583890/4213622936.jpeg" alt="Hero" onerror="this.style.background='linear-gradient(135deg,#9840b1,#e74c8c)';this.src=''"/>
        <div class="slide-overlay">
          <h2>Шинэ цуглуулга</h2>
          <h1>НАМАР 2025</h1>
          <p>Шинэ улиралд зориулсан загварлаг хувцас, гутал</p>
          <button class="slide-btn" onclick="document.getElementById('products-section').scrollIntoView({behavior:'smooth'})">Дэлгэрэнгүй →</button>
        </div>
      </div>
    </div>
    <div class="hero-side">
      <img src="https://ext.same-assets.com/3288583890/3374360827.jpeg" alt="side" onerror="this.style.background='linear-gradient(135deg,#e74c8c,#9840b1)';this.src=''"/>
    </div>
  </div>
</section>

<!-- ── Products ───────────────────────────── -->
<section class="tickets-section" id="products-section">
  <div class="container">
    <div class="section-header">
      <h2 id="products-title">Шинэ бараанууд</h2>
      <div style="display:flex;gap:10px;align-items:center">
        <select id="sort-select" onchange="loadProducts()" style="border:1px solid #ddd;border-radius:6px;padding:6px 10px;font-family:inherit;font-size:13px">
          <option value="newest">Шинэ эхэлж</option>
          <option value="price_asc">Үнэ: бага → их</option>
          <option value="price_desc">Үнэ: их → бага</option>
        </select>
        <a href="#" class="view-all">Бүгдийг үзэх</a>
      </div>
    </div>
    <div class="products-grid" id="products-grid">
      <?php for($i=0;$i<5;$i++): ?>
      <div class="product-card" style="height:280px;background:linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);background-size:200% 100%;animation:shimmer 1.5s infinite"></div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- ── Сагс panel ─────────────────────────── -->
<div class="cart-panel" id="cart-panel">
  <div class="cart-head">
    <h3>Миний сагс <span id="cart-count-head" style="font-size:14px;color:#9840b1;font-weight:400"></span></h3>
    <button onclick="closeCart()" style="background:none;border:none;font-size:24px;cursor:pointer;color:#999">✕</button>
  </div>
  <div class="cart-body" id="cart-body">
    <div class="cart-empty">Сагс хоосон байна</div>
  </div>
  <div class="cart-foot">
    <div class="cart-total">
      <span>Нийт:</span>
      <span id="cart-total-price">0 ₮</span>
    </div>
    <button class="cart-checkout-btn" onclick="openCheckout()">Захиалга хийх →</button>
  </div>
</div>

<!-- ── Login / Register Modal ─────────────── -->
<div class="modal-overlay" id="auth-modal">
  <div class="modal">
    <button class="modal-close" onclick="closeAuthModal()">✕</button>
    <div id="auth-modal-content"></div>
  </div>
</div>

<!-- ── Checkout Modal ─────────────────────── -->
<div class="checkout-overlay" id="checkout-overlay">
  <div class="checkout-modal" id="checkout-modal-content"></div>
</div>

<!-- ── Footer ────────────────────────────── -->
<footer class="main-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col">
        <div class="footer-logo"><span class="logo-text">shoppy</span><small>МОНГОЛЫН ДЭЛГҮҮР</small></div>
        <p>Веб үйлчилгээ</p>
      </div>
      <div class="footer-col"><h4>Тусламж</h4><a href="#">Түгээмэл асуулт</a><a href="#">Үйлчилгээний нөхцөл</a></div>
      <div class="footer-col"><h4>Холбоо барих</h4><a href="#">order@shoppy.mn</a></div>
    </div>
  </div>
</footer>
<div class="copyright">
  <div class="container">
    <p>shoppy.mn © 2025 Зохиогчийн эрх хамгаалагдсан</p>
    <p>Powered by <strong>PHP + MySQL</strong></p>
  </div>
</div>

<style>
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}

.password-field {
  position: relative;
  display: flex;
  align-items: center;
}

.password-field input {
  flex: 1;
  padding-right: 45px;
}

.password-toggle {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 5px;
  border-radius: 3px;
  transition: color 0.2s;
}

.password-toggle:hover {
  color: #333;
  background-color: rgba(0,0,0,0.05);
}
</style>

<script>
// ══════════════════════════════════════════════
//  Shoppy.mn — Front-end JS  (API холболттой)
// ══════════════════════════════════════════════

const API = {
  auth_status : 'auth_status.php',
  login       : 'login.php',
  register    : 'register.php',
  logout      : 'logout.php',
  products    : 'products.php',
  categories  : 'categories.php',
  cart        : 'cart.php',
  orders      : 'orders.php',
};

let state = {
  user       : <?= $current_user ? json_encode($current_user) : 'null' ?>,
  cartCount  : 0,
  cartItems  : [],
  cartTotal  : 0,
  categories : [],
  currentCat : null,
};

// ── Utility ──────────────────────────────────
function toast(msg, type='') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className   = 'toast show ' + type;
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.className = 'toast', 3000);
}

async function api(url, opts={}) {
  try {
    const res  = await fetch(url, { credentials:'include', ...opts });
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch(e) {
      console.error('JSON parse алдаа:', text.substring(0, 200));
      return { success: false, message: 'Серверийн алдаа гарлаа' };
    }
  } catch(e) {
    console.error('Fetch алдаа:', e);
    return { success: false, message: 'Холболтын алдаа гарлаа' };
  }
}

function fmt(n) {
  return Number(n).toLocaleString('mn-MN') + ' ₮';
}

// ── Auth ─────────────────────────────────────
function renderAuthArea() {
  const el = document.getElementById('auth-area');
  if (state.user) {
    el.innerHTML = `
      <div class="user-menu action-item">
        <div class="action-icon" style="background:#9840b1;border-radius:50%;color:#fff;font-weight:700;font-size:13px">
          ${state.user.name[0].toUpperCase()}
        </div>
        <span>${state.user.name.split(' ')[0]}</span>
        <div class="user-dropdown">
          <a href="#" onclick="viewOrders();return false">Миний захиалга</a>
          <button onclick="doLogout()">Гарах</button>
        </div>
      </div>`;
  } else {
    el.innerHTML = `
      <a href="#" class="action-item" onclick="openLogin();return false">
        <div class="action-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
        <span>Нэвтрэх</span>
      </a>`;
  }
}

function openLogin() {
  document.getElementById('auth-modal-content').innerHTML = `
    <h3>Нэвтрэх</h3>
    <label>И-мэйл</label>
    <input id="ml-email" type="email" placeholder="name@example.com"/>
    <label>Нууц үг</label>
    <div class="password-field">
      <input id="ml-pass" type="password" placeholder="Нууц үгээ оруулна уу"/>
      <button type="button" class="password-toggle" onclick="togglePassword('ml-pass')" title="Нууц үг харуулах/нуух">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </button>
    </div>
    <button class="modal-btn" id="login-btn" onclick="submitLogin()">Нэвтрэх</button>
    <div class="modal-switch">Бүртгэлгүй юу? <a onclick="openRegister()">Шинээр бүртгүүлэх</a></div>`;
  document.getElementById('auth-modal').classList.add('open');
  setTimeout(() => document.getElementById('ml-email')?.focus(), 100);
}

function openRegister() {
  document.getElementById('auth-modal-content').innerHTML = `
    <h3>Бүртгүүлэх</h3>
    <label>Овог нэр</label>
    <input id="mr-name" type="text" placeholder="Таны нэр"/>
    <label>И-мэйл</label>
    <input id="mr-email" type="email" placeholder="name@example.com"/>
    <label>Утас</label>
    <input id="mr-phone" type="tel" placeholder="99112233"/>
    <label>Нууц үг</label>
    <div class="password-field">
      <input id="mr-pass" type="password" placeholder="8-аас дээш тэмдэгт"/>
      <button type="button" class="password-toggle" onclick="togglePassword('mr-pass')" title="Нууц үг харуулах/нуух">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </button>
    </div>
    <button class="modal-btn" id="reg-btn" onclick="submitRegister()">Бүртгүүлэх</button>
    <div class="modal-switch">Бүртгэлтэй юу? <a onclick="openLogin()">Нэвтрэх</a></div>`;
  document.getElementById('auth-modal').classList.add('open');
}

function closeAuthModal() { document.getElementById('auth-modal').classList.remove('open'); }

function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const button = input.parentElement.querySelector('.password-toggle');
  const isPassword = input.type === 'password';
  
  input.type = isPassword ? 'text' : 'password';
  button.innerHTML = isPassword ? 
    `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
      <line x1="1" y1="1" x2="23" y2="23"/>
    </svg>` :
    `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
      <circle cx="12" cy="12" r="3"/>
    </svg>`;
}

async function submitLogin() {
  const btn   = document.getElementById('login-btn');
  const email = document.getElementById('ml-email').value.trim();
  const pass  = document.getElementById('ml-pass').value;
  if (!email || !pass) { toast('Бүх талбарыг бөглөнө үү','error'); return; }
  btn.textContent = 'Нэвтэрч байна...'; btn.disabled = true;
  const d = await api(API.login, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({email, password: pass})
  });
  btn.textContent = 'Нэвтрэх'; btn.disabled = false;
  if (d.success) {
    state.user = d.user;
    closeAuthModal(); renderAuthArea();
    toast('Тавтай морил, ' + d.user.name + '!', 'success');
    loadCart();
  } else {
    toast(d.message, 'error');
  }
}

async function submitRegister() {
  const btn  = document.getElementById('reg-btn');
  const name = document.getElementById('mr-name').value.trim();
  const email= document.getElementById('mr-email').value.trim();
  const phone= document.getElementById('mr-phone').value.trim();
  const pass = document.getElementById('mr-pass').value;
  btn.textContent = 'Бүртгэж байна...'; btn.disabled = true;
  const d = await api(API.register, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({name, email, phone, password: pass})
  });
  btn.textContent = 'Бүртгүүлэх'; btn.disabled = false;
  if (d.success) {
    state.user = d.user;
    closeAuthModal(); renderAuthArea();
    toast('Бүртгэл амжилттай!', 'success');
    loadCart();
  } else {
    toast(d.message, 'error');
  }
}

async function doLogout() {
  await api(API.logout);
  state.user = null; state.cartCount = 0; state.cartItems = [];
  renderAuthArea(); updateCartBadge(0);
  document.getElementById('cart-body').innerHTML = '<div class="cart-empty">Сагс хоосон байна</div>';
  toast('Амжилттай гарлаа');
}

// ── Categories ───────────────────────────────
async function loadCategories() {
  const d = await api(API.categories);
  if (!d.success) return;
  state.categories = d.categories;
  // Nav
  const nav = document.getElementById('nav-cats');
  nav.innerHTML = d.categories.map(c =>
    `<a href="#" onclick="filterCat(${c.id},'${c.name}');return false">${c.name}</a>`
  ).join('') + `<a href="#" onclick="filterCat(null,'Бүх бараа');return false">Бүгд</a>`;
  // Search select
  const sel = document.getElementById('search-category');
  d.categories.forEach(c => {
    const o = document.createElement('option');
    o.value = c.id; o.textContent = c.name;
    sel.appendChild(o);
  });
}

function filterCat(id, name) {
  state.currentCat = id;
  document.getElementById('products-title').textContent = name;
  loadProducts();
}

// ── Products ─────────────────────────────────
async function loadProducts() {
  const grid = document.getElementById('products-grid');
  const sort = document.getElementById('sort-select').value;
  let url = API.products + '?limit=20';
  if (state.currentCat) url += '&category_id=' + state.currentCat;
  const q = document.getElementById('search-input').value.trim();
  if (q) url += '&search=' + encodeURIComponent(q);

  const d = await api(url);
  if (!d.success) { grid.innerHTML = '<p>Алдаа гарлаа</p>'; return; }

  let prods = d.products;
  if (sort === 'price_asc')  prods.sort((a,b) => a.price - b.price);
  if (sort === 'price_desc') prods.sort((a,b) => b.price - a.price);

  if (!prods.length) {
    grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#999;padding:40px">Бараа олдсонгүй</p>';
    return;
  }

  grid.innerHTML = prods.map(p => `
    <div class="product-card" onclick="addToCart(${p.id},'${p.name.replace(/'/g,"\\'")}')">
      <div class="product-image">
        <img src="${p.image_url || ''}" alt="${p.name}" onerror="this.src='';this.parentElement.style.background='linear-gradient(135deg,#f0e8f2,#e8d8f0)'"/>
        <button class="add-to-cart-btn">+ Сагсанд нэмэх</button>
      </div>
      <h3>${p.name}</h3>
      <span class="price">${fmt(p.price)}</span>
    </div>`).join('');
}

function doSearch() {
  state.currentCat = null;
  const q = document.getElementById('search-input').value.trim();
  document.getElementById('products-title').textContent = q ? `"${q}" хайлтын үр дүн` : 'Бүх бараа';
  loadProducts();
}
document.getElementById('search-input').addEventListener('keydown', e => { if(e.key==='Enter') doSearch(); });

// ── Cart ─────────────────────────────────────
function updateCartBadge(n) {
  const b = document.getElementById('cart-badge');
  b.textContent = n;
  b.style.display = n > 0 ? 'flex' : 'none';
}

async function loadCart() {
  if (!state.user) return;
  const d = await api(API.cart);
  if (!d.success) return;
  state.cartItems = d.items;
  state.cartTotal = d.total;
  updateCartBadge(d.count);
  document.getElementById('cart-count-head').textContent = d.count ? `(${d.count})` : '';
  document.getElementById('cart-total-price').textContent = fmt(d.total);

  const body = document.getElementById('cart-body');
  if (!d.items.length) {
    body.innerHTML = '<div class="cart-empty">Сагс хоосон байна</div>';
    return;
  }
  body.innerHTML = d.items.map(i => `
    <div class="cart-item">
      <img src="${i.image_url||''}" alt="${i.name}" onerror="this.src='';this.style.background='#f0e8f2'"/>
      <div class="cart-item-info">
        <div class="cart-item-name">${i.name}</div>
        <div class="cart-item-price">${fmt(i.price)}</div>
        <div class="cart-item-qty">Тоо: ${i.quantity}</div>
      </div>
      <button class="cart-del" onclick="removeFromCart(${i.id})" title="Устгах">✕</button>
    </div>`).join('');
}

async function addToCart(product_id, name) {
  if (!state.user) { openLogin(); toast('Нэвтэрч орно уу', 'error'); return; }
  const d = await api(API.cart, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({product_id, quantity: 1})
  });
  if (d.success) { toast(name + ' сагсанд нэмэгдлээ', 'success'); loadCart(); }
  else toast(d.message, 'error');
}

async function removeFromCart(id) {
  const d = await api(API.cart + '?id=' + id, { method:'DELETE' });
  if (d.success) loadCart();
  else toast(d.message, 'error');
}

function toggleCart() {
  const p = document.getElementById('cart-panel');
  const o = document.getElementById('overlay-bg');
  const open = !p.classList.contains('open');
  p.classList.toggle('open', open);
  o.classList.toggle('open', open);
  if (open) loadCart();
}
function closeCart() {
  document.getElementById('cart-panel').classList.remove('open');
  document.getElementById('overlay-bg').classList.remove('open');
}

// ── Checkout ─────────────────────────────────
function openCheckout() {
  if (!state.user) { openLogin(); return; }
  if (!state.cartItems.length) { toast('Сагс хоосон байна', 'error'); return; }
  closeCart();

  const itemsHtml = state.cartItems.map(i =>
    `<div class="co-item"><span>${i.name} ×${i.quantity}</span><strong>${fmt(i.price * i.quantity)}</strong></div>`
  ).join('');

  document.getElementById('checkout-modal-content').innerHTML = `
    <div class="co-form">
      <h3>Захиалга хийх</h3>
      <label>Хүлээн авагч</label>
      <input id="co-name" type="text" value="${state.user.name}" placeholder="Нэр"/>
      <label>Утас</label>
      <input id="co-phone" type="tel" placeholder="99112233"/>
      <label>Хаяг</label>
      <textarea id="co-addr" rows="3" placeholder="Дүүрэг, хороо, байр, тоот"></textarea>
      <label>Төлбөрийн арга</label>
      <div class="pay-opts">
        <div class="pay-opt active" onclick="selectPay(this,'Карт')">💳 Карт</div>
        <div class="pay-opt" onclick="selectPay(this,'QPay')">📱 QPay</div>
        <div class="pay-opt" onclick="selectPay(this,'Storepay')">🛍 Storepay</div>
        <div class="pay-opt" onclick="selectPay(this,'Данс')">🏦 Данс</div>
      </div>
      <input type="hidden" id="co-method" value="Карт"/>
      <button class="modal-btn" id="co-btn" onclick="submitOrder()">Захиалга баталгаажуулах</button>
    </div>
    <div class="co-summary">
      <h4>Захиалгын дүн</h4>
      ${itemsHtml}
      <div class="co-item"><span>Хүргэлт</span><strong>5,000 ₮</strong></div>
      <div class="co-grand"><span>Нийт:</span><span>${fmt(state.cartTotal + 5000)}</span></div>
    </div>`;

  document.getElementById('checkout-overlay').classList.add('open');
}

function selectPay(el, method) {
  document.querySelectorAll('.pay-opt').forEach(o => o.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('co-method').value = method;
}

async function submitOrder() {
  const btn     = document.getElementById('co-btn');
  const name    = document.getElementById('co-name').value.trim();
  const phone   = document.getElementById('co-phone').value.trim();
  const addr    = document.getElementById('co-addr').value.trim();
  const method  = document.getElementById('co-method').value;

  if (!name || !phone || !addr) { toast('Бүх талбарыг бөглөнө үү', 'error'); return; }

  btn.innerHTML = 'Баталгаажуулж байна <span class="loading-dots"></span>';
  btn.disabled  = true;

  const d = await api(API.orders, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({receiver_name:name, phone, address:addr, payment_method:method})
  });

  btn.innerHTML = 'Захиалга баталгаажуулах';
  btn.disabled  = false;

  if (d.success) {
    document.getElementById('checkout-modal-content').innerHTML = `
      <div class="order-success" style="grid-column:1/-1">
        <div class="checkmark">✅</div>
        <h3>Захиалга амжилттай!</h3>
        <p>Захиалгын дугаар: <strong>#${d.order_id}</strong></p>
        <p>Нийт дүн: <strong>${fmt(d.total)}</strong></p>
        <p style="margin-top:10px">Таны и-мэйл хаяг руу мэдэгдэл очно.</p>
        <button class="modal-btn" style="margin-top:24px" onclick="closeCheckout()">Нүүр хуудас руу</button>
      </div>`;
    loadCart();
    toast('Захиалга амжилттай!', 'success');
  } else {
    toast(d.message, 'error');
  }
}

function closeCheckout() {
  document.getElementById('checkout-overlay').classList.remove('open');
}
document.getElementById('checkout-overlay').addEventListener('click', function(e) {
  if (e.target === this) closeCheckout();
});

// ── Orders ───────────────────────────────────
async function viewOrders() {
  const d = await api(API.orders);
  const section = document.getElementById('products-section');
  if (!d.success) { toast('Алдаа гарлаа', 'error'); return; }

  const html = d.orders.length
    ? d.orders.map(o => `
        <div class="order-row">
          <div class="order-row-head">
            <strong>Захиалга #${o.id}</strong>
            <span class="order-status ${o.status}">${o.status === 'pending' ? 'Хүлээгдэж байна' : 'Баталгаажсан'}</span>
          </div>
          <div style="font-size:13px;color:#6d6272">
            ${new Date(o.created_at).toLocaleDateString('mn-MN')} · ${fmt(o.total_price)} · ${o.payment_method}
          </div>
          <div style="font-size:13px;margin-top:4px">📍 ${o.address}</div>
        </div>`).join('')
    : '<p style="text-align:center;color:#999;padding:40px">Захиалга байхгүй байна</p>';

  section.querySelector('.container').innerHTML = `
    <div class="section-header">
      <h2>Миний захиалгууд</h2>
      <a href="#" onclick="resetView();return false" class="view-all">← Буцах</a>
    </div>
    <div class="orders-list">${html}</div>`;
}

function resetView() {
  document.getElementById('products-section').querySelector('.container').innerHTML = `
    <div class="section-header">
      <h2 id="products-title">Шинэ бараанууд</h2>
      <div style="display:flex;gap:10px;align-items:center">
        <select id="sort-select" onchange="loadProducts()" style="border:1px solid #ddd;border-radius:6px;padding:6px 10px;font-family:inherit;font-size:13px">
          <option value="newest">Шинэ эхэлж</option>
          <option value="price_asc">Үнэ: бага → их</option>
          <option value="price_desc">Үнэ: их → бага</option>
        </select>
        <a href="#" class="view-all">Бүгдийг үзэх</a>
      </div>
    </div>
    <div class="products-grid" id="products-grid"></div>`;
  loadProducts();
}

// ── Init ─────────────────────────────────────
(async function init() {
  renderAuthArea();
  await loadCategories();
  await loadProducts();
  if (state.user) loadCart();
})();
</script>
</body>
</html>
