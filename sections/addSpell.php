<?php

require_once __DIR__ . '/../src/bootstrap.php';

$db = DbConector::singleton();

$charId = filter_input(INPUT_GET, 'charId', FILTER_VALIDATE_INT);
$spellId = filter_input(INPUT_GET, 'id_spell', FILTER_VALIDATE_INT);
if (!$charId || !$spellId || !$db->getCharForUser($charId, require_login())) {
    http_response_code(404);
    exit('Personaje o conjuro no encontrado.');
}

$db->addSpell($charId, $spellId);

<<<<<<< Updated upstream

$prevUrl = $_GET["prevPath"];
=======
$prevUrl = basename((string) ($_GET["prevPath"] ?? 'allSpells.php'));
>>>>>>> Stashed changes

if (strpos($prevUrl, "--") != -1) {
    $prevUrl = str_replace("--", "&", $prevUrl);
}

// echo($prevUrl);

header("Location: ".$prevUrl);
exit;

?>
