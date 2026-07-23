<?php

session_start();


if(!isset($_SESSION['admin'])){

header("Location: login.php");

}


include "../includes/db.php";

$query = "SELECT * FROM houses";

$result = mysqli_query($conn,$query);

?>


<!DOCTYPE html>
<html>

<head>

<title>Admin Dashboard</title>

<link rel="stylesheet" href="../css/style.css">

</head>


<body>


<h1>Admin Dashboard</h1>


<a href="add_house.php">
<button>
Add New House
</button>
</a>

<a href="bookings.php">

<button>
View Bookings
</button>

</a>

<h2>All Houses</h2>


<table border="1" cellpadding="10">


<tr>

<th>Image</th>
<th>Title</th>
<th>Location</th>
<th>Price</th>
<th>Action</th>

</tr>



<?php

while($house=mysqli_fetch_assoc($result)){

?>


<tr>

<td>

<img src="../uploads/<?php echo $house['image']; ?>" width="100">

</td>


<td>
<?php echo $house['title']; ?>
</td>


<td>
<?php echo $house['location']; ?>
</td>


<td>
<?php echo $house['price']; ?>
</td>


<td>

<a href="delete_house.php?id=<?php echo $house['id']; ?>">
Delete
</a>


</td>


</tr>


<?php } ?>


</table>


</body>

</html>