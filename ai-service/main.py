# AI Microservice - FastAPI

from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Dict, Any
import logging

app = FastAPI(title="School AI Service", version="1.0.0")

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Models
class StudentRiskRequest(BaseModel):
    student_id: int
    attendance_rate: float
    average_grade: float
    behavior_score: float
    participation_score: float
    payment_status: str

class RiskPrediction(BaseModel):
    student_id: int
    risk_level: str
    risk_percentage: float
    factors: Dict[str, float]
    recommendations: List[str]

class ChatRequest(BaseModel):
    question: str
    context: str = ""

class ChatResponse(BaseModel):
    answer: str
    confidence: float
    sources: List[str]

# Endpoints
@app.get("/health")
async def health_check():
    return {"status": "healthy", "service": "ai-service"}

@app.post("/api/v1/risk/predict", response_model=RiskPrediction)
async def predict_academic_risk(request: StudentRiskRequest):
    """
    Predice el riesgo académico de un estudiante usando ML
    """
    try:
        # TODO: Implementar modelo ML real
        # Por ahora, lógica basada en reglas
        
        factors = {
            "attendance": calculate_attendance_factor(request.attendance_rate),
            "grades": calculate_grade_factor(request.average_grade),
            "behavior": calculate_behavior_factor(request.behavior_score),
            "participation": calculate_participation_factor(request.participation_score),
            "payment": calculate_payment_factor(request.payment_status)
        }
        
        risk_percentage = calculate_risk_percentage(factors)
        risk_level = determine_risk_level(risk_percentage)
        recommendations = generate_recommendations(factors, risk_percentage)
        
        return RiskPrediction(
            student_id=request.student_id,
            risk_level=risk_level,
            risk_percentage=risk_percentage,
            factors=factors,
            recommendations=recommendations
        )
    except Exception as e:
        logger.error(f"Error predicting risk: {str(e)}")
        raise HTTPException(status_code=500, detail="Error processing prediction")

@app.post("/api/v1/chat/ask", response_model=ChatResponse)
async def educational_assistant(request: ChatRequest):
    """
    Asistente educativo con RAG (Retrieval Augmented Generation)
    """
    try:
        # TODO: Implementar RAG con vector database
        # TODO: Integrar con OpenAI/Anthropic
        
        return ChatResponse(
            answer="Esta funcionalidad estará disponible próximamente con integración de LLM.",
            confidence=0.0,
            sources=[]
        )
    except Exception as e:
        logger.error(f"Error in chat: {str(e)}")
        raise HTTPException(status_code=500, detail="Error processing chat")

# Helper functions
def calculate_attendance_factor(rate: float) -> float:
    if rate >= 0.95:
        return 1.0
    elif rate >= 0.85:
        return 0.5
    elif rate >= 0.75:
        return 0.0
    else:
        return -0.5

def calculate_grade_factor(grade: float) -> float:
    if grade >= 90:
        return 1.0
    elif grade >= 75:
        return 0.5
    elif grade >= 60:
        return 0.0
    else:
        return -0.5

def calculate_behavior_factor(score: float) -> float:
    return (score - 50) / 50  # Normalize to -1 to 1

def calculate_participation_factor(score: float) -> float:
    return (score - 50) / 50  # Normalize to -1 to 1

def calculate_payment_factor(status: str) -> float:
    return 0.5 if status == "paid" else -0.5

def calculate_risk_percentage(factors: Dict[str, float]) -> float:
    avg_factor = sum(factors.values()) / len(factors)
    return max(0, min(100, (1 - avg_factor) * 50))

def determine_risk_level(percentage: float) -> str:
    if percentage < 25:
        return "low"
    elif percentage < 50:
        return "medium"
    elif percentage < 75:
        return "high"
    else:
        return "critical"

def generate_recommendations(factors: Dict[str, float], risk: float) -> List[str]:
    recommendations = []
    
    if factors["attendance"] < -0.3:
        recommendations.append("Mejorar asistencia - contactar a padres")
    if factors["grades"] < -0.3:
        recommendations.append("Reforzamiento académico necesario")
    if factors["behavior"] < -0.3:
        recommendations.append("Intervención de orientación psicológica")
    if factors["participation"] < -0.3:
        recommendations.append("Fomentar participación en clase")
    if factors["payment"] < 0:
        recommendations.append("Revisar situación financiera")
    if risk > 70:
        recommendations.append("URGENTE: Reunión con padres y coordinación")
    
    return recommendations

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
