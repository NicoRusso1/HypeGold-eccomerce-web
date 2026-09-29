import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { SearchBox } from './search-box';

describe('SearchBox', () => {
  let component: SearchBox;
  let fixture: ComponentFixture<SearchBox>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    vi.useFakeTimers();

    await TestBed.configureTestingModule({
      imports: [SearchBox],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(SearchBox);
    component = fixture.componentInstance;
    component.categories = [{ id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 3 }];
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
    vi.useRealTimers();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('no busca con menos de 2 caracteres', async () => {
    component['onInput']('c');
    await vi.advanceTimersByTimeAsync(400);

    httpMock.expectNone((r) => r.url.includes('/products/search'));
  });

  it('busca con debounce y muestra sugerencias', async () => {
    component['onInput']('cad');
    await vi.advanceTimersByTimeAsync(300);

    const req = httpMock.expectOne((r) => r.url.includes('/products/search') && r.params.get('q') === 'cad');
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

    expect(component['suggestions']()).toHaveLength(1);
    expect(component['loading']()).toBe(false);
  });

  it('no repite la peticion si el texto no cambio', async () => {
    component['onInput']('cadena');
    await vi.advanceTimersByTimeAsync(300);
    httpMock.expectOne((r) => r.url.includes('/products/search')).flush({ data: [] });

    component['onInput']('cadena');
    await vi.advanceTimersByTimeAsync(300);

    httpMock.expectNone((r) => r.url.includes('/products/search'));
  });

  it('muestra "sin resultados" y categorias sugeridas', async () => {
    component['onInput']('inexistente');
    await vi.advanceTimersByTimeAsync(300);

    httpMock.expectOne((r) => r.url.includes('/products/search')).flush({ data: [] });
    fixture.detectChanges();
    await fixture.whenStable();

    expect(component['searched']()).toBe(true);
    expect(component['suggestions']()).toHaveLength(0);

    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';
    expect(text).toContain('No encontramos productos');
    expect(text).toContain('Cadenas');
  });
});
