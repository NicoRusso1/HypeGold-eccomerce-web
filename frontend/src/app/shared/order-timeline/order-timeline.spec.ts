import { ComponentFixture, TestBed } from '@angular/core/testing';
import { OrderTimeline } from './order-timeline';

describe('OrderTimeline', () => {
  let fixture: ComponentFixture<OrderTimeline>;

  async function crear(status: string): Promise<ComponentFixture<OrderTimeline>> {
    await TestBed.configureTestingModule({ imports: [OrderTimeline] }).compileComponents();
    const f = TestBed.createComponent(OrderTimeline);
    f.componentInstance.status = status;
    f.detectChanges();
    return f;
  }

  it('should create', async () => {
    fixture = await crear('pendiente');
    expect(fixture.componentInstance).toBeTruthy();
  });

  it('muestra los 4 pasos para un pedido pendiente', async () => {
    fixture = await crear('pendiente');
    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';

    expect(text).toContain('Pendiente');
    expect(text).toContain('Pagado');
    expect(text).toContain('Enviado');
    expect(text).toContain('Entregado');
  });

  it('muestra un aviso especial cuando el pedido esta cancelado', async () => {
    fixture = await crear('cancelado');
    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';

    expect(text).toContain('cancelado');
    expect(fixture.componentInstance['isCancelado']()).toBe(true);
  });

  it('calcula el indice correcto segun el estado', async () => {
    fixture = await crear('enviado');
    expect(fixture.componentInstance['currentIndex']()).toBe(2);
  });
});
