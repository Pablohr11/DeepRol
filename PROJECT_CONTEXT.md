# DeepRol — contexto maestro y seguimiento del proyecto

> **Última revisión:** 30 de julio de 2026  
> **Estado del documento:** contexto operativo vivo  
> **Ruta canónica:** `/PROJECT_CONTEXT.md`

## Instrucción rápida para otro LLM

Lee este archivo completo antes de proponer o realizar cambios. Después:

1. Comprueba el estado real del repositorio y de los archivos implicados.
2. Conserva los cambios locales existentes: el árbol de trabajo contiene trabajo del usuario y cambios todavía no consolidados.
3. Mantén compatibilidad con PHP 7.4.
4. Usa las decisiones visuales y funcionales documentadas aquí como requisitos vigentes, salvo que el usuario indique lo contrario.
5. Ejecuta las verificaciones proporcionales al cambio.
6. Actualiza este documento al terminar: fecha, estado, archivos afectados, pruebas y riesgos pendientes.

La petición más reciente del usuario siempre prevalece sobre este documento. Este archivo describe el estado conocido; no sustituye la inspección del código.

---

## 1. Resumen del producto

DeepRol es una aplicación web local en castellano para gestionar partidas y contenido de Dungeons & Dragons 5e. Su núcleo actual permite:

- iniciar sesión o entrar como invitado;
- consultar una portada de acceso antes de mostrar la aplicación al invitado;
- buscar de forma unificada en contenido privado y catálogos públicos;
- gestionar personajes y abrir su ficha;
- crear personajes a partir de una ficha PDF vacía;
- editar datos de un personaje o sustituir su ficha por un PDF actualizado;
- consultar los espacios de conjuro según clase, subclase y nivel;
- consultar y filtrar conjuros;
- mostrar un banner visual asociado a la escuela de cada conjuro;
- consultar un bestiario en castellano;
- explorar razas, subrazas, linajes, clases y subclases;
- crear apuntes generales y apuntes vinculados a un personaje;
- mantener los apuntes de cada personaje aislados en su pestaña;
- adaptar el tema visual a la última clase de personaje utilizada;
- alternar entre un modo claro y uno oscuro desde cualquier acceso principal;
- tirar un d20 desde la navegación principal.

La aplicación todavía contiene áreas de demostración o incompletas: **Ajustes** y parte de las acciones del encabezado. **Partidas** ya dispone de un primer sistema multijugador funcional sin tablero.

## 2. Objetivos y preferencias vigentes del usuario

- Toda la interfaz y el contenido visible deben priorizar el castellano.
- El diseño debe aplicarse a **todas** las vistas, no solo a la portada.
- Dirección visual: fantasía oscura sobria, legible y madura.
- Base cromática: grafito, marfil y latón mate poco saturado.
- Evitar que el morado sea el color dominante.
- El color de clase debe actuar como acento ambiental, no como un neón invasivo.
- El acento de clase debe tener presencia reconocible en navegación, superficies, encabezados, iconos y estados activos.
- El tema activo depende de la última clase de personaje utilizada; por ejemplo, druida produce una atmósfera verdosa.
- La luminosidad es independiente del acento de clase: el usuario puede elegir tema claro u oscuro y conservar ambos comportamientos.
- El modo claro debe priorizar contraste alto: texto principal AAA y texto secundario AA sobre las superficies principales.
- El invitado debe ver primero una landing que invite a entrar.
- Las imágenes de personaje no son obligatorias.
- La creación debe ofrecer clases, subclases, razas y subrazas del catálogo oficial seleccionado.
- El bestiario debe mostrarse en castellano.
- La ficha de personaje se puede actualizar mediante campos editables o subiendo un PDF nuevo.
- En la pestaña de apuntes de un personaje solo deben aparecer apuntes de ese personaje.

Una petición histórica del usuario fue evitar confirmaciones innecesarias durante trabajos relacionados. No debe interpretarse como permiso permanente para operaciones destructivas, pérdida de datos o acciones externas.

## 3. Entorno y tecnología

### Entorno local verificado

- Windows y XAMPP.
- PHP: `C:\xampp\php\php.exe`, versión 7.4.33.
- MySQL/MariaDB de XAMPP.
- Base de datos local: `deeprol`.
- Usuario local observado: `root`, sin contraseña.
- Raíz de trabajo observada:
  `C:\Users\Pablohr11\Documents\GitHub\DeepRol`.

Las credenciales anteriores describen el entorno de desarrollo observado y no deben reutilizarse como configuración de producción.

### Tecnologías

- PHP 7.4 sin framework.
- MySQL/MariaDB mediante `mysqli`.
- HTML, CSS y JavaScript nativos.
- Aplicación principal basada en un shell con `iframe`.
- PDF.js local para lectura de formularios PDF.
- pdf-lib local para generar o actualizar fichas.
- Composer:
  - `darkgoldblade01/dnd-5e-api` en `dev-master`;
  - `mikehaertl/php-pdftk` `^0.13.1`.

`/vendor/` está ignorado por Git. No se debe asumir que las dependencias de Composer están instaladas en otra máquina.

## 4. Arquitectura y mapa de archivos

### Entrada, sesión y navegación

| Archivo | Responsabilidad |
| --- | --- |
| `index.php` | Decide entre landing y shell autenticado/invitado; contiene la navegación lateral, cabecera e `iframe` principal. |
| `login.php` | Formulario de acceso adaptado al sistema visual. |
| `sections/landing.php` | Landing para visitantes sin sesión ni acceso de invitado. |
| `sections/_partials/leftColumn.php` | Menú lateral compartido. |
| `scripts/index.js` | Navegación del `iframe`, sincronización del tema y tirada d20. |
| `scripts/header.js` | Búsqueda global con sugerencias, teclado, navegación y accesos del encabezado. |
| `scripts/theme.js` | Resuelve, guarda y propaga el acento de clase y el modo claro/oscuro. |
| `scripts/search.js` | Mantiene la navegación del shell desde la página completa de resultados. |
| `styles/theme.css` | Capa visual global; debe cargarse después de los estilos heredados de cada vista. |
| `styles/search.css` | Presentación de la página completa de búsqueda. |

La autenticación actual se identifica mediante una cookie `logged` que contiene un id de usuario. `guestAccess` habilita el acceso como invitado. Este mecanismo es legado y debe reforzarse antes de desplegar la aplicación públicamente.

### Vistas principales

| Ruta | Función | Estado conocido |
| --- | --- | --- |
| `sections/home.php` | Dashboard principal con personajes, conjuros, accesos y actividad. | Adaptada. |
| `sections/search.php?q=…` | Resultados globales agrupados. | Funcional; preserva la navegación del shell. |
| `sections/personajes.php` | Listado de personajes. | Adaptada. |
| `sections/personaje.php?id=…` | Ficha y detalle de un personaje. | Adaptada y compacta; integra multiclase, progresión automática, idiomas, actualización, conjuros y apuntes filtrados. |
| `sections/addPersonajes.php` | Creación de personaje. | Funcional; multiclase e idiomas explícitos; imágenes opcionales. |
| `sections/allSpells.php` | Catálogo y filtros de conjuros. | Adaptada. |
| `sections/spell.php?id=…` | Detalle de conjuro. | Adaptada; banner por escuela. |
| `sections/notes.php` | Lista de apuntes generales o de un personaje. | Adaptada y filtrable por personaje. |
| `sections/note.php` | Edición/creación de un apunte. | Adaptada. |
| `sections/bestiario.php` | Bestiario local en castellano. | Disponible. |
| `sections/razas.php` | Razas, subrazas y linajes jugables y no jugables. | Disponible. |
| `sections/clases.php` | Catálogo de clases y subclases. | Disponible; enlaza fichas completas. |
| `sections/clase.php?class=…&subclass=…` | Detalle de clase o subclase, progresión y espacios de conjuro. | Funcional. |
| `sections/partidas.php` | Vestíbulo de partidas, creación como DM y unión por código. | Funcional. |
| `sections/partida.php?id=…` | Mesa compartida, iniciativa, turnos, vida, estados, recursos e historial. | Funcional; WebSocket con respaldo HTTP. |
| `sections/test.php` | Ajustes. | Marcador de posición. |

### Lógica de dominio

| Archivo | Responsabilidad |
| --- | --- |
| `classes/DbConnector.php` | Acceso a base de datos. La clase conserva el nombre legado `DbConector`. |
| `classes/CharacterOptionCatalog.php` | Catálogo de clases, subclases, razas y subrazas. |
| `classes/CharacterProgression.php` | Centraliza nivel total, competencia, modificadores, salvaciones, habilidades, dados de golpe, mejoras de característica e idiomas. |
| `classes/SpellSlotProgression.php` | Calcula espacios de conjuro de una clase o combinación multiclase; separa magia de pacto. |
| `classes/CharacterSheetUpdater.php` | Valida, normaliza y recalcula automáticamente las actualizaciones de ficha. |
| `classes/CompendiumRepository.php` | Lee y normaliza los catálogos del compendio. |
| `classes/BestiaryLocalizer.php` | Localización de campos y términos del bestiario. |
| `classes/GlobalSearchService.php` | Unifica y ordena resultados públicos y privados sin mezclar usuarios. |
| `classes/ClassDetailCatalog.php` | Perfiles, rasgos, hitos y descripciones resumidas de clases; carga el catálogo estructurado de detalles de subclase. |
| `classes/GameRules.php` | Reglas puras de invitaciones, turnos y control de combatientes. |
| `classes/GameRepository.php` | Persistencia de salas, miembros, encuentros, eventos, NPC, conjuros propios y tokens de socket. |
| `classes/GameCommandService.php` | Autoriza y ejecuta comandos de la mesa compartida. |
| `src/gameApi.php` | Creación/unión, estado y respaldo HTTP autenticado mediante CSRF. |
| `websocket/server.php` | Entrada del servidor WebSocket compatible con PHP 7.4 y sin dependencias externas. |
| `src/globalSearch.php` | Endpoint JSON de sugerencias globales; limita la respuesta y no usa caché. |
| `src/updateCharacterSheet.php` | Endpoint de actualización de ficha, metadatos y archivos. |
| `src/saveNote.php` | Persistencia de apuntes. |
| `sections/_partials/characterUpdateModal.php` | Interfaz de subida/edición de ficha. |
| `sections/_partials/spellView.php` | Presentación compartida del detalle de conjuro. |

### Datos y recursos

| Ruta | Contenido |
| --- | --- |
| `data/character-options.json` | 13 clases, 118 subclases, 52 razas/linajes y 73 subrazas/variantes en la instantánea actual. |
| `data/subclass-details.json` | Rasgos estructurados por nivel, tablas y perfiles auxiliares de las subclases con detalle curado. |
| `data/bestiary-srd.json` | 334 criaturas en la instantánea local. |
| `data/nonplayable-ancestries.json` | 52 entradas no jugables verificadas por pruebas. |
| `data/source-books.json` | 28 fuentes editoriales/referencias. |
| `resources/templates/ficha-personaje.pdf` | Plantilla PDF vacía usada al crear fichas. |
| `resources/imgs/spell-banners/school-banners.png` | Recurso visual de banners de escuela. |
| `resources/chars/<personaje>/` | PDFs, imágenes opcionales y `sheet.json` de cada personaje. |
| `scripts/vendor/` | PDF.js y pdf-lib servidos localmente. |
| `init/migrations/` | Migraciones manuales existentes. |
| `database/migrations/20260728_create_game_sessions.sql` | Migración idempotente del sistema de partidas. |
| `websocket/README.md` | Arranque local, variables de entorno y despliegue `wss://`. |
| `tmp/pdfs/` | Extracciones, informes y scripts reproducibles de la auditoría de conjuros; no son recursos de ejecución normal. |

Los tres PDF entregados por el usuario para la auditoría de conjuros no están dentro del repositorio:

- `manual del jugador dnd Pero el cutre.pdf`;
- `El caldero de Tasha para todo.pdf`;
- `Guia de Xanathar Para Todo.pdf`.

En otra máquina pueden no estar disponibles. Los artefactos derivados que permiten revisar la auditoría están en `tmp/pdfs/`.

## 5. Base de datos

### Tablas observadas

- `chars`
- `character_class_levels`
- `character_languages`
- `clases`
- `conjuros`
- `conjuros_backup_20260725_194336`
- `notes`
- `razas`
- `spellset`
- `usuario`
- `games`
- `game_members`
- `game_custom_spells`
- `game_npcs`
- `game_encounters`
- `game_combatants`
- `game_events`
- `game_socket_tokens`

### Esquema funcional relevante

`chars`:

- `id_char`
- `id_user`
- `name`
- `raza`
- `subraza`
- `nivel`
- `clase`
- `subclase`
- `pdf_path`
- `image_path`
- `full_body_image_path`

`character_class_levels`:

- `id_character_class`
- `id_char`
- `class_name`
- `subclass_name`
- `class_level`
- `is_primary`
- `sort_order`

`character_languages`:

- `id_character_language`
- `id_char`
- `language_name`
- `sort_order`

`notes`:

- `ID`
- `ID_User`
- `RelatedChar`
- `Nombre`
- `Date`
- `Value`

`conjuros`:

- `id_spell`
- `name`
- `descr`
- `duracion`
- `concentracion`
- `casteo`
- `level`
- `rango`
- `clases`
- `escuela`

Sistema de partidas:

- `games` conserva propietario, código único de seis caracteres, versión de estado, encuentro actual y ajustes preparados para tablero.
- `game_members` relaciona usuarios, rol DM/jugador y personaje elegido.
- `game_encounters` y `game_combatants` conservan ronda, turno, iniciativa, vida, vida temporal, estados, concentración, recursos y `position_json` reservado para tablero.
- `game_custom_spells` y `game_npcs` contienen elementos privados de la partida.
- `game_events` es el historial persistente y la fuente de notificaciones en tiempo real.
- `game_socket_tokens` contiene únicamente hashes de tokens temporales de cuatro horas.

### Instantánea local observada el 27-07-2026

| Tabla | Filas |
| --- | ---: |
| `chars` | 7 |
| `character_class_levels` | 7 |
| `character_languages` | 0 |
| `conjuros` | 477 |
| `notes` | 8 |
| `spellset` | 56 |
| `usuario` | 2 |

Estos recuentos son datos del entorno local, no requisitos de esquema ni datos semilla portables.

### Migraciones existentes

- `init/migrations/2026-07-26-character-class-level.sql`
  añade `nivel` y `clase`, con normalización de personajes de muestra.
- `init/migrations/2026-07-26-character-subtypes.sql`
  añade `subraza` y `subclase`, normaliza nombres e incorpora Artífice.
- `init/migrations/2026-07-27-character-progression.sql`
  crea las relaciones normalizadas de clases/niveles e idiomas y migra los personajes existentes sin borrar datos.
- `init/migrations/2026-07-27-character-progression.down.sql`
  ofrece la reversión explícita de las dos tablas nuevas; no debe ejecutarse sin copia si ya contienen datos.
- `database/migrations/20260728_create_game_sessions.sql`
  crea el módulo de partidas sin alterar tablas previas; fue ejecutada en la base local el 28-07-2026.
- `database/migrations/20260728_drop_game_sessions.sql`
  documenta la reversión destructiva manual y no se ha ejecutado.

No hay todavía un sistema de migraciones versionado y automatizado. Antes de cambiar el esquema:

1. inspeccionar el esquema real;
2. crear una migración reversible o, como mínimo, idempotente;
3. conservar los datos locales;
4. documentar la ejecución en este archivo.

## 6. Cambios funcionales y visuales ya realizados

Esta sección consolida el historial de la conversación que dio forma al estado actual.

### Rediseño general

- Se replicó en `home.php` la estructura de un dashboard de fantasía oscura aportado como referencia.
- Se adaptaron los estilos de la portada, personajes, detalle de personaje, conjuros, detalle de conjuro y apuntes.
- Se revisaron las hojas CSS para que el sistema visual afecte a todas las vistas.
- Se aumentó ligeramente la legibilidad general.
- Se reemplazó la dominancia morada por una base de grafito, marfil y latón mate poco saturado.
- Se añadieron estados de foco visibles y soporte para reducción de movimiento.

### Tema por clase

- `scripts/theme.js` conserva el último tema en `localStorage` mediante la clave `deeprol.lastCharacterTheme`.
- El shell y el contenido del `iframe` sincronizan el tema.
- Temas reconocidos: `arcano`, `artifice`, `barbaro`, `bardo`, `brujo`, `clerigo`, `druida`, `explorador`, `guerrero`, `hechicero`, `mago`, `monje`, `paladin`, `picaro` y `sangre`.
- `arcano` es el tema de reserva.
- Las nuevas vistas deben cargar `styles/theme.css` al final para permitir que la capa global prevalezca sobre CSS heredado.
- El modo de luminosidad se guarda aparte en `localStorage` con la clave `deeprol.colorMode`.
- Los valores válidos son `dark` y `light`; `dark` mantiene el aspecto histórico predeterminado.
- El selector está disponible en la cabecera del shell, la landing y el login; cada botón usa `data-color-mode-toggle`.
- La identidad de clase se aplica mediante lavados ambientales, bordes, sombras interiores, navegación activa, iconos y selecciones; no se limita al texto decorativo.

### Conjuros

- Se repararon y adaptaron listado y detalle.
- El detalle identifica la escuela y aplica una variante de banner para:
  `abjuracion`, `adivinacion`, `conjuracion`, `encantamiento`,
  `evocacion`, `ilusion`, `nigromancia` y `transmutacion`.
- La variante de reserva es `conjuracion`.
- Se usa `school-banners.png` como recurso local y se conservan iconos/imágenes de escuela cuando existen.
- Se auditó la tabla local contra el Manual del jugador, Tasha, Xanathar y referencias de Roll20.
- Antes de modificarla se duplicó la tabla como `conjuros_backup_20260725_194336`, con 410 filas.
- La operación inicial dejó 478 filas; la verificación/depuración final dejó 477 nombres únicos, sin nombres vacíos ni duplicados.
- Se comprobaron 116 registros del catálogo sin errores finales.
- Los informes completos están en:
  - `tmp/pdfs/db_update_report.json`;
  - `tmp/pdfs/db_final_verification.json`.

### Personajes y creación

- Se adaptaron el listado y la ficha de personaje al nuevo diseño.
- Se corrigieron rutas de recursos locales que provocaban tiempos de espera al cargar:
  `theme.css`, `char.css`, `theme.js`, `char.js`, `notes.js` e imágenes.
- Se corrigió el error de sintaxis de `sections/personaje.php` que impedía renderizar la ficha.
- El formulario de creación genera una ficha basada en
  `resources/templates/ficha-personaje.pdf`.
- Se añadieron los campos necesarios para completar la ficha.
- Clase y nivel se guardan en base de datos.
- Cada personaje admite varias clases con nivel y subclase independientes; `chars.nivel` conserva el nivel total y `chars.clase`/`subclase` la clase inicial por compatibilidad.
- Se incorporaron selección de subclase y subraza desde el catálogo.
- Los idiomas se seleccionan de forma explícita, admiten valores personalizados y se guardan tanto en base de datos como en `sheet.json` y PDF.
- Las imágenes de retrato y cuerpo completo son opcionales.
- Cada personaje mantiene metadatos adicionales en `sheet.json`.
- Características, salvaciones, habilidades, percepción pasiva, bono de competencia, dados de golpe y ataque/CD de conjuro se recalculan al crear o actualizar.
- Las mejoras de característica se contabilizan por los hitos de nivel de cada clase; no se asignan puntos automáticamente porque D&D exige una elección del jugador.
- La vista calcula los espacios compartidos multiclase y mantiene la magia de pacto separada mediante `SpellSlotProgression`.
- La ficha recupera una composición compacta en escritorio: características, salvaciones, habilidades, vida y espacios principales quedan visibles juntos para agilizar combates.

### Actualización de ficha

- Desde la ficha se puede:
  - subir un PDF actualizado;
  - editar información general;
  - editar atributos, salvaciones, habilidades, combate y narrativa;
  - generar un PDF versionado con los nuevos valores.
- La actualización sincroniza, según corresponda:
  - la fila de `chars`;
  - `sheet.json`;
  - el PDF de la ficha.
- El endpoint valida propietario y token CSRF.
- La creación de versiones evita sobrescribir directamente la ficha anterior.
- La respuesta del guardado mantiene un contrato JSON limpio aunque PHP emita
  avisos de subida antes de ejecutar el endpoint; el cliente tolera además
  respuestas contaminadas de instalaciones antiguas para no comunicar un falso
  error después de haber persistido la ficha.
- La regeneración controla el tamaño de fuente de los campos PDF extensos para
  impedir desbordamientos o texto recortado en narrativa, equipo y rasgos.
- La edición de nombre permanece restringida porque el directorio del personaje depende del nombre actual.
- No existe todavía una interfaz de historial o restauración de versiones.

### Apuntes

- Se adaptaron visualmente lista y editor.
- `DbConector::getNotes($userId, $characterId = null)` admite filtro opcional.
- La pestaña de apuntes dentro de un personaje abre:
  `notes.php?framed=true&character_id=<id>`.
- La consulta valida que el personaje pertenezca al usuario y filtra por `RelatedChar`.
- Crear, abrir y volver conserva el contexto del personaje.
- La vista general de apuntes continúa mostrando todos los apuntes del usuario.

### Nuevas secciones de compendio

- Se añadieron al menú:
  - Bestiario;
  - Razas y linajes;
  - Clases.
- El bestiario se presenta en castellano mediante catálogo local y localizador.
- Razas incluye opciones jugables, subrazas/variantes y ascendencias no jugables.
- Clases incluye clases y subclases del catálogo curado.

### Acceso y experiencia de invitado

- El visitante sin sesión ve una landing antes de entrar.
- El login se adaptó al nuevo sistema visual.
- La aplicación conserva un modo invitado separado del usuario autenticado.

### Búsqueda global

- El campo del encabezado consulta personajes, conjuros, bestiario, razas/linajes, clases/subclases, apuntes y secciones.
- Personajes y apuntes solo se consultan cuando hay usuario identificado y siempre se filtran por su id.
- El invitado recibe únicamente conjuros, compendios y secciones públicas.
- Las sugerencias aparecen agrupadas después de dos caracteres, con cancelación de solicitudes anteriores.
- Flechas, Enter, Escape, `Ctrl/Cmd + K` y `/` permiten operar el buscador con teclado.
- Enter sin una sugerencia seleccionada abre `sections/search.php?q=…`.
- Los enlaces a bestiario, razas y clases trasladan el término mediante `q`; `scripts/compendium.js` lo aplica al filtro local.
- Si MySQL no está disponible, los catálogos JSON continúan siendo consultables.

## 7. Flujos importantes

### Creación de personaje

1. El usuario abre `sections/addPersonajes.php`.
2. El formulario carga opciones desde `CharacterOptionCatalog`.
3. El usuario distribuye hasta 20 niveles entre clases, elige subclases e idiomas y el servidor valida duplicados, umbrales y catálogo.
4. Se crea el directorio `resources/chars/<personaje>/`.
5. Se genera la ficha desde la plantilla PDF.
6. `CharacterProgression` calcula todos los campos derivados y se guarda `sheet.json` con metadatos de competencias, idiomas y clases.
7. Se crea la fila compatible en `chars`, las relaciones `character_class_levels`/`character_languages` y la relación de conjuros necesaria.
8. La ficha queda disponible en `sections/personaje.php?id=<id>`.

No se debe dar por válida una ruta construida con entrada sin normalizar. Mantener las validaciones de nombre, `basename`, extensión, tamaño y tipo MIME.

### Actualización de personaje

1. La ficha abre `characterUpdateModal.php`.
2. El navegador puede leer los campos existentes del PDF con PDF.js.
3. El usuario elige entre subir un PDF o editar campos.
4. `src/updateCharacterSheet.php` comprueba sesión, propiedad y CSRF.
5. `CharacterSheetUpdater` valida/normaliza los datos y `CharacterProgression` recalcula todos los valores derivados con el nivel total.
6. Se actualizan `chars`, relaciones de clases e idiomas, JSON y/o PDF.
7. El nuevo PDF usa un nombre versionado.

Límites observados: PDF de hasta 20 MB e imágenes de hasta 8 MB. Confirmar los valores en código si se modifican cargas.

### Tema dinámico

1. Al abrir o usar un personaje se proporciona su clase a `DeepRolTheme`.
2. La clase se normaliza y se asigna al tema más próximo.
3. El estado se persiste en `localStorage`.
4. El shell lo propaga al `iframe`.
5. `styles/theme.css` resuelve las variables cromáticas del tema.
6. De forma ortogonal, `DeepRolTheme.setColorMode()` aplica `data-color-mode`, persiste la luminosidad y emite `deeprol:colormodechange`.
7. `scripts/index.js` copia también el modo de luminosidad al documento del `iframe`.

### Apuntes de personaje

1. `personaje.php` incorpora `notes.php` con `character_id`.
2. El servidor comprueba que ese personaje pertenece al usuario.
3. La consulta incluye `ID_User` y `RelatedChar`.
4. Los enlaces del editor conservan `character_id`.

No sustituir este filtro por uno exclusivamente de cliente: el aislamiento debe mantenerse en servidor.

## 8. Estado visual

### Variables y dirección

Las nuevas reglas deben reutilizar las variables de `styles/theme.css` en lugar de introducir valores morados o colores de clase directamente en cada vista.

- fondos oscuros: grafito y negro azulado;
- fondos claros: papel cálido con superficies marfil bien separadas;
- texto principal: marfil en oscuro y grafito casi negro en claro;
- texto secundario: grises cálidos con contraste AA;
- bordes y acciones: latón mate poco saturado;
- clase activa: matiz ambiental reconocible en superficies y componentes, manteniendo saturación contenida;
- superficies: capas oscuras con contraste suficiente;
- brillo: contenido y puntual, nunca dominante.

Todavía pueden existir valores morados antiguos dentro de CSS específico. La capa global cargada al final los neutraliza. Al tocar una vista conviene migrar las reglas afectadas a variables, pero no hacer reescrituras masivas sin comprobar regresiones.

### Reglas para nuevas vistas

- Incluir `scripts/theme.js`.
- Cargar `styles/theme.css` como última hoja de estilo.
- Reutilizar tarjetas, botones, formularios, navegación y estados comunes.
- Mantener foco de teclado visible.
- Respetar `prefers-reduced-motion`.
- Verificar a anchuras de escritorio y móvil.
- Evitar dependencias remotas si existe una alternativa local.

## 9. Pruebas y verificación

### Suite actual

- `tests/ThemeCoverageTest.php`
- `tests/CharacterNotesScopeTest.php`
- `tests/CharacterProgressionTest.php`
- `tests/CharacterSaveTransportTest.php`
- `tests/CharacterSheetUpdaterTest.php`
- `tests/CharacterOptionCatalogTest.php`
- `tests/SpellSlotProgressionTest.php`
- `tests/CompendiumRepositoryTest.php`
- `tests/PersonajeRenderSmokeTest.php`
- `tests/GlobalSearchServiceTest.php`
- `tests/GlobalSearchIntegrationTest.php`
- `tests/ClassDetailViewTest.php`
- `tests/WildfireSubclassViewTest.php`
- `tests/GameRulesTest.php`
- `tests/GameSessionIntegrationTest.php`
- `tests/GameWebSocketRunner.php` y su cliente `tests/GameWebSocketClient.php`

### Último resultado conocido

El 30-07-2026 finalizaron correctamente 16 comprobaciones con PHP 7.4.33: las quince pruebas `*Test.php` y el recorrido WebSocket independiente. `ThemeCoverageTest` cubrió 20 vistas. Se verificó además en navegador el guardado real de una ficha, su recarga con los datos persistidos y la presentación del PDF regenerado sin texto desbordado.

Este resultado es histórico: hay que volver a ejecutar las pruebas después de nuevos cambios.

### Comandos recomendados

Lint de PHP:

```powershell
& 'C:\xampp\php\php.exe' -l 'ruta\archivo.php'
```

Suite:

```powershell
& 'C:\xampp\php\php.exe' 'tests\ThemeCoverageTest.php'
& 'C:\xampp\php\php.exe' 'tests\CharacterNotesScopeTest.php'
& 'C:\xampp\php\php.exe' 'tests\CharacterProgressionTest.php'
& 'C:\xampp\php\php.exe' 'tests\CharacterSaveTransportTest.php'
& 'C:\xampp\php\php.exe' 'tests\CharacterSheetUpdaterTest.php'
& 'C:\xampp\php\php.exe' 'tests\CharacterOptionCatalogTest.php'
& 'C:\xampp\php\php.exe' 'tests\SpellSlotProgressionTest.php'
& 'C:\xampp\php\php.exe' 'tests\CompendiumRepositoryTest.php'
& 'C:\xampp\php\php.exe' 'tests\PersonajeRenderSmokeTest.php'
& 'C:\xampp\php\php.exe' 'tests\GlobalSearchServiceTest.php'
& 'C:\xampp\php\php.exe' 'tests\GlobalSearchIntegrationTest.php'
& 'C:\xampp\php\php.exe' 'tests\ClassDetailViewTest.php'
& 'C:\xampp\php\php.exe' 'tests\GameRulesTest.php'
& 'C:\xampp\php\php.exe' 'tests\GameSessionIntegrationTest.php'
& 'C:\xampp\php\php.exe' 'tests\GameWebSocketRunner.php'
```

Comprobación de sintaxis JavaScript:

```powershell
Get-Content -Raw 'scripts\archivo.js' | node --check -
```

Se prefiere la entrada estándar porque `node --check ruta` ha producido errores `EPERM` en este entorno.

Para comandos Git puede ser necesario:

```powershell
git -c safe.directory=C:/Users/Pablohr11/Documents/GitHub/DeepRol status --short
```

## 10. Riesgos y deuda técnica

### Prioridad alta

- **Autenticación insegura:** la cookie `logged` funciona como identificador de usuario.
- **Contraseñas:** el flujo legado compara contraseñas sin un esquema robusto de hash.
- **Sesiones:** migrar a sesiones de servidor, cookies `HttpOnly`, `Secure` cuando corresponda y regeneración de id.
- **Autorización:** mantener comprobaciones de propietario en todos los endpoints; revisar los antiguos.
- **CSRF:** la actualización de ficha lo valida, pero hay que revisar el resto de mutaciones.

### Prioridad media

- `spellset.spells` almacena una lista serializada/textual; conviene normalizarla a una tabla relacional.
- Personaje combina tres fuentes de verdad: base de datos, `sheet.json` y PDF.
- Los PDFs versionados se acumulan y no existe limpieza, historial ni restauración.
- El directorio depende del nombre del personaje, por lo que renombrar requiere una migración transaccional de archivos y datos.
- El sistema de migraciones es manual.
- La arquitectura con `iframe` complica rutas, historial, accesibilidad y sincronización del tema.
- El editor de apuntes todavía puede depender de Quill remoto; una caída de CDN afectaría al editor.

### Producto pendiente

- Añadir el tablero virtual sobre los campos `settings.board`, `position_json` y futuros eventos `board.*`.
- Automatizar el arranque y supervisión del proceso WebSocket; en producción debe publicarse mediante `wss://`.
- Implementar Ajustes.
- Dar funcionalidad a notificaciones, mensajes y acciones de perfil.
- Añadir historial/restauración de fichas.
- Añadir paginación o virtualización si crecen bestiario y conjuros.
- Crear una gestión clara de imágenes y versiones huérfanas.

### Codificación

PowerShell puede mostrar texto UTF-8 como mojibake (`Ã¡`, `â†’`, etc.) aunque el archivo esté bien. No corregir caracteres basándose solo en la salida del terminal:

1. comprobar bytes/codificación;
2. renderizar en navegador;
3. mantener UTF-8 sin BOM salvo necesidad expresa.

## 11. Estado de Git y protección de trabajo local

En la última revisión el árbol de trabajo estaba muy modificado:

- había numerosos archivos rastreados modificados;
- había archivos nuevos sin seguimiento;
- coexistían cambios del usuario, nuevas funciones, datos y artefactos generados;
- no se verificó que todo estuviera confirmado en un commit.

Reglas:

- ejecutar `git status --short` antes de editar;
- no usar `git reset --hard`, `git checkout --`, limpiezas masivas ni borrado recursivo;
- no eliminar archivos generados sin identificar su propietario y su uso;
- no sobrescribir cambios ajenos;
- no hacer commit ni push salvo petición explícita;
- distinguir código de producto, datos locales y artefactos de auditoría.

## 12. Convenciones de implementación

- Compatibilidad obligatoria con PHP 7.4: no usar sintaxis exclusiva de PHP 8.
- La clase de base de datos mantiene el nombre legado `DbConector`; cambiarlo requiere revisar todas las referencias.
- Consultas mutables y datos del usuario deben usar parámetros preparados.
- Toda mutación sobre personajes o apuntes debe validar el usuario propietario en servidor.
- La interfaz visible debe permanecer en castellano.
- Usar recursos locales para elementos críticos.
- Mantener las imágenes opcionales en creación y actualización.
- Evitar incrustar contenido extenso de libros comerciales en el repositorio; usar datos permitidos, referencias y contenido proporcionado de forma legítima.
- Tratar los catálogos JSON como datos versionados: validar estructura y añadir pruebas cuando se amplían.
- Editar archivos de forma localizada; no reformatear todo un archivo sin necesidad.

## 13. Procedimiento obligatorio para cada cambio

### Antes

1. Leer este documento completo.
2. Leer `AGENTS.md`.
3. Ejecutar `git status --short`.
4. Inspeccionar la ruta de ejecución, estilos, datos y pruebas relacionados.
5. Verificar el esquema real si el cambio toca la base de datos.
6. Identificar qué archivos existentes pertenecen al usuario y conservarlos.

### Durante

1. Mantener el cambio dentro del alcance solicitado.
2. Preservar PHP 7.4, UTF-8, autorización y tema global.
3. Añadir o actualizar pruebas cuando cambie lógica reutilizable.
4. Evitar dependencias externas o migraciones destructivas innecesarias.

### Después

1. Ejecutar lint de todos los PHP modificados.
2. Ejecutar `node --check` para cada JavaScript modificado.
3. Ejecutar pruebas específicas y, cuando sea razonable, la suite completa.
4. Inspeccionar visualmente las vistas afectadas.
5. Revisar `git diff` y `git status`.
6. Actualizar:
   - fecha de revisión;
   - resumen del estado;
   - mapa de archivos si cambió;
   - base de datos/migraciones;
   - pruebas y su resultado;
   - riesgos o pendientes;
   - registro de cambios inferior.

## 14. Seguimiento activo

| Área | Estado | Próxima mejora sugerida |
| --- | --- | --- |
| Home | Funcional y adaptada | Sustituir datos de muestra residuales por actividad real. |
| Personajes | Funcional; ficha compacta de combate, multiclase, idiomas y progresión automática | Mejorar historial de fichas y renombrado seguro. |
| Creación de personaje | Funcional; multiclase e idiomas verificados | Revisar exhaustividad del catálogo y añadir elección guiada de dotes/ASI. |
| Actualización de ficha | Funcional; guardado confirmado de extremo a extremo, PDF legible, derivados y clases/idiomas sincronizados | Historial, restauración y limpieza de versiones. |
| Conjuros | Funcional, 477 filas locales | Normalizar relaciones con clases y fuentes. |
| Apuntes | Funcional y aislado por personaje | Servir el editor completamente en local. |
| Bestiario | Funcional | Revisar traducciones y ampliar filtros. |
| Razas y linajes | Funcional | Revisar procedencia y exhaustividad editorial. |
| Clases y subclases | Funcional; fichas y tablas 1–20; Círculo del Fuego Salvaje dispone de progresión operativa completa | Incorporar gradualmente el mismo detalle curado al resto de subclases cuando exista una fuente local legítima. |
| Tema global | Funcional, 20 vistas cubiertas, claro de alto contraste verificado, oscuro independiente y acento protagonista | Reducir CSS heredado y ampliar la auditoría WCAG a componentes interactivos. |
| Landing/login | Funcional | Endurecer autenticación y sesiones. |
| Partidas | Funcional en primera versión multijugador: códigos, WebSocket, combates, NPC, bestiario, conjuros propios, recursos e historial | Automatizar el proceso WebSocket y construir el tablero sobre el protocolo ya reservado. |
| Ajustes | Pendiente | Definir preferencias reales. |
| Búsqueda global | Funcional | Añadir paginación o un índice si el volumen de datos crece. |

## 15. Registro de cambios del contexto

### 2026-07-30 — Guardado fiable de fichas y PDF legible

- Petición: corregir el guardado de fichas, que se mostraba como fallido.
- Diagnóstico: el servidor sí actualizaba la base de datos, `sheet.json` y el PDF, pero un aviso de PHP sobre el directorio temporal de subida aparecía antes del JSON; `response.json()` fallaba y la interfaz comunicaba un error falso, favoreciendo reintentos y versiones duplicadas.
- Resultado: el endpoint limpia cualquier salida previa y desactiva la exposición de avisos en la respuesta JSON; el cliente lee primero como texto y recupera de forma compatible un JSON precedido por avisos heredados; los campos extensos del PDF usan tamaños de fuente controlados para conservar la maquetación.
- Archivos principales: `src/updateCharacterSheet.php`, `scripts/char.js`, `tests/CharacterSaveTransportTest.php` y `PROJECT_CONTEXT.md`.
- Base de datos/migraciones: sin cambios de esquema ni datos permanentes; el personaje usado en la prueba fue restaurado a su versión anterior y se eliminaron solo las versiones temporales generadas durante el diagnóstico.
- Pruebas ejecutadas y resultado: lint PHP correcto, sintaxis JavaScript correcta y 16 comprobaciones PHP superadas, incluida la integración WebSocket. La prueba real en navegador guardó, recargó y mostró los datos persistidos; el PDF resultante conservó 334 campos canónicos, 336 widgets y apariencias completas.
- Decisiones: se refuerza el contrato en servidor y cliente porque la configuración concreta de `upload_tmp_dir` puede variar entre instalaciones locales; los errores continúan registrándose en el servidor sin insertarse en el cuerpo JSON.
- Riesgos o trabajo pendiente: los PDF versionados siguen acumulándose; conviene añadir historial, restauración y una política explícita de limpieza.

### 2026-07-29 — Progresión detallada del Círculo del Fuego Salvaje

- Petición: hacer más útiles las fichas de subclase y mostrar, como ejemplo, todo lo que obtiene el Círculo del Fuego Salvaje en cada nivel.
- Resultado: la ficha muestra ganancias en niveles 2, 3, 5, 6, 7, 9, 10 y 14; separa los dos rasgos iniciales, integra las cinco tandas de conjuros siempre preparados, detalla costes, acciones, alcances, dados, usos y recargas, e incorpora el perfil completo del espíritu con características y acciones.
- Archivos principales: `data/subclass-details.json`, `ClassDetailCatalog.php`, `clase.php`, `class-detail.css`, `ClassDetailViewTest.php` y `WildfireSubclassViewTest.php`.
- Base de datos/migraciones: sin cambios.
- Pruebas ejecutadas y resultado: 15 comprobaciones PHP correctas, incluida la integración WebSocket; JSON válido y lint PHP correcto; revisión visual clara en escritorio y móvil, sin desbordamiento global ni avisos de consola.
- Decisiones: los detalles se almacenan como datos estructurados para reutilizar la misma vista en futuras subclases; el contenido es un resumen operativo de la fuente local proporcionada y evita reproducir párrafos extensos.
- Riesgos o trabajo pendiente: las demás subclases conservan por ahora sus hitos resumidos hasta incorporar detalles curados equivalentes.

### 2026-07-28 — Partidas multijugador y fichas detalladas de clases

- Petición: crear salas de partida con código alfanumérico de seis caracteres, unión de jugadores, WebSocket, control de combate sin tablero, conjuros propios, NPC, bestiario, vida, iniciativa, estados, concentración, gastos e historial; después incorporar automáticamente a los jugadores y ofrecer detalles de clases/subclases.
- Resultado: vestíbulo y mesa compartida completos; permisos distintos para DM y jugador; tokens temporales almacenados como hash; estado autoritativo y eventos persistentes; respaldo HTTP; personajes vinculados añadidos al crear el encuentro o al unirse más tarde; reconciliación automática de grupos antiguos sin duplicados; 13 fichas de clase y 118 rutas de subclase con progresión, fuentes, hitos, descripciones resumidas y tablas de espacios.
- Archivos principales: `GameRules.php`, `GameRepository.php`, `GameCommandService.php`, `ClassDetailCatalog.php`, `partidas.php`, `partida.php`, `clases.php`, `clase.php`, `gameApi.php`, `GameWebSocketServer.php`, `server.php`, `games.js`, `game.js`, `games.css` y `class-detail.css`.
- Base de datos/migraciones: ejecutada `database/migrations/20260728_create_game_sessions.sql`; añade ocho tablas `game_*`/`games` sin modificar las anteriores. La reversión está documentada y no ejecutada.
- Pruebas ejecutadas y resultado: 14 comprobaciones correctas con PHP 7.4.33, incluidas reglas, integración MariaDB, incorporación automática previa/tardía, idempotencia, autorización y un cliente WebSocket real; sintaxis de ambos JavaScript correcta; revisión visual de vestíbulo y ficha de Mago/subclase sin desbordamiento horizontal.
- Decisiones: sin dependencia WebSocket externa; el socket solo acepta identidad mediante token aleatorio temporal; el DM controla toda la mesa y cada jugador únicamente su combatiente; un usuario sin personajes entra como espectador; `position_json`, `settings.board` y la convención `board.*` reservan la ampliación futura.
- Riesgos o trabajo pendiente: el proceso WebSocket debe ejecutarse aparte (`php websocket/server.php`) y servirse detrás de TLS/proxy en producción; la autenticación HTTP heredada por cookie sigue siendo deuda; los resúmenes de subclase evitan reproducir texto comercial extenso y pueden enriquecerse con fuentes legítimas.

### 2026-07-27 — Progresión automática, idiomas, multiclase y ficha compacta

- Petición: recalcular características y habilidades al crear/mejorar personajes, añadir idiomas, permitir multiclase indicando niveles y devolver la ficha a una composición directa sin desplazamiento innecesario en combate.
- Resultado: modelo central de progresión; nivel total por suma; niveles/subclases por clase; salvaciones y habilidades automáticas; percepción, competencia, dados de golpe, magia y mejoras de característica sincronizados; idiomas normalizados; ficha y editor multiclase; presentación compacta en escritorio.
- Archivos principales: `CharacterProgression.php`, `SpellSlotProgression.php`, `CharacterSheetUpdater.php`, `DbConnector.php`, `addPersonajes.php`, `personaje.php`, `characterUpdateModal.php`, `updateCharacterSheet.php`, `form.js`, `char.js`, `form.css` y `char.css`.
- Base de datos/migraciones: creadas y ejecutadas `character_class_levels` y `character_languages` mediante `2026-07-27-character-progression.sql`; siete personajes existentes migrados a una clase inicial; migración inversa documentada pero no ejecutada.
- Pruebas ejecutadas y resultado: diez pruebas PHP correctas, lint de todos los PHP modificados, sintaxis JavaScript correcta, interacción real de creación/edición multiclase e idiomas, ficha a 1280×720 con estadísticas tácticas visibles y consola del navegador sin errores.
- Decisiones: `chars` conserva campos principales compatibles; el bono usa nivel total; hitos ASI usan nivel de cada clase; los puntos de ASI no se reparten sin elección; espacios estándar se combinan solo cuando aplican las reglas multiclase y los de brujo permanecen separados.
- Riesgos o trabajo pendiente: personajes anteriores no reciben idiomas inventados durante la migración; deben indicarse al editar. La elección de dotes frente a mejora de característica continúa siendo decisión manual.

### 2026-07-27 — Revisión integral del modo claro y ergonomía de la ficha

- Petición: ajustar todas las vistas porque el modo claro seguía siendo poco legible y la ficha de personaje resultaba incómoda.
- Resultado: lienzo claro más neutro, paneles blancos sólidos, límites más definidos, tipografía secundaria más oscura, controles de al menos 40–44 px y neutralización de fondos oscuros heredados en navegación, formularios, compendios, apuntes, conjuros, login y creación.
- Ficha de personaje: atributos en dos filas amplias, habilidades en bloque independiente, salvaciones y espacios de conjuro con texto y objetivos táctiles mayores, columna lateral más compacta, editor de ficha completamente claro y adaptación móvil a ancho completo.
- Archivos principales: `styles/theme.css`, `styles/char.css` y `PROJECT_CONTEXT.md`.
- Base de datos/migraciones: sin cambios.
- Pruebas ejecutadas y resultado: nueve pruebas PHP correctas (`ThemeCoverageTest` cubre 18 vistas); revisión visual de landing, shell/home, personajes, ficha, editor de ficha, conjuros, detalle de conjuro, apuntes, bestiario, login, creación y ficha móvil.
- Decisiones: reservar el acento de clase para bordes, iconos y estados activos; evitar velos de color sobre el texto; conservar oscuras únicamente las tarjetas fotográficas que garantizan texto marfil.
- Riesgos o trabajo pendiente: la capa final todavía compensa CSS heredado con valores oscuros y convendría migrar cada hoja a variables semánticas en una refactorización futura.

### 2026-07-27 — Mayor protagonismo del color de clase

- Petición: hacer más visibles los colores asociados a cada clase.
- Resultado: el acento colorea ahora fondos ambientales, bordes, sombras interiores, navegación activa, encabezados, iconos, pestañas y controles seleccionados en los modos claro y oscuro.
- Archivos principales: `styles/theme.css`, `tests/ThemeCoverageTest.php` y `PROJECT_CONTEXT.md`.
- Base de datos/migraciones: sin cambios.
- Pruebas: `ThemeCoverageTest` valida 18 vistas, contraste del modo claro y 15 acentos de clase distintos; revisión visual de home en claro y oscuro.
- Decisiones: aumentar presencia mediante capas translúcidas y bordes, sin elevar la saturación ni convertir el tema en neón.

### 2026-07-27 — Refuerzo de legibilidad del modo claro

- Petición: corregir la escasa legibilidad del primer modo claro.
- Resultado: fondo más definido, superficies marfil sólidas, bordes más visibles, texto principal casi negro, texto secundario más oscuro y neutralización de grises heredados en formularios, fichas, conjuros, apuntes, compendios, búsqueda y creación.
- Archivos principales: `styles/theme.css`, `tests/ThemeCoverageTest.php` y `PROJECT_CONTEXT.md`.
- Base de datos/migraciones: sin cambios.
- Pruebas: nueve pruebas PHP correctas; cobertura de 18 vistas; contraste estático mínimo AAA para texto principal y AA para texto secundario; revisión interactiva de landing, home, personajes, conjuros, bestiario y login.
- Decisiones: conservar el papel cálido y el acento de clase, pero eliminar transparencias lavadas y elevar el contraste como requisito verificable.

### 2026-07-27 — Modos claro y oscuro intercambiables

- Petición: ofrecer un tema claro y otro oscuro que se puedan alternar con un botón.
- Resultado: selector accesible y persistente en shell, landing y login; el modo se propaga al `iframe` sin recargar y conserva el acento de la última clase.
- Archivos principales: `scripts/theme.js`, `scripts/index.js`, `styles/theme.css`, `sections/_partials/header.php`, `sections/landing.php`, `login.php` y `tests/ThemeCoverageTest.php`.
- Base de datos/migraciones: sin cambios; la preferencia vive en `localStorage` bajo `deeprol.colorMode`.
- Pruebas: lint PHP, validación de sintaxis JavaScript, nueve pruebas PHP, cobertura de 18 vistas y revisión interactiva de landing, login, home, personajes, conjuros y bestiario en ambos modos.
- Decisiones: modo oscuro predeterminado para mantener continuidad; modo claro de papel cálido y grafito; acento de clase independiente; control disponible también en móvil.

### 2026-07-27 — Búsqueda global del encabezado

- Petición: sustituir la redirección exclusiva al grimorio por una búsqueda realmente global.
- Resultado: sugerencias agrupadas y página completa para personajes, conjuros, bestiario, razas/linajes, clases/subclases, apuntes y secciones.
- Privacidad: personajes y apuntes se filtran por el usuario actual; no aparecen en modo invitado.
- Archivos principales: `GlobalSearchService.php`, `globalSearch.php`, `search.php`, `header.js`, `search.js`, `header.css`, `search.css`, `DbConnector.php` y `compendium.js`.
- Base de datos/migraciones: sin cambios de esquema; se añadieron consultas preparadas de solo lectura.
- Pruebas: lint PHP y JavaScript, nueve pruebas PHP, cobertura de 18 vistas y comprobación interactiva en navegador.
- Decisiones: mínimo de dos caracteres, resultados limitados por grupo, navegación con teclado y degradación parcial cuando MySQL no está disponible.

### 2026-07-27 — Creación del contexto maestro

- Se creó este documento exportable.
- Se consolidó el historial funcional y visual de la conversación.
- Se documentaron arquitectura, rutas, datos, esquema, recursos y flujos.
- Se registraron las verificaciones conocidas y la suite actual.
- Se separó el estado persistente del producto de las instantáneas locales y los artefactos de auditoría.
- Se añadió `AGENTS.md` como punto de entrada obligatorio para futuros agentes.

### Plantilla para futuras entradas

```markdown
### AAAA-MM-DD — Título breve

- Petición:
- Resultado:
- Archivos principales:
- Base de datos/migraciones:
- Pruebas ejecutadas y resultado:
- Decisiones:
- Riesgos o trabajo pendiente:
```

---

## 16. Resumen de traspaso en una frase

DeepRol es una aplicación PHP 7.4 local de gestión de D&D 5e en castellano, con dashboard de fantasía, modos claro/oscuro, acento por clase, personajes multiclase, ficha compacta, conjuros, compendios y apuntes por personaje; ahora incluye partidas multijugador por WebSocket con códigos, combate persistente y fichas detalladas de clases/subclases, aunque conserva deuda en autenticación, automatización de procesos/migraciones, historial de fichas, `iframe`, tablero virtual y Ajustes.
