<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arcade Tetris - Ingreso</title>
    <style>
        :root { --neon-color: #0ff; --bg-color: #0a0a12; }
        body {
            margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color); color: #fff;
            display: flex; justify-content: center; align-items: center; height: 100vh;
            background-image: radial-gradient(circle at center, #1a1a2e 0%, #0a0a12 100%);
        }
        .login-box {
            background: rgba(20, 20, 30, 0.8); padding: 40px; border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 255, 255, 0.3), inset 0 0 20px rgba(0, 255, 255, 0.1);
            border: 2px solid rgba(0, 255, 255, 0.3); text-align: center;
            animation: float 4s ease-in-out infinite, neonPulse 1.5s ease-in-out infinite alternate; width: 300px;
        }
        h1 { margin-top: 0; color: #fff; text-shadow: 0 0 10px var(--neon-color), 0 0 20px var(--neon-color), 0 0 30px var(--neon-color); font-size: 2.8em; letter-spacing: 2px; }
        p { color: #8892b0; margin-bottom: 30px; font-weight: bold; }
        input {
            width: 90%; padding: 15px; margin-bottom: 25px; background: rgba(0,0,0,0.5);
            border: 1px solid #333; color: #0ff; border-radius: 8px; font-size: 1.1em;
            text-align: center; outline: none; transition: 0.3s;
        }
        input:focus { border-color: var(--neon-color); box-shadow: 0 0 15px rgba(0, 255, 255, 0.5); }
        button {
            width: 100%; padding: 15px; font-size: 1.2em; font-weight: bold; cursor: pointer;
            background: transparent; color: var(--neon-color); border: 2px solid var(--neon-color);
            border-radius: 8px; transition: 0.3s; text-transform: uppercase; letter-spacing: 2px;
        }
        button:hover { background: var(--neon-color); color: #000; box-shadow: 0 0 25px var(--neon-color); }
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }
        @keyframes neonPulse { 
            0% { box-shadow: 0 0 20px rgba(0, 255, 255, 0.2); border-color: rgba(0, 255, 255, 0.2); } 
            100% { box-shadow: 0 0 35px rgba(0, 255, 255, 0.4); border-color: rgba(0, 255, 255, 0.4); } 
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>TETRIS</h1>
        <p>Inserta tu nombre y prepárate...</p>
        
        <form action="game.php" method="POST">
            <input type="text" name="jugador" required placeholder="Tu Nickname" autocomplete="off">
            <button type="submit">Iniciar Práctica</button>
        </form>
    </div>
</body>
</html>
