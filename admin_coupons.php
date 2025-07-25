<?php
// Note: This file is now included in admin_dashboard.php
// The session check and HTML structure are handled by the parent file.
?>
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
            <div style="display: flex;">
                <input type="text" id="coupon-code" style="flex-grow: 1;">
                <button type="button" id="generate-random-code">Generate Random</button>
            </div>
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
            <label for="product-ids">Apply to Products</label>
            <select id="product-ids" multiple style="width: 100%;"></select>
        </div>
        <div class="form-group">
            <label for="category-ids">Apply to Categories</label>
            <select id="category-ids" multiple style="width: 100%;"></select>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch and display existing coupons
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
                        <td>${coupon.discount_value}${coupon.discount_type === 'percentage' ? '%' : ' Taka'}</td>
                        <td>${coupon.product_ids ? coupon.product_ids.join(', ') : 'All'}</td>
                        <td>${coupon.category ? coupon.category.join(', ') : 'All'}</td>
                        <td>
                            <button onclick="deleteCoupon('${coupon.code}')">Delete</button>
                        </td>
                    </tr>
                `;
            });
            html += '</table>';
            container.innerHTML = html;
        });

    // Populate product and category dropdowns
    const productIdsSelect = document.getElementById('product-ids');
    const categoryIdsSelect = document.getElementById('category-ids');

    fetch('get_products.php')
        .then(response => response.json())
        .then(products => {
            products.forEach(product => {
                const option = new Option(product.name, product.id);
                productIdsSelect.add(option);
            });
        });

    fetch('get_categories.php')
        .then(response => response.json())
        .then(categories => {
            categories.forEach(category => {
                const option = new Option(category.name, category.name);
                categoryIdsSelect.add(option);
            });
        });

    // Coupon template functionality
    document.querySelectorAll('.coupon-template').forEach(template => {
        template.addEventListener('click', function() {
            document.getElementById('coupon-code').value = this.dataset.code;
            document.getElementById('discount-type').value = this.dataset.type;
            document.getElementById('discount-value').value = this.dataset.discount;
            updatePreview();
        });
    });

    // Random code generation
    document.getElementById('generate-random-code').addEventListener('click', function() {
        const randomCode = Math.random().toString(36).substring(2, 10).toUpperCase();
        document.getElementById('coupon-code').value = randomCode;
        updatePreview();
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
        const product_ids = Array.from(productIdsSelect.selectedOptions).map(option => option.value);
        const category_ids = Array.from(categoryIdsSelect.selectedOptions).map(option => option.value);

        fetch('create_coupon.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                code,
                discount_type,
                discount_value,
                product_ids: product_ids.length > 0 ? product_ids : null,
                category: category_ids.length > 0 ? category_ids : null
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
                    // Re-fetch coupons to show the newly created one
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
                                        <td>${coupon.discount_value}${coupon.discount_type === 'percentage' ? '%' : ' Taka'}</td>
                                        <td>${coupon.product_ids ? coupon.product_ids.join(', ') : 'All'}</td>
                                        <td>${coupon.category ? coupon.category.join(', ') : 'All'}</td>
                                        <td>
                                            <button onclick="deleteCoupon('${coupon.code}')">Delete</button>
                                        </td>
                                    </tr>
                                `;
                            });
                            html += '</table>';
                            container.innerHTML = html;
                        });
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
