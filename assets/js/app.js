// ViaCart - Core JavaScript & Shopping Cart Engine

const CartEngine = {
  STORAGE_KEY: 'viacart_cart_items',

  getCart() {
    try {
      const data = localStorage.getItem(this.STORAGE_KEY);
      return data ? JSON.parse(data) : [];
    } catch (e) {
      console.error('Error reading cart', e);
      return [];
    }
  },

  saveCart(cart) {
    try {
      localStorage.setItem(this.STORAGE_KEY, JSON.stringify(cart));
      this.updateBadge();
    } catch (e) {
      console.error('Error saving cart', e);
    }
  },

  addItem(product, qty = 1) {
    let cart = this.getCart();
    const existingIndex = cart.findIndex(item => item.id == product.id);

    if (existingIndex > -1) {
      cart[existingIndex].qty += parseInt(qty);
    } else {
      cart.push({
        id: product.id,
        name: product.name,
        price: parseFloat(product.price),
        image: product.image,
        category: product.category || '',
        qty: parseInt(qty)
      });
    }

    this.saveCart(cart);
    this.showToast(`"${product.name}" added to cart!`);
  },

  buyNow(product, qty = 1) {
    const item = {
      id: product.id,
      name: product.name,
      price: parseFloat(product.price),
      image: product.image,
      category: product.category || '',
      qty: parseInt(qty)
    };
    localStorage.setItem('buy_now_item', JSON.stringify([item]));
    window.location.href = 'checkout?buy_now=true';
  },

  addComboItem(combo, qty = 1) {
    let cart = this.getCart();
    const comboCartId = 'combo_' + combo.id;
    const existingIndex = cart.findIndex(item => item.id == comboCartId);

    if (existingIndex > -1) {
      cart[existingIndex].qty += parseInt(qty);
    } else {
      cart.push({
        id: comboCartId,
        type: 'combo',
        name: combo.name,
        price: parseFloat(combo.price),
        image: combo.image,
        items: combo.items || [],
        qty: parseInt(qty)
      });
    }

    this.saveCart(cart);
    this.showToast(`"${combo.name}" combo added to cart!`);
  },

  updateQty(productId, delta) {
    let cart = this.getCart();
    const index = cart.findIndex(item => item.id == productId);

    if (index > -1) {
      cart[index].qty += delta;
      if (cart[index].qty <= 0) {
        cart.splice(index, 1);
      }
      this.saveCart(cart);
    }
  },

  removeItem(productId) {
    let cart = this.getCart().filter(item => item.id != productId);
    this.saveCart(cart);
  },

  clearCart() {
    localStorage.removeItem(this.STORAGE_KEY);
    this.updateBadge();
  },

  getTotalCount() {
    return this.getCart().reduce((sum, item) => sum + item.qty, 0);
  },

  getTotalPrice() {
    return this.getCart().reduce((sum, item) => sum + (item.price * item.qty), 0);
  },

  updateBadge() {
    const badges = document.querySelectorAll('.cart-badge');
    const count = this.getTotalCount();
    badges.forEach(badge => {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'inline-block' : 'none';
    });
  },

  showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'app-toast';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('show');
    }, 10);

    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }
};

// Public Site Sidebar Drawer Controls (Mobile)
const PublicDrawer = {
  open() {
    const sidebar = document.querySelector('.public-sidebar');
    const overlay = document.querySelector('.public-sidebar-overlay');
    if (sidebar) sidebar.classList.add('open');
    if (overlay) overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  },
  close() {
    const sidebar = document.querySelector('.public-sidebar');
    const overlay = document.querySelector('.public-sidebar-overlay');
    if (sidebar) sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
    document.body.style.overflow = '';
  },
  toggle() {
    const sidebar = document.querySelector('.public-sidebar');
    if (sidebar && sidebar.classList.contains('open')) {
      this.close();
    } else {
      this.open();
    }
  }
};

// Admin Sidebar Drawer Controls (Mobile)
const AdminDrawer = {
  open() {
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.querySelector('.admin-sidebar-overlay');
    if (sidebar) sidebar.classList.add('open');
    if (overlay) overlay.classList.add('open');
  },
  close() {
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.querySelector('.admin-sidebar-overlay');
    if (sidebar) sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
  },
  toggle() {
    const sidebar = document.querySelector('.admin-sidebar');
    if (sidebar && sidebar.classList.contains('open')) {
      this.close();
    } else {
      this.open();
    }
  }
};

// Countdown Timers for Deal Expiry (used on deals.php)
function initCountdownTimers() {
  const timers = document.querySelectorAll('[data-countdown]');
  if (timers.length === 0) return;

  function tick() {
    timers.forEach(el => {
      const endTime = new Date(el.getAttribute('data-countdown')).getTime();
      const now = Date.now();
      const diff = endTime - now;

      if (diff <= 0) {
        el.textContent = 'Deal Ended';
        el.classList.add('countdown-ended');
        return;
      }

      const days = Math.floor(diff / 86400000);
      const hours = Math.floor((diff % 86400000) / 3600000);
      const mins = Math.floor((diff % 3600000) / 60000);
      const secs = Math.floor((diff % 60000) / 1000);

      let parts = [];
      if (days > 0) parts.push(days + 'd');
      parts.push(String(hours).padStart(2, '0') + 'h');
      parts.push(String(mins).padStart(2, '0') + 'm');
      parts.push(String(secs).padStart(2, '0') + 's');

      el.textContent = parts.join(' ');
    });
  }

  tick();
  setInterval(tick, 1000);
}

// Initialize Cart Badge on Page Load
document.addEventListener('DOMContentLoaded', () => {
  CartEngine.updateBadge();
  initCountdownTimers();

  // Close mobile sidebar when a nav link is tapped
  document.querySelectorAll('.public-sidebar-menu a').forEach(link => {
    link.addEventListener('click', (e) => {
      if (link.getAttribute('href') === 'javascript:void(0)') return;
      PublicDrawer.close();
    });
  });

  // Close mobile sidebar if the viewport grows back to desktop size
  window.addEventListener('resize', () => {
    if (window.innerWidth > 900) PublicDrawer.close();
  });

  // Image Upload Live Preview Helper
  const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
  fileInputs.forEach(input => {
    input.addEventListener('change', (e) => {
      const previewId = input.getAttribute('data-preview');
      const previewEl = document.getElementById(previewId);
      if (previewEl && e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = (evt) => {
          previewEl.src = evt.target.result;
          previewEl.style.display = 'block';
        };
        reader.readAsDataURL(e.target.files[0]);
      }
    });
  });
});
