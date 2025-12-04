# Arquitectura del Sistema KPixelCraft

## Visión General
El sistema sigue una arquitectura de microservicios híbrida, con un monolito modular (Symfony) para el core, un frontend SPA (React), y microservicios especializados (Python AI).

## Diagrama de Componentes

```mermaid
graph TD
    Client[Cliente Web/Móvil] --> LB[Load Balancer / Nginx]
    LB --> Frontend[Frontend React (Vite)]
    LB --> Backend[Backend Symfony API]
    LB --> AI[AI Service (FastAPI)]
    
    Backend --> DB[(PostgreSQL)]
    Backend --> Redis[(Redis Cache)]
    AI --> DB
    
    Backend --> Stripe[Stripe API]
    Backend --> Mailer[SMTP Service]
```

## Backend (Symfony 7)
El backend está organizado siguiendo principios de **Domain-Driven Design (DDD)** y **Clean Architecture**.

### Estructura de Directorios
- `src/Entity`: Modelos de dominio y persistencia (Doctrine).
- `src/Repository`: Acceso a datos.
- `src/Service`: Lógica de negocio y servicios de dominio.
- `src/Controller`: Puntos de entrada HTTP (aunque usamos API Platform principalmente).
- `src/Dto`: Objetos de transferencia de datos.
- `src/Security`: Lógica de autenticación y autorización (Voters, Authenticators).

### Patrones Clave
- **API Platform**: Para exponer recursos REST/GraphQL automáticamente.
- **CQRS**: Separación de comandos (escritura) y consultas (lectura) en operaciones complejas.
- **Event Dispatcher**: Para desacoplar lógica secundaria (ej. enviar email tras registro).

## Frontend (React + TypeScript)
- **Vite**: Build tool.
- **Tailwind CSS**: Styling.
- **Zustand/Context**: Gestión de estado.
- **React Query**: Gestión de estado asíncrono (server state).

## AI Service (Python FastAPI)
Microservicio dedicado para tareas de cómputo intensivo o lógica difusa.
- **Riesgo**: Algoritmos de lógica difusa para detectar deserción.
- **Horarios**: Algoritmo CSP (Constraint Satisfaction Problem) para generar horarios.

## Base de Datos (PostgreSQL)
Esquema relacional normalizado.
- **Usuarios**: Tabla única con discriminador o tablas separadas por rol (actualmente Single Table Inheritance o Mapped Superclass).
- **Enrollments**: Historial académico.
