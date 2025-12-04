import unittest
import sys
import os

# Add parent dir to path to import main
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from main import calcular_riesgo_avanzado, CSPScheduler, StudentData, ScheduleRequest

class TestAILogic(unittest.TestCase):

    def test_risk_critical(self):
        student = StudentData(
            nombre="Juan Perez",
            asistencia=60, # Muy baja
            promedio=50,   # Reprobado
            conducta=80,
            faltas=10,
            pagos_atrasados=True,
            materias_reprobadas=3
        )
        result = calcular_riesgo_avanzado(student)
        self.assertEqual(result['nivel_riesgo'], "CRÍTICO")
        self.assertIn("Rendimiento Crítico", result['factores_riesgo'])

    def test_risk_low(self):
        student = StudentData(
            nombre="Maria Lopez",
            asistencia=95,
            promedio=90,
            conducta=95,
            faltas=0,
            pagos_atrasados=False,
            materias_reprobadas=0
        )
        result = calcular_riesgo_avanzado(student)
        self.assertEqual(result['nivel_riesgo'], "BAJO")

    def test_scheduler_simple(self):
        req = ScheduleRequest(
            grados=["1A"],
            maestros=["Prof. A", "Prof. B"],
            especializaciones={
                "Prof. A": ["Matemáticas", "Física"],
                "Prof. B": ["Historia", "Lenguaje"]
            },
            materias=["Matemáticas", "Historia", "Física", "Lenguaje"],
            horas_por_dia=4,
            dias=["Lunes"]
        )
        scheduler = CSPScheduler(req)
        result = scheduler.solve()
        
        self.assertIn("horario", result)
        horario = result['horario']
        self.assertEqual(len(horario), 4) # 4 materias asignadas

if __name__ == '__main__':
    unittest.main()
