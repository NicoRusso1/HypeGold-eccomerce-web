# HypeGold — Tienda online de joyas

Proyecto de e-commerce de joyas para **HypeGold Joyas** (Concordia, Entre Ríos). Monorepo con backend en Laravel, frontend en Angular + Tailwind CSS, y documentación de la API con Swagger.

## Estructura del repositorio

```
├── backend/    # API REST en Laravel 12 + Sanctum + Swagger
├── frontend/   # SPA en Angular + Tailwind CSS
└── docs/       # Documentación del proyecto (presentación, assets)
```

## Stack

- **Backend:** Laravel 12 (PHP 8.2+), Laravel Sanctum, MySQL, L5-Swagger.
- **Frontend:** Angular, Tailwind CSS.
- **Gestión:** Jira (proyecto `HG`).
- **Pagos:** Mercado Pago (modo sandbox).

## Puesta en marcha

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

La documentación de la API queda disponible en `http://localhost:8000/api/documentation`.

`php artisan migrate --seed` crea dos usuarios de prueba (solo para desarrollo local):

| Rol | Email | Contraseña |
|---|---|---|
| Administrador | `admin@hypegold.com` | `password` |
| Cliente | `cliente@hypegold.com` | `password` |

### Frontend

```bash
cd frontend
npm install
ng serve
```

La app queda disponible en `http://localhost:4200`.

## Convenciones de Git

- **Ramas:** `main` (estable), `develop` (integración), `feature/HG-123-descripcion-corta`, `fix/HG-123-descripcion-corta`.
- **Commits:** Conventional Commits + clave de Jira. Ejemplo: `feat(catalogo): agregar filtros por material [HG-19]`.

## Documentación

El documento de presentación del proyecto está en [`docs/HypeGold-Presentacion-del-Proyecto.docx`](docs/HypeGold-Presentacion-del-Proyecto.docx).

## Gestión del proyecto

El backlog completo (épicas, historias y subtareas) está en Jira, proyecto **HG** en `hypegold.atlassian.net`.
