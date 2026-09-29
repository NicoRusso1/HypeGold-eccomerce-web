import { Routes } from '@angular/router';
import { Home } from './pages/home/home';
import { Register } from './pages/auth/register/register';
import { Login } from './pages/auth/login/login';
import { ForgotPassword } from './pages/auth/forgot-password/forgot-password';
import { ResetPassword } from './pages/auth/reset-password/reset-password';
import { Profile } from './pages/profile/profile';
import { Catalog } from './pages/catalog/catalog';
import { ProductDetailPage } from './pages/product-detail/product-detail';
import { CartPage } from './pages/cart/cart';
import { AddressesPage } from './pages/addresses/addresses';
import { CheckoutPage } from './pages/checkout/checkout';
import { FavoritesPage } from './pages/favorites/favorites';
import { OrdersPage } from './pages/orders/orders';
import { OrderDetailPage } from './pages/order-detail/order-detail';
import { authGuard } from './core/guards/auth.guard';

export const routes: Routes = [
  { path: '', component: Home, title: 'HypeGold Joyas' },
  { path: 'catalogo', component: Catalog, title: 'Catálogo · HypeGold' },
  { path: 'catalogo/:categoria', component: Catalog, title: 'Catálogo · HypeGold' },
  { path: 'producto/:slug', component: ProductDetailPage, title: 'HypeGold' },
  { path: 'carrito', component: CartPage, title: 'Mi carrito · HypeGold', canActivate: [authGuard] },
  { path: 'checkout', component: CheckoutPage, title: 'Finalizar compra · HypeGold', canActivate: [authGuard] },
  { path: 'favoritos', component: FavoritesPage, title: 'Mis favoritos · HypeGold', canActivate: [authGuard] },
  { path: 'cuenta/registro', component: Register, title: 'Crear cuenta · HypeGold' },
  { path: 'cuenta/ingresar', component: Login, title: 'Ingresar · HypeGold' },
  { path: 'cuenta/olvide-contrasena', component: ForgotPassword, title: 'Recuperar contraseña · HypeGold' },
  { path: 'cuenta/restablecer-contrasena', component: ResetPassword, title: 'Restablecer contraseña · HypeGold' },
  { path: 'cuenta/perfil', component: Profile, title: 'Mi perfil · HypeGold', canActivate: [authGuard] },
  {
    path: 'cuenta/direcciones',
    component: AddressesPage,
    title: 'Mis direcciones · HypeGold',
    canActivate: [authGuard],
  },
  { path: 'cuenta/pedidos', component: OrdersPage, title: 'Mis pedidos · HypeGold', canActivate: [authGuard] },
  {
    path: 'cuenta/pedidos/:id',
    component: OrderDetailPage,
    title: 'Mi pedido · HypeGold',
    canActivate: [authGuard],
  },
];
