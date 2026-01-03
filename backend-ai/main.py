from fastapi import FastAPI, Request
from pymongo import MongoClient
from fastapi.middleware.cors import CORSMiddleware
import certifi
import uvicorn
import sys

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- CONFIGURACIÓN NUCLEAR PARA EL ERROR SSL ---
uri = "mongodb+srv://afernandezsiesjc_db_user:R00tR00t*12345@smartwrench-ai.yxa4uem.mongodb.net/?retryWrites=true&w=majority"

try:
    client = MongoClient(
        uri,
        tls=True,
        tlsAllowInvalidCertificates=True, # ESTO ES LO MÁS IMPORTANTE
        tlsCAFile=certifi.where(),
        connectTimeoutMS=5000,
        serverSelectionTimeoutMS=5000
    )
    db = client['smartwrench_db']
    collection = db['manuales']
    # Intentamos una operación simple para validar
    client.admin.command('ping')
    print("✅ SISTEMA: Conexión con MongoDB Atlas establecida")
except Exception as e:
    print(f"❌ ERROR CRÍTICO DE CONEXIÓN: {e}")

@app.post("/diagnostico")
async def diagnostico(request: Request):
    try:
        data = await request.json()
        vehiculo = data.get("vehiculo_id", "")
        print(f"📥 PETICIÓN RECIBIDA PARA: {vehiculo}")

        # Si MongoDB falla, devolvemos un dato de prueba para que veas que el sistema funciona
        try:
            query = {"$or": [
                {"marca": {"$regex": vehiculo, "$options": "i"}},
                {"modelo": {"$regex": vehiculo, "$options": "i"}}
            ]}
            resultado = collection.find_one(query)
            
            if resultado:
                return {
                    "status": "success",
                    "pasos": resultado['especificaciones']['pasos'],
                    "fuente_oficial": resultado['especificaciones']['fuente']
                }
        except Exception as mongo_err:
            print(f"⚠️ Error en base de datos: {mongo_err}")
            return {
                "status": "success",
                "pasos": ["MODO EMERGENCIA: Error de conexión con la nube, pero el backend responde.", "Revisa el puerto 27017 de tu red."],
                "fuente_oficial": "Sistema Local"
            }

        return {"status": "error", "pasos": ["Vehículo no encontrado."]}

    except Exception as e:
        return {"status": "error", "pasos": [f"Error interno: {str(e)}"]}

if __name__ == "__main__":
    uvicorn.run(app, host="127.0.0.1", port=8000)