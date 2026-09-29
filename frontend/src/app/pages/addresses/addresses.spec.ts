import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { AddressesPage } from './addresses';

describe('AddressesPage', () => {
  let component: AddressesPage;
  let fixture: ComponentFixture<AddressesPage>;
  let httpMock: HttpTestingController;

  const direccion = {
    id: 1,
    label: 'Casa',
    street: 'Av. Siempre Viva 742',
    city: 'Concordia',
    province: 'Entre Ríos',
    postal_code: 'E3200',
    phone: '345 4040162',
    is_default: true,
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AddressesPage],
      providers: [provideHttpClient(), provideHttpClientTesting()],
    }).compileComponents();

    fixture = TestBed.createComponent(AddressesPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and load addresses', () => {
    expect(component).toBeTruthy();

    const req = httpMock.expectOne((r) => r.url.endsWith('/addresses') && r.method === 'GET');
    req.flush({ data: [direccion] });

    expect(component['addresses']()).toHaveLength(1);
    expect(component['loading']()).toBe(false);
  });

  it('muestra errores de validacion al guardar una direccion incompleta', () => {
    httpMock.expectOne((r) => r.url.endsWith('/addresses') && r.method === 'GET').flush({ data: [] });

    component['nuevaDireccion']();
    component['guardar']();

    expect(component['form'].invalid).toBe(true);
    httpMock.expectNone((r) => r.method === 'POST');
  });

  it('crea una direccion nueva', () => {
    httpMock.expectOne((r) => r.url.endsWith('/addresses') && r.method === 'GET').flush({ data: [] });

    component['nuevaDireccion']();
    component['form'].setValue({
      label: 'Casa',
      street: 'Av. Siempre Viva 742',
      city: 'Concordia',
      province: 'Entre Ríos',
      postal_code: 'E3200',
      phone: '345 4040162',
      is_default: false,
    });
    component['guardar']();

    const postReq = httpMock.expectOne((r) => r.url.endsWith('/addresses') && r.method === 'POST');
    postReq.flush({ data: direccion });

    // Tras guardar, se recarga el listado.
    httpMock.expectOne((r) => r.url.endsWith('/addresses') && r.method === 'GET').flush({ data: [direccion] });

    expect(component['showForm']()).toBe(false);
    expect(component['addresses']()).toHaveLength(1);
  });

  it('elimina una direccion', () => {
    httpMock.expectOne((r) => r.url.endsWith('/addresses') && r.method === 'GET').flush({ data: [direccion] });

    component['eliminar'](direccion);

    httpMock.expectOne((r) => r.url.endsWith('/addresses/1') && r.method === 'DELETE').flush(null);
    httpMock.expectOne((r) => r.url.endsWith('/addresses') && r.method === 'GET').flush({ data: [] });

    expect(component['addresses']()).toHaveLength(0);
  });
});
