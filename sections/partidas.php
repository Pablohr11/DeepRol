<?php

require_once __DIR__ . "/../classes/GameRepository.php";

$userId = isset($_COOKIE["logged"]) ? (int) $_COOKIE["logged"] : 0;
if ($userId <= 0) {
    header("Location: ../login.php");
    exit;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function issueGameCsrfToken(string $cookieName): string
{
    $token = bin2hex(random_bytes(24));
    setcookie($cookieName, $token, [
        "expires" => time() + 14400,
        "path" => "/",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Strict",
    ]);
    return $token;
}

$csrfCookieName = "deeprol_game_csrf";
$csrfToken = isset($_COOKIE[$csrfCookieName])
    && preg_match("/^[a-f0-9]{48}$/", (string) $_COOKIE[$csrfCookieName])
        ? (string) $_COOKIE[$csrfCookieName]
        : issueGameCsrfToken($csrfCookieName);
$games = [];
$characters = [];
$loadError = "";
try {
    $repository = new GameRepository();
    $games = $repository->listGamesForUser($userId);
    $characters = $repository->charactersForUser($userId);
} catch (Throwable $exception) {
    error_log("DeepRol partidas: " . $exception->getMessage());
    $loadError = "No se han podido cargar las partidas.";
}
$ownedGames = array_filter($games, static function (array $game): bool {
    return (string) ($game["role"] ?? "") === "dm";
});
$activeEncounters = array_filter($games, static function (array $game): bool {
    return (string) ($game["encounter_status"] ?? "") === "active";
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark light">
    <title>Partidas · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/games.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    <script defer src="../scripts/games.js"></script>
</head>
<body>
    <main
        class="gamesLobby"
        data-games-lobby
        data-api="../src/gameApi.php"
        data-csrf="<?= e($csrfToken) ?>"
    >
        <header class="gamesHero">
            <div class="gamesHeroCopy">
                <span class="gamesEyebrow">Mesa compartida</span>
                <h1>Partidas en directo</h1>
                <p>
                    Crea una sala, reúne al grupo con un código corto y lleva el
                    combate desde una única pantalla sincronizada.
                </p>
                <div class="gamesHeroActions">
                    <button class="primaryGameButton" type="button" data-open-dialog="createGameDialog">
                        Crear como Dungeon Master
                    </button>
                    <button class="secondaryGameButton" type="button" data-open-dialog="joinGameDialog">
                        Unirme con un código
                    </button>
                </div>
            </div>
            <div class="gamesOverview" aria-label="Resumen de partidas">
                <article>
                    <span>Salas</span>
                    <strong><?= count($games) ?></strong>
                    <small>en tu biblioteca</small>
                </article>
                <article>
                    <span>Como DM</span>
                    <strong><?= count($ownedGames) ?></strong>
                    <small>bajo tu control</small>
                </article>
                <article>
                    <span>En combate</span>
                    <strong><?= count($activeEncounters) ?></strong>
                    <small>ahora mismo</small>
                </article>
            </div>
        </header>

        <?php if ($loadError !== ""): ?>
            <div class="gameNotice error" role="alert"><?= e($loadError) ?></div>
        <?php endif; ?>
        <div class="gameNotice" data-lobby-message hidden></div>

        <section class="gameListSection" aria-labelledby="yourGamesTitle">
            <div class="sectionHeading">
                <div>
                    <span class="gamesEyebrow">Tu mesa</span>
                    <h2 id="yourGamesTitle">Partidas recientes</h2>
                </div>
                <span><?= count($games) ?> <?= count($games) === 1 ? "partida" : "partidas" ?></span>
            </div>

            <?php if (!$games): ?>
                <div class="emptyGames">
                    <span class="emptyGameDie" aria-hidden="true">20</span>
                    <h3>La mesa está preparada</h3>
                    <p>
                        Crea tu primera partida como Dungeon Master o introduce
                        el código que te ha enviado tu grupo.
                    </p>
                    <button type="button" data-open-dialog="createGameDialog">Crear una partida</button>
                </div>
            <?php else: ?>
                <div class="gameCards">
                    <?php foreach ($games as $game): ?>
                        <?php
                        $isDm = (string) $game["role"] === "dm";
                        $isLive = (string) ($game["encounter_status"] ?? "") === "active";
                        ?>
                        <a class="gameCard<?= $isLive ? " isLive" : "" ?>" href="partida.php?id=<?= (int) $game["id_game"] ?>">
                            <div class="gameCardTopline">
                                <span class="gameRole"><?= $isDm ? "Dungeon Master" : "Jugador" ?></span>
                                <?php if ($isLive): ?>
                                    <span class="liveBadge"><i></i> En directo</span>
                                <?php else: ?>
                                    <span class="gameStatus">Preparada</span>
                                <?php endif; ?>
                            </div>
                            <div class="gameCardBody">
                                <span class="gameSigil" aria-hidden="true">✦</span>
                                <div>
                                    <h3><?= e($game["name"]) ?></h3>
                                    <p>
                                        <?php if (!empty($game["encounter_name"])): ?>
                                            <?= e($game["encounter_name"]) ?>
                                            <?php if ($isLive): ?>
                                                · Ronda <?= (int) $game["round_number"] ?>
                                            <?php endif; ?>
                                        <?php elseif (!empty($game["description"])): ?>
                                            <?= e($game["description"]) ?>
                                        <?php else: ?>
                                            Aún no hay un encuentro preparado.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <footer>
                                <span><strong><?= (int) $game["member_count"] ?></strong> participantes</span>
                                <?php if ($isDm): ?>
                                    <span class="inviteCodeSmall"><?= e($game["invite_code"]) ?></span>
                                <?php elseif (!empty($game["character_name"])): ?>
                                    <span><?= e($game["character_name"]) ?></span>
                                <?php endif; ?>
                                <b>Entrar <span aria-hidden="true">→</span></b>
                            </footer>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="gameFeatureStrip" aria-label="Funciones de las partidas">
            <article>
                <span aria-hidden="true">01</span>
                <div>
                    <h3>Combate sincronizado</h3>
                    <p>Turno, iniciativa, vida temporal, estados y concentración.</p>
                </div>
            </article>
            <article>
                <span aria-hidden="true">02</span>
                <div>
                    <h3>Recursos con memoria</h3>
                    <p>Espacios de conjuro, usos de objetos y recursos de clase.</p>
                </div>
            </article>
            <article>
                <span aria-hidden="true">03</span>
                <div>
                    <h3>Bestiario integrado</h3>
                    <p>Incorpora enemigos del compendio sin copiar sus estadísticas.</p>
                </div>
            </article>
        </section>

        <dialog id="createGameDialog" class="gameDialog">
            <form data-game-form="create">
                <header>
                    <div>
                        <span class="gamesEyebrow">Nueva campaña</span>
                        <h2>Crear una partida</h2>
                    </div>
                    <button type="button" data-close-dialog aria-label="Cerrar">×</button>
                </header>
                <label>
                    <span>Nombre de la partida</span>
                    <input type="text" name="name" maxlength="120" required placeholder="La corona bajo la niebla">
                </label>
                <label>
                    <span>Introducción <small>opcional</small></span>
                    <textarea name="description" maxlength="1000" rows="4" placeholder="Una frase para que el grupo reconozca la aventura."></textarea>
                </label>
                <div class="dialogHint">
                    Al crearla recibirás un código alfanumérico de seis caracteres.
                </div>
                <button class="primaryGameButton" type="submit">Crear y abrir la mesa</button>
            </form>
        </dialog>

        <dialog id="joinGameDialog" class="gameDialog">
            <form data-game-form="join">
                <header>
                    <div>
                        <span class="gamesEyebrow">Invitación</span>
                        <h2>Unirme a una partida</h2>
                    </div>
                    <button type="button" data-close-dialog aria-label="Cerrar">×</button>
                </header>
                <label>
                    <span>Código de seis caracteres</span>
                    <input
                        class="inviteCodeInput"
                        type="text"
                        name="invite_code"
                        minlength="6"
                        maxlength="6"
                        pattern="[A-Za-z0-9]{6}"
                        autocomplete="off"
                        required
                        placeholder="K7M2XP"
                    >
                </label>
                <label>
                    <span>
                        Personaje
                        <?php if (!$characters): ?><small>no disponible</small><?php endif; ?>
                    </span>
                    <select name="character_id" <?= $characters ? "required" : "" ?>>
                        <?php if ($characters): ?>
                            <option value="">Selecciona el personaje con el que jugarás</option>
                        <?php else: ?>
                            <option value="">Entrar como espectador</option>
                        <?php endif; ?>
                        <?php foreach ($characters as $character): ?>
                            <option value="<?= (int) $character["id_char"] ?>">
                                <?= e($character["name"]) ?> ·
                                <?= e($character["clase"] ?: "Sin clase") ?>
                                <?= (int) $character["nivel"] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="dialogHint">
                    <?php if ($characters): ?>
                        Tu personaje se incorporará automáticamente al encuentro actual y podrás controlarlo.
                    <?php else: ?>
                        No tienes personajes: entrarás como espectador hasta que crees uno y vuelvas a vincularlo.
                    <?php endif; ?>
                </div>
                <button class="primaryGameButton" type="submit">Entrar en la mesa</button>
            </form>
        </dialog>
    </main>
</body>
</html>
