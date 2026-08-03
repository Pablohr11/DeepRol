<?php
    $isLogged = isset($_COOKIE["logged"]) && (int) $_COOKIE["logged"] > 0;
    $initial = $isLogged && !empty($_COOKIE["userInitial"])
        ? strtoupper(substr($_COOKIE["userInitial"], 0, 1))
        : "I";
    $accountLabel = $isLogged ? "Aventurero" : "Invitado";
?>

<header id="header">
    <form id="globalSearch" class="globalSearch" role="search">
        <span class="searchIcon" aria-hidden="true">⌕</span>
        <input
            id="globalSearchInput"
            type="search"
            placeholder="Buscar en todo DeepRol..."
            autocomplete="off"
            aria-label="Buscar en DeepRol"
            aria-controls="globalSearchPanel"
            aria-expanded="false"
            aria-autocomplete="list"
            role="combobox"
            maxlength="80"
        >
        <kbd>Enter</kbd>
        <section
            id="globalSearchPanel"
            class="globalSearchPanel"
            aria-label="Sugerencias de búsqueda"
            hidden
        >
            <p id="globalSearchStatus" class="globalSearchStatus" role="status">
                Escribe al menos dos caracteres.
            </p>
            <div id="globalSearchResults" class="globalSearchResults" role="listbox"></div>
            <button id="globalSearchAll" class="globalSearchAll" type="submit" hidden>
                Ver todos los resultados
                <span aria-hidden="true">→</span>
            </button>
        </section>
    </form>

    <div id="rightPanel">
        <button
            class="headerAction colorModeToggle"
            type="button"
            data-color-mode-toggle
            title="Activar tema claro"
            aria-label="Activar tema claro"
            aria-pressed="false"
        >
            <span data-color-mode-icon aria-hidden="true">☀</span>
        </button>
        <button class="headerAction" type="button" title="Notificaciones" aria-label="Notificaciones">
            <span aria-hidden="true">♧</span>
            <i></i>
        </button>
        <button class="headerAction" type="button" title="Mensajes" aria-label="Mensajes">
            <span aria-hidden="true">✉</span>
        </button>
        <span class="headerDivider"></span>
        <a id="accountButton" href="<?= $isLogged ? '#' : 'login.php' ?>" aria-label="<?= $isLogged ? 'Abrir perfil' : 'Iniciar sesión' ?>">
            <span class="accountAvatar"><?= htmlspecialchars($initial, ENT_QUOTES, "UTF-8") ?></span>
            <span class="accountCopy">
                <strong><?= htmlspecialchars($accountLabel, ENT_QUOTES, "UTF-8") ?></strong>
                <small><?= $isLogged ? "Dungeon Master" : "Iniciar sesión" ?></small>
            </span>
            <span class="accountChevron" aria-hidden="true">⌄</span>
        </a>
    </div>
</header>
