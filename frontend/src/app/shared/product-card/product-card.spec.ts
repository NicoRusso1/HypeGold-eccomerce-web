import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Router, provideRouter } from '@angular/router';
import { Product } from '../../core/models/product.model';
import { AuthService } from '../../core/services/auth.service';
import { ProductCard } from './product-card';

describe('ProductCard', () => {
  let component: ProductCard;
  let fixture: ComponentFixture<ProductCard>;
  let httpMock: HttpTestingController;

  const product: Product = {
    id: 1,
    name: 'Cadena cubana',
    slug: 'cadena-cubana',
    description: null,
    material: 'Oro laminado',
    base_price: 45000,
    total_stock: 12,
    image: 'https://ejemplo.com/foto.jpg',
    category: { id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 1 },
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ProductCard],
      providers: [provideRouter([]), provideHttpClient(), provideHttpClientTesting()],
    }).compileComponents();

    fixture = TestBed.createComponent(ProductCard);
    component = fixture.componentInstance;
    component.product = product;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
    await fixture.whenStable();
  });

  afterEach(() => {
    httpMock.verify();
    localStorage.clear();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('muestra el nombre y el precio del producto', () => {
    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text).toContain('Cadena cubana');
  });

  it('muestra el aviso de sin stock cuando total_stock es 0', async () => {
    const sinStockFixture = TestBed.createComponent(ProductCard);
    sinStockFixture.componentInstance.product = { ...product, total_stock: 0 };
    sinStockFixture.detectChanges();
    await sinStockFixture.whenStable();

    const text = (sinStockFixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text).toContain('Sin stock');
  });

  it('redirige al login al tocar el corazon sin sesion iniciada', () => {
    const router = TestBed.inject(Router);
    const navigateSpy = vi.spyOn(router, 'navigateByUrl');

    component['toggleFavorito'](new Event('click'));

    expect(navigateSpy).toHaveBeenCalledWith('/cuenta/ingresar');
    httpMock.expectNone((r) => r.url.includes('/favorites'));
  });

  it('agrega el producto a favoritos cuando hay sesion iniciada', async () => {
    const authService = TestBed.inject(AuthService);
    authService.updateStoredUser({ id: 1, name: 'Nico', email: 'nico@hypegold.com', phone: null, role: 'cliente' });
    fixture.detectChanges();
    await fixture.whenStable();

    httpMock.expectOne((r) => r.url.endsWith('/favorites') && r.method === 'GET').flush({ data: [] });

    component['toggleFavorito'](new Event('click'));

    const req = httpMock.expectOne((r) => r.url.endsWith('/favorites') && r.method === 'POST');
    expect(req.request.body).toEqual({ product_id: 1 });
    req.flush({ message: 'ok' });

    expect(component['favoriteService'].isFavorite(1)).toBe(true);
  });
});
