<?php

	

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
	<link rel="stylesheet" type="text/css" href="styles/login.css">
</head>
<body>
    <?php 
        include("sections/_partials/header.php")
    ?>
	<div id="formContainer">
		<div class="loginOption">
			<form>
				<input type="text" id="formName" name="user">		
			</form>
		</div>
		<div class="loginDecorate"></div>
	</div>
    <div id="footer">
        <?php 
            include("sections/_partials/footer.php")
        ?>
    </div>
</body>
</html>