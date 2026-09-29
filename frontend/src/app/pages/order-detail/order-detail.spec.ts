import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap, provideRouter } from '@angular/router';
import { of } from 'rxjs';
import { OrderDetailPage } from './order-detail';

describe('OrderDetailPage', () => {
  let component: OrderDetailPage;
  let fixture: ComponentFixture<OrderDetailPage>;
  let httpMock: HttpTestingController;

  const orderResponse = {
    data: {
      id: 7,
      status: 'enviado',
      total: 45000,
      discount: 0,
      coupon_code: null,
      created_at: '2026-09-29T00:00:00Z',
      shipping: { label: 'Casa', street: 'Urquiza 456', city: 'Concordia', province: 'Entre Ríos', postal_code: 'E3200', phone: '345' },
      items: [
        { id: 1, product_name: 'Cadena cubana', variant_talle: '50cm', sku: 'SKU-1', unit_price: 45000, quantity: 1, subtotal: 45000 },
      ],
    },
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [OrderDetailPage],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        provideRouter([]),
        { provide: ActivatedRoute, useValue: { paramMap: of(convertToParamMap({ id: '7' })) } },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(OrderDetailPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and load the order by id', () => {
    expect(component).toBeTruthy();

    const req = httpMock.expectOne((r) => r.url.endsWith('/orders/7'));
    req.flush(orderResponse);

    expect(component['order']()?.id).toBe(7);
    expect(component['loading']()).toBe(false);
  });

  it('muestra notFound cuando la peticion falla', () => {
    const req = httpMock.expectOne((r) => r.url.endsWith('/orders/7'));
    req.flush({ message: 'No encontrado' }, { status: 404, statusText: 'Not Found' });

    expect(component['notFound']()).toBe(true);
  });
});
