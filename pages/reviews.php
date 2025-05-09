<?php
// Database configuration
$host = 'localhost';
$db   = 'luxury_home_decor';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $review = $_POST['review'] ?? '';
    $rating = (int)($_POST['rating'] ?? 0);
    
    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($review)) $errors[] = 'Review text is required';
    if ($rating < 1 || $rating > 5) $errors[] = 'Please select a valid rating';
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('review_', true) . '.' . $extension;
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $imagePath = $destination;
            } else {
                $errors[] = 'Failed to upload image';
            }
        } else {
            $errors[] = 'Only JPG, PNG, and GIF images are allowed';
        }
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_name, user_email, review_text, rating, image_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $review, $rating, $imagePath]);
        
        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fetch all reviews
$stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
$reviews = $stmt->fetchAll();
?>
<?php include '../logic/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --border-color: #ddd;
            --star-color: #ffc107;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        
        h1, h2 {
            color: var(--primary-color);
        }
        
        .review-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        input[type="text"],
        input[type="email"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .rating-container {
            display: flex;
            gap: 10px;
            margin: 10px 0;
        }
        
        .rating-container input[type="radio"] {
            display: none;
        }
        
        .rating-container label {
            font-size: 24px;
            color: var(--border-color);
            cursor: pointer;
        }
        
        .rating-container input[type="radio"]:checked ~ label {
            color: var(--star-color);
        }
        
        .rating-container label:hover,
        .rating-container label:hover ~ label {
            color: var(--star-color);
        }
        
        .file-upload {
            margin-top: 10px;
        }
        
        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: var(--secondary-color);
        }
        
        .error {
            color: #e74c3c;
            margin: 5px 0;
        }
        
        .reviews-container {
            margin-top: 40px;
        }
        
        .review-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .review-author {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .review-date {
            color: #777;
            font-size: 14px;
        }
        
        .review-rating {
            color: var(--star-color);
            margin: 5px 0;
        }
        
        .review-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 15px;
            border-radius: 4px;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .review-form {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <h1>Leave a Review</h1>
    
    <div class="review-form">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Your Name*</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Your Email (optional)</label>
                <input type="email" id="email" name="email">
            </div>
            
            <div class="form-group">
                <label>Your Rating*</label>
                <div class="rating-container">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                        <label for="star<?php echo $i; ?>">★</label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="review">Your Review*</label>
                <textarea id="review" name="review" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">Upload Image (optional)</label>
                <input type="file" id="image" name="image" class="file-upload" accept="image/*">
                <small>Max size: 2MB (JPG, PNG, GIF)</small>
            </div>
            
            <button type="submit" class="submit-btn">Submit Review</button>
        </form>
    </div>
    
    <div class="reviews-container">
        <h2>Customer Reviews</h2>
        
        <?php if (empty($reviews)): ?>
            <p>No reviews yet. Be the first to review!</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <span class="review-author"><?php echo htmlspecialchars($review['user_name']); ?></span>
                        <span class="review-date"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></span>
                    </div>
                    
                    <div class="review-rating">
                        <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                    </div>
                    
                    <div class="review-text">
                        <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                    </div>
                    
                    <?php if (!empty($review['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($review['image_path']); ?>" alt="Review image" class="review-image">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>