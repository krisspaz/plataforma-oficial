from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Optional
import numpy as np
from datetime import datetime
import logging

# Importaciones para RAG y Vector DB
try:
    import openai
    from pinecone import Pinecone, ServerlessSpec
except ImportError:
    openai = None
    Pinecone = None

app = FastAPI(title="School AI Service", version="2.0.0")
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Configuración
OPENAI_API_KEY = "sk-..."  # Configurar desde env
PINECONE_API_KEY = "..."   # Configurar desde env
PINECONE_ENV = "us-west1-gcp"

# Inicializar Pinecone para RAG
if Pinecone:
    pc = Pinecone(api_key=PINECONE_API_KEY)
    index_name = "educational-knowledge"
    
    # Crear índice si no existe
    if index_name not in pc.list_indexes().names():
        pc.create_index(
            name=index_name,
            dimension=1536,  # Dimensión de embeddings de OpenAI
            metric='cosine',
            spec=ServerlessSpec(cloud='aws', region='us-west-2')
        )
    
    index = pc.Index(index_name)

# === MODELOS ===

class StudentRiskInput(BaseModel):
    student_id: int
    attendance_rate: float
    average_grade: float
    behavior_score: float
    participation_rate: float
    payment_status: str
    failed_subjects: int
    family_situation: str

class ChatInput(BaseModel):
    question: str
    context: Optional[dict] = None
    user_role: str = "student"

class RAGQueryInput(BaseModel):
    query: str
    top_k: int = 5
    filter: Optional[dict] = None

class DocumentInput(BaseModel):
    content: str
    metadata: dict

# === ENDPOINTS EXISTENTES ===

@app.post("/api/v1/risk/predict")
async def predict_academic_risk(data: StudentRiskInput):
    """Predicción avanzada de riesgo académico"""
    
    # Algoritmo mejorado con pesos ajustados
    attendance_weight = 0.25
    grade_weight = 0.30
    behavior_weight = 0.15
    participation_weight = 0.10
    payment_weight = 0.10
    failed_subjects_weight = 0.10
    
    # Normalizar valores
    attendance_score = data.attendance_rate / 100
    grade_score = data.average_grade / 100
    behavior_score = data.behavior_score / 10
    participation_score = data.participation_rate / 100
    payment_score = 1.0 if data.payment_status == "current" else 0.3
    failed_score = max(0, 1 - (data.failed_subjects / 5))
    
    # Calcular score de riesgo (0-1, donde 1 es alto riesgo)
    risk_score = 1 - (
        attendance_score * attendance_weight +
        grade_score * grade_weight +
        behavior_score * behavior_weight +
        participation_score * participation_weight +
        payment_score * payment_weight +
        failed_score * failed_subjects_weight
    )
    
    # Clasificar nivel de riesgo
    if risk_score < 0.3:
        risk_level = "bajo"
        priority = 1
    elif risk_score < 0.6:
        risk_level = "medio"
        priority = 2
    else:
        risk_level = "alto"
        priority = 3
    
    # Generar recomendaciones específicas
    recommendations = []
    if data.attendance_rate < 80:
        recommendations.append("Mejorar asistencia - contactar a padres")
    if data.average_grade < 70:
        recommendations.append("Tutorías académicas urgentes")
    if data.behavior_score < 6:
        recommendations.append("Intervención psicopedagógica")
    if data.failed_subjects > 2:
        recommendations.append("Plan de recuperación académica")
    
    return {
        "student_id": data.student_id,
        "risk_level": risk_level,
        "risk_score": round(risk_score, 3),
        "dropout_probability": round(risk_score * 0.8, 3),
        "priority": priority,
        "recommendations": recommendations,
        "factors": {
            "attendance": round(attendance_score, 2),
            "grades": round(grade_score, 2),
            "behavior": round(behavior_score, 2),
            "participation": round(participation_score, 2)
        }
    }

# === NUEVOS ENDPOINTS RAG ===

@app.post("/api/v1/rag/embed")
async def embed_document(doc: DocumentInput):
    """Almacenar documento en vector database para RAG"""
    if not openai or not Pinecone:
        raise HTTPException(status_code=501, detail="RAG not configured")
    
    try:
        # Generar embedding con OpenAI
        response = openai.Embedding.create(
            model="text-embedding-ada-002",
            input=doc.content
        )
        
        embedding = response['data'][0]['embedding']
        
        # Almacenar en Pinecone
        vector_id = f"doc_{datetime.now().timestamp()}"
        index.upsert(vectors=[{
            'id': vector_id,
            'values': embedding,
            'metadata': {
                **doc.metadata,
                'content': doc.content[:1000],  # Primeros 1000 chars
                'created_at': datetime.now().isoformat()
            }
        }])
        
        return {
            "success": True,
            "vector_id": vector_id,
            "dimension": len(embedding)
        }
    except Exception as e:
        logger.error(f"Embedding error: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/api/v1/rag/query")
async def query_knowledge_base(query: RAGQueryInput):
    """Buscar en base de conocimiento usando RAG"""
    if not openai or not Pinecone:
        raise HTTPException(status_code=501, detail="RAG not configured")
    
    try:
        # Generar embedding de la query
        response = openai.Embedding.create(
            model="text-embedding-ada-002",
            input=query.query
        )
        
        query_embedding = response['data'][0]['embedding']
        
        # Buscar vectores similares
        results = index.query(
            vector=query_embedding,
            top_k=query.top_k,
            include_metadata=True,
            filter=query.filter
        )
        
        # Formatear resultados
        matches = []
        for match in results['matches']:
            matches.append({
                'id': match['id'],
                'score': match['score'],
                'content': match['metadata'].get('content', ''),
                'metadata': {k: v for k, v in match['metadata'].items() if k != 'content'}
            })
        
        return {
            "query": query.query,
            "matches": matches,
            "total": len(matches)
        }
    except Exception as e:
        logger.error(f"Query error: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/api/v1/chat/rag")
async def chat_with_rag(chat: ChatInput):
    """Chat educativo con RAG - usa contexto de base de conocimiento"""
    if not openai or not Pinecone:
        raise HTTPException(status_code=501, detail="RAG not configured")
    
    try:
        # 1. Buscar contexto relevante
        context_results = await query_knowledge_base(
            RAGQueryInput(query=chat.question, top_k=3)
        )
        
        # 2. Construir prompt con contexto
        context_text = "\n\n".join([
            f"Fuente {i+1}: {match['content']}"
            for i, match in enumerate(context_results['matches'])
        ])
        
        system_prompt = f"""Eres un asistente educativo experto. 
Usa el siguiente contexto para responder la pregunta del usuario.
Si el contexto no es suficiente, indica que necesitas más información.

CONTEXTO:
{context_text}
"""
        
        # 3. Generar respuesta con GPT-4
        response = openai.ChatCompletion.create(
            model="gpt-4-turbo-preview",
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": chat.question}
            ],
            temperature=0.7,
            max_tokens=800
        )
        
        answer = response.choices[0].message.content
        
        return {
            "question": chat.question,
            "answer": answer,
            "sources": [match['id'] for match in context_results['matches']],
            "confidence": "high" if context_results['total'] > 0 else "low"
        }
    except Exception as e:
        logger.error(f"RAG chat error: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "version": "2.0.0",
        "features": {
            "risk_prediction": True,
            "rag": openai is not None and Pinecone is not None,
            "chat": True
        }
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
