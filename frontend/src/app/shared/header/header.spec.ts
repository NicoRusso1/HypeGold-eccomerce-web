import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { Header } from './header';

describe('Header', () => {
  let component: Header;
  let fixture: ComponentFixture<Header>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Header],
      providers: [provideRouter([]), provideHttpClient(), provideHttpClientTesting()],
    }).compileComponents();

    fixture = TestBed.createComponent(Header);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    await fixture.whenStable();
  });

  afterEach(() => {
    httpMock.verify();
    localStorage.clear();
  });

  it('should create and load categories', () => {
    expect(component).toBeTruthy();

    const req = httpMock.expectOne((r) => r.url.endsWith('/categories'));
    req.flush({ data: [{ id: 1, name: 'Cadenas', slug: 'cadenas', description: null, products_count: 4 }] });

    expect(component['categories']()).toHaveLength(1);
  });

  it('muestra la cantidad de items del carrito cuando hay sesion iniciada', async () => {
    const authService = TestBed.inject(AuthService);
    authService.updateStoredUser({ id: 1, name: 'Nico', email: 'nico@hypegold.com', phone: null, role: 'cliente' });

    fixture.detectChanges();
    await fixture.whenStable();

    httpMock.expectOne((r) => r.url.endsWith('/categories')).flush({ data: [] });
    httpMock.expectOne((r) => r.url.endsWith('/cart')).flush({
      data: { id: 1, items: [], items_count: 3, total: 0 },
    });

    expect(component['cartService'].itemsCount()).toBe(3);
  });
});
