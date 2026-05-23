<?php
// Lógica de negocio PHP
$nombre_jugador = "Jugador 1";
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['jugador'])) {
    $nombre_jugador = htmlspecialchars(trim($_POST['jugador']));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arcade Tetris - Jugando</title>
    <style>
        :root { --neon: #f0f; }
        body {
            margin: 0; background-color: #050505; color: #fff; font-family: 'Courier New', Courier, monospace;
            display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh;
        }
        .game-wrapper {
            display: flex; gap: 30px; background: #111; padding: 20px; border-radius: 15px;
            box-shadow: 0 0 30px rgba(255, 0, 255, 0.2); border: 1px solid #333;
            flex-wrap: wrap; justify-content: center;
        }
        canvas {
            background-color: #000; border: 2px solid #333; border-radius: 5px;
            box-shadow: inset 0 0 20px rgba(0,0,0,1);
        }
        .ui-panel {
            display: flex; flex-direction: column; justify-content: space-between; width: 200px;
        }
        .info-box {
            background: #000; padding: 15px; border-radius: 8px; border: 1px solid #333; margin-bottom: 10px;
            text-align: center; box-shadow: inset 0 0 10px rgba(255, 0, 255, 0.1);
        }
        .info-box h2 { margin: 0; font-size: 1.2em; color: var(--neon); text-shadow: 0 0 5px var(--neon); }
        .info-box p { font-size: 2em; margin: 10px 0 0; font-weight: bold; }
        .controls { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 20px; }
        .btn-ctrl {
            background: #222; border: 1px solid #444; color: #fff; padding: 15px 0; border-radius: 5px;
            font-size: 1.2em; cursor: pointer; transition: 0.2s; user-select: none; touch-action: manipulation;
        }
        .btn-ctrl:active { background: var(--neon); color: #000; box-shadow: 0 0 15px var(--neon); transform: scale(0.95); }
        .btn-up { grid-column: 2; }
        .btn-left { grid-column: 1; }
        .btn-down { grid-column: 2; }
        .btn-right { grid-column: 3; }
        .btn-action { grid-column: 1 / span 3; background: #300; border-color: #f00; color: #f00; margin-top: 10px; }
        .btn-action:active { background: #f00; color: #fff; box-shadow: 0 0 15px #f00; }
        .exit-btn {
            text-decoration: none; color: #fff; background: transparent; border: 1px solid #fff;
            padding: 10px; text-align: center; border-radius: 5px; margin-top: 20px; display: block; transition: 0.3s;
        }
        .exit-btn:hover { background: #fff; color: #000; }
        @media (max-width: 600px) { .game-wrapper { flex-direction: column; gap: 15px; padding: 10px; } }
    </style>
</head>
<body>

    <div class="game-wrapper">
        <canvas id="tetris" width="240" height="400"></canvas>

        <div class="ui-panel">
            <div>
                <div class="info-box">
                    <h2>JUGADOR</h2>
                    <div style="font-size: 1.2em; margin-top: 5px; color:#ccc;"><?php echo $nombre_jugador; ?></div>
                </div>
                <div class="info-box">
                    <h2>PUNTAJE</h2>
                    <p id="score">0</p>
                </div>
            </div>

            <div class="controls">
                <button class="btn-ctrl btn-up" id="btn-rot">↻</button>
                <button class="btn-ctrl btn-left" id="btn-left">◀</button>
                <button class="btn-ctrl btn-down" id="btn-down">▼</button>
                <button class="btn-ctrl btn-right" id="btn-right">▶</button>
                <button class="btn-ctrl btn-action" id="btn-drop">caída libre</button>
            </div>

            <a href="index.php" class="exit-btn">Cerrar Sesión</a>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('tetris');
        const context = canvas.getContext('2d');
        context.scale(20, 20);

        // Colores Neón para las fichas
        const colors = [
            null,
            '#FF0D72', // T
            '#0DC2FF', // O
            '#0DFF72', // L
            '#F538FF', // J
            '#FF8E0D', // I
            '#FFE138', // S
            '#3877FF'  // Z
        ];

        function createMatrix(w, h) {
            const matrix = [];
            while (h--) { matrix.push(new Array(w).fill(0)); }
            return matrix;
        }

        function createPiece(type) {
            if (type === 'T') return [[0,0,0], [1,1,1], [0,1,0]];
            if (type === 'O') return [[2,2], [2,2]];
            if (type === 'L') return [[0,3,0], [0,3,0], [0,3,3]];
            if (type === 'J') return [[0,4,0], [0,4,0], [4,4,0]];
            if (type === 'I') return [[0,5,0,0], [0,5,0,0], [0,5,0,0], [0,5,0,0]];
            if (type === 'S') return [[0,6,6], [6,6,0], [0,0,0]];
            if (type === 'Z') return [[7,7,0], [0,7,7], [0,0,0]];
        }

        // Dibuja la cuadrícula de fondo
        function drawGrid() {
            context.strokeStyle = '#222';
            context.lineWidth = 0.05;
            for(let i = 0; i < 12; i++) {
                for(let j = 0; j < 20; j++) {
                    context.strokeRect(i, j, 1, 1);
                }
            }
        }

        // Dibuja las fichas con efecto de luz
        function drawMatrix(matrix, offset) {
            matrix.forEach((row, y) => {
                row.forEach((value, x) => {
                    if (value !== 0) {
                        context.fillStyle = colors[value];
                        context.shadowBlur = 10;
                        context.shadowColor = colors[value];
                        context.fillRect(x + offset.x, y + offset.y, 1, 1);
                        
                        // Borde interno de la ficha
                        context.shadowBlur = 0;
                        context.strokeStyle = 'rgba(255,255,255,0.5)';
                        context.lineWidth = 0.1;
                        context.strokeRect(x + offset.x, y + offset.y, 1, 1);
                    }
                });
            });
        }

        function draw() {
            context.fillStyle = '#000';
            context.shadowBlur = 0; 
            context.fillRect(0, 0, canvas.width, canvas.height);
            drawGrid();
            drawMatrix(arena, {x: 0, y: 0});
            drawMatrix(player.matrix, player.pos);
        }

        function merge(arena, player) {
            player.matrix.forEach((row, y) => {
                row.forEach((value, x) => {
                    if (value !== 0) arena[y + player.pos.y][x + player.pos.x] = value;
                });
            });
        }

        function collide(arena, player) {
            const [m, o] = [player.matrix, player.pos];
            for (let y = 0; y < m.length; ++y) {
                for (let x = 0; x < m[y].length; ++x) {
                    if (m[y][x] !== 0 && (arena[y + o.y] && arena[y + o.y][x + o.x]) !== 0) {
                        return true;
                    }
                }
            }
            return false;
        }

        function playerDrop() {
            player.pos.y++;
            if (collide(arena, player)) {
                player.pos.y--;
                merge(arena, player);
                playerReset();
                arenaSweep();
                updateScore();
            }
            dropCounter = 0;
        }
        
        function playerHardDrop() {
            while(!collide(arena, player)) { player.pos.y++; }
            player.pos.y--;
            merge(arena, player);
            playerReset();
            arenaSweep();
            updateScore();
            dropCounter = 0;
        }

        function playerMove(dir) {
            player.pos.x += dir;
            if (collide(arena, player)) player.pos.x -= dir;
        }

        function playerReset() {
            const pieces = 'ILJOTSZ';
            player.matrix = createPiece(pieces[pieces.length * Math.random() | 0]);
            player.pos.y = 0;
            player.pos.x = (arena[0].length / 2 | 0) - (player.matrix[0].length / 2 | 0);
            if (collide(arena, player)) {
                arena.forEach(row => row.fill(0));
                player.score = 0;
                updateScore();
            }
        }

        function playerRotate(dir) {
            const pos = player.pos.x;
            let offset = 1;
            rotate(player.matrix, dir);
            while (collide(arena, player)) {
                player.pos.x += offset;
                offset = -(offset + (offset > 0 ? 1 : -1));
                if (offset > player.matrix[0].length) {
                    rotate(player.matrix, -dir);
                    player.pos.x = pos;
                    return;
                }
            }
        }

        function rotate(matrix, dir) {
            for (let y = 0; y < matrix.length; ++y) {
                for (let x = 0; x < y; ++x) {
                    [matrix[x][y], matrix[y][x]] = [matrix[y][x], matrix[x][y]];
                }
            }
            if (dir > 0) matrix.forEach(row => row.reverse());
            else matrix.reverse();
        }

        function arenaSweep() {
            let rowCount = 1;
            outer: for (let y = arena.length - 1; y > 0; --y) {
                for (let x = 0; x < arena[y].length; ++x) {
                    if (arena[y][x] === 0) continue outer;
                }
                const row = arena.splice(y, 1)[0].fill(0);
                arena.unshift(row);
                ++y;
                player.score += rowCount * 50;
                rowCount *= 2;
            }
        }

        let dropCounter = 0;
        let dropInterval = 1000;
        let lastTime = 0;

        function update(time = 0) {
            const deltaTime = time - lastTime;
            lastTime = time;
            dropCounter += deltaTime;
            
            // Aumenta la dificultad (velocidad) según el puntaje
            dropInterval = Math.max(100, 1000 - (player.score * 2)); 

            if (dropCounter > dropInterval) playerDrop();
            draw();
            requestAnimationFrame(update);
        }

        function updateScore() {
            document.getElementById('score').innerText = player.score;
        }

        const arena = createMatrix(12, 20);
        const player = { pos: {x: 0, y: 0}, matrix: null, score: 0 };

        // Controles de Teclado
        document.addEventListener('keydown', event => {
            if (event.keyCode === 37) playerMove(-1); // Izq
            else if (event.keyCode === 39) playerMove(1);  // Der
            else if (event.keyCode === 40) playerDrop();   // Abajo lento
            else if (event.keyCode === 38) playerRotate(1); // Arriba rota
            else if (event.keyCode === 32) playerHardDrop(); // Espacio caída libre
        });

        // Controles de Botones en Pantalla (Interactivo)
        document.getElementById('btn-left').addEventListener('click', () => playerMove(-1));
        document.getElementById('btn-right').addEventListener('click', () => playerMove(1));
        document.getElementById('btn-down').addEventListener('click', () => playerDrop());
        document.getElementById('btn-rot').addEventListener('click', () => playerRotate(1));
        document.getElementById('btn-drop').addEventListener('click', () => playerHardDrop());

        playerReset();
        updateScore();
        update();
    </script>
</body>
</html>
