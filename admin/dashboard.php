<?php
require_once '../config/admin/db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        // Add new product
        $category_id = $_POST['category_id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $warranty = isset($_POST['warranty']) ? $_POST['warranty'] : null;
        $offer = isset($_POST['offer']) ? $_POST['offer'] : null;
        $status = $_POST['status'];
        
        // Handle image upload
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $error = "File is not an image.";
        } elseif ($_FILES["image"]["size"] > 5000000) {
            $error = "Sorry, your file is too large.";
        } elseif (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, image_path, name, price, description, warranty, offer, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $target_file, $name, $price, $description, $warranty, $offer, $status]);
            $success = "Product added successfully!";
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    } elseif (isset($_POST['add_category'])) {
        // Add new category
        $category_name = $_POST['category_name'];
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$category_name]);
        $success = "Category added successfully!";
    } elseif (isset($_POST['update_product'])) {
        // Update product
        $product_id = $_POST['product_id'];
        $category_id = $_POST['category_id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $warranty = isset($_POST['warranty']) ? $_POST['warranty'] : null;
        $offer = isset($_POST['offer']) ? $_POST['offer'] : null;
        $status = $_POST['status'];
        
        // Check if new image was uploaded
        if ($_FILES['image']['size'] > 0) {
            // Handle image upload
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $stmt = $pdo->prepare("UPDATE products SET category_id=?, image_path=?, name=?, price=?, description=?, warranty=?, offer=?, status=? WHERE id=?");
                $stmt->execute([$category_id, $target_file, $name, $price, $description, $warranty, $offer, $status, $product_id]);
                $success = "Product updated successfully!";
            }
        } else {
            $stmt = $pdo->prepare("UPDATE products SET category_id=?, name=?, price=?, description=?, warranty=?, offer=?, status=? WHERE id=?");
            $stmt->execute([$category_id, $name, $price, $description, $warranty, $offer, $status, $product_id]);
            $success = "Product updated successfully!";
        }
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Product deleted successfully!";
}

// Fetch all categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all products with their category names
$products = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Check if we're editing a product
$editing_product = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $editing_product = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<?php include '../logic/side_bar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Products Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main-content {
    margin-left: 250px; /* Same as sidebar width */
    padding: 20px;
    transition: margin-left var(--transition-speed);
}

body.sidebar-collapsed .main-content {
    margin-left: 80px; /* Same as collapsed sidebar width */
}
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .form-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
        }
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            border: none;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }
        .btn-danger {
            background-color: #ef4444;
            color: white;
            border: none;
        }
        .btn-danger:hover {
            background-color: #dc2626;
        }
        .btn-secondary {
            background-color: #6b7280;
            color: white;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.375rem;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.375rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-on-sale {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-sold-out {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .status-coming-soon {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>

<body class="bg-gray-100">
<div class="interstellarContent" id="cosmicMainContent">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Products Management</h1>
            <a href="admin-logout.php" class="btn btn-danger">Logout</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success mb-6"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error mb-6"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Add Category Form -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Add New Category</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="category_name" class="form-label">Category Name</label>
                        <input type="text" id="category_name" name="category_name" class="form-input" required>
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                </form>
            </div>

            <!-- Add/Edit Product Form -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">
                    <?php echo $editing_product ? 'Edit Product' : 'Add New Product'; ?>
                </h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($editing_product): ?>
                        <input type="hidden" name="product_id" value="<?php echo $editing_product['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="category_id" class="form-label">Category</label>
                        <select id="category_id" name="category_id" class="form-input" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"
                                    <?php if ($editing_product && $editing_product['category_id'] == $category['id']) echo 'selected'; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" id="image" name="image" class="form-input" <?php if (!$editing_product) echo 'required'; ?>>
                        <?php if ($editing_product): ?>
                            <div class="mt-2">
                                <img src="<?php echo $editing_product['image_path']; ?>" alt="Current Image" class="product-image">
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Leave empty to keep current image</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" id="name" name="name" class="form-input" 
                               value="<?php echo $editing_product ? $editing_product['name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price" class="form-label">Price (KSH)</label>
                        <input type="number" id="price" name="price" class="form-input" step="0.01" min="0"
                               value="<?php echo $editing_product ? $editing_product['price'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-input" rows="3" required><?php echo $editing_product ? $editing_product['description'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="warranty" class="form-label">Warranty (for technology products)</label>
                        <input type="text" id="warranty" name="warranty" class="form-input"
                               value="<?php echo $editing_product ? $editing_product['warranty'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="offer" class="form-label">Offer (if available)</label>
                        <input type="text" id="offer" name="offer" class="form-input"
                               value="<?php echo $editing_product ? $editing_product['offer'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-input" required>
                            <option value="on sale" <?php if ($editing_product && $editing_product['status'] == 'on sale') echo 'selected'; ?>>On Sale</option>
                            <option value="sold out" <?php if ($editing_product && $editing_product['status'] == 'sold out') echo 'selected'; ?>>Sold Out</option>
                            <option value="coming soon" <?php if ($editing_product && $editing_product['status'] == 'coming soon') echo 'selected'; ?>>Coming Soon</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-2">
                        <?php if ($editing_product): ?>
                            <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                            <a href="admin-products.php" class="btn btn-secondary">Cancel</a>
                        <?php else: ?>
                            <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Products List</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price (KSH)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warranty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Offer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900"><?php echo $product['name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo substr($product['description'], 0, 50) . '...'; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $product['category_name']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo number_format($product['price'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $product['warranty'] ? $product['warranty'] : 'N/A'; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $product['offer'] ? $product['offer'] : 'N/A'; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                        $status_class = '';
                                        if ($product['status'] == 'on sale') $status_class = 'status-on-sale';
                                        elseif ($product['status'] == 'sold out') $status_class = 'status-sold-out';
                                        else $status_class = 'status-coming-soon';
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="admin-products.php?edit=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="admin-products.php?delete=<?php echo $product['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this product?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</body>
</html>