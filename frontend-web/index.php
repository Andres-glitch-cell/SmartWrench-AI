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

// 2. LÓGICA DE VERIFICACIÓN TRIPLE
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
                $error_msj = "ERROR: Credenciales inválidas para esta terminal.";
            }
        } else {
            $error_msj = "ERROR: Empresa no autorizada en el sistema.";
        }
    } else {
        $error_msj = "FORMATO REQUERIDO: EMPRESA:ID:USUARIO";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartWrench AI - Dashboard Técnico</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;700&family=JetBrains+Mono&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* --- AJUSTES PARA EL SCROLL (MANTENIENDO TU ESTILO) --- */
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        .app-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .grid-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            flex: 1;
            height: calc(100vh - 160px);
            gap: 20px;
            padding: 20px;
            overflow: hidden;
        }

        .display-area {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .terminal-window {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .terminal-body {
            flex: 1;
            overflow-y: auto !important;
            scroll-behavior: auto;
        }

        .no-manual-scroll {
            overflow-y: hidden !important;
        }

        /* Estilos para el texto formateado de la IA */
        .ia-content b {
            color: #00ffcc;
            font-weight: 700;
        }

        .ia-content i {
            color: #ffcc00;
            font-style: italic;
        }

        .ia-content p {
            margin-bottom: 15px;
            line-height: 1.6;
        }
    </style>
</head>

<body class="<?php echo !$acceso_permitido ? 'lock-screen' : ''; ?>">

    <?php if (!$acceso_permitido): ?>
        <div class="login-overlay">
            <div class="login-card">
                <div class="login-header">
                    <span class="lock-icon">🔒</span>
                    <h2>SISTEMA OEM</h2>
                    <p>AUTENTICACIÓN DE SEGURIDAD REQUERIDA</p>
                </div>

                <?php if ($error_msj): ?>
                    <div class="error-banner"><?php echo $error_msj; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-field">
                        <input type="text" name="login_string" placeholder="EMPRESA:ID:USUARIO" required autofocus
                            autocomplete="off">
                    </div>
                    <button type="submit" class="btn-primary full-width">ESTABLECER CONEXIÓN</button>
                </form>
                <p class="login-hint">EJEMPLO: TOYOTA:T-800:admin_toyota</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="app-container">
        <header class="main-header">
            <div class="brand">
                <h1>SMARTWRENCH <span class="badge-ai">AI</span></h1>
                <div class="session-info">
                    <span class="pulse-dot"></span>
                    TERMINAL ACTIVA: <strong><?php echo $empresa_display ?: 'MODO_ESPERA'; ?></strong>
                </div>
            </div>

            <div class="system-status">
                <div class="status-pill">
                    <span class="label">SERVER</span>
                    <span class="value online">LIVE</span>
                </div>
                <div class="status-pill">
                    <span class="label">CORE</span>
                    <span class="value">v3.0-FLASH</span>
                </div>
            </div>
        </header>

        <main class="grid-layout">
            <aside class="side-panel">
                <div class="panel-card">
                    <h3>🔍 ENTRADA DE DATOS</h3>
                    <div class="input-block">
                        <label>DESCRIPCIÓN DE LA AVERÍA</label>
                        <textarea id="vin" placeholder="Escribe aquí el código de error o síntoma..."></textarea>
                    </div>

                    <div class="input-block">
                        <label>EVIDENCIA FOTOGRÁFICA</label>
                        <div class="upload-zone" onclick="document.getElementById('foto_averia').click()">
                            <span class="upload-icon">📸</span>
                            <span id="file-name">ADJUNTAR IMAGEN TÉCNICA</span>
                            <input type="file" id="foto_averia" accept="image/*" style="display:none">
                        </div>
                    </div>

                    <div class="status-list">
                        <div class="status-item" id="led-pdf"><span class="dot"></span> PDF Manuals Engine</div>
                        <div class="status-item" id="led-ocr"><span class="dot"></span> Vision Computer OCR</div>
                        <div class="status-item" id="led-cloud"><span class="dot"></span> Cloud Data Sync</div>
                    </div>

                    <div style="display: flex; gap: 10px; width: 100%;">
                        <button id="btnPreguntar" class="btn-primary">
                            <span>🤖</span> INICIAR
                        </button>
                        <button id="btnManual" class="btn-secondary">
                            <span>📖</span> GUÍA OEM
                        </button>
                    </div>
                </div>
            </aside>

            <section class="display-area">
                <div class="terminal-window">
                    <div class="terminal-top">
                        <div class="window-controls">
                            <span class="win-dot close"></span>
                            <span class="win-dot min"></span>
                            <span class="win-dot max"></span>
                        </div>
                        <div class="terminal-title">SMARTWRENCH_CONSOLE.EXE</div>
                    </div>
                    <div class="terminal-body" id="output">
                        <p class="typing-text">SISTEMA INICIALIZADO. ESPERANDO CONSULTA...</p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="main-footer">
            <div class="footer-left">© 2026 SmartWrench AI | ENGINE: GEMINI-3</div>
            <div class="footer-right">LICENCIA: <strong>SW-PRO-2024</strong></div>
        </footer>
    </div>

    <script>
        const output = document.getElementById('output');

        // Función para abrir manual OEM
        document.getElementById('btnManual').addEventListener('click', () => {
            alert("Accediendo a la base de datos de manuales OEM...");
            // Aquí podrías poner: window.open('ruta/al/manual.pdf', '_blank');
        });

        function scrollToBottom() {
            output.scrollTop = output.scrollHeight;
        }

        async function updateStatusLeds(state) {
            const leds = ['led-pdf', 'led-ocr', 'led-cloud'];
            leds.forEach(id => {
                const el = document.getElementById(id);
                el.classList.remove('led-green', 'led-yellow', 'led-red');
            });

            for (const id of leds) {
                await new Promise(resolve => setTimeout(resolve, 400));
                const el = document.getElementById(id);
                if (state === 'success') el.classList.add('led-green');
                if (state === 'warning') el.classList.add('led-yellow');
                if (state === 'error') el.classList.add('led-red');
            }
        }

        function formatText(text) {
            return text
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
                .replace(/\*(.*?)\*/g, '<i>$1</i>');
        }

        function typeEffect(element, rawText, speed = 10) {
            let i = 0;
            let currentHTML = "<strong>[DIAGNÓSTICO FINAL]</strong><br><br>";
            output.classList.add('no-manual-scroll');
            const formattedText = formatText(rawText);

            function typing() {
                if (i < formattedText.length) {
                    if (formattedText.charAt(i) === '<') {
                        let tag = '';
                        while (formattedText.charAt(i) !== '>') {
                            tag += formattedText.charAt(i);
                            i++;
                        }
                        tag += '>';
                        i++;
                        currentHTML += tag;
                    } else {
                        currentHTML += formattedText.charAt(i);
                        i++;
                    }
                    element.innerHTML = currentHTML;
                    output.scrollTop = output.scrollHeight;
                    setTimeout(typing, speed);
                } else {
                    output.classList.remove('no-manual-scroll');
                    scrollToBottom();
                }
            }
            typing();
        }

        document.getElementById('foto_averia').addEventListener('change', function () {
            const name = this.files[0] ? this.files[0].name : "ADJUNTAR IMAGEN TÉCNICA";
            document.getElementById('file-name').innerText = name.toUpperCase();
        });

        document.getElementById('btnPreguntar').addEventListener('click', async () => {
            const pregunta = document.getElementById('vin').value;
            const fileInput = document.getElementById('foto_averia');

            if (!pregunta) {
                output.innerHTML = '<p class="text-error">⚠️ ERROR: INGRESE UNA CONSULTA VÁLIDA.</p>';
                updateStatusLeds('warning');
                return;
            }

            output.innerHTML = '<p class="typing-text">INICIANDO SECUENCIA DE ANÁLISIS... COMPROBANDO MÓDULOS...</p>';
            scrollToBottom();

            const formData = new FormData();
            formData.append('pregunta', pregunta);
            if (fileInput.files[0]) formData.append('archivo', fileInput.files[0]);

            try {
                const response = await fetch('http://127.0.0.1:8000/diagnostico_avanzado', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                    await updateStatusLeds('success');
                    const contentDiv = document.createElement('div');
                    contentDiv.className = "ia-content";
                    output.innerHTML = "";
                    output.appendChild(contentDiv);
                    typeEffect(contentDiv, data.analisis);
                } else {
                    await updateStatusLeds('error');
                    output.innerHTML = `<p class="text-error">❌ ERROR CRÍTICO: ${data.analisis}</p>`;
                }
            } catch (error) {
                await updateStatusLeds('error');
                output.innerHTML = '<p class="text-error">❌ ERROR DE CONEXIÓN: MOTOR OFF-LINE.</p>';
                scrollToBottom();
            }
        });
    </script>
</body>

</html>