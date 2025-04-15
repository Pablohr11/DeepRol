<?php

	require_once("classes/DbConnector.php");
	$db = DbConector::singleton();

    $fichas = (scandir("./resources/fichas"));
    array_shift($fichas); array_shift($fichas);

    $imgs = (scandir("./resources/chars"));
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
	<style>
		.charactersDiv {
			display: grid;
			grid-template-columns: 25% 25% 25% 25%;
		}
		#popUpLike, #embedContainer {
			display: none;
			position: fixed;
			top:10%;
			left: 15%;
		    width: 80%;
    		height: 80%;
		}

		.charOption {
			display: grid;
			height: 250px;
			margin: 10px;
			overflow: hidden;
			grid-template-rows: 200px auto;
			border-radius: 10px 10px 5px 5px;
		}
		
		.charOption img {
			width: 100%;
			height: 200px;
			object-fit: cover;
    		object-position: top;
		}

		.charOption h2 {
			background-color: #653d14;
			margin: 0px;
		    display: grid;
		    justify-content: center;
		    align-items: center;
		}

		#embedTopBar {
			display: grid;
			justify-content: end;
			background-color: #202020;
			padding: 5px 10px;
			border-top-left-radius: 10px;
			border-top-right-radius: 10px;
		}

		#closeEmbed {
			display: grid;
			background-color: #690500;
			width: 20px;
			height: 20px;
			justify-content: center;
    		align-content: center;
    		border-radius: 20px;
    		cursor: pointer;
		}
	</style>
</head>
<body>
	<div class="charactersDiv">
		<?php foreach ($chars as $key => $char): ?>
			<div class="charOption" id="<?=$char["name"]?>" onclick="showCharacter(this)" pdfName='<?=$char["pdf_path"]?>'>
				<img src="./resources/chars/<?=$char["image_path"]?>">
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

    		document.getElementById("embed").src = "./resources/fichas/"+trigger.getAttribute("pdfName");

    		console.log(document.getElementById("embed").src);

    		document.getElementById("embedContainer").style.display = "block";

    		document.getElementById("closeEmbed").addEventListener('click', function() {
    			document.getElementById("embedContainer").style.display = "none";
    		})
    	}


    </script>
</body>
</html>