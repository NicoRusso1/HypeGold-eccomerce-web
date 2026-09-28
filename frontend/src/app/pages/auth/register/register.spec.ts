import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { provideHttpClient } from '@angular/common/http';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { Register } from './register';

describe('Register', () => {
  let component: Register;
  let fixture: ComponentFixture<Register>;
  let httpMock: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Register],
      providers: [provideRouter([]), provideHttpClient(), provideHttpClientTesting()],
    }).compileComponents();

    fixture = TestBed.createComponent(Register);
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

  it('muestra los errores del servidor cuando el email ya existe', () => {
    component['form'].setValue({
      name: 'Nicolás Russo',
      email: 'nico@hypegold.com',
      password: 'contraseña-segura',
      password_confirmation: 'contraseña-segura',
    });

    component['submit']();

    const req = httpMock.expectOne((r) => r.url.endsWith('/register'));
    req.flush(
      { errors: { email: ['Ya existe una cuenta registrada con ese email.'] } },
      { status: 422, statusText: 'Unprocessable Entity' },
    );

    expect(component['serverErrors']()['email'][0]).toContain('Ya existe');
  });
});
