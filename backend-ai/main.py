from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from fastapi.middleware.cors import CORSMiddleware # 1. Importar el permiso

app = FastAPI(title="SmartWrench AI API")

# 2. Configurar el permiso (CORS) para que tu web pueda entrar
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"], # Permite conexiones desde cualquier lugar (como Live Server)
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

class Consulta(BaseModel):
    empresa_id: str
    licencia_key: str
    vehiculo_id: str
    pregunta: str

@app.post("/diagnostico")
async def diagnostico(data: Consulta):
    if data.licencia_key != "SW-PRO-2024":
        raise HTTPException(status_code=401, detail="Licencia inválida")

    return {
        "status": "success",
        "pasos": [
            "1. Localizar los tornillos de la culata.",
            "2. Seguir el orden de apriete en cruz.",
            "3. Aplicar par inicial de 40 Nm.",
            "4. Aplicar dos pases finales de 90 grados."
        ],
        "esquema_url": "https://ejemplo.com/esquema.png",
        "fuente_oficial": f"Manual_Servicio_{data.vehiculo_id}_Pág_120.pdf"
    }