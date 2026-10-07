<?php
// admin/admin_footer.php - Shared Admin Layout Footer

$active_tab = isset($admin_page) ? $admin_page : 'dashboard';
?>
    </main>
</div>

<!-- Mobile Bottom Navigation (Admin, Native App Style) -->
<nav class="admin-bottom-nav">
    <a href="index" class="bnav-item <?= $active_tab === 'dashboard' ? 'active' : '' ?>">
        <span class="bnav-icon"><i class="fa-solid fa-chart-line"></i></span>
        <span>Home</span>
    </a>
    <a href="products" class="bnav-item <?= $active_tab === 'products' ? 'active' : '' ?>">
        <span class="bnav-icon"><i class="fa-solid fa-bag-shopping"></i></span>
        <span>Products</span>
    </a>
    <a href="orders" class="bnav-item <?= $active_tab === 'orders' ? 'active' : '' ?>">
        <span class="bnav-icon"><i class="fa-solid fa-box"></i></span>
        <span>Orders</span>
    </a>
    <a href="categories" class="bnav-item <?= $active_tab === 'categories' ? 'active' : '' ?>">
        <span class="bnav-icon"><i class="fa-solid fa-folder"></i></span>
        <span>Categories</span>
    </a>
    <a href="javascript:void(0);" onclick="AdminDrawer.toggle();" class="bnav-item">
        <span class="bnav-icon"><i class="fa-solid fa-bars"></i></span>
        <span>More</span>
    </a>
</nav>

<script src="../assets/js/app.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/app.js'); ?>"></script>
</body>
</html>
