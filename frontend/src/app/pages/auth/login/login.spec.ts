import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { Login } from './login';

describe('Login', () => {
  let component: Login;
  let fixture: ComponentFixture<Login>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Login],
      providers: [provideRouter([]), provideHttpClient(), provideHttpClientTesting()],
    }).compileComponents();

    fixture = TestBed.createComponent(Login);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    await fixture.whenStable();
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('no envia el formulario si es invalido', () => {
    component['submit']();
    httpMock.expectNone(() => true);
  });

  it('muestra el error del servidor con credenciales invalidas', () => {
    component['form'].setValue({ email: 'nico@hypegold.com', password: 'incorrecta' });

    component['submit']();

    const req = httpMock.expectOne((r) => r.url.endsWith('/login'));
    req.flush(
      { errors: { email: ['Las credenciales no coinciden con nuestros registros.'] } },
      { status: 422, statusText: 'Unprocessable Entity' },
    );

    expect(component['serverErrors']()['email'][0]).toContain('credenciales');
  });
});
