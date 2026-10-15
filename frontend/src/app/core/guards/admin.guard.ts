import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

/**
 * Protege las rutas del panel de administración: solo usuarios con rol
 * "administrador". Si no está logueado, lo manda a login; si está logueado
 * pero no es admin, lo manda al inicio.
 */
export const adminGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (!authService.isLoggedIn()) {
    return router.createUrlTree(['/cuenta/ingresar']);
  }

  if (authService.currentUser()?.role !== 'administrador') {
    return router.createUrlTree(['/']);
  }

  return true;
};
