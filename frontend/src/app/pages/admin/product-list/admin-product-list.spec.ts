import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { ConfirmDialogService } from '../../../core/services/confirm-dialog.service';
import { AdminProductListPage } from './admin-product-list';

describe('AdminProductListPage', () => {
  let component: AdminProductListPage;
  let fixture: ComponentFixture<AdminProductListPage>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminProductListPage],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(AdminProductListPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and load products', () => {
    expect(component).toBeTruthy();

    const req = httpMock.expectOne((r) => r.url.endsWith('/admin/products') && r.method === 'GET');
    req.flush({
      data: [
        {
          id: 1,
          name: 'Cadena cubana',
          slug: 'cadena-cubana',
          description: null,
          material: 'Oro laminado',
          base_price: 45000,
          active: true,
          category: { id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 1 },
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
    });

    expect(component['products']()).toHaveLength(1);
    expect(component['loading']()).toBe(false);
  });

  it('muestra un mensaje cuando no hay productos', () => {
    httpMock.expectOne((r) => r.url.endsWith('/admin/products')).flush({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
    });
    fixture.detectChanges();

    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text).toContain('Todavía no hay productos cargados');
  });

  it('elimina un producto tras confirmar', async () => {
    const producto = {
      id: 1,
      name: 'Cadena cubana',
      slug: 'cadena-cubana',
      description: null,
      material: 'Oro laminado',
      base_price: 45000,
      active: true,
      category: null,
    };
    httpMock.expectOne((r) => r.url.endsWith('/admin/products')).flush({
      data: [producto],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
    });

    const confirmDialog = TestBed.inject(ConfirmDialogService);
    const promesa = component['eliminar'](producto);
    confirmDialog.resolve(true);
    await promesa;

    httpMock.expectOne((r) => r.url.endsWith('/admin/products/1') && r.method === 'DELETE').flush(null);
    httpMock.expectOne((r) => r.url.endsWith('/admin/products')).flush({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
    });

    expect(component['products']()).toHaveLength(0);
  });

  it('busca productos por nombre', () => {
    httpMock.expectOne((r) => r.url.endsWith('/admin/products')).flush({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
    });

    component['busqueda'].set('cubana');
    component['buscar']();

    const req = httpMock.expectOne((r) => r.url.includes('/admin/products') && r.params.get('search') === 'cubana');
    req.flush({ data: [], meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 } });

    expect(component['page']).toBe(1);
  });

  it('no elimina el producto si se cancela la confirmacion', async () => {
    const producto = {
      id: 1,
      name: 'Cadena cubana',
      slug: 'cadena-cubana',
      description: null,
      material: 'Oro laminado',
      base_price: 45000,
      active: true,
      category: null,
    };
    httpMock.expectOne((r) => r.url.endsWith('/admin/products')).flush({
      data: [producto],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
    });

    const confirmDialog = TestBed.inject(ConfirmDialogService);
    const promesa = component['eliminar'](producto);
    confirmDialog.resolve(false);
    await promesa;

    httpMock.expectNone((r) => r.method === 'DELETE');
  });
});
