# Improved FastAPI AI microservice for Symfony + React integration
# Production-ready structure

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from typing import List, Dict, Optional
import numpy as np
import logging

app = FastAPI(
    title="KPixelCraft School AI Service",
    version="1.1.0",
    description="Microservicio de IA para Predicción de Riesgo y Generación de Horarios"
)

# Logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(levelname)s - %(message)s"
)
logger = logging.getLogger(__name__)

# CORS (permit Symfony y React)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)


# ======================== MODELOS ========================

class StudentData(BaseModel):
    nombre: str
    asistencia: float = Field(..., ge=0, le=100)
    promedio: float = Field(..., ge=0, le=100)
    conducta: float = Field(..., ge=0, le=100)
    faltas: int = Field(..., ge=0)
    pagos_atrasados: bool
    situacion_familiar: Optional[str] = None


class ScheduleRequest(BaseModel):
    grados: List[str]
    maestros: List[str]
    especializaciones: Dict[str, List[str]]  # maestro → lista de materias
    materias: List[str]
    horas_por_dia: int = 6
    dias: List[str] = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"]


# ======================== IA RIESGO ========================

def calcular_riesgo(student: StudentData) -> Dict:
    score = 0

    score += (100 - student.asistencia) * 0.25
    score += (100 - student.promedio) * 0.40
    score += (100 - student.conducta) * 0.20
    score += min(student.faltas, 30) * 0.5

    if student.pagos_atrasados:
        score += 10

    if student.situacion_familiar:
        score += 5

    score = min(score, 100)

    if score <= 25:
        nivel = "bajo"
    elif score <= 50:
        nivel = "medio"
    elif score <= 75:
        nivel = "alto"
    else:
        nivel = "crítico"

    return {
        "nombre": student.nombre,
        "nivel_riesgo": nivel,
        "puntaje": score,
        "probabilidad_desercion": round(score / 100, 2),
        "recomendaciones": generar_recomendaciones(nivel)
    }


def generar_recomendaciones(nivel: str) -> List[str]:
    opciones = {
        "bajo": ["Mantener seguimiento normal."],
        "medio": ["Revisar tareas atrasadas.", "Llamada preventiva a padres."],
        "alto": ["Reunión con orientación.", "Plan de apoyo académico."],
        "crítico": ["Intervención inmediata.", "Visita domiciliar.", "Supervisión semanal."]
    }
    return opciones[nivel]


@app.post("/api/v1/risk/predict")
def predict_risk(data: StudentData):
    logger.info(f"Analizando riesgo de {data.nombre}")
    return calcular_riesgo(data)


# ======================== HORARIOS ========================

def generar_horario(req: ScheduleRequest) -> Dict:
    horario = {}
    conflictos = []

    slots_totales = len(req.dias) * req.horas_por_dia

    if len(req.materias) > slots_totales:
        raise HTTPException(400, "Demasiadas materias para los slots disponibles.")

    slot_index = 0

    for materia in req.materias:
        dia = req.dias[slot_index // req.horas_por_dia]
        hora = (slot_index % req.horas_por_dia) + 1

        maestro_asignado = None

        for maestro, especs in req.especializaciones.items():
            if materia in especs:
                maestro_asignado = maestro
                break

        if not maestro_asignado:
            conflictos.append(f"Ningún maestro puede impartir {materia}")

        horario[f"{dia} - Hora {hora}"] = {
            "materia": materia,
            "maestro": maestro_asignado
        }

        slot_index += 1

    return {
        "horario": horario,
        "conflictos": conflictos,
        "slots_usados": len(req.materias),
        "slots_totales": slots_totales,
        "optimización": round((len(req.materias) / slots_totales) * 100, 2)
    }


@app.post("/api/v1/schedule/generate")
def schedule(req: ScheduleRequest):
    return generar_horario(req)


# ======================== HEALTH ========================

@app.get("/health")
def health():
    return {"status": "ok", "service": "AI microservice"}


@app.get("/")
def root():
    return {"message": "IA escolar funcionando (producción-ready)."}
