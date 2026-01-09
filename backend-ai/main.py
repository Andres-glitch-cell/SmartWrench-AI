import os
import re
import uvicorn
import base64
import fitz  # PyMuPDF
from fastapi import FastAPI, UploadFile, File, Form
from fastapi.middleware.cors import CORSMiddleware
from mistralai import Mistral

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# 🔑 TU CLAVE DE MISTRAL
API_KEY = "KIKwmATw4aMJCY9Z5lyHCpnlXuOMhN2C"
client = Mistral(api_key=API_KEY)

def extraer_contexto_masivo(pregunta_usuario):
    """
    Escanea todos los PDFs en la carpeta docs buscando páginas que 
    contengan palabras clave de la pregunta del técnico.
    """
    contexto = ""
    # Ruta a la carpeta docs (un nivel arriba del backend)
    ruta_docs = os.path.abspath(os.path.join(os.getcwd(), "..", "docs"))
    
    # Limpiamos la pregunta para sacar palabras clave (ej: "motor", "p0300", "aceite")
    keywords = [w for w in re.sub(r'[^a-zA-Z0-9 ]', '', pregunta_usuario).lower().split() if len(w) > 3]

    if os.path.exists(ruta_docs):
        for archivo in os.listdir(ruta_docs):
            if archivo.endswith(".pdf"):
                try:
                    with fitz.open(os.path.join(ruta_docs, archivo)) as doc:
                        print(f"🔍 Escaneando masivamente: {archivo}...")
                        for pagina in doc:
                            texto_pag = pagina.get_text()
                            # Si la página contiene alguna de nuestras palabras clave
                            if any(key in texto_pag.lower() for key in keywords):
                                contexto += f"\n--- Fragmento de {archivo} ---\n{texto_pag}"
                            
                            # Límite de seguridad para no saturar la memoria de la IA
                            if len(contexto) > 12000:
                                break
                except Exception as e:
                    print(f"⚠️ Error leyendo {archivo}: {e}")
    
    # Si no encontró nada con keywords, devolvemos un resumen de las primeras páginas
    return contexto[:12000] if contexto else "No se encontró información específica en los manuales."

@app.post("/diagnostico_avanzado")
async def diagnostico_avanzado(
    pregunta: str = Form(...),
    archivo: UploadFile = File(None)
):
    try:
        # 1. Búsqueda inteligente en la base de datos de PDFs
        print(f"📡 Buscando solución para: '{pregunta}' en manuales OEM...")
        manuales_texto = extraer_contexto_masivo(pregunta)
        
        # 2. Configurar el modelo (Pixtral para fotos, Mistral-7b para texto)
        modelo = "pixtral-12b" if archivo else "open-mistral-7b"
        
        # 3. Prompt técnico ultra-específico
        prompt_sistema = (
            "Eres SmartWrench AI, el sistema experto de Toyota. "
            f"A continuación tienes información técnica extraída de los manuales de taller: {manuales_texto}. "
            "Usa esta información para dar una solución precisa. "
            "IMPORTANTE: No uses asteriscos, almohadillas ni guiones. Solo texto plano y profesional."
        )

        contenido = [{"type": "text", "text": pregunta}]

        if archivo:
            img_data = await archivo.read()
            base64_image = base64.b64encode(img_data).decode('utf-8')
            contenido.append({
                "type": "image_url",
                "image_url": f"data:{archivo.content_type};base64,{base64_image}"
            })

        chat_response = client.chat.complete(
            model=modelo,
            messages=[
                {"role": "system", "content": prompt_sistema},
                {"role": "user", "content": contenido}
            ]
        )

        respuesta_ia = chat_response.choices[0].message.content
        texto_limpio = re.sub(r'[*#_~\-•]', '', respuesta_ia).strip()

        print("✅ DIAGNÓSTICO FINALIZADO")
        return {"status": "success", "analisis": texto_limpio}

    except Exception as e:
        print(f"❌ ERROR: {str(e)}")
        return {"status": "error", "analisis": f"ERROR DE SISTEMA: {str(e)}"}

if __name__ == "__main__":
    print("--------------------------------------------------")
    print("🚀 SMARTWRENCH AI v7.0 [MASSIVE PDF READER]")
    print("🖥️  SISTEMA: WINDOWS x64 | PUERTO: 8000")
    print("--------------------------------------------------")
    uvicorn.run(app, host="127.0.0.1", port=8000)