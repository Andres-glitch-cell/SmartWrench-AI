from fastapi import FastAPI, HTTPException
from pydantic import BaseModel

app = FastAPI(title="SmartWrench AI API")

class Consulta(BaseModel):
    empresa_id: str
    licencia_key: str
    vehiculo_id: str  # Paso 1: Identificación
    pregunta: str     # Paso 2: Consulta

@app.post("/diagnostico")
async def diagnostico(data: Consulta):
    # Simulación de validación de licencia
    if data.licencia_key != "SW-PRO-2024":
        raise HTTPException(status_code=401, detail="Licencia inválida")

    # Paso 3: Respuesta Estructurada
    return {
        "status": "success",
        "pasos": [
            "1. Localizar los tornillos de la culata.",
            "2. Seguir el orden de apriete en cruz.",
            "3. Aplicar par inicial de 40 Nm.",
            "4. Aplicar dos pases finales de 90 grados."
        ],
        "esquema_url": "https://ejemplo.com/esquema.png",
        # Paso 4: Verificación
        "fuente_oficial": f"Manual_Servicio_{data.vehiculo_id}_Pág_120.pdf"
    }