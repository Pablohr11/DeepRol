# Tiempo real de DeepRol

El servidor no necesita paquetes Composer adicionales y funciona con PHP 7.4.

Desde la raíz del proyecto:

```powershell
C:\xampp\php\php.exe websocket\server.php
```

Escucha por defecto en `127.0.0.1:8081`. Se puede cambiar con:

```powershell
$env:DEEPROL_WS_HOST = "0.0.0.0"
$env:DEEPROL_WS_PORT = "8081"
C:\xampp\php\php.exe websocket\server.php
```

Variables disponibles:

- `DEEPROL_WS_HOST`: interfaz de escucha.
- `DEEPROL_WS_PORT`: puerto de escucha.
- `DEEPROL_WS_PUBLIC_URL`: URL que la vista entrega al navegador, por ejemplo
  `wss://partidas.ejemplo.com/socket`.
- `DEEPROL_WS_ALLOWED_ORIGINS`: orígenes HTTP permitidos, separados por comas.

En producción debe publicarse detrás de un proxy inverso con TLS para ofrecer
`wss://`. Los mensajes usan comandos y eventos independientes de la interfaz.
El esquema de combatientes ya incluye `position_json` y la partida reserva
`settings.board`, de forma que un tablero futuro pueda incorporarse mediante
eventos `board.*` sin cambiar el protocolo existente.
