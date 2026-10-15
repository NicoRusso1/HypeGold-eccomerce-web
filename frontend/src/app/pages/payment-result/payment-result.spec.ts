import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap, provideRouter } from '@angular/router';
import { of } from 'rxjs';
import { PaymentResultPage } from './payment-result';

describe('PaymentResultPage', () => {
  let fixture: ComponentFixture<PaymentResultPage>;
  let component: PaymentResultPage;
  let httpMock: HttpTestingController;

  async function crear(kind: 'success' | 'pending' | 'failure', orderId: string | null): Promise<void> {
    await TestBed.configureTestingModule({
      imports: [PaymentResultPage],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        provideRouter([]),
        {
          provide: ActivatedRoute,
          useValue: {
            snapshot: {
              data: { kind },
              queryParamMap: convertToParamMap(orderId ? { order: orderId } : {}),
            },
            paramMap: of(convertToParamMap({})),
          },
        },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(PaymentResultPage);
    component = fixture.componentInstance;
    httpMock = TestBed.inject(HttpTestingController);
    fixture.detectChanges();
  }

  afterEach(() => {
    httpMock.verify();
  });

  it('should create and show the success message', async () => {
    await crear('success', '55');

    expect(component).toBeTruthy();
    expect(component['resultado']).toBe('success');

    httpMock.expectOne((r) => r.url.endsWith('/orders/55')).flush({
      data: {
        id: 55,
        status: 'pagado',
        total: 45000,
        discount: 0,
        coupon_code: null,
        created_at: '2026-09-29T00:00:00Z',
        shipping: {},
      },
    });

    expect(component['order']()?.id).toBe(55);
  });

  it('muestra el mensaje de pendiente', async () => {
    await crear('pending', null);

    expect(component['contenido'].titulo).toContain('pendiente');
    expect(component['loading']()).toBe(false);
  });

  it('muestra el mensaje de error', async () => {
    await crear('failure', null);

    expect(component['contenido'].titulo).toContain('No pudimos procesar');
  });
});
