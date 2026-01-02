from pymongo import MongoClient
import certifi

def obtener_conexion_db():
    """
    Establece la conexión con MongoDB Atlas y devuelve la colección.
    """
    uri = "mongodb+srv://afernandezsiesjc_db_user:R00tR00t*12345@smartwrench-ai.yxa4uem.mongodb.net/"
    
    try:
        # Conectamos usando el certificado de seguridad
        client = MongoClient(uri, tlsCAFile=certifi.where())
        db = client['smartwrench_db']
        
        # Solo devolvemos la colección, ya no borramos ni insertamos nada
        return db['manuales']
        
    except Exception as e:
        print(f"❌ Error de conexión a MongoDB Atlas: {e}")
        return None

if __name__ == "__main__":
    # Prueba rápida de conexión
    coleccion = obtener_conexion_db()
    if coleccion is not None:
        total = coleccion.count_documents({})
        print(f"✅ Conexión exitosa. Tienes {total} manuales en la nube.")