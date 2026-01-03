/* ! Función para abrir el Manual */
function openManual() {
    const elementoID = document.getElementById("btnManual")
    elementoID.addEventListener("click", () => {
        window.location.href = "SmartWrenchIA-Manual.pdf"
    })
}

window.onload = openManual

// --- FUNCIÓN PARA EFECTO DE ESCRITURA (SIMULACIÓN IA) ---
function escribirLento(elemento, texto) {
    return new Promise((resolve) => {
        let i = 0;
        elemento.innerHTML = "";
        const timer = setInterval(() => {
            elemento.innerHTML += texto.charAt(i);
            i++;
            if (i >= texto.length) {
                clearInterval(timer);
                resolve();
            }
        }, 20);
    });
}

document.getElementById('btnPreguntar').addEventListener('click', async () => {
    const vin = document.getElementById('vin').value;
    const output = document.getElementById('output');

    // Referencias a los indicadores del panel de características
    const featOEM = document.getElementById('feat-oem');
    const featTorque = document.getElementById('feat-torque');
    const featMongo = document.getElementById('feat-mongo');

    if (!vin) {
        output.innerHTML = "<p style='color: #ff4b2b;'>⚠️ Error: Se requiere identificación del vehículo.</p>";
        return;
    }

    // 1. RESET Y ESTADO INICIAL
    // Apagamos las luces de los módulos para iniciar el escaneo
    [featOEM, featTorque, featMongo].forEach(f => {
        if (f) f.classList.remove('active', 'scanning');
    });

    output.innerHTML = `
        <p class="typing">🔍 Escaneando bases de datos OEM...</p>
        <p class="typing">🧠 Identificando motorización para ${vin.toUpperCase()}...</p>
    `;

    try {
        // --- ACTIVACIÓN MÓDULO MONGODB ---
        if (featMongo) featMongo.classList.add('scanning');

        // URL DEL TÚNEL (Recuerda actualizarla si reinicias localtunnel)
        const tunnelURL = 'http://localhost:8000/diagnostico';

        const response = await fetch(tunnelURL, {
            method: 'POST',
            mode: 'cors',
            headers: {
                'Content-Type': 'application/json',
                'Bypass-Tunnel-Reminder': 'true'
            },
            body: JSON.stringify({
                empresa_id: "Taller_Andres",
                licencia_key: "SW-PRO-2024",
                vehiculo_id: vin,
                pregunta: "procedimiento general"
            })
        });

        if (!response.ok) throw new Error(`Error de red: ${response.status}`);

        const data = await response.json();

        // --- MÓDULO MONGODB COMPLETADO Y ACTIVACIÓN OEM ---
        if (featMongo) {
            featMongo.classList.remove('scanning');
            featMongo.classList.add('active');
        }
        if (featOEM) featOEM.classList.add('scanning');

        // 2. PROCESO DE RENDERIZADO LETRA POR LETRA
        setTimeout(async () => {
            if (data.status === "success" || data.pasos) {

                // --- MÓDULO OEM COMPLETADO Y ACTIVACIÓN CÁLCULO ---
                if (featOEM) {
                    featOEM.classList.remove('scanning');
                    featOEM.classList.add('active');
                }
                if (featTorque) featTorque.classList.add('active');

                // Creamos la estructura base del reporte
                output.innerHTML = `
                    <div class="res-card">
                        <h3 style="color: #00ff88;">✅ REPORTE TÉCNICO GENERADO (IA ENGINE)</h3>
                        <div id="contenedorPasos" style="text-align: left; margin-top: 15px;"></div>
                        
                        <div id="docInfo" style="display:none; margin-top: 20px; border-top: 1px solid #30363d; padding-top: 10px; font-size: 0.8rem; color: #8b949e;">
                            <p><strong>FUERZA DE APRIETE:</strong> Verificado por SmartWrench Engine</p>
                            <p><strong>DOCUMENTO OEM:</strong> ${data.fuente_oficial || 'Manual Técnico General'}</p>
                            <p><strong>ORIGEN:</strong> MongoDB Atlas Cloud</p>
                        </div>
                    </div>
                `;

                const contenedor = document.getElementById('contenedorPasos');

                // Escribimos cada paso de forma secuencial
                for (let i = 0; i < data.pasos.length; i++) {
                    const pasoDiv = document.createElement('div');
                    pasoDiv.className = "step-row";
                    pasoDiv.style = "margin: 10px 0; border-left: 3px solid #00ff88; padding-left: 15px; background: rgba(0,255,136,0.05); min-height: 20px; color: #e6edf3;";
                    contenedor.appendChild(pasoDiv);

                    await escribirLento(pasoDiv, `PASO ${i + 1}: ${data.pasos[i]}`);
                }

                // Al final, mostramos la info del documento con una transición suave
                const docInfo = document.getElementById('docInfo');
                docInfo.style.display = 'block';
                docInfo.style.animation = 'fadeIn 1s forwards';

            } else {
                throw new Error("Datos incompletos");
            }
        }, 1000);

    } catch (error) {
        // En caso de error, apagamos los indicadores
        [featOEM, featTorque, featMongo].forEach(f => {
            if (f) f.classList.remove('active', 'scanning');
        });

        console.error("Error detallado:", error);
        output.innerHTML = `
            <div style="color: #ff4444; padding: 20px; border: 1px solid #ff4444; background: rgba(255,68,68,0.1); border-radius: 8px;">
                <p><strong>❌ ERROR CRÍTICO DE SISTEMA</strong></p>
                <p style="font-size: 0.8rem; margin-top: 10px;">
                    El motor de IA no responde. Verifique el túnel y el servidor Python.
                </p>
            </div>
        `;
    }
});