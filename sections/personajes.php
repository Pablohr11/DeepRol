<?php

	require_once __DIR__ . '/../src/bootstrap.php';
	$db = DbConector::singleton();

    $fichas = (scandir("../resources/fichas"));
    array_shift($fichas); array_shift($fichas);

    $imgs = (scandir("../resources/chars"));
    array_shift($imgs); array_shift($imgs);

	$userId = require_login();
	$chars = $db->getChars($userId);
?>
<!DOCTYPE html>

<html>
<head>
	<meta charset="utf-32">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<link rel="stylesheet" href="../styles/personajes.css">
</head>
<body>
	<div class="charactersDiv">
		<?php foreach ($chars as $key => $char): ?>
<<<<<<< Updated upstream
			<div class="charOption" id="<?=$char["name"]?>" charId=<?=$char["id_char"] ?> onclick="showCharacter(this)" pdfName='<?=$char["pdf_path"]?>'>
				<img src="../resources/chars/<?=$char["image_path"]?>">
				<h2 class="charName"><?=$char["name"]?></h2>
=======
			<div class="charOption" data-char-id="<?=h($char["id_char"])?>" onclick="showCharacter(this)">
				<img src="../resources/chars/<?=rawurlencode($char["name"])?>/<?=rawurlencode($char["image_path"])?>" alt="">
				<h2 class="charName"><?=h($char["name"])?></h2>
>>>>>>> Stashed changes
			</div>			
		<?php endforeach ?>
		<div class="addChar">
			<a href="./addPersonajes.php">
				<span class="flexCenter">+</span>
			</a>
		</div>		

	</div>

<<<<<<< Updated upstream
	<div id="embedContainer">
		<div id="embedTopBar">
			<span id="closeEmbed">X</span>
		</div>
		<embed
			id="embed"
	        src="./resources/fichas/Pablo.pdf"
	        type="application/pdf"
	        width="100%"
	        height="100%"
	        title="Embedded PDF Viewer"
	    />
	</div>
	    
=======
		var charIdToShow = trigger.dataset.charId;
>>>>>>> Stashed changes

    <script type="text/javascript">
    	function showCharacter(trigger) {
    		console.log(trigger);

			var charIdToShow = trigger.getAttribute("charId");

			window.location.replace("./personaje.php?id="+charIdToShow);
    	}


    </script>
</body>
</html>
