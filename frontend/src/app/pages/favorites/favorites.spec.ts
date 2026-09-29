import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { FavoritesPage } from './favorites';

describe('FavoritesPage', () => {
  let component: FavoritesPage;
  let fixture: ComponentFixture<FavoritesPage>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [FavoritesPage],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(FavoritesPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and load favorites', () => {
    expect(component).toBeTruthy();

    const req = httpMock.expectOne((r) => r.url.endsWith('/favorites') && r.method === 'GET');
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
          category: null,
        },
      ],
    });

    expect(component['favoriteService'].favorites()).toHaveLength(1);
    expect(component['loading']()).toBe(false);
  });

  it('muestra un mensaje cuando no hay favoritos', () => {
    httpMock.expectOne((r) => r.url.endsWith('/favorites') && r.method === 'GET').flush({ data: [] });
    fixture.detectChanges();

    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text).toContain('Todavía no guardaste ningún producto');
  });
});
