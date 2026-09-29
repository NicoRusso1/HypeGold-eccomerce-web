import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { OrdersPage } from './orders';

describe('OrdersPage', () => {
  let component: OrdersPage;
  let fixture: ComponentFixture<OrdersPage>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [OrdersPage],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(OrdersPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and load orders', () => {
    expect(component).toBeTruthy();

    const req = httpMock.expectOne((r) => r.url.endsWith('/orders') && r.method === 'GET');
    req.flush({
      data: [
        {
          id: 1,
          status: 'pendiente',
          total: 45000,
          discount: 0,
          coupon_code: null,
          created_at: '2026-09-29T00:00:00Z',
          shipping: {},
          items_count: 2,
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
    });

    expect(component['orders']()).toHaveLength(1);
    expect(component['loading']()).toBe(false);
  });

  it('muestra un mensaje cuando no hay pedidos', () => {
    httpMock.expectOne((r) => r.url.endsWith('/orders')).flush({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
    });
    fixture.detectChanges();

    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text).toContain('Todavía no hiciste ningún pedido');
  });
});
