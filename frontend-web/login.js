/* ! Función para abrir el Manual (Acceso Directo Windows) */
function openManual() {
    const elementoID = document.getElementById("btnManual");
    if (elementoID) {
        elementoID.onclick = () => {
            // Abre el PDF en una nueva pestaña
            window.open("SmartWrenchIA-Manual.pdf", "_blank");
        };
    }
}

// --- FUNCIÓN PARA EFECTO DE ESCRITURA ---
function escribirLento(elemento, texto) {
    return new Promise((resolve) => {
        let i = 0;
        elemento.innerHTML = "";
        // Eliminamos caracteres de formato Markdown para una salida tipo terminal limpia
        const textoLimpio = texto.replace(/[*#_~]/g, ''); 
        
        const timer = setInterval(() => {
            if (i < textoLimpio.length) {
                elemento.innerHTML += textoLimpio.charAt(i);
                i++;
                // Hacemos scroll automático en el contenedor de salida
                const chatArea = document.getElementById('output');
                if (chatArea) chatArea.scrollTop = chatArea.scrollHeight;
            } else {
                clearInterval(timer);
                resolve();
            }
        }, 15); // Velocidad de terminal técnica
    });
}

document.getElementById('btnPreguntar').addEventListener('click', async () => {
    const vinInput = document.getElementById('vin');
    const vin = vinInput.value.trim(); // Limpiamos espacios
    const output = document.getElementById('output');
    
    const fotoInput = document.getElementById('foto_averia'); 
    const foto = fotoInput ? fotoInput.files[0] : null;

    const featOEM = document.getElementById('feat-oem');
    const featTorque = document.getElementById('feat-torque');
    const featMongo = document.getElementById('feat-mongo');
    const features = [featOEM, featTorque, featMongo];

    // Validación de entrada técnica
    if (!vin && !foto) {
        output.innerHTML = `
            <div style="color: #ff4b2b; font-family: 'Orbitron'; padding: 10px; border: 1px solid #ff4b2b; background: rgba(255,75,43,0.1);">
                ⚠️ SISTEMA: REQUIERE ENTRADA DE TEXTO O EVIDENCIA VISUAL.
            </div>`;
        return;
    }

    // 1. INICIAR PROTOCOLO DE ESCANEO (UI)
    features.forEach(f => {
        if (f) { 
            f.style.opacity = "1";
            f.classList.add('scanning'); 
            f.classList.remove('active');
        }
    });

    output.innerHTML = `
        <div class="scanning-loader" style="font-family: 'Courier New'; color: #00ff88; font-size: 0.85rem;">
            <p>📡 ESTABLECIENDO CONEXIÓN CON MOTOR IA (PUERTO 8000)...</p>
            <p>📂 INDEXANDO BASE DE DATOS LOCAL /DOCS...</p>
            <p>🧠 PROCESANDO CONSULTA CON GEMINI 1.5 FLASH...</p>
            <p>☁️ SINCRONIZANDO CON MONGODB CLOUD...</p>
        </div>
    `;

    try {
        const formData = new FormData();
        // Si no hay texto pero hay foto, enviamos una pregunta por defecto
        formData.append('pregunta', vin || "Analiza la imagen adjunta y diagnostica posibles fallos.");
        
        if (foto) {
            formData.append('archivo', foto);
        }

        // Llamada al backend de Python en Windows
       const response = await fetch('http://127.0.0.1:8000/diagnostico_avanzado', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Error de Servidor: ${response.status}`);
        }

        const data = await response.json();

        if (data.status === 'success') {
            // 2. MOSTRAR RESULTADOS
            output.innerHTML = `
                <div class="res-card">
                    <h3 style="color: #00ff88; font-family: 'Orbitron'; font-size: 0.85rem; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 15px;">
                        ✅ INFORME GENERADO EXITOSAMENTE
                    </h3>
                    <div id="contenedorIA" style="text-align: left; color: #e6edf3; font-family: 'Roboto'; line-height: 1.6; font-size: 0.95rem;"></div>
                    
                    <div id="docInfo" style="display:none; margin-top: 25px; border-top: 1px solid #333; padding-top: 10px; font-size: 0.65rem; color: #666; font-family: 'Orbitron';">
                        <p>PROCEDENCIA: MANUALES OEM + GEMINI VISION</p>
                        <p>REGISTRO: ALMACENADO EN HISTORIAL MONGODB</p>
                    </div>
                </div>
            `;

            const contenedor = document.getElementById('contenedorIA');
            await escribirLento(contenedor, data.analisis);

            // Actualizar indicadores a estado activo (Verde)
            features.forEach(f => {
                if (f) { 
                    f.classList.remove('scanning'); 
                    f.classList.add('active'); 
                }
            });

            document.getElementById('docInfo').style.display = 'block';
        } else {
            throw new Error(data.analisis);
        }

    } catch (error) {
        console.error("Fallo crítico:", error);
        
        // Reset visual de errores
        features.forEach(f => { if (f) f.classList.remove('scanning'); });

        output.innerHTML = `
            <div style="color: #ff4444; padding: 20px; border: 1px solid #ff4444; background: rgba(255,68,68,0.1); font-family: 'Orbitron';">
                <p style="font-size: 0.9rem;">❌ ERROR DE ENLACE TÉCNICO</p>
                <div style="font-size: 0.7rem; color: #aaa; margin-top: 15px; line-height: 1.4;">
                    <p>CAUSA: ${error.message}</p>
                    <p style="margin-top: 10px; color: #ffbc00;">PASOS DE RECUPERACIÓN:</p>
                    <ul style="margin-left: 15px;">
                        <li>Verifica que Uvicorn esté activo en el puerto 8000.</li>
                        <li>Comprueba la conexión a Internet para MongoDB Atlas.</li>
                        <li>Pulsa Ctrl + F5 en el navegador para recargar scripts.</li>
                    </ul>
                </div>
            </div>
        `;
    }
});

// Inicialización de funciones de Windows
window.onload = openManual;