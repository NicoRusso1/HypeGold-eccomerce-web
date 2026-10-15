import { CurrencyPipe } from '@angular/common';
import { Component, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { Order } from '../../core/models/order.model';
import { OrderService } from '../../core/services/order.service';

type Resultado = 'success' | 'pending' | 'failure';

const CONTENIDO: Record<Resultado, { icono: string; titulo: string; mensaje: string; fondo: string }> = {
  success: {
    icono: '✓',
    titulo: '¡Pago aprobado!',
    mensaje: 'Recibimos tu pago. Te vamos a contactar por WhatsApp para coordinar el envío.',
    fondo: 'bg-green-100',
  },
  pending: {
    icono: '⏳',
    titulo: 'Tu pago está pendiente',
    mensaje: 'Mercado Pago todavía está procesando el pago. Te avisamos apenas se acredite.',
    fondo: 'bg-amber-100',
  },
  failure: {
    icono: '✕',
    titulo: 'No pudimos procesar el pago',
    mensaje: 'El pago no se completó. Podés volver a intentarlo desde tu pedido.',
    fondo: 'bg-red-100',
  },
};

@Component({
  imports: [RouterLink, CurrencyPipe],
  selector: 'app-payment-result',
  styleUrl: './payment-result.css',
  templateUrl: './payment-result.html',
})
export class PaymentResultPage {
  private readonly route = inject(ActivatedRoute);
  private readonly orderService = inject(OrderService);

  protected readonly resultado = (this.route.snapshot.data['kind'] as Resultado) ?? 'pending';
  protected readonly contenido = CONTENIDO[this.resultado];

  protected readonly orderId = signal<number | null>(null);
  protected readonly order = signal<Order | null>(null);
  protected readonly loading = signal(true);

  constructor() {
    const idParam = this.route.snapshot.queryParamMap.get('order');
    const id = idParam ? Number(idParam) : null;
    this.orderId.set(id);

    if (!id) {
      this.loading.set(false);
      return;
    }

    this.orderService.getOrder(id).subscribe({
      next: (order) => {
        this.order.set(order);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }
}
