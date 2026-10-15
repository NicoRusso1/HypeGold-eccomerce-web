import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { AdminStockListPage } from './admin-stock-list';

describe('AdminStockListPage', () => {
  let component: AdminStockListPage;
  let fixture: ComponentFixture<AdminStockListPage>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminStockListPage],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(AdminStockListPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  function flushStock(): void {
    httpMock.expectOne((r) => r.url.endsWith('/admin/stock') && r.method === 'GET').flush({
      data: [
        {
          id: 1,
          talle: 'Único',
          sku: 'SKU-1',
          stock: 2,
          low_stock: true,
          product: { id: 1, name: 'Cadena cubana', category: 'Cadenas' },
        },
        {
          id: 2,
          talle: null,
          sku: 'SKU-2',
          stock: 15,
          low_stock: false,
          product: { id: 2, name: 'Anillo solitario', category: 'Anillos' },
        },
      ],
      meta: { low_stock_threshold: 5, low_stock_count: 1 },
    });
  }

  it('should create and load stock', () => {
    expect(component).toBeTruthy();

    flushStock();

    expect(component['items']()).toHaveLength(2);
    expect(component['loading']()).toBe(false);
    expect(component['meta']()?.low_stock_count).toBe(1);
  });

  it('filtra solo los items con stock bajo', () => {
    flushStock();

    expect(component['itemsVisibles']).toHaveLength(2);

    component['soloStockBajo'].set(true);

    expect(component['itemsVisibles']).toHaveLength(1);
    expect(component['itemsVisibles'][0].sku).toBe('SKU-1');
  });

  it('guarda el nuevo stock de una variante', () => {
    flushStock();

    const item = component['items']()[0];
    component['onCambioValor'](item, 20);
    expect(component['huboCambio'](item)).toBe(true);

    component['guardar'](item);

    const req = httpMock.expectOne((r) => r.url.endsWith('/admin/stock/1') && r.method === 'PUT');
    expect(req.request.body).toEqual({ stock: 20 });
    req.flush({
      data: { id: 1, talle: 'Único', sku: 'SKU-1', stock: 20, low_stock: false, product: { id: 1, name: 'Cadena cubana', category: 'Cadenas' } },
    });

    expect(component['items']()[0].stock).toBe(20);
    expect(component['huboCambio'](component['items']()[0])).toBe(false);
  });
});
