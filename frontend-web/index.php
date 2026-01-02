<?php
// 1. BASE DE DATOS DE CREDENCIALES OEM
$db_empresas = [
    "TOYOTA" => ["ID" => "T-800", "USER" => "admin_toyota"],
    "SEAT" => ["ID" => "S-2024", "USER" => "seat_tech"],
    "RENAULT" => ["ID" => "R-MEG", "USER" => "renault_user"],
    "BMW" => ["ID" => "B-740", "USER" => "bmw_master"],
    "AUDI" => ["ID" => "A-2026", "USER" => "audi_vrs"],
    "VOLKSWAGEN" => ["ID" => "VW-ID", "USER" => "vw_power"]
];

$acceso_permitido = false;
$error_msj = "";
$empresa_display = "";

// 2. LÓGICA DE VERIFICACIÓN TRIPLE (Formato -> Empresa:ID:Usuario)
if (isset($_POST['login_string'])) {
    $entrada = explode(":", strtoupper(trim($_POST['login_string'])));

    if (count($entrada) === 3) {
        $emp_in = $entrada[0];
        $id_in = $entrada[1];
        $user_in = strtolower($entrada[2]);

        if (isset($db_empresas[$emp_in])) {
            $datos = $db_empresas[$emp_in];
            if ($id_in === $datos["ID"] && $user_in === $datos["USER"]) {
                $acceso_permitido = true;
                $empresa_display = $emp_in;
            } else {
                $error_msj = "PERMISO DENEGADO: ID o Usuario incorrectos.";
            }
        } else {
            $error_msj = "PERMISO DENEGADO: Empresa no autorizada.";
        }
    } else {
        $error_msj = "FORMATO INVÁLIDO: Use Empresa:ID:Usuario";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartWrench AI - Acceso Industrial</title>
    <link rel="stylesheet" href="styles.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;700&display=swap"
        rel="stylesheet">
    <style>
        /* Estilos para el sistema de login triple */
        .login-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 15, 20, 0.98);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .login-box {
            background: #1a1f25;
            padding: 40px;
            border: 2px solid #00ff88;
            box-shadow: 0 0 30px rgba(0, 255, 136, 0.15);
            text-align: center;
            border-radius: 8px;
            max-width: 400px;
            width: 90%;
        }

        .error-box {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid #ff4444;
            color: #ff4444;
            padding: 10px;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .login-input {
            background: #0d1117;
            border: 1px solid #30363d;
            color: white;
            padding: 12px;
            width: 100%;
            margin: 15px 0;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            text-align: center;
        }

        .login-input:focus {
            outline: none;
            border-color: #00ff88;
        }

        .hint {
            color: #8b949e;
            font-size: 0.7rem;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <?php if (!$acceso_permitido): ?>
        <div class="login-overlay">
            <div class="login-box">
                <h2 style="color: #00ff88; font-family: 'Orbitron'; letter-spacing: 2px;">ACCESO OEM</h2>
                <p style="color: #8b949e; margin-bottom: 20px;">Autenticación por Empresa, ID y Usuario</p>

                <?php if ($error_msj): ?>
                    <div class="error-box"><?php echo $error_msj; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="text" name="login_string" class="login-input" placeholder="EMPRESA:ID:USUARIO" required
                        autofocus autocomplete="off">
                    <button type="submit" class="action-btn" style="width: 100%;">
                        VERIFICAR CREDENCIALES
                    </button>
                </form>
                <p class="hint">Ejemplo: TOYOTA:X342:toyota</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="container" style="<?php echo !$acceso_permitido ? 'filter: blur(10px); pointer-events: none;' : ''; ?>">
        <header>
            <div class="logo-section">
                <h1>SMARTWRENCH <span class="ai-badge">AI</span></h1>
                <p class="subtitle">SISTEMA DE ASISTENCIA TÉCNICA AVANZADA</p>
                <?php if ($acceso_permitido): ?>
                    <p style="color: #00ff88; font-size: 0.8rem; margin-top: 5px; font-family: 'Orbitron';">
                        SESIÓN ACTIVA: [ <?php echo $empresa_display; ?> ]
                    </p>
                <?php endif; ?>
            </div>
            <div class="system-stats">
                <div class="stat">
                    <span class="label">SERVIDOR:</span>
                    <span class="value online">CONECTADO</span>
                </div>
                <div class="stat">
                    <span class="label">MODELO:</span>
                    <span class="value">v2.4-Turbo</span>
                </div>
            </div>
        </header>

        <main>
            <section class="control-panel">
                <div class="input-group">
                    <label for="vin">IDENTIFICACIÓN DEL VEHÍCULO</label>
                    <input type="text" id="vin" placeholder="VIN o Modelo del vehículo...">
                </div>

                <div class="ai-features">
                    <div class="feature-item" id="feat-oem">
                        <span class="status-dot"></span>
                        <span class="feature-text">Análisis de Manuales OEM</span>
                    </div>
                    <div class="feature-item" id="feat-torque">
                        <span class="status-dot"></span>
                        <span class="feature-text">Cálculo de Pares de Apriete</span>
                    </div>
                    <div class="feature-item" id="feat-mongo">
                        <span class="status-dot"></span>
                        <span class="feature-text">Motor MongoDB Cloud</span>
                    </div>
                </div>

                <div class="button-group">
                    <button id="btnPreguntar" class="action-btn">
                        <span class="icon">🤖</span> ANALIZAR CON IA
                    </button>

                    <button id="btnManual" class="action-btn secondary">
                        <span class="icon">📖</span> MANUAL USO
                    </button>
                </div>

            </section>

            <section class="display-panel">
                <div class="chat-area" id="chat">
                    <div class="terminal-header">
                        <span class="dot red"></span>
                        <span class="dot yellow"></span>
                        <span class="dot green"></span>
                        <span class="terminal-title">TERMINAL DE DIAGNÓSTICO</span>
                    </div>
                    <div id="output">
                        <p class="cursor">Esperando entrada de datos...</p>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <p>© 2026 SmartWrench AI | Licencia: <span id="licencia">SW-PRO-2024</span> | Datos basados en normativas
                ISO/DIN</p>
        </footer>
    </div>
    <script src="login.js"></script>
</body>

</html>