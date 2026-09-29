import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap, provideRouter } from '@angular/router';
import { of } from 'rxjs';
import { ProductDetailPage } from './product-detail';

describe('ProductDetailPage', () => {
  let component: ProductDetailPage;
  let fixture: ComponentFixture<ProductDetailPage>;
  let httpMock: HttpTestingController;

  const productDetailResponse = {
    data: {
      id: 1,
      name: 'Cadena cubana',
      slug: 'cadena-cubana',
      description: 'Cadena cubana de oro laminado.',
      material: 'Oro laminado',
      base_price: 45000,
      total_stock: 22,
      category: { id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 1 },
      images: [
        { id: 1, url: 'https://ejemplo.com/1.jpg', order: 0 },
        { id: 2, url: 'https://ejemplo.com/2.jpg', order: 1 },
      ],
      variants: [
        { id: 1, talle: '45cm', sku: 'SKU-1', stock: 10, price: 45000 },
        { id: 2, talle: '50cm', sku: 'SKU-2', stock: 0, price: 47000 },
      ],
    },
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ProductDetailPage],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        provideRouter([]),
        {
          provide: ActivatedRoute,
          useValue: { paramMap: of(convertToParamMap({ slug: 'cadena-cubana' })) },
        },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(ProductDetailPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and load the product by slug', () => {
    expect(component).toBeTruthy();

    const req = httpMock.expectOne((r) => r.url.endsWith('/products/cadena-cubana'));
    req.flush(productDetailResponse);

    expect(component['product']()?.name).toBe('Cadena cubana');
    expect(component['loading']()).toBe(false);
  });

  it('selecciona la primera variante disponible por defecto', () => {
    const req = httpMock.expectOne((r) => r.url.endsWith('/products/cadena-cubana'));
    req.flush(productDetailResponse);

    expect(component['selectedVariantId']()).toBe(1);
    expect(component['selectedVariant']()?.stock).toBe(10);
  });

  it('permite cambiar de variante seleccionada', () => {
    const req = httpMock.expectOne((r) => r.url.endsWith('/products/cadena-cubana'));
    req.flush(productDetailResponse);

    component['seleccionarVariante'](2);

    expect(component['selectedVariant']()?.stock).toBe(0);
  });

  it('muestra notFound cuando la peticion falla', () => {
    const req = httpMock.expectOne((r) => r.url.endsWith('/products/cadena-cubana'));
    req.flush({ message: 'No encontrado' }, { status: 404, statusText: 'Not Found' });

    expect(component['notFound']()).toBe(true);
    expect(component['loading']()).toBe(false);
  });
});
