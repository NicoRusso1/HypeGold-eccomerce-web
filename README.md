<div align="center">

<img src="frontend/public/assets/logo-isotipo-cropped.png" width="90" alt="HypeGold" />

### [![Typing SVG](https://readme-typing-svg.demolab.com?font=Fira+Code&weight=700&size=26&pause=1200&color=D4A62A&center=true&vCenter=true&width=560&lines=HypeGold+Joyas;Oro+que+se+hace+notar;E-commerce+full-stack;Laravel+%2B+Angular+%2B+Tailwind)](https://git.io/typing-svg)

Tienda online de joyería en enchapado de oro 18k — proyecto full-stack de e-commerce, de punta a punta: catálogo, carrito, checkout con Mercado Pago, panel de administración y despliegue real.

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Angular](https://img.shields.io/badge/Angular-22-DD0031?logo=angular&logoColor=white)](https://angular.dev)
[![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?logo=typescript&logoColor=white)](https://www.typescriptlang.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Mercado Pago](https://img.shields.io/badge/Mercado_Pago-Checkout_Pro-00B1EA?logo=mercadopago&logoColor=white)](https://www.mercadopago.com.ar/developers)

**[🌐 Ver demo en vivo](https://hype-gold-eccomerce-web.vercel.app)** · **[📑 Documentación de la API](https://hypegold-eccomerce-web.onrender.com/api/documentation)**

</div>

> [!NOTE]
> Es un proyecto de portfolio desplegado en hosting gratuito (Vercel + Render) — la primera carga del día puede tardar unos segundos mientras el backend "despierta". Mercado Pago queda en modo sandbox, así que no se procesan pagos reales.

---

## 📸 Capturas

<table>
  <tr>
    <td width="50%"><img src="docs/screenshots/home.jpg" alt="Home" /></td>
    <td width="50%"><img src="docs/screenshots/catalogo.jpg" alt="Catálogo" /></td>
  </tr>
  <tr>
    <td width="50%"><img src="docs/screenshots/producto.jpg" alt="Detalle de producto" /></td>
    <td width="50%"><img src="docs/screenshots/admin.jpg" alt="Panel de administración" /></td>
  </tr>
</table>

## ✨ Funcionalidades

**Tienda**
- Catálogo con filtros (categoría, material, precio) y buscador con autocompletado
- Detalle de producto con variantes (talle/medida) y stock en tiempo real
- Carrito, favoritos, cupones de descuento
- Checkout con pago vía Mercado Pago (Checkout Pro) y webhook de confirmación
- Cuenta de usuario: direcciones, historial de pedidos, comprobante de compra
- Reseñas de productos

**Panel de administración**
- Dashboard de ventas con filtros por fecha
- ABM de productos (con variantes e imágenes), categorías y cupones
- Gestión de stock y de pedidos (cambios de estado con notificación por email)
- Buscador en los listados de productos, pedidos, categorías y stock

## 🛠 Stack

| | |
|---|---|
| **Backend** | Laravel 12 · PHP 8.2+ · Laravel Sanctum · MySQL / PostgreSQL · L5-Swagger |
| **Frontend** | Angular 22 (standalone + signals) · Tailwind CSS 4 · Vitest |
| **Pagos** | Mercado Pago (Checkout Pro) |
| **Deploy** | Vercel (frontend) · Render (backend + Postgres) — [ver guía](DEPLOY.md) |
| **Gestión** | Jira (proyecto `HG`) |

## 📂 Estructura del repositorio

```
├── backend/    # API REST en Laravel 12 + Sanctum + Swagger
├── frontend/   # SPA en Angular + Tailwind CSS
└── docs/       # Documentación del proyecto (presentación, capturas)
```

## ⚙️ Puesta en marcha

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

## ☁️ Deploy

El sitio se puede desplegar gratis (como demo) en Vercel (frontend) + Render (backend). Guía paso a paso en [`DEPLOY.md`](DEPLOY.md).

## 🌳 Convenciones de Git

- **Ramas:** `main` (estable), `develop` (integración), `feature/HG-123-descripcion-corta`, `fix/HG-123-descripcion-corta`.
- **Commits:** Conventional Commits + clave de Jira. Ejemplo: `feat(catalogo): agregar filtros por material [HG-19]`.

## 📄 Documentación

El documento de presentación del proyecto está en [`docs/HypeGold-Presentacion-del-Proyecto.docx`](docs/HypeGold-Presentacion-del-Proyecto.docx).

## 📋 Gestión del proyecto

El backlog completo (épicas, historias y subtareas) está en Jira, proyecto **HG** en `hypegold.atlassian.net`.

---

<div align="center">

Hecho en Concordia, Entre Ríos 🇦🇷

</div>
