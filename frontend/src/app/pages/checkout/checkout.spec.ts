import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { CheckoutPage } from './checkout';

describe('CheckoutPage', () => {
  let component: CheckoutPage;
  let fixture: ComponentFixture<CheckoutPage>;
  let httpMock: HttpTestingController;

  const cartConItems = {
    data: {
      id: 1,
      items: [
        {
          id: 10,
          quantity: 2,
          unit_price: 45000,
          subtotal: 90000,
          variant: { id: 1, talle: '50cm', stock: 12 },
          product: { id: 1, name: 'Cadena cubana', slug: 'cadena-cubana', image: null },
        },
      ],
      items_count: 2,
      total: 90000,
    },
  };

  const direccion = {
    id: 5,
    label: 'Casa',
    street: 'Av. Siempre Viva 742',
    city: 'Concordia',
    province: 'Entre Ríos',
    postal_code: 'E3200',
    phone: '345 4040162',
    is_default: true,
  };

  function flushInitialRequests(cart = cartConItems, addresses = [direccion]): void {
    httpMock.expectOne((r) => r.url.endsWith('/cart') && r.method === 'GET').flush(cart);
    httpMock.expectOne((r) => r.url.endsWith('/addresses') && r.method === 'GET').flush({ data: addresses });
  }

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CheckoutPage],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(CheckoutPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and preselect the default address', () => {
    expect(component).toBeTruthy();

    flushInitialRequests();

    expect(component['selectedAddressId']()).toBe(5);
    expect(component['loading']()).toBe(false);
  });

  it('confirma la compra y muestra el pedido creado', () => {
    flushInitialRequests();

    component['confirmarCompra']();

    const postReq = httpMock.expectOne((r) => r.url.endsWith('/orders') && r.method === 'POST');
    expect(postReq.request.body).toEqual({ address_id: 5 });
    postReq.flush({
      data: {
        id: 100,
        status: 'pendiente',
        total: 90000,
        created_at: '2026-09-29T00:00:00Z',
        shipping: direccion,
        items: [
          {
            id: 1,
            product_name: 'Cadena cubana',
            variant_talle: '50cm',
            sku: 'SKU-1',
            unit_price: 45000,
            quantity: 2,
            subtotal: 90000,
          },
        ],
      },
    });

    // El checkout refresca el carrito tras confirmar.
    httpMock
      .expectOne((r) => r.url.endsWith('/cart') && r.method === 'GET')
      .flush({ data: { id: 1, items: [], items_count: 0, total: 0 } });

    expect(component['order']()?.id).toBe(100);
  });

  it('muestra un error si falla la confirmacion por falta de stock', () => {
    flushInitialRequests();

    component['confirmarCompra']();

    const postReq = httpMock.expectOne((r) => r.url.endsWith('/orders') && r.method === 'POST');
    postReq.flush(
      { errors: { stock: ['No hay stock suficiente de Cadena cubana.'] } },
      { status: 422, statusText: 'Unprocessable Entity' },
    );

    expect(component['error']()).toBe('No hay stock suficiente de Cadena cubana.');
    expect(component['order']()).toBeNull();
  });
});
