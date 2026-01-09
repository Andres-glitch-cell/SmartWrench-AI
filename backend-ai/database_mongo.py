from pymongo import MongoClient
import certifi
import sys

def obtener_conexion_db():
    """
    Establece la conexión con MongoDB Atlas y devuelve la colección.
    Incluye mejoras de estabilidad para entornos Windows.
    """
    # Tu URI de conexión (Asegúrate de que tu IP esté en la Whitelist de MongoDB Atlas)
    uri = "mongodb+srv://afernandezsiesjc_db_user:R00tR00t*12345@smartwrench-ai.yxa4uem.mongodb.net/"
    
    try:
        # 1. Añadimos timeout de 5 segundos para evitar que el sistema se congele
        # 2. Forzamos el uso del certificado de certifi para evitar errores SSL en Windows
        client = MongoClient(
            uri, 
            tlsCAFile=certifi.where(),
            serverSelectionTimeoutMS=5000 
        )
        
        # 3. Validamos la conexión con un comando 'ping'
        client.admin.command('ping')
        
        db = client['smartwrench_db']
        
        # Devolvemos la colección 'manuales'
        return db['manuales']
        
    except Exception as e:
        print(f"\n[!] AVISO: No se pudo conectar a MongoDB Cloud.")
        print(f"    DETALLE: {e}")
        print("    SISTEMA: El diagnóstico continuará funcionando en modo LOCAL.\n")
        return None

if __name__ == "__main__":
    print("--- SMARTWRENCH AI: PRUEBA DE CONEXIÓN DATABASE ---")
    coleccion = obtener_conexion_db()
    
    if coleccion is not None:
        try:
            total = coleccion.count_documents({})
            print(f"✅ ESTADO: ONLINE")
            print(f"✅ RECURSOS: {total} manuales detectados en la nube.")
        except Exception as e:
            print(f"❌ ERROR AL CONTAR DOCUMENTOS: {e}")
    else:
        print(f"❌ ESTADO: OFFLINE")
        print("💡 CONSEJO: Revisa tu conexión a internet o la Whitelist en MongoDB Atlas.")