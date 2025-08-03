<?php
    require_once("../src/autoload.php");
    $db = DbConector::singleton();

    $uid = $_GET["uid"];

    $chars = $db->getChars($uid);

    if (isset($_POST["submit"])) {
        $userId = $_COOKIE["logged"];
        $charId = $_POST["personaje"];
        $noteName = $_POST["noteName"];
        $noteDate = $_POST["noteDate"];
        var_dump($db->createNote($userId, $charId, $noteName, $noteDate));
        header("Location: notes.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/newNote.css">
</head>
<body>
    <form action="" method="post" id="nnForm">
        <div>
            <input type="text" name="noteName" id="noteName">
            <input type="date" name="noteDate" id="noteDate">

            <script>
            document.addEventListener("DOMContentLoaded", () => {
                const date = new Date();
                const day = String(date.getDate()).padStart(2, '0');    
                const month = String(date.getMonth() + 1).padStart(2, '0'); 
                const year = date.getFullYear();    

                const formattedDate = `${year}-${month}-${day}`;
                console.log(formattedDate);

                const input = document.getElementById("noteDate");
                input.value = formattedDate;
                input.style.pointerEvents = "none";
            });
            </script>
        </div>
        <div>
            <?php foreach ($chars as $key => $char) {?>
                
                <label class="selectable-char">
                    <input type="radio" name="personaje" value="<?=$char["id_char"] ?>">
                    <div class="selectable-char-content">
                        <div class="selectable-char-img-wrapper">
                            <img src="<?= $CHARPATH . $char['name'] . '/imagenPequeña.png' ?>" class="selectable-char-img">
                        </div>
                        <h3 class="charName"><?= htmlspecialchars($char['name']) ?></h3>
                    </div>
                </label>
            <?php } ?>
        </div>
        <button type="submit" name="submit">Crear</button>
    </form>
</body>
</html>