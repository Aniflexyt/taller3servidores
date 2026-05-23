<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TETRIS PHP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: radial-gradient(circle at top, #1b1b2f, #0f0f1a);
            font-family: 'Press Start 2P', cursive;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            text-align: center;
            animation: fadeIn 1.5s ease-in-out;
        }

        h1 {
            font-size: 40px;
            color: #00f7ff;
            text-shadow: 0 0 15px #00f7ff, 0 0 30px #00f7ff;
            margin-bottom: 20px;
        }

        p {
            font-size: 12px;
            color: #aaa;
            margin-bottom: 40px;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(45deg, #ff00cc, #3333ff);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 12px;
            box-shadow: 0 0 20px rgba(255, 0, 255, 0.4);
            transition: 0.3s;
        }

        .btn:hover {
            transform: scale(1.1);
            box-shadow: 0 0 30px rgba(0, 255, 255, 0.6);
        }

        .grid {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 40px 40px;
            animation: moveGrid 10s linear infinite;
        }

        @keyframes moveGrid {
            from { transform: translateY(0); }
            to { transform: translateY(40px); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .glow {
            color: #ff00cc;
            text-shadow: 0 0 10px #ff00cc;
        }
    </style>
</head>

<body>

<div class="grid"></div>

<div class="container">
    <h1>🎮 TETRIS<span class="glow">PHP</span></h1>
    <p>Un juego clásico reinventado con estilo web moderno</p>

    <a href="game.php" class="btn">▶ INICIAR JUEGO</a>
</div>

</body>
</html>