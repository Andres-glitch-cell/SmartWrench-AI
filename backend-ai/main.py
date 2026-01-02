from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from pymongo import MongoClient
from fastapi.middleware.cors import CORSMiddleware
import certifi

app = FastAPI()
@app.get("/")
async def root():
    return {
        "status": "online",
        "mensaje": "SmartWrench AI API está funcionando",
        "instrucciones": "Usa la web en el puerto 8080 para realizar consultas."
    }

# Configuración de CORS para que tu navegador no bloquee la conexión
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- CONEXIÓN A LA NUBE CORREGIDA ---
uri = "mongodb+srv://afernandezsiesjc_db_user:R00tR00t*12345@smartwrench-ai.yxa4uem.mongodb.net/"

try:
    # Usamos certifi para evitar problemas de seguridad en Windows
    client = MongoClient(uri, tlsCAFile=certifi.where())
    # Nombre de la base de datos limpio (sin la URI dentro)
    db = client['smartwrench_db']
    collection = db['manuales']
    print("✅ Servidor conectado a MongoDB Atlas con éxito")
except Exception as e:
    print(f"❌ Error de conexión en el servidor: {e}")

# Modelo de datos que espera la API
class Consulta(BaseModel):
    vehiculo_id: str
    licencia_key: str
    empresa_id: str
    pregunta: str

@app.post("/diagnostico")
async def diagnostico(data: Consulta):
    # Verificación de seguridad simple
    if data.licencia_key != "SW-PRO-2024":
        raise HTTPException(status_code=401, detail="Licencia inválida")

    # Búsqueda en MongoDB (insensible a mayúsculas/minúsculas)
    query = {
        "$or": [
            {"marca": {"$regex": data.vehiculo_id, "$options": "i"}},
            {"modelo": {"$regex": data.vehiculo_id, "$options": "i"}}
        ]
    }
    
    resultado = collection.find_one(query)

    if resultado:
        return {
            "status": "success",
            "pasos": resultado['especificaciones']['pasos'],
            "fuente_oficial": resultado['especificaciones']['fuente']
        }
    
    return {
        "status": "error",
        "pasos": ["Vehículo no localizado en la base de datos cloud.", "Sugerencia: Use par estándar de seguridad (40Nm)."],
        "fuente_oficial": "Manuales SmartWrench"
    }