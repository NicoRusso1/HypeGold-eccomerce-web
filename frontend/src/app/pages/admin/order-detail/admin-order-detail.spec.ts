import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap, provideRouter } from '@angular/router';
import { AdminOrderDetailPage } from './admin-order-detail';

describe('AdminOrderDetailPage', () => {
  let component: AdminOrderDetailPage;
  let fixture: ComponentFixture<AdminOrderDetailPage>;
  let httpMock: HttpTestingController;

  const orderResponse = {
    data: {
      id: 7,
      status: 'pagado',
      total: 45000,
      discount: 0,
      coupon_code: null,
      created_at: '2026-09-29T00:00:00Z',
      customer: { id: 2, name: 'Juana Pérez', email: 'juana@hypegold.com', phone: '3454000000' },
      shipping: { label: 'Casa', street: 'Urquiza 456', city: 'Concordia', province: 'Entre Ríos', postal_code: 'E3200', phone: '345' },
      items: [
        { id: 1, product_name: 'Cadena cubana', variant_talle: '50cm', sku: 'SKU-1', unit_price: 45000, quantity: 1, subtotal: 45000 },
      ],
    },
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminOrderDetailPage],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        provideRouter([]),
        { provide: ActivatedRoute, useValue: { snapshot: { paramMap: convertToParamMap({ id: '7' }) } } },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(AdminOrderDetailPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and load the order by id', () => {
    expect(component).toBeTruthy();

    const req = httpMock.expectOne((r) => r.url.endsWith('/admin/orders/7'));
    req.flush(orderResponse);

    expect(component['order']()?.id).toBe(7);
    expect(component['transicionesDisponibles']).toEqual(['enviado', 'cancelado']);
  });

  it('cambia el estado del pedido', () => {
    httpMock.expectOne((r) => r.url.endsWith('/admin/orders/7')).flush(orderResponse);

    component['cambiarEstado']('enviado');

    const req = httpMock.expectOne((r) => r.url.endsWith('/admin/orders/7/status') && r.method === 'PUT');
    expect(req.request.body).toEqual({ status: 'enviado' });
    req.flush({ data: { ...orderResponse.data, status: 'enviado' } });

    expect(component['order']()?.status).toBe('enviado');
  });

  it('muestra el error cuando la transicion de estado falla', () => {
    httpMock.expectOne((r) => r.url.endsWith('/admin/orders/7')).flush(orderResponse);

    component['cambiarEstado']('entregado');

    const req = httpMock.expectOne((r) => r.url.endsWith('/admin/orders/7/status'));
    req.flush({ errors: { status: ['No se puede pasar de "pagado" a "entregado".'] } }, { status: 422, statusText: 'Unprocessable Entity' });

    expect(component['statusError']()).toContain('No se puede pasar');
  });
});
