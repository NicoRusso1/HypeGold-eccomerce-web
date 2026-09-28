import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute, provideRouter } from '@angular/router';
import { ResetPassword } from './reset-password';

function activatedRouteStub(queryParams: Record<string, string>) {
  return { snapshot: { queryParams } };
}

describe('ResetPassword', () => {
  let httpMock: HttpTestingController;

  async function setup(queryParams: Record<string, string>) {
    await TestBed.configureTestingModule({
      imports: [ResetPassword],
      providers: [
        provideRouter([]),
        provideHttpClient(),
        provideHttpClientTesting(),
        { provide: ActivatedRoute, useValue: activatedRouteStub(queryParams) },
      ],
    }).compileComponents();

    const fixture: ComponentFixture<ResetPassword> = TestBed.createComponent(ResetPassword);
    httpMock = TestBed.inject(HttpTestingController);
    await fixture.whenStable();
    return { fixture, component: fixture.componentInstance };
  }

  afterEach(() => {
    httpMock.verify();
  });

  it('marca el enlace como invalido si faltan token o email', async () => {
    const { component } = await setup({});
    expect(component['linkInvalido']()).toBe(true);
  });

  it('precarga token y email desde la URL', async () => {
    const { component } = await setup({ token: 'abc123', email: 'nico@hypegold.com' });
    expect(component['form'].value.token).toBe('abc123');
    expect(component['form'].value.email).toBe('nico@hypegold.com');
  });

  it('muestra exito al restablecer la contraseña', async () => {
    const { component } = await setup({ token: 'abc123', email: 'nico@hypegold.com' });
    component['form'].patchValue({ password: 'contraseña-nueva', password_confirmation: 'contraseña-nueva' });

    component['submit']();

    const req = httpMock.expectOne((r) => r.url.endsWith('/reset-password'));
    req.flush({ message: 'ok' });

    expect(component['success']()).toBe(true);
  });
});
