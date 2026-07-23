<?php

include "../includes/db.php";


$id=$_GET['id'];


$query="DELETE FROM houses WHERE id=$id";


mysqli_query($conn,$query);


header("Location: dashboard.php");


?>