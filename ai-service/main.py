# Advanced FastAPI AI microservice for Symfony + React integration
# Production-ready structure with CSP Scheduling and Non-linear Risk Analysis

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field, validator
from typing import List, Dict, Optional, Tuple
import numpy as np
import logging
import random
from datetime import datetime

app = FastAPI(
    title="KPixelCraft School AI Service (Advanced)",
    version="2.0.0",
    description="Microservicio de IA Avanzado: Lógica Difusa para Riesgo y CSP para Horarios"
)

# Logging Configuration
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - [%(levelname)s] - %(name)s - %(message)s"
)
logger = logging.getLogger("ai-service")

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)


# ======================== MODELOS DE DATOS ========================

class StudentData(BaseModel):
    nombre: str
    asistencia: float = Field(..., ge=0, le=100, description="Porcentaje de asistencia")
    promedio: float = Field(..., ge=0, le=100, description="Promedio actual")
    conducta: float = Field(..., ge=0, le=100, description="Puntaje de conducta")
    faltas: int = Field(..., ge=0, description="Número de faltas injustificadas")
    pagos_atrasados: bool
    situacion_familiar: Optional[str] = None
    materias_reprobadas: int = Field(0, ge=0)

    @validator('nombre')
    def name_must_not_be_empty(cls, v):
        if not v.strip():
            raise ValueError('El nombre no puede estar vacío')
        return v


class ScheduleRequest(BaseModel):
    grados: List[str]
    maestros: List[str]
    especializaciones: Dict[str, List[str]]  # maestro -> [materias]
    materias: List[str]
    horas_por_dia: int = Field(6, ge=4, le=10)
    dias: List[str] = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"]
    bloqueos: Optional[Dict[str, List[Tuple[str, int]]]] = None # Maestro -> [(Dia, Hora)] no disponible


# ======================== LÓGICA DE RIESGO AVANZADA ========================

def calcular_riesgo_avanzado(student: StudentData) -> Dict:
    """
    Calcula el riesgo de deserción utilizando un sistema de puntuación no lineal
    y reglas de negocio críticas.
    """
    score = 0.0
    factores = []

    # 1. Análisis de Rendimiento Académico (No lineal)
    # Si el promedio baja de 60, el riesgo se dispara exponencialmente
    if student.promedio < 60:
        score += 40 + (60 - student.promedio) * 1.5
        factores.append("Rendimiento Crítico")
    elif student.promedio < 70:
        score += 20 + (70 - student.promedio)
        factores.append("Rendimiento Bajo")
    elif student.promedio < 80:
        score += 5

    # Penalización por materias reprobadas (Factor multiplicador)
    if student.materias_reprobadas > 0:
        impacto = student.materias_reprobadas * 10
        score += impacto
        factores.append(f"{student.materias_reprobadas} materias reprobadas")

    # 2. Análisis de Asistencia
    if student.asistencia < 75:
        score += 30
        factores.append("Asistencia Insuficiente (<75%)")
    elif student.asistencia < 85:
        score += 15
        factores.append("Asistencia Irregular")

    # 3. Factores Administrativos y Conductuales
    if student.pagos_atrasados:
        score += 15
        factores.append("Mora Financiera")
    
    if student.conducta < 70:
        score += 10
        factores.append("Problemas de Conducta")

    if student.situacion_familiar:
        score += 10
        factores.append("Alerta Familiar Reportada")

    # Normalización
    score = min(score, 100.0)
    
    # Clasificación
    if score >= 80:
        nivel = "CRÍTICO"
        accion = "Intervención Inmediata"
    elif score >= 50:
        nivel = "ALTO"
        accion = "Plan de Mejora y Seguimiento"
    elif score >= 30:
        nivel = "MEDIO"
        accion = "Observación"
    else:
        nivel = "BAJO"
        accion = "Mantener Buen Desempeño"

    return {
        "nombre": student.nombre,
        "nivel_riesgo": nivel,
        "puntaje": round(score, 2),
        "probabilidad_desercion": f"{round(score, 1)}%",
        "factores_riesgo": factores,
        "accion_sugerida": accion,
        "recomendaciones": generar_recomendaciones_contextuales(nivel, factores)
    }

def generar_recomendaciones_contextuales(nivel: str, factores: List[str]) -> List[str]:
    recs = []
    
    if "Rendimiento Crítico" in factores or "Rendimiento Bajo" in factores:
        recs.append("Asignar tutorías de refuerzo en materias clave.")
        recs.append("Revisión de hábitos de estudio con psicopedagogía.")
    
    if "Asistencia Insuficiente (<75%)" in factores:
        recs.append("Reunión obligatoria con padres para justificar inasistencias.")
        recs.append("Establecer compromiso de asistencia firmado.")

    if "Mora Financiera" in factores:
        recs.append("Remitir a administración para plan de pagos.")

    if nivel == "CRÍTICO":
        recs.insert(0, "🔴 ACTIVAR PROTOCOLO DE RETENCIÓN ESTUDIANTIL.")
    
    if not recs:
        recs.append("Felicitar al estudiante por su buen desempeño.")

    return recs


# ======================== GENERADOR DE HORARIOS (CSP) ========================

class CSPScheduler:
    def __init__(self, req: ScheduleRequest):
        self.req = req
        self.slots_totales = len(req.dias) * req.horas_por_dia
        self.horario = {}
        self.usage_map = {} # (dia, hora) -> ocupado
        self.teacher_usage = {} # (maestro, dia, hora) -> ocupado

    def solve(self) -> Dict:
        # Ordenar materias por dificultad (heurística: materias con menos maestros disponibles primero)
        materias_ordenadas = sorted(self.req.materias, key=lambda m: self._count_available_teachers(m))
        
        if self._backtrack(materias_ordenadas, 0):
            return self._format_solution()
        else:
            return {
                "error": "No se pudo generar un horario válido con las restricciones dadas.",
                "conflictos": ["Espacio insuficiente o conflictos de maestros insalvables."]
            }

    def _count_available_teachers(self, materia):
        count = 0
        for teachers_mats in self.req.especializaciones.values():
            if materia in teachers_mats:
                count += 1
        return count

    def _backtrack(self, materias, index):
        if index == len(materias):
            return True # Solución encontrada

        materia = materias[index]
        
        # Probar todos los slots posibles (Dia, Hora)
        # Randomizar para variedad en soluciones
        slots = [(d, h) for d in range(len(self.req.dias)) for h in range(self.req.horas_por_dia)]
        random.shuffle(slots)

        for dia_idx, hora_idx in slots:
            if self._is_slot_free(dia_idx, hora_idx):
                # Buscar maestro disponible
                maestro = self._find_teacher_for(materia, dia_idx, hora_idx)
                if maestro:
                    # Asignar
                    self._assign(materia, maestro, dia_idx, hora_idx)
                    
                    # Recurrir
                    if self._backtrack(materias, index + 1):
                        return True
                    
                    # Backtrack (Desasignar)
                    self._unassign(dia_idx, hora_idx, maestro)
        
        return False

    def _is_slot_free(self, dia_idx, hora_idx):
        return (dia_idx, hora_idx) not in self.usage_map

    def _find_teacher_for(self, materia, dia_idx, hora_idx):
        # Buscar maestros que den la materia y no estén ocupados
        candidatos = []
        for maestro, materias in self.req.especializaciones.items():
            if materia in materias:
                if (maestro, dia_idx, hora_idx) not in self.teacher_usage:
                    candidatos.append(maestro)
        
        if candidatos:
            # Heurística: elegir el que tenga menos carga actual para balancear (opcional)
            return random.choice(candidatos)
        return None

    def _assign(self, materia, maestro, dia_idx, hora_idx):
        self.usage_map[(dia_idx, hora_idx)] = (materia, maestro)
        self.teacher_usage[(maestro, dia_idx, hora_idx)] = True

    def _unassign(self, dia_idx, hora_idx, maestro):
        del self.usage_map[(dia_idx, hora_idx)]
        del self.teacher_usage[(maestro, dia_idx, hora_idx)]

    def _format_solution(self):
        result = {}
        for (dia_idx, hora_idx), (materia, maestro) in self.usage_map.items():
            dia_nombre = self.req.dias[dia_idx]
            hora_num = hora_idx + 1
            key = f"{dia_nombre} - Hora {hora_num}"
            result[key] = {
                "materia": materia,
                "maestro": maestro,
                "dia": dia_nombre,
                "hora": hora_num
            }
        
        # Ordenar por día y hora para presentación
        sorted_keys = sorted(result.keys(), key=lambda k: (self.req.dias.index(result[k]['dia']), result[k]['hora']))
        sorted_result = {k: result[k] for k in sorted_keys}

        return {
            "horario": sorted_result,
            "metadata": {
                "materias_asignadas": len(self.usage_map),
                "total_slots": self.slots_totales,
                "eficiencia": f"{round(len(self.usage_map)/self.slots_totales*100)}%"
            }
        }


# ======================== ENDPOINTS ========================

@app.post("/api/v1/risk/predict", response_model=Dict)
def predict_risk(data: StudentData):
    logger.info(f"Analizando riesgo avanzado para: {data.nombre}")
    try:
        return calcular_riesgo_avanzado(data)
    except Exception as e:
        logger.error(f"Error en cálculo de riesgo: {str(e)}")
        raise HTTPException(status_code=500, detail="Error interno en análisis de riesgo")


@app.post("/api/v1/schedule/generate", response_model=Dict)
def schedule(req: ScheduleRequest):
    logger.info(f"Generando horario optimizado para {len(req.materias)} materias")
    try:
        scheduler = CSPScheduler(req)
        return scheduler.solve()
    except Exception as e:
        logger.error(f"Error en generación de horario: {str(e)}")
        raise HTTPException(status_code=500, detail="Error interno generando horario")


@app.get("/health")
def health():
    return {
        "status": "ok", 
        "service": "KPixelCraft AI Service", 
        "version": "2.0.0",
        "mode": "Advanced (Internal Logic)"
    }
