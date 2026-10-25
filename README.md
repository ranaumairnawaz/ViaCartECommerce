# 🛒 ViaCart — Modern React E-Commerce Platform (with Zero-Config PHP/SQLite Backend)

<p align="center">
  <img src="https://img.shields.io/badge/React-18.x-61DAFB?style=for-the-badge&logo=react" alt="React">
  <img src="https://img.shields.io/badge/TypeScript-5.x-3178C6?style=for-the-badge&logo=typescript" alt="TypeScript">
  <img src="https://img.shields.io/badge/PHP-Backend-777BB4?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/SQLite-Zero--Config-003B57?style=for-the-badge&logo=sqlite" alt="SQLite">
  <img src="https://img.shields.io/badge/Vite-Bundler-646CFF?style=for-the-badge&logo=vite" alt="Vite">
</p>

---

## 📖 What is ViaCart?

**ViaCart** is a modern **full-stack e-commerce shopping platform** that pairs a reactive **React & TypeScript** storefront with a **zero-configuration PHP & SQLite backend**.

It offers businesses the speed, fluidity, and aesthetic appeal of a modern Single Page Application (SPA), while retaining the extreme portability and low hosting costs of plain PHP and SQLite. It can be deployed on any standard shared hosting (e.g. cPanel, Hostinger, Namecheap) without requiring Node.js servers or separate MySQL database provisioning.

---

## 🎯 Why ViaCart? Problem & Solution

| The Challenge | How ViaCart Solves It |
| :--- | :--- |
| **High Hosting Costs for Modern SPAs:** Most modern React stores require dedicated Node.js runtimes, cloud databases, and complex CI/CD pipelines. | Features a self-contained **PHP & SQLite API backend** (`deploy/api/`) that runs instantly on any shared host with zero setup or monthly database costs. |
| **Complex Database Configurations:** Creating databases, setting up permissions, and running migrations can be daunting for non-technical clients. | Uses an automated SQLite database (`database.sqlite`) that self-initializes the moment the application is launched, holding all products, orders, and settings in a single portable file. |
| **Sluggish Traditional Storefronts:** Legacy PHP e-commerce systems reload the entire web page on every filter and cart action. | Built with **React and Vite**, delivering instantaneous client-side filtering, animated cart drawers, and frictionless checkout flows. |

---

## 🌟 Key Features

### 🛍️ Customer Storefront Experience
- **Interactive Product Catalog:** Dynamic search, category filters, price sorting, and detailed product modal views.
- **Fluid Shopping Cart Drawer:** Add items to cart with instant subtotal calculation, quantity adjustments, and local persistence.
- **Streamlined Checkout:** Clean single-page checkout form with shipping address validation and payment selection.
- **Order Confirmation & Tracking:** Instant order receipt generation and unique order reference tracking.

### 🛡️ Merchant & Admin Capabilities
- **Product & Inventory Management:** Add, update, and remove products with title, description, category, price, stock, and media uploads.
- **Order Management:** View incoming customer orders, review customer delivery notes, and track order fulfillment statuses.
- **Category & Taxonomy Control:** Manage product categories on the fly with real-time storefront synchronization.

### 📁 Dual-Layer Architecture (Source + Ready-to-Deploy)
The repository is thoughtfully organized into two distinct workspaces:
1. `frontend-source/`: Complete, unminified React + TypeScript source code for developers who want to customize styles, components, or layout.
2. `deploy/`: Pre-compiled production bundle with the PHP/SQLite API backend, ready to upload directly to any web server root.

---

## 🛠️ Technical Stack

- **Frontend:** React 18, TypeScript, Vite, Tailwind CSS, Lucide Icons
- **Backend:** Lightweight Vanilla PHP (RESTful JSON API)
- **Database:** SQLite 3 (Automatic initialization via PDO, zero external server needed)
- **Deployment Compatibility:** Apache (with `.htaccess`), Nginx, Shared Hosting (cPanel), VPS

---

## 📦 Project Structure

```
ViaCart/
├── frontend-source/              ← Editable React + TypeScript source application
│   ├── src/                      ← Components, Pages, State, and Styles
│   ├── package.json              ← NPM dependencies
│   ├── vite.config.ts            ← Vite build configuration
│   └── tsconfig.json             ← TypeScript configuration
│
└── deploy/                       ← Production-ready bundle (Drag-and-drop to host)
    ├── index.html                ← Built Single Page Application
    ├── assets/                   ← Compiled JavaScript & CSS bundles
    ├── api/                      ← PHP + SQLite API backend
    │   ├── data/                 ← Stores database.sqlite (Auto-created)
    │   └── endpoints/            ← Products, Orders, Categories, Settings
    ├── uploads/                  ← Product images & attachments
    └── .htaccess                 ← Clean URL rewrite & security rules
```

---

## 🚀 Quick Start Guide

### Option A: Direct Deployment to Shared Hosting (Easiest)
1. Upload the entire contents of the `deploy/` directory to your web root (`public_html/`).
2. Ensure write permissions (`755` or `775`) on `deploy/api/data/` and `deploy/uploads/`.
3. Open your domain in the browser — the SQLite database will self-provision automatically!

### Option B: Local Frontend Customization & Build
```bash
# Navigate to the frontend source
cd frontend-source

# Install dependencies
npm install

# Start local development server
npm run dev

# Build production assets for deployment
npm run build
```
*(The generated `dist/` files can then be copied into `deploy/`)*

---

## 👨‍💻 Author
**Rana Umair Nawaz**  
- [GitHub Profile](https://github.com/ranaumairnawaz)  
- [LinkedIn Profile](https://linkedin.com/in/ranaumairnawaz)
