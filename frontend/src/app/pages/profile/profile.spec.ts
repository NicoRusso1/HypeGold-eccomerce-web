import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { Profile } from './profile';

describe('Profile', () => {
  let component: Profile;
  let fixture: ComponentFixture<Profile>;
  let httpMock: HttpTestingController;
  let authService: AuthService;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Profile],
      providers: [provideRouter([]), provideHttpClient(), provideHttpClientTesting()],
    }).compileComponents();

    authService = TestBed.inject(AuthService);
    authService.updateStoredUser({
      id: 1,
      name: 'Nico Russo',
      email: 'nico@hypegold.com',
      phone: null,
      role: 'cliente',
    });

    fixture = TestBed.createComponent(Profile);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
    await fixture.whenStable();
  });

  afterEach(() => {
    httpMock.verify();
    localStorage.clear();
  });

  it('should create and prefill the form with the current user', () => {
    expect(component).toBeTruthy();
    expect(component['profileForm'].value.name).toBe('Nico Russo');
  });

  it('muestra los errores del servidor al actualizar el perfil', () => {
    component['submitProfile']();

    const req = httpMock.expectOne((r) => r.method === 'PUT' && r.url.endsWith('/profile'));
    req.flush(
      { errors: { email: ['El email ya está en uso.'] } },
      { status: 422, statusText: 'Unprocessable Entity' },
    );

    expect(component['profileErrors']()['email'][0]).toContain('email');
  });

  it('muestra el error del servidor al cambiar la contraseña', () => {
    component['passwordForm'].setValue({
      current_password: 'incorrecta',
      password: 'nueva-contraseña',
      password_confirmation: 'nueva-contraseña',
    });

    component['submitPassword']();

    const req = httpMock.expectOne((r) => r.url.endsWith('/profile/password'));
    req.flush(
      { errors: { current_password: ['La contraseña actual no coincide.'] } },
      { status: 422, statusText: 'Unprocessable Entity' },
    );

    expect(component['passwordErrors']()['current_password'][0]).toContain('no coincide');
  });
});
