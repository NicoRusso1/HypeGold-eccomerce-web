import { Routes } from '@angular/router';
import { Home } from './pages/home/home';

export const routes: Routes = [
  { path: '', component: Home, title: 'HypeGold Joyas' },
  // El resto de las rutas (catálogo, producto, carrito, cuenta) se agregan
  // en sus historias correspondientes (HG-18, HG-19, HG-20, HG-23, etc.).
];
