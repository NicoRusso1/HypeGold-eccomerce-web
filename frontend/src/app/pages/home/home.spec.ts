import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { Home } from './home';

describe('Home', () => {
  let component: Home;
  let fixture: ComponentFixture<Home>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Home],
      providers: [provideRouter([]), provideHttpClient(), provideHttpClientTesting()],
    }).compileComponents();

    fixture = TestBed.createComponent(Home);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    await fixture.whenStable();
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create', () => {
    expect(component).toBeTruthy();

    httpMock.expectOne((r) => r.url.endsWith('/categories')).flush({ data: [] });
    httpMock.expectOne((r) => r.url.endsWith('/products')).flush({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 3, total: 0 },
    });
  });

  it('carga las categorias y los destacados desde la API', () => {
    const catReq = httpMock.expectOne((r) => r.url.endsWith('/categories'));
    catReq.flush({ data: [{ id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 4 }] });

    const prodReq = httpMock.expectOne((r) => r.url.endsWith('/products'));
    expect(prodReq.request.params.get('sort')).toBe('recientes');
    prodReq.flush({
      data: [
        {
          id: 1,
          name: 'Cadena cubana',
          slug: 'cadena-cubana',
          description: null,
          material: 'Oro laminado',
          base_price: 45000,
          total_stock: 10,
          image: null,
          category: { id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 4 },
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 3, total: 1 },
    });

    expect(component['categorias']()).toHaveLength(1);
    expect(component['destacados']()).toHaveLength(1);
  });
});
