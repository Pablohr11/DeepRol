<?php require_once __DIR__ . '/../src/bootstrap.php'; ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../styles/index.css">
    <style>
        body{padding:clamp(2rem,6vw,6rem);color:#eee;background:#171717}.welcome{max-width:850px;margin:auto}.actions{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-top:2rem}.actions a{padding:1.5rem;text-decoration:none;color:inherit;background:#26211c;border:1px solid #8f7448;border-radius:8px}.actions a:hover{transform:translateY(-2px);background:#332a20}.muted{color:#c8bca9}
    </style>
    <title>Inicio · DeepRol</title>
</head>
<body><main class="welcome">
    <h1>Tu mesa de juego</h1>
    <p class="muted">Organiza personajes, consulta conjuros y guarda las notas de tu campaña.</p>
    <div class="actions">
        <a href="personajes.php"><strong>Personajes</strong><br><span class="muted">Fichas y estadísticas</span></a>
        <a href="allSpells.php"><strong>Conjuros</strong><br><span class="muted">Busca y filtra habilidades</span></a>
        <a href="notes.php"><strong>Apuntes</strong><br><span class="muted">Diario de campaña</span></a>
        <?php if (!current_user_id()): ?><a href="../login.php"><strong>Iniciar sesión</strong><br><span class="muted">Accede a tus datos</span></a><?php endif; ?>
    </div>
</main></body></html>
