<?php
    require_once("../classes/DbConnector.php");
    $db = DbConector::singleton();

    $uid = $_GET["uid"];

    $db->getChars($uid);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <form action="" method="post">
        <div>
            <input type="text" name="noteName" id="noteName">
            <input type="date" name="noteDate" id="noteDate">
            <script>
                const date = new Date();
                const day = String(date.getDate()).padStart(2, '0');    
                const month = String(date.getMonth() + 1).padStart(2, '0'); 
                const year = date.getFullYear();    

                const formattedDate = `${year}-${month}-${day}`;
                console.log(formattedDate);
                document.getElementById("noteDate").value = formattedDate;
                document.getElementById("noteDate").setAttribute("disabled", true);
            </script>
        </div>
    </form>
</body>
</html>

