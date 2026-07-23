<?php

include "../includes/db.php";

if (isset($_POST['submit'])) {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $location = mysqli_real_escape_string($conn, trim($_POST['location']));
    $price = (float)$_POST['price'];
    $bedrooms = (int)$_POST['bedrooms'];
    $bathrooms = (int)$_POST['bathrooms'];
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    $images = $_FILES['images'];
    $captions = isset($_POST['captions']) ? $_POST['captions'] : [];
    $mainImage = '';

    if (!empty($images['name'][0])) {
        $mainImage = basename($images['name'][0]);
    }

    $sql = "INSERT INTO houses (title, location, price, bedrooms, bathrooms, description, image) VALUES ('$title', '$location', $price, $bedrooms, $bathrooms, '$description', '$mainImage')";

    if (mysqli_query($conn, $sql)) {
        $houseId = mysqli_insert_id($conn);

        for ($i = 0; $i < count($images['name']); $i++) {
            if (empty($images['name'][$i])) {
                continue;
            }

            $imageName = basename($images['name'][$i]);
            $tmpImage = $images['tmp_name'][$i];
            $caption = isset($captions[$i]) ? mysqli_real_escape_string($conn, trim($captions[$i])) : '';

            if (move_uploaded_file($tmpImage, "../uploads/" . $imageName)) {
                mysqli_query($conn, "INSERT INTO house_images (house_id, image, caption) VALUES ($houseId, '$imageName', '$caption')");
            }
        }

        echo "House Added Successfully";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add House</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Add New House</h2>

    <form class="form-container" method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <input type="text" name="title" placeholder="House Name" required>
            <input type="text" name="location" placeholder="Location" required>
        </div>

        <div class="form-grid form-grid-3">
            <input type="number" name="price" placeholder="Price" required>
            <input type="number" name="bedrooms" placeholder="Bedrooms" required>
            <input type="number" name="bathrooms" placeholder="Bathrooms" required>
        </div>

        <textarea name="description" placeholder="Description"></textarea>

        <div class="image-upload-grid">
            <div class="image-upload-item">
                <label>Main Image</label>
                <input type="file" name="images[]" accept="image/*" required>
                <input type="text" name="captions[]" placeholder="Caption for this image">
            </div>
            <div class="image-upload-item">
                <label>Extra Image 1</label>
                <input type="file" name="images[]" accept="image/*">
                <input type="text" name="captions[]" placeholder="Caption for this image">
            </div>
            <div class="image-upload-item">
                <label>Extra Image 2</label>
                <input type="file" name="images[]" accept="image/*">
                <input type="text" name="captions[]" placeholder="Caption for this image">
            </div>
        </div>

        <button type="submit" class="btn btn-full" name="submit">Add House</button>
    </form>
</body>
</html>
