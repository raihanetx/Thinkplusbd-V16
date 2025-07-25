<?php
session_start();
// Dummy admin check
if (!isset($_SESSION['admin'])) {
    // header('Location: admin_login.php');
    // exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Coupons</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="logo-admin">
                <img src="https://i.postimg.cc/4NtztqPt/IMG-20250603-130207-removebg-preview-1.png" alt="THINK PLUS BD Logo">
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="admin_dashboard.php"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a></li>
                    <li><a href="admin_dashboard.php?page=categories"><i class="fas fa-tags"></i> <span>Manage Categories</span></a></li>
                    <li><a href="admin_dashboard.php?page=edit_products"><i class="fas fa-edit"></i> <span>Edit Products</span></a></li>
                    <li><a href="admin_reviews.php"><i class="fas fa-star"></i> <span>Manage Reviews</span></a></li>
                    <li><a href="admin_coupons.php" class="active"><i class="fas fa-tags"></i> <span>Manage Coupons</span></a></li>
                    <li><a href="product_code_generator.html" target="_blank"><i class="fas fa-plus-circle"></i> <span>Add Product Helper</span></a></li>
                    <li><a href="admin_dashboard.php?logout=1"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main-content" id="adminMainContent">
            <header class="admin-topbar">
                <div style="display:flex; align-items:center;">
                    <i class="fas fa-bars sidebar-toggle" id="sidebarToggle"></i>
                    <h1>Manage Coupons</h1>
                </div>
                <a href="admin_dashboard.php?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            <div class="admin-page-content">
                <div class="content-card">
                    <h2 class="card-title">All Coupons</h2>
                    <div id="coupons-container"></div>
                </div>
                <div class="content-card">
                    <h2 class="card-title">Create Coupon</h2>
                    <div class="coupon-templates">
                        <div class="coupon-template" data-code="SAVE10" data-discount="10" data-type="percentage">
                            <h3>10% Off</h3>
                            <p>For all products</p>
                        </div>
                        <div class="coupon-template" data-code="50OFF" data-discount="50" data-type="fixed">
                            <h3>50 Taka Off</h3>
                            <p>For all products</p>
                        </div>
                        <div class="coupon-template" data-code="NEWUSER" data-discount="15" data-type="percentage">
                            <h3>15% Off</h3>
                            <p>For new users</p>
                        </div>
                    </div>
                    <form id="create-coupon-form">
                        <div class="form-group">
                            <label for="coupon-code">Coupon Code</label>
                            <input type="text" id="coupon-code" required>
                        </div>
                        <div class="form-group">
                            <label for="discount-type">Discount Type</label>
                            <select id="discount-type">
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="discount-value">Discount Value</label>
                            <input type="number" id="discount-value" required>
                        </div>
                        <div class="form-group">
                            <label for="product-ids">Apply to Product IDs (comma-separated)</label>
                            <input type="text" id="product-ids">
                        </div>
                        <div class="form-group">
                            <label for="category">Apply to Category</label>
                            <input type="text" id="category">
                        </div>
                        <button type="submit">Create Coupon</button>
                        <button type="button" id="save-and-create-another">Save & Create Another</button>
                    </form>
                    <div class="coupon-preview">
                        <h3>Coupon Preview</h3>
                        <div class="coupon-preview-card">
                            <h4 id="preview-code">COUPONCODE</h4>
                            <p><span id="preview-discount">10%</span> off</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="admin_dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('get_coupons.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('coupons-container');
                    if (data.error) {
                        container.innerHTML = `<p>${data.error}</p>`;
                        return;
                    }
                    if (data.length === 0) {
                        container.innerHTML = '<p>No coupons to display.</p>';
                        return;
                    }
                    let html = '<table>';
                    html += '<tr><th>Code</th><th>Discount</th><th>Product IDs</th><th>Category</th><th>Action</th></tr>';
                    data.forEach(coupon => {
                        html += `
                            <tr>
                                <td>${coupon.code}</td>
                                <td>${coupon.discount_percentage}%</td>
                                <td>${coupon.product_ids ? coupon.product_ids.join(', ') : 'All'}</td>
                                <td>${coupon.category || 'All'}</td>
                                <td>
                                    <button onclick="deleteCoupon('${coupon.code}')">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                    html += '</table>';
                    container.innerHTML = html;
                });

            document.querySelectorAll('.coupon-template').forEach(template => {
                template.addEventListener('click', function() {
                    document.getElementById('coupon-code').value = this.dataset.code;
                    document.getElementById('discount-type').value = this.dataset.type;
                    document.getElementById('discount-value').value = this.dataset.discount;
                });
            });

            const createCouponForm = document.getElementById('create-coupon-form');
            const couponCodeInput = document.getElementById('coupon-code');
            const discountTypeInput = document.getElementById('discount-type');
            const discountValueInput = document.getElementById('discount-value');
            const previewCode = document.getElementById('preview-code');
            const previewDiscount = document.getElementById('preview-discount');

            function updatePreview() {
                previewCode.textContent = couponCodeInput.value || 'COUPONCODE';
                const discountType = discountTypeInput.value;
                const discountValue = discountValueInput.value;
                if (discountType === 'percentage') {
                    previewDiscount.textContent = `${discountValue || 0}%`;
                } else {
                    previewDiscount.textContent = `৳${discountValue || 0}`;
                }
            }

            couponCodeInput.addEventListener('input', updatePreview);
            discountTypeInput.addEventListener('change', updatePreview);
            discountValueInput.addEventListener('input', updatePreview);

            function validateForm() {
                if (couponCodeInput.value.trim() === '') {
                    alert('Coupon code is required.');
                    return false;
                }
                if (discountValueInput.value.trim() === '' || isNaN(discountValueInput.value)) {
                    alert('Discount value is required and must be a number.');
                    return false;
                }
                return true;
            }

            function submitForm(reload = true) {
                if (!validateForm()) return;

                const code = couponCodeInput.value;
                const discount_type = discountTypeInput.value;
                const discount_value = discountValueInput.value;
                const product_ids = document.getElementById('product-ids').value.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
                const category = document.getElementById('category').value;

                fetch('create_coupon.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        code,
                        discount_type,
                        discount_value,
                        product_ids: product_ids.length > 0 ? product_ids : null,
                        category: category || null
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (reload) {
                            location.reload();
                        } else {
                            createCouponForm.reset();
                            updatePreview();
                            alert('Coupon created successfully!');
                        }
                    } else {
                        alert('Failed to create coupon.');
                    }
                });
            }

            createCouponForm.addEventListener('submit', function(event) {
                event.preventDefault();
                submitForm();
            });

            document.getElementById('save-and-create-another').addEventListener('click', function() {
                submitForm(false);
            });
        });

        function deleteCoupon(couponCode) {
            fetch('delete_coupon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ code: couponCode }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete coupon.');
                }
            });
        }
    </script>
</body>
</html>
