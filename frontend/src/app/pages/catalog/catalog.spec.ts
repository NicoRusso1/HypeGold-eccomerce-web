import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap, provideRouter } from '@angular/router';
import { of } from 'rxjs';
import { Catalog } from './catalog';

describe('Catalog', () => {
  let component: Catalog;
  let fixture: ComponentFixture<Catalog>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Catalog],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        provideRouter([]),
        {
          provide: ActivatedRoute,
          useValue: {
            paramMap: of(convertToParamMap({ categoria: 'cadenas' })),
            snapshot: { queryParamMap: convertToParamMap({}) },
          },
        },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(Catalog);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and load products for the route category', () => {
    expect(component).toBeTruthy();

    const req = httpMock.expectOne((r) => r.url.endsWith('/products'));
    expect(req.request.params.get('category')).toBe('cadenas');
    req.flush({
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
          category: { id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 1 },
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 12, total: 1 },
    });

    expect(component['products']()).toHaveLength(1);
    expect(component['loading']()).toBe(false);
  });

  it('muestra un mensaje de error si la peticion falla', () => {
    const req = httpMock.expectOne((r) => r.url.endsWith('/products'));
    req.error(new ProgressEvent('error'));

    expect(component['error']()).toBe(true);
    expect(component['loading']()).toBe(false);
  });
});
