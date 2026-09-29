import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { CartPage } from './cart';

describe('CartPage', () => {
  let component: CartPage;
  let fixture: ComponentFixture<CartPage>;
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

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CartPage],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(CartPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and load the cart', () => {
    expect(component).toBeTruthy();

    const req = httpMock.expectOne((r) => r.url.endsWith('/cart') && r.method === 'GET');
    req.flush(cartConItems);

    expect(component['cartService'].cart()?.items).toHaveLength(1);
    expect(component['loading']()).toBe(false);
  });

  it('permite aumentar la cantidad de un item', () => {
    const getReq = httpMock.expectOne((r) => r.url.endsWith('/cart') && r.method === 'GET');
    getReq.flush(cartConItems);

    component['cambiarCantidad'](10, 3);

    const putReq = httpMock.expectOne((r) => r.url.endsWith('/cart/items/10') && r.method === 'PUT');
    expect(putReq.request.body).toEqual({ quantity: 3 });
    putReq.flush({
      data: { ...cartConItems.data, items: [{ ...cartConItems.data.items[0], quantity: 3, subtotal: 135000 }] },
    });

    expect(component['cartService'].cart()?.items[0].quantity).toBe(3);
  });

  it('muestra un error cuando se supera el stock disponible', () => {
    const getReq = httpMock.expectOne((r) => r.url.endsWith('/cart') && r.method === 'GET');
    getReq.flush(cartConItems);

    component['cambiarCantidad'](10, 99);

    const putReq = httpMock.expectOne((r) => r.url.endsWith('/cart/items/10') && r.method === 'PUT');
    putReq.flush({ errors: { quantity: ['No hay stock suficiente. Disponible: 12.'] } }, { status: 422, statusText: 'Unprocessable Entity' });

    expect(component['errorPorItem']()[10]).toBe('No hay stock suficiente. Disponible: 12.');
  });

  it('permite vaciar el carrito', () => {
    const getReq = httpMock.expectOne((r) => r.url.endsWith('/cart') && r.method === 'GET');
    getReq.flush(cartConItems);

    component['vaciarCarrito']();

    const deleteReq = httpMock.expectOne((r) => r.url.endsWith('/cart') && r.method === 'DELETE');
    deleteReq.flush({ data: { id: 1, items: [], items_count: 0, total: 0 } });

    expect(component['cartService'].cart()?.items).toHaveLength(0);
  });
});
