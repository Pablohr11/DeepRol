<?php

	require_once("../classes/DbConnector.php");
	$db = DbConector::singleton();

    $fichas = (scandir("../resources/fichas"));
    array_shift($fichas); array_shift($fichas);

    $imgs = (scandir("../resources/chars"));
    array_shift($imgs); array_shift($imgs);

	$chars = [];

	if (isset($_COOKIE["logged"]) && $_COOKIE["logged"] != 0) {
		$chars = $db->getChars($_COOKIE["logged"]);
	}
?>
<!DOCTYPE html>

<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<link rel="stylesheet" href="../styles/personajes.css">
    <script defer src="../scripts/personajes.js"></script>
</head>
<body>
	<div class="charactersDiv">
		<?php foreach ($chars as $key => $char): ?>
			<div class="charOption" id="<?=$char["name"]?>" charId=<?=$char["id_char"] ?> onclick="showCharacter(this)" pdfName='<?=$char["pdf_path"]?>'>
				<img src="../resources/chars/<?=$char["name"]?>/<?=$char["image_path"]?>">
				<h2 class="charName"><?=$char["name"]?></h2>
			</div>			
		<?php endforeach ?>
		<div class="addChar">
            <video id="background-video"  loop muted>
				<source src="../resources/vids/book.mp4" type="video/mp4">
			</video>
			<a id="addCharButton" href="./addPersonajes.php">
				<span class="flexCenter">+</span>
			</a>
		</div>		

	</div>
<script type="text/javascript">
	function showCharacter(trigger) {
		console.log(trigger);

		var charIdToShow = trigger.getAttribute("charId");

		window.location.replace("./personaje.php?id="+charIdToShow);
	}


</script>
</body>
</html>