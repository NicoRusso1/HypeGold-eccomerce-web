import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { AdminOrderListPage } from './admin-order-list';

describe('AdminOrderListPage', () => {
  let component: AdminOrderListPage;
  let fixture: ComponentFixture<AdminOrderListPage>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminOrderListPage],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(AdminOrderListPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and load orders', () => {
    expect(component).toBeTruthy();

    const req = httpMock.expectOne((r) => r.url.endsWith('/admin/orders') && r.method === 'GET');
    req.flush({
      data: [
        {
          id: 1,
          status: 'pendiente',
          total: 45000,
          discount: 0,
          coupon_code: null,
          created_at: '2026-01-01T00:00:00Z',
          customer: { id: 1, name: 'Juana Pérez', email: 'juana@hypegold.com', phone: null },
          shipping: { label: null, street: 'Calle 1', city: 'Concordia', province: 'Entre Ríos', postal_code: '3200', phone: '123' },
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
    });

    expect(component['orders']()).toHaveLength(1);
    expect(component['loading']()).toBe(false);
  });

  it('recarga al cambiar el filtro de estado', () => {
    httpMock.expectOne((r) => r.url.endsWith('/admin/orders')).flush({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
    });

    component['onCambioFiltro']('pagado');

    const req = httpMock.expectOne((r) => r.url.includes('/admin/orders') && r.params.get('status') === 'pagado');
    req.flush({ data: [], meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 } });

    expect(component['estadoFiltro']()).toBe('pagado');
  });

  it('busca pedidos por numero o cliente', () => {
    httpMock.expectOne((r) => r.url.endsWith('/admin/orders')).flush({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
    });

    component['busqueda'].set('juana');
    component['buscar']();

    const req = httpMock.expectOne((r) => r.url.includes('/admin/orders') && r.params.get('search') === 'juana');
    req.flush({ data: [], meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 } });

    expect(component['busqueda']()).toBe('juana');
  });
});
