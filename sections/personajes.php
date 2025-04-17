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
</head>
<body>
	<div class="charactersDiv">
		<?php foreach ($chars as $key => $char): ?>
			<div class="charOption" id="<?=$char["name"]?>" charId=<?=$char["id_char"] ?> onclick="showCharacter(this)" pdfName='<?=$char["pdf_path"]?>'>
				<img src="../resources/chars/<?=$char["image_path"]?>">
				<h2><?=$char["name"]?></h2>
			</div>			
		<?php endforeach ?>

	</div>

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
	    

    <script type="text/javascript">
    	function showCharacter(trigger) {
    		console.log(trigger);

			window.location.replace("./personaje.php?id=1");

    		// document.getElementById("embed").src = "../resources/fichas/"+trigger.getAttribute("pdfName");

    		// console.log(document.getElementById("embed").src);

    		// document.getElementById("embedContainer").style.display = "block";

    		// document.getElementById("closeEmbed").addEventListener('click', function() {
    		// 	document.getElementById("embedContainer").style.display = "none";
    		// })
    	}


    </script>
</body>
</html>