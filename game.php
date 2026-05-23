<?php
// Lógica de negocio PHP: Recibir el nombre del jugador
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
    <title>Arcade Tetris - Pro Edition</title>
    <style>
        :root { --neon: #f0f; }
        body {
            margin: 0; background-color: #050505; color: #fff; font-family: 'Courier New', Courier, monospace;
            display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh;
            overflow: hidden;
        }
        .game-wrapper {
            display: flex; gap: 30px; background: #111; padding: 20px; border-radius: 15px;
            box-shadow: 0 0 40px rgba(255, 0, 255, 0.3); border: 2px solid #333;
            flex-wrap: wrap; justify-content: center; position: relative; transition: transform 0.1s ease-out;
        }
        /* Animación Screen Shake */
        .game-wrapper.shake {
            animation: shake 0.2s cubic-bezier(.36,.07,.19,.97) both;
        }
        @keyframes shake {
            10%, 90% { transform: translate3d(-2px, 0, 0); }
            20%, 80% { transform: translate3d(3px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-5px, 0, 0); }
            40%, 60% { transform: translate3d(5px, 0, 0); }
        }
        canvas#tetris {
            background-color: #000; border: 2px solid #333; border-radius: 5px;
            box-shadow: inset 0 0 30px rgba(0,0,0,1);
        }
        .ui-panel {
            display: flex; flex-direction: column; justify-content: space-between; width: 220px;
        }
        .info-box {
            background: #000; padding: 12px; border-radius: 8px; border: 1px solid #333; margin-bottom: 10px;
            text-align: center; box-shadow: inset 0 0 15px rgba(255, 0, 255, 0.1);
        }
        .info-box h2 { margin: 0; font-size: 1em; color: var(--neon); text-shadow: 0 0 10px var(--neon); letter-spacing: 1px; }
        .info-box p { font-size: 1.8em; margin: 5px 0 0; font-weight: bold; }
        
        /* Contenedor del Canvas Secundario */
        canvas#next {
            background: #000; border: 1px solid #222; margin-top: 8px; border-radius: 4px;
        }
        .controls { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 15px; }
        .btn-ctrl {
            background: #222; border: 2px solid #444; color: #fff; padding: 14px 0; border-radius: 8px;
            font-size: 1.2em; cursor: pointer; transition: 0.2s; user-select: none; touch-action: manipulation;
        }
        .btn-ctrl:active { background: var(--neon); color: #000; box-shadow: 0 0 20px var(--neon); transform: scale(0.95); border-color: var(--neon); }
        .btn-up { grid-column: 2; }
        .btn-left { grid-column: 1; }
        .btn-down { grid-column: 2; }
        .btn-right { grid-column: 3; }
        .btn-action { grid-column: 1 / span 3; background: #300; border-color: #f00; color: #f00; margin-top: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9em;}
        .btn-action:active { background: #f00; color: #fff; box-shadow: 0 0 20px #f00; border-color: #f00;}
        .exit-btn {
            text-decoration: none; color: #fff; background: transparent; border: 2px solid #fff;
            padding: 10px; text-align: center; border-radius: 8px; margin-top: 15px; display: block; transition: 0.3s; font-weight: bold; font-size: 0.9em;
        }
        .exit-btn:hover { background: #fff; color: #000; box-shadow: 0 0 20px #fff; }
        @media (max-width: 600px) { .game-wrapper { flex-direction: column; gap: 15px; padding: 15px; } .ui-panel {width: 100%;} }
    </style>
</head>
<body>

    <div class="game-wrapper" id="game-wrapper">
        <canvas id="tetris" width="240" height="400"></canvas>

        <div class="ui-panel">
            <div>
                <div class="info-box">
                    <h2>JUGADOR</h2>
                    <div style="font-size: 1.1em; margin-top: 5px; color:#fff; font-weight: bold; text-shadow: 0 0 5px #fff;"><?php echo $nombre_jugador; ?></div>
                </div>
                
                <div class="info-box">
                    <h2>SIGUIENTE</h2>
                    <canvas id="next" width="80" height="80"></canvas>
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

            <a href="index.php" class="exit-btn">Finalizar Práctica</a>
        </div>
    </div>

    <script>
        // Configuración de Canvas Principal
        const canvas = document.getElementById('tetris');
        const context = canvas.getContext('2d');
        context.scale(20, 20);

        // Configuración de Canvas de Siguiente Pieza
        const nextCanvas = document.getElementById('next');
        const nextContext = nextCanvas.getContext('2d');
        nextContext.scale(20, 20);

        const gameWrapper = document.getElementById('game-wrapper');
        const colors = [ null, '#FF0D72', '#0DC2FF', '#0DFF72', '#F538FF', '#FF8E0D', '#FFE138', '#3877FF' ];

        // --- SISTEMA DE PARTÍCULAS ---
        class Particle {
            constructor(x, y, color) {
                this.x = x; this.y = y;
                this.vx = (Math.random() - 0.5) * 0.4;
                this.vy = (Math.random() - 0.5) * 0.4;
                this.alpha = 1; this.color = color;
                this.size = Math.random() * 0.15 + 0.05;
                this.decay = Math.random() * 0.02 + 0.015;
            }
            update() { this.x += this.vx; this.y += this.vy; this.alpha -= this.decay; }
            draw() {
                if (this.alpha <= 0) return;
                context.save();
                context.globalAlpha = this.alpha;
                context.fillStyle = this.color;
                context.shadowBlur = 10; context.shadowColor = this.color;
                context.beginPath(); context.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                context.fill(); context.restore();
            }
        }

        class ExplosionManager {
            constructor() { this.particles = []; }
            createExplosion(rowIdx, color) {
                for (let x = 0; x < 12; x++) {
                    for (let i = 0; i < 8; i++) {
                        this.particles.push(new Particle(x + Math.random(), rowIdx + Math.random(), color));
                    }
                }
            }
            updateAndDraw() {
                this.particles = this.particles.filter(p => p.alpha > 0);
                this.particles.forEach(p => { p.update(); p.draw(); });
            }
        }
        const explosions = new ExplosionManager();

        // --- MANEJO DE MATRICES ---
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

        function drawGrid() {
            context.strokeStyle = '#222';
            context.lineWidth = 0.05;
            for(let i = 0; i < 12; i++) {
                for(let j = 0; j < 20; j++) { context.strokeRect(i, j, 1, 1); }
            }
        }

        // --- PROPUESTA 1: LÓGICA DE LA PIEZA FANTASMA (GHOST PIECE) ---
        function getGhostPositionY() {
            let ghostY = player.pos.y;
            // Clonamos virtualmente el movimiento hacia abajo hasta detectar colisión
            while (!collide(arena, { pos: { x: player.pos.x, y: ghostY + 1 }, matrix: player.matrix })) {
                ghostY++;
            }
            return ghostY;
        }

        // Renderizado Dinámico de Elementos
        function drawMatrix(matrix, offset, type = 'normal') {
            matrix.forEach((row, y) => {
                row.forEach((value, x) => {
                    if (value !== 0) {
                        context.save();
                        if (type === 'ghost') {
                            // Estilo para la silueta fantasma (semi-transparente con borde neón)
                            context.globalAlpha = 0.15;
                            context.fillStyle = '#ffffff';
                            context.fillRect(x + offset.x, y + offset.y, 1, 1);
                            context.strokeStyle = colors[value];
                            context.lineWidth = 0.08;
                            context.strokeRect(x + offset.x, y + offset.y, 1, 1);
                        } else {
                            if (type === 'player') {
                                context.shadowBlur = 8;
                                context.shadowColor = colors[value];
                            }
                            context.fillStyle = colors[value];
                            context.fillRect(x + offset.x, y + offset.y, 1, 1);
                            context.strokeStyle = 'rgba(255,255,255,0.4)';
                            context.lineWidth = 0.05;
                            context.strokeRect(x + offset.x, y + offset.y, 1, 1);
                        }
                        context.restore();
                    }
                });
            });
        }

        // --- PROPUESTA 2: RENDERIZAR SIGUIENTE PIEZA ---
        function drawNext() {
            nextContext.fillStyle = '#000';
            nextContext.fillRect(0, 0, nextCanvas.width, nextCanvas.height);
            
            if (!player.next) return;
            
            const m = player.next.matrix;
            // Centrado matemático de la pieza dentro del cuadro de visualización
            const offsetX = (4 - m[0].length) / 2;
            const offsetY = (4 - m.length) / 2;

            m.forEach((row, y) => {
                row.forEach((value, x) => {
                    if (value !== 0) {
                        nextContext.fillStyle = colors[value];
                        nextContext.fillRect(x + offsetX, y + offsetY, 1, 1);
                        nextContext.strokeStyle = 'rgba(255,255,255,0.3)';
                        nextContext.lineWidth = 0.05;
                        nextContext.strokeRect(x + offsetX, y + offsetY, 1, 1);
                    }
                });
            });
        }

        function draw() {
            context.fillStyle = '#000';
            context.fillRect(0, 0, canvas.width, canvas.height);
            drawGrid();
            
            // 1. Dibujar el mapa consolidado
            drawMatrix(arena, {x: 0, y: 0}, 'normal');
            
            // 2. Dibujar Proyección Fantasma
            const ghostY = getGhostPositionY();
            drawMatrix(player.matrix, { x: player.pos.x, y: ghostY }, 'ghost');
            
            // 3. Dibujar la pieza activa del usuario
            drawMatrix(player.matrix, player.pos, 'player');
            
            // 4. Actualizar explosiones de partículas
            explosions.updateAndDraw();
        }

        function merge(arena, player) {
            player.matrix.forEach((row, y) => {
                row.forEach((value, x) => {
                    if (value !== 0) arena[y + player.pos.y][x + player.pos.x] = value;
                });
            });
        }

        function collide(arena, current) {
            const [m, o] = [current.matrix, current.pos];
            for (let y = 0; y < m.length; ++y) {
                for (let x = 0; x < m[y].length; ++x) {
                    if (m[y][x] !== 0 && (arena[y + o.y] && arena[y + o.y][x + o.x]) !== 0) {
                        return true;
                    }
                }
            }
            return false;
        }

        function triggerScreenShake(duration = 200) {
            gameWrapper.classList.add('shake');
            setTimeout(() => { gameWrapper.classList.remove('shake'); }, duration);
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
            const ghostY = getGhostPositionY();
            player.pos.y = ghostY; // Salto directo a la colisión calculada
            merge(arena, player);
            playerReset();
            arenaSweep();
            updateScore();
            triggerScreenShake(200);
            dropCounter = 0;
        }

        function playerMove(dir) {
            player.pos.x += dir;
            if (collide(arena, player)) player.pos.x -= dir;
        }

        function getRandomPieceType() {
            const pieces = 'ILJOTSZ';
            return pieces[pieces.length * Math.random() | 0];
        }

        // Sistema de Reinicio y Cola de Espera
        function playerReset() {
            if (!player.next) {
                player.next = { matrix: createPiece(getRandomPieceType()) };
            }
            
            // Asigna la pieza en cola y genera la siguiente de forma anticipada
            player.matrix = player.next.matrix;
            player.next.matrix = createPiece(getRandomPieceType());
            
            player.pos.y = 0;
            player.pos.x = (arena[0].length / 2 | 0) - (player.matrix[0].length / 2 | 0);
            
            if (collide(arena, player)) {
                triggerScreenShake(400);
                arena.forEach(row => row.fill(0));
                player.score = 0;
                updateScore();
            }
            
            drawNext(); // Actualiza el canvas secundario
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
            let rowCount = 0;
            outer: for (let y = arena.length - 1; y > 0; --y) {
                for (let x = 0; x < arena[y].length; ++x) {
                    if (arena[y][x] === 0) continue outer;
                }
                
                let val = arena[y][0] || 1;
                explosions.createExplosion(y, colors[val]);

                const row = arena.splice(y, 1)[0].fill(0);
                arena.unshift(row);
                ++y;
                rowCount++;
            }
            
            if(rowCount > 0) {
               player.score += rowCount * 100 * rowCount;
               updateScore();
               if (rowCount >= 3) triggerScreenShake(300);
               else triggerScreenShake(150);
            }
        }

        let dropCounter = 0;
        let dropInterval = 1000;
        let lastTime = 0;

        function update(time = 0) {
            const deltaTime = time - lastTime;
            lastTime = time;
            dropCounter += deltaTime;
            
            dropInterval = Math.max(100, 1000 - (player.score / 50)); 

            if (dropCounter > dropInterval) playerDrop();
            draw();
            requestAnimationFrame(update);
        }

        function updateScore() {
            document.getElementById('score').innerText = player.score;
        }

        // Instanciación del Estado Inicial del Juego
        const arena = createMatrix(12, 20);
        const player = { pos: {x: 0, y: 0}, matrix: null, next: null, score: 0 };

        // Eventos del Teclado
        document.addEventListener('keydown', event => {
            if (event.keyCode === 37) playerMove(-1);
            else if (event.keyCode === 39) playerMove(1);
            else if (event.keyCode === 40) playerDrop();
            else if (event.keyCode === 38) playerRotate(1);
            else if (event.keyCode === 32) playerHardDrop();
        });

        // Mapeo de Eventos en Pantalla Interactiva
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
