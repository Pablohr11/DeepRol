<?php
    require_once __DIR__ . '/../src/bootstrap.php';
    $db = DbConector::singleton();

    $uid = require_login();

    $chars = $db->getChars($uid);
<<<<<<< Updated upstream
=======

    if (isset($_POST["submit"])) {
        $userId = $uid;
        $charId = $_POST["personaje"];
        $noteName = $_POST["noteName"];
        $noteDate = $_POST["noteDate"];
        $db->createNote($userId, $charId, $noteName, $noteDate);
        header("Location: notes.php");
        exit;
    }
>>>>>>> Stashed changes
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-32">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/newNote.css">
</head>
<body>
    <form action="" method="post" id="nnForm">
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
        <div>
            <?php foreach ($chars as $key => $char) { ?>
                <label class="selectable-char">
                    <input type="radio" name="personaje" value="<?= htmlspecialchars($char['name']) ?>">
                    <div class="selectable-char-content">
                        <div class="selectable-char-img-wrapper">
                            <img src="<?= $CHARPATH . $char['name'] . '/imagenPequeña.png' ?>" class="selectable-char-img">
                        </div>
                        <p><?= htmlspecialchars($char['name']) ?></p>
                    </div>
                </label>
            <?php } ?>
        </div>
    </form>
</body>
</html>
