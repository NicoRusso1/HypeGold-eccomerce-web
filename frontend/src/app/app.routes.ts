import { Routes } from '@angular/router';
import { Home } from './pages/home/home';
import { Register } from './pages/auth/register/register';
import { Login } from './pages/auth/login/login';

export const routes: Routes = [
  { path: '', component: Home, title: 'HypeGold Joyas' },
  { path: 'cuenta/registro', component: Register, title: 'Crear cuenta · HypeGold' },
  { path: 'cuenta/ingresar', component: Login, title: 'Ingresar · HypeGold' },
  // El resto de las rutas (catálogo, producto, carrito, perfil) se agregan
  // en sus historias correspondientes (HG-16, HG-18, HG-19, HG-20, HG-23, etc.).
];
