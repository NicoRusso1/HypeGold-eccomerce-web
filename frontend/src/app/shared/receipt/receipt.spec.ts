import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Receipt } from './receipt';
import { Order } from '../../core/models/order.model';

describe('Receipt', () => {
  let fixture: ComponentFixture<Receipt>;

  const pedido: Order = {
    id: 7,
    status: 'pagado',
    total: 45000,
    discount: 5000,
    coupon_code: 'BIENVENIDO',
    created_at: '2026-10-01T00:00:00Z',
    shipping: { label: 'Casa', street: 'Urquiza 456', city: 'Concordia', province: 'Entre Ríos', postal_code: 'E3200', phone: '345' },
    items: [
      { id: 1, product_name: 'Cadena cubana', variant_talle: '50cm', sku: 'SKU-1', unit_price: 50000, quantity: 1, subtotal: 50000 },
    ],
  };

  async function crear(customerName: string | null = null): Promise<ComponentFixture<Receipt>> {
    await TestBed.configureTestingModule({ imports: [Receipt] }).compileComponents();
    const f = TestBed.createComponent(Receipt);
    f.componentRef.setInput('order', pedido);
    f.componentRef.setInput('customerName', customerName);
    f.detectChanges();
    return f;
  }

  it('should create', async () => {
    fixture = await crear();
    expect(fixture.componentInstance).toBeTruthy();
  });

  it('muestra el numero de pedido, el total y el estado', async () => {
    fixture = await crear();
    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';

    expect(text).toContain('Pedido #7');
    expect(text).toContain('Pagado');
    expect(text).toContain('Cadena cubana');
    expect(text).toContain('no tiene validez como factura fiscal');
  });

  it('muestra el nombre del cliente cuando se pasa (vista admin)', async () => {
    fixture = await crear('Juana Pérez');
    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';

    expect(text).toContain('Juana Pérez');
  });

  it('no muestra el bloque de cliente cuando no se pasa (vista del propio cliente)', async () => {
    fixture = await crear(null);
    const text = (fixture.nativeElement as HTMLElement).textContent ?? '';

    expect(text).not.toContain('Cliente');
  });

  it('llama a window.print al imprimir', async () => {
    fixture = await crear();
    const printSpy = vi.spyOn(window, 'print').mockImplementation(() => {});

    fixture.componentInstance['imprimir']();

    expect(printSpy).toHaveBeenCalled();
  });
});
