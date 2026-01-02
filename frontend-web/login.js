// Verificación de que el archivo carga
console.log("Archivo login.js cargado correctamente");

document.addEventListener('DOMContentLoaded', () => {
    const boton = document.getElementById('btnPreguntar');
    const inputVin = document.getElementById('vin');
    const chatArea = document.getElementById('chat');

    if (!boton) {
        console.error("No se encontró el botón con ID 'btnPreguntar'");
        return;
    }

    boton.addEventListener('click', async () => {
        console.log("Botón pulsado. VIN detectado:", inputVin.value);

        if (!inputVin.value) {
            alert("Escribe un modelo o VIN (ej: Seat Leon)");
            return;
        }

        chatArea.innerHTML = "<p>⏳ Consultando base de datos técnica...</p>";

        try {
            const response = await fetch('http://127.0.0.1:8000/diagnostico', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    empresa_id: "Talleres_Andres_SL",
                    licencia_key: "SW-PRO-2024",
                    vehiculo_id: inputVin.value,
                    pregunta: "Orden de apriete"
                })
            });

            if (!response.ok) throw new Error("Error en el servidor");

            const data = await response.json();
            console.log("Datos recibidos:", data);

            // Mostrar resultado en la web
            chatArea.innerHTML = `
                <div style="color: #fff; text-align: left;">
                    <p>✅ <strong>Pasos para ${inputVin.value}:</strong></p>
                    <ul>${data.pasos.map(p => `<li>${p}</li>`).join('')}</ul>
                    <p><small>Fuente: ${data.fuente_oficial}</small></p>
                </div>`;

        } catch (error) {
            console.error("Error de conexión:", error);
            chatArea.innerHTML = "<p style='color:red;'>❌ Error: ¿Está el backend encendido?</p>";
        }
    });
});