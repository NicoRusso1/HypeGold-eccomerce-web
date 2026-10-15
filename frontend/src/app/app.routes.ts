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
import { PaymentResultPage } from './pages/payment-result/payment-result';
import { AdminProductListPage } from './pages/admin/product-list/admin-product-list';
import { AdminProductFormPage } from './pages/admin/product-form/admin-product-form';
import { AdminCategoryListPage } from './pages/admin/category-list/admin-category-list';
import { AdminStockListPage } from './pages/admin/stock-list/admin-stock-list';
import { AdminOrderListPage } from './pages/admin/order-list/admin-order-list';
import { AdminOrderDetailPage } from './pages/admin/order-detail/admin-order-detail';
import { AdminDashboardPage } from './pages/admin/dashboard/admin-dashboard';
import { authGuard } from './core/guards/auth.guard';
import { adminGuard } from './core/guards/admin.guard';

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
  {
    path: 'pago/exito',
    component: PaymentResultPage,
    title: 'Pago exitoso · HypeGold',
    data: { kind: 'success' },
    canActivate: [authGuard],
  },
  {
    path: 'pago/pendiente',
    component: PaymentResultPage,
    title: 'Pago pendiente · HypeGold',
    data: { kind: 'pending' },
    canActivate: [authGuard],
  },
  {
    path: 'pago/error',
    component: PaymentResultPage,
    title: 'Pago no procesado · HypeGold',
    data: { kind: 'failure' },
    canActivate: [authGuard],
  },
  {
    path: 'admin/productos',
    component: AdminProductListPage,
    title: 'Productos · Admin HypeGold',
    canActivate: [adminGuard],
  },
  {
    path: 'admin/productos/nuevo',
    component: AdminProductFormPage,
    title: 'Nuevo producto · Admin HypeGold',
    canActivate: [adminGuard],
  },
  {
    path: 'admin/productos/:id/editar',
    component: AdminProductFormPage,
    title: 'Editar producto · Admin HypeGold',
    canActivate: [adminGuard],
  },
  {
    path: 'admin/categorias',
    component: AdminCategoryListPage,
    title: 'Categorías · Admin HypeGold',
    canActivate: [adminGuard],
  },
  {
    path: 'admin/stock',
    component: AdminStockListPage,
    title: 'Stock · Admin HypeGold',
    canActivate: [adminGuard],
  },
  {
    path: 'admin/pedidos',
    component: AdminOrderListPage,
    title: 'Pedidos · Admin HypeGold',
    canActivate: [adminGuard],
  },
  {
    path: 'admin/pedidos/:id',
    component: AdminOrderDetailPage,
    title: 'Pedido · Admin HypeGold',
    canActivate: [adminGuard],
  },
  {
    path: 'admin/ventas',
    component: AdminDashboardPage,
    title: 'Ventas · Admin HypeGold',
    canActivate: [adminGuard],
  },
];
