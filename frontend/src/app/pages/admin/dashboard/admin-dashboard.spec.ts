import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { AdminDashboardPage } from './admin-dashboard';

describe('AdminDashboardPage', () => {
  let component: AdminDashboardPage;
  let fixture: ComponentFixture<AdminDashboardPage>;
  let httpMock: HttpTestingController;

  const resumenResponse = {
    data: {
      total_ventas: 60000,
      cantidad_ventas: 3,
      pedidos_por_estado: { pendiente: 1, pagado: 2, enviado: 0, entregado: 1, cancelado: 0 },
      productos_mas_vendidos: [{ product_name: 'Cadena cubana', cantidad: 5 }],
      ventas_por_dia: [
        { fecha: '2026-10-01', total: 10000 },
        { fecha: '2026-10-02', total: 50000 },
      ],
    },
    meta: { from: '2026-09-02', to: '2026-10-02' },
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminDashboardPage],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    fixture = TestBed.createComponent(AdminDashboardPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and load the sales summary', () => {
    expect(component).toBeTruthy();

    const req = httpMock.expectOne((r) => r.url.endsWith('/admin/dashboard') && r.method === 'GET');
    req.flush(resumenResponse);

    expect(component['resumen']()?.total_ventas).toBe(60000);
    expect(component['loading']()).toBe(false);
  });

  it('calcula la altura relativa de las barras del gráfico', () => {
    httpMock.expectOne((r) => r.url.endsWith('/admin/dashboard')).flush(resumenResponse);

    const barras = component['grafico']();
    expect(barras).toHaveLength(2);
    expect(barras[1].alturaPorcentaje).toBe(100);
    expect(barras[0].alturaPorcentaje).toBe(20);
  });

  it('vuelve a pedir el resumen al aplicar el filtro de fechas', () => {
    httpMock.expectOne((r) => r.url.endsWith('/admin/dashboard')).flush(resumenResponse);

    component['desde'].set('2026-10-01');
    component['hasta'].set('2026-10-02');
    component['aplicarFiltro']();

    const req = httpMock.expectOne(
      (r) => r.url.includes('/admin/dashboard') && r.params.get('from') === '2026-10-01' && r.params.get('to') === '2026-10-02',
    );
    req.flush(resumenResponse);

    expect(component['loading']()).toBe(false);
  });

  it('muestra un error cuando falla la carga', () => {
    httpMock.expectOne((r) => r.url.endsWith('/admin/dashboard')).flush(
      { message: 'error' },
      { status: 500, statusText: 'Server Error' },
    );

    expect(component['error']()).toBe(true);
  });
});
