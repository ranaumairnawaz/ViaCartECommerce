<?php
// cart.php - Shopping Cart Overview Page

$current_page_key = 'cart';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:40px; padding-bottom:80px;">
    <h1 style="font-size:2.2rem; font-weight:800; margin-bottom:24px;">Your Shopping Cart</h1>

    <div id="cartContentGrid" class="layout-cart">
        <!-- Cart Table -->
        <div class="cart-table-wrap">
            <table class="cart-table responsive-stack">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="cartTableBody">
                    <!-- JavaScript dynamically populates cart items -->
                </tbody>
            </table>
        </div>

        <!-- Summary Card -->
        <div class="cart-summary-card">
            <h3 style="font-size:1.3rem; font-weight:800; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:12px;">Order Summary</h3>
            <div class="summary-row">
                <span>Items Subtotal</span>
                <span id="summarySubtotal">Rs. 0</span>
            </div>
            <div class="summary-row">
                <span>Estimated Shipping</span>
                <span id="summaryShipping">Rs. 0</span>
            </div>
            <div class="summary-row total">
                <span>Total Amount</span>
                <span id="summaryTotal">Rs. 0</span>
            </div>
            <a href="checkout" class="btn-primary" style="width:100%; text-align:center; justify-content:center; margin-top:20px; padding:14px;">
                Proceed to Checkout &rarr;
            </a>
            <a href="shop" class="btn-outline" style="display:flex; align-items:center; justify-content:center; width:100%; text-align:center; margin-top:10px;">
                Continue Shopping
            </a>
        </div>
    </div>

    <!-- Empty Cart Fallback -->
    <div id="emptyCartView" class="is-hidden" style="background:#fff; border:1px dashed var(--border-color); border-radius:var(--radius-md); padding:80px 20px; text-align:center;">
        <div style="font-size:4rem; margin-bottom:16px;"><i class="fa-solid fa-cart-shopping"></i></div>
        <h2 style="font-size:1.8rem; font-weight:800;">Your Cart is Empty</h2>
        <p style="color:var(--text-muted); margin-top:8px; margin-bottom:24px;">Explore our luxury handbag collection and add your favorite items!</p>
        <a href="shop" class="btn-primary">Browse Shop Collection &rarr;</a>
    </div>
</div>

<script>
function renderCartPage() {
    const items = CartEngine.getCart();
    const tableBody = document.getElementById('cartTableBody');
    const contentGrid = document.getElementById('cartContentGrid');
    const emptyView = document.getElementById('emptyCartView');

    if (items.length === 0) {
        contentGrid.classList.add('is-hidden');
        emptyView.classList.remove('is-hidden');
        return;
    }

    contentGrid.classList.remove('is-hidden');
    emptyView.classList.add('is-hidden');

    let html = '';
    let subtotal = 0;

    items.forEach(item => {
        const itemSubtotal = item.price * item.qty;
        subtotal += itemSubtotal;

        if (item.type === 'combo') {
            const bundleList = (item.items || []).map(i => i.name + (i.qty > 1 ? ' ×' + i.qty : '')).join(', ');
            html += `
                <tr>
                    <td data-label="Product">
                        <div style="display:flex; align-items:center; gap:16px;">
                            <img src="${item.image}" alt="${item.name}" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid #e2e8f0;">
                            <div>
                                <strong style="display:block; font-size:1rem; color:var(--secondary-color);"><i class="fa-solid fa-gift"></i> ${item.name}</strong>
                                <span style="font-size:0.8rem; color:var(--text-muted);">${bundleList}</span>
                            </div>
                        </div>
                    </td>
                    <td data-label="Price">Rs. ${item.price.toLocaleString()}</td>
                    <td data-label="Quantity">
                        <div class="qty-control" style="margin-left:auto;">
                            <button class="qty-btn" onclick="CartEngine.updateQty('${item.id}', -1); renderCartPage();">-</button>
                            <span>${item.qty}</span>
                            <button class="qty-btn" onclick="CartEngine.updateQty('${item.id}', 1); renderCartPage();">+</button>
                        </div>
                    </td>
                    <td data-label="Subtotal" style="font-weight:700;">Rs. ${itemSubtotal.toLocaleString()}</td>
                    <td data-label="Action">
                        <button onclick="CartEngine.removeItem('${item.id}'); renderCartPage();" style="background:none; border:none; color:#ef4444; font-weight:600; cursor:pointer;">Remove</button>
                    </td>
                </tr>
            `;
            return;
        }

        html += `
            <tr>
                <td data-label="Product">
                    <div style="display:flex; align-items:center; gap:16px;">
                        <img src="${item.image}" alt="${item.name}" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid #e2e8f0;">
                        <div>
                            <strong style="display:block; font-size:1rem; color:var(--secondary-color);">${item.name}</strong>
                        </div>
                    </div>
                </td>
                <td data-label="Price">Rs. ${item.price.toLocaleString()}</td>
                <td data-label="Quantity">
                    <div class="qty-control" style="margin-left:auto;">
                        <button class="qty-btn" onclick="CartEngine.updateQty(${item.id}, -1); renderCartPage();">-</button>
                        <span>${item.qty}</span>
                        <button class="qty-btn" onclick="CartEngine.updateQty(${item.id}, 1); renderCartPage();">+</button>
                    </div>
                </td>
                <td data-label="Subtotal" style="font-weight:700;">Rs. ${itemSubtotal.toLocaleString()}</td>
                <td data-label="Action">
                    <button onclick="CartEngine.removeItem(${item.id}); renderCartPage();" style="background:none; border:none; color:#ef4444; font-weight:600; cursor:pointer;">Remove</button>
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = html;

    const shipping = subtotal > 5000 ? 0 : 250;
    const total = subtotal + shipping;

    document.getElementById('summarySubtotal').textContent = 'Rs. ' + subtotal.toLocaleString();
    document.getElementById('summaryShipping').textContent = shipping === 0 ? 'FREE' : 'Rs. ' + shipping.toLocaleString();
    document.getElementById('summaryTotal').textContent = 'Rs. ' + total.toLocaleString();
}

document.addEventListener('DOMContentLoaded', renderCartPage);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
