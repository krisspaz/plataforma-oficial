# ADR-001: Adopción de Arquitectura Hexagonal

## Estado
Aceptado

## Contexto
El proyecto necesita una arquitectura que permita:
- Alta testabilidad
- Bajo acoplamiento
- Independencia de frameworks
- Facilidad para cambiar infraestructura
- Separación clara de responsabilidades

## Decisión
Adoptamos **Arquitectura Hexagonal** (Ports & Adapters) con DDD táctico:

### Estructura de Capas
```
/src
  /Domain          # Lógica de negocio pura
    /Student
      /ValueObject # Objetos de valor inmutables
      /Event       # Eventos de dominio
      StudentRepositoryInterface.php
      
  /Application     # Casos de uso
    /Student
      /Command     # Comandos (escritura)
      /Query       # Queries (lectura)
      /DTO         # Data Transfer Objects
      
  /Infrastructure  # Implementaciones técnicas
    /Persistence
      /Doctrine    # Adaptador Doctrine
    /EventListener # Event handlers
    
  /Presentation    # Controladores HTTP
    /Api
```

### Principios Aplicados
1. **Dependency Inversion**: Domain no depende de nada
2. **CQRS**: Separación Command/Query
3. **Event Sourcing**: Eventos inmutables para auditoría
4. **Repository Pattern**: Abstracción de persistencia
5. **Value Objects**: Validación en construcción

## Consecuencias

### Positivas
- ✅ Testabilidad 100% sin base de datos
- ✅ Cambiar ORM sin afectar dominio
- ✅ Lógica de negocio clara y centralizada
- ✅ Eventos para integraciones asíncronas
- ✅ Código auto-documentado

### Negativas
- ⚠️ Más archivos y carpetas
- ⚠️ Curva de aprendizaje inicial
- ⚠️ Requiere disciplina del equipo

## Alternativas Consideradas
1. **MVC tradicional**: Rechazado por acoplamiento
2. **Clean Architecture**: Similar, elegimos Hexagonal por simplicidad
3. **Microservicios desde inicio**: Prematuro para el tamaño actual

## Fecha
2025-12-03

## Autores
- Sistema de Gestión Escolar Team
