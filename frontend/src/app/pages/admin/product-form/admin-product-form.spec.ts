import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute, Router, convertToParamMap, provideRouter } from '@angular/router';
import { AdminProductFormPage } from './admin-product-form';

describe('AdminProductFormPage', () => {
  let httpMock: HttpTestingController;

  function flushCategories(): void {
    httpMock
      .expectOne((r) => r.url.endsWith('/categories'))
      .flush({ data: [{ id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 1 }] });
  }

  describe('modo creación', () => {
    let component: AdminProductFormPage;
    let fixture: ComponentFixture<AdminProductFormPage>;

    beforeEach(async () => {
      await TestBed.configureTestingModule({
        imports: [AdminProductFormPage],
        providers: [
          provideHttpClient(),
          provideHttpClientTesting(),
          provideRouter([]),
          { provide: ActivatedRoute, useValue: { snapshot: { paramMap: convertToParamMap({}) } } },
        ],
      }).compileComponents();

      fixture = TestBed.createComponent(AdminProductFormPage);
      component = fixture.componentInstance;
      httpMock = TestBed.inject(HttpTestingController);
      fixture.detectChanges();
    });

    afterEach(() => {
      httpMock.verify();
    });

    it('should create with one empty variant by default', () => {
      expect(component).toBeTruthy();
      expect(component['variantsArray'].length).toBe(1);

      flushCategories();
    });

    it('no envia el formulario si es invalido', () => {
      flushCategories();

      component['guardar']();

      httpMock.expectNone((r) => r.url.endsWith('/admin/products') && r.method === 'POST');
      expect(component['form'].invalid).toBe(true);
    });

    it('crea el producto y navega a editarlo', () => {
      flushCategories();
      const router = TestBed.inject(Router);
      const navigateSpy = vi.spyOn(router, 'navigate').mockResolvedValue(true);

      component['form'].patchValue({ category_id: 1, name: 'Cadena nueva', material: 'Oro 18k', base_price: 30000 });
      component['variantsArray'].at(0).patchValue({ sku: 'SKU-1', stock: 5 });
      component['guardar']();

      const req = httpMock.expectOne((r) => r.url.endsWith('/admin/products') && r.method === 'POST');
      expect(req.request.body.name).toBe('Cadena nueva');
      req.flush({ data: { id: 9, name: 'Cadena nueva', slug: 'cadena-nueva', active: true, category: null } });

      expect(navigateSpy).toHaveBeenCalledWith(['/admin/productos', 9, 'editar']);
    });

    it('muestra errores de validacion del backend', () => {
      flushCategories();

      component['form'].patchValue({ category_id: 1, name: 'Cadena nueva', material: 'Oro 18k', base_price: 30000 });
      component['variantsArray'].at(0).patchValue({ sku: 'SKU-1', stock: 5 });
      component['guardar']();

      const req = httpMock.expectOne((r) => r.url.endsWith('/admin/products') && r.method === 'POST');
      req.flush({ errors: { variants: ['El SKU "SKU-1" ya está en uso por otra variante.'] } }, { status: 422, statusText: 'Unprocessable Entity' });

      expect(component['errors']()['variants'][0]).toContain('ya está en uso');
    });
  });

  describe('modo edición', () => {
    let component: AdminProductFormPage;
    let fixture: ComponentFixture<AdminProductFormPage>;

    beforeEach(async () => {
      await TestBed.configureTestingModule({
        imports: [AdminProductFormPage],
        providers: [
          provideHttpClient(),
          provideHttpClientTesting(),
          provideRouter([]),
          { provide: ActivatedRoute, useValue: { snapshot: { paramMap: convertToParamMap({ id: '5' }) } } },
        ],
      }).compileComponents();

      fixture = TestBed.createComponent(AdminProductFormPage);
      component = fixture.componentInstance;
      httpMock = TestBed.inject(HttpTestingController);
      fixture.detectChanges();
    });

    afterEach(() => {
      httpMock.verify();
    });

    it('carga el producto existente con sus variantes e imagenes', () => {
      flushCategories();

      const req = httpMock.expectOne((r) => r.url.endsWith('/admin/products/5') && r.method === 'GET');
      req.flush({
        data: {
          id: 5,
          name: 'Cadena cubana',
          slug: 'cadena-cubana',
          description: null,
          material: 'Oro laminado',
          base_price: 45000,
          active: true,
          category: { id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 1 },
          variants: [{ id: 11, talle: '50cm', sku: 'SKU-A', stock: 10, extra_price: 0 }],
          images: [{ id: 1, url: 'https://ejemplo.com/foto.jpg', order: 0 }],
        },
      });

      expect(component['form'].value.name).toBe('Cadena cubana');
      expect(component['variantsArray'].length).toBe(1);
      expect(component['images']()).toHaveLength(1);
      expect(component['loading']()).toBe(false);
    });

    it('sube una imagen nueva', () => {
      flushCategories();
      httpMock.expectOne((r) => r.url.endsWith('/admin/products/5')).flush({
        data: { id: 5, name: 'X', slug: 'x', active: true, category: null, variants: [], images: [] },
      });

      const archivo = new File(['contenido'], 'foto.jpg', { type: 'image/jpeg' });
      component['onImagenSeleccionada']({ target: { files: [archivo] } } as unknown as Event);

      const req = httpMock.expectOne((r) => r.url.endsWith('/admin/products/5/images') && r.method === 'POST');
      req.flush({ data: { id: 2, url: 'https://ejemplo.com/nueva.jpg', order: 0 } });

      expect(component['images']()).toHaveLength(1);
    });

    it('elimina una imagen', () => {
      flushCategories();
      httpMock.expectOne((r) => r.url.endsWith('/admin/products/5')).flush({
        data: {
          id: 5,
          name: 'X',
          slug: 'x',
          active: true,
          category: null,
          variants: [],
          images: [{ id: 7, url: 'https://ejemplo.com/foto.jpg', order: 0 }],
        },
      });

      component['eliminarImagen']({ id: 7, url: 'https://ejemplo.com/foto.jpg', order: 0 });

      httpMock.expectOne((r) => r.url.endsWith('/admin/products/5/images/7') && r.method === 'DELETE').flush(null);

      expect(component['images']()).toHaveLength(0);
    });
  });
});
