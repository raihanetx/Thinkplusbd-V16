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
    <title>Admin - Manage Reviews</title>
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
                    <li><a href="admin_reviews.php" class="active"><i class="fas fa-star"></i> <span>Manage Reviews</span></a></li>
                    <li><a href="admin_coupons.php"><i class="fas fa-tags"></i> <span>Manage Coupons</span></a></li>
                    <li><a href="product_code_generator.html" target="_blank"><i class="fas fa-plus-circle"></i> <span>Add Product Helper</span></a></li>
                    <li><a href="admin_dashboard.php?logout=1"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main-content" id="adminMainContent">
            <header class="admin-topbar">
                <div style="display:flex; align-items:center;">
                    <i class="fas fa-bars sidebar-toggle" id="sidebarToggle"></i>
                    <h1>Manage Reviews</h1>
                </div>
                <a href="admin_dashboard.php?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            <div class="admin-page-content">
                <div class="content-card">
                    <h2 class="card-title">All Reviews</h2>
                    <div id="reviews-container"></div>
                </div>
            </div>
        </main>
    </div>
    <script src="admin_dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('get_reviews.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('reviews-container');
                    if (data.error) {
                        container.innerHTML = `<p>${data.error}</p>`;
                        return;
                    }
                    if (data.length === 0) {
                        container.innerHTML = '<p>No reviews to display.</p>';
                        return;
                    }
                    let html = '<table>';
                    html += '<tr><th>Product ID</th><th>Name</th><th>Rating</th><th>Comment</th><th>Timestamp</th><th>Status</th><th>Action</th></tr>';
                    data.forEach(review => {
                        html += `
                            <tr>
                                <td>${review.product_id}</td>
                                <td>${review.name}</td>
                                <td>${review.rating}</td>
                                <td>${review.comment}</td>
                                <td>${review.timestamp}</td>
                                <td>${review.status}</td>
                                <td>
                                    <button onclick="approveReview(${review.id})">Approve</button>
                                    <button onclick="deleteReview(${review.id})">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                    html += '</table>';
                    container.innerHTML = html;
                });
        });

        function approveReview(reviewId) {
            fetch('approve_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: reviewId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to approve review.');
                }
            });
        }

        function deleteReview(reviewId) {
            fetch('delete_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: reviewId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete review.');
                }
            });
        }
    </script>
</body>
</html>
