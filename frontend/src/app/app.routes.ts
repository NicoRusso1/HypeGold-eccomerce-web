import { Routes } from '@angular/router';
import { Home } from './pages/home/home';
import { Register } from './pages/auth/register/register';
import { Login } from './pages/auth/login/login';
import { ForgotPassword } from './pages/auth/forgot-password/forgot-password';
import { ResetPassword } from './pages/auth/reset-password/reset-password';
import { Profile } from './pages/profile/profile';
import { authGuard } from './core/guards/auth.guard';

export const routes: Routes = [
  { path: '', component: Home, title: 'HypeGold Joyas' },
  { path: 'cuenta/registro', component: Register, title: 'Crear cuenta · HypeGold' },
  { path: 'cuenta/ingresar', component: Login, title: 'Ingresar · HypeGold' },
  { path: 'cuenta/olvide-contrasena', component: ForgotPassword, title: 'Recuperar contraseña · HypeGold' },
  { path: 'cuenta/restablecer-contrasena', component: ResetPassword, title: 'Restablecer contraseña · HypeGold' },
  { path: 'cuenta/perfil', component: Profile, title: 'Mi perfil · HypeGold', canActivate: [authGuard] },
  // El resto de las rutas (catálogo, producto, carrito) se agregan en sus
  // historias correspondientes (HG-18, HG-19, HG-20, HG-23, etc.).
];
