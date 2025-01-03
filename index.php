<?php


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeepRol</title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/leftColumn.css">
    <link rel="stylesheet" href="styles/footer.css">
    <script defer src="scripts/header.js"></script>
</head>
<body>
    <?php 
        include("sections/_partials/header.php")
    ?>
    <div id="content">
        <div id="leftBar" class="dark">
            <?php 
                include("sections/_partials/leftColumn.php")
            ?>
        </div>
        <div id="mainContent" class="dark"></div>
    </div>
    <div id="footer">
        <?php 
            include("sections/_partials/footer.php")
        ?>
    </div>
</body>
</html>