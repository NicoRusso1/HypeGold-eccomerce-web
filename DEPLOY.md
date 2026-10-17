# Deploy (demo gratis)

Esta guía deja el sitio online gratis, para mostrarlo como demo. No está pensada para un negocio real en producción (ver "Limitaciones" al final).

- **Frontend (Angular):** [Vercel](https://vercel.com) — gratis.
- **Backend (Laravel API) + base de datos:** [Render](https://render.com) — gratis (el servicio web "duerme" tras 15 min sin uso; la primera visita del día tarda ~30-60s en responder).

## 1. Base de datos en Render

1. Creá una cuenta en Render y andá a **New +** → **PostgreSQL**.
2. Nombre: `hypegold-db` (o el que quieras). Plan: **Free**.
3. Una vez creada, copiá la **Internal Database URL** (la vas a necesitar en el paso 2).

## 2. Backend en Render

1. **New +** → **Web Service** → conectá el repo de GitHub.
2. **Root Directory:** `backend`
3. **Runtime:** Docker (Render va a detectar el `backend/Dockerfile` automáticamente).
4. **Plan:** Free.
5. En **Environment**, cargá estas variables (copiá los valores desde tu `backend/.env` local donde aplique):

   | Variable | Valor |
   |---|---|
   | `APP_NAME` | `HypeGold` |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_KEY` | Generala local con `php artisan key:generate --show` y pegá el resultado (incluye el prefijo `base64:`) |
   | `APP_URL` | La URL que Render te asigna al servicio (ej. `https://hypegold-backend.onrender.com`) — podés completarla después del primer deploy y volver a guardar |
   | `DB_CONNECTION` | `pgsql` |
   | `DB_URL` | La Internal Database URL del paso 1 |
   | `SESSION_DRIVER` | `database` |
   | `CACHE_STORE` | `database` |
   | `QUEUE_CONNECTION` | `database` |
   | `MAIL_MAILER` | `log` (los emails no se mandan de verdad, es una demo) |
   | `FRONTEND_URL` | La URL de Vercel del paso 3 (podés dejarla vacía y completarla después) |
   | `CORS_ALLOWED_ORIGINS` | Igual que `FRONTEND_URL` |
   | `MERCADOPAGO_ACCESS_TOKEN` / `MERCADOPAGO_PUBLIC_KEY` | Tus credenciales de **sandbox** de Mercado Pago |
   | `L5_SWAGGER_CONST_HOST` | `${APP_URL}/api` |

6. Deploy. El `Dockerfile` corre las migraciones automáticamente al arrancar (`php artisan migrate --force`), así que no hace falta correrlas a mano.
7. Para cargar los datos de muestra (categorías, productos, usuario admin), abrí la **Shell** del servicio en Render y corré:
   ```bash
   php artisan db:seed
   ```

## 3. Frontend en Vercel

1. En Vercel: **Add New** → **Project** → importá el repo.
2. **Root Directory:** `frontend` (Vercel va a leer `frontend/vercel.json` automáticamente para el build y las rewrites de rutas del SPA).
3. Antes de desplegar (o después, si ya tenés la URL del backend): editá [`frontend/src/environments/environment.ts`](frontend/src/environments/environment.ts) y poné la URL real de tu servicio de Render en `apiUrl` (con `/api` al final), por ejemplo:
   ```ts
   apiUrl: 'https://hypegold-backend.onrender.com/api',
   ```
   Commiteá ese cambio y Vercel vuelve a desplegar solo.
4. Deploy. Vercel te da una URL tipo `https://hypegold.vercel.app`.

## 4. Conectar las dos puntas

Una vez que tengas ambas URLs:

1. En Render, actualizá `FRONTEND_URL` y `CORS_ALLOWED_ORIGINS` con la URL de Vercel y guardá (esto redeploya el backend).
2. Si la URL del backend en Render no es la que pusiste en el paso 3, actualizá `environment.ts` de nuevo y commiteá.

## Limitaciones de esta demo (a propósito, para no gastar de más)

- **Mercado Pago queda en sandbox**: no se puede cobrar de verdad. Para eso hace falta una cuenta de MP verificada como negocio con CUIT.
- **Los emails no se envían**: quedan solo en el log del servidor. Para mandarlos de verdad hace falta un proveedor de mail (Resend, Mailgun, etc.).
- **Las fotos que se suban desde el panel admin después del deploy no persisten**: cada vez que Render reconstruye el contenedor (un nuevo deploy), se pierden. Las fotos que ya están en el repo (el catálogo de muestra) no se ven afectadas. Si en algún momento esto importa, hay que mover el storage a algo externo (ej. Cloudinary o un bucket S3).
- **Cold start**: si nadie entró en 15 minutos, el backend se "duerme" y la primera carga del día tarda más.
