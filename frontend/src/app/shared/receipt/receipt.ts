import { CurrencyPipe, DatePipe } from '@angular/common';
import { Component, input } from '@angular/core';
import { Order } from '../../core/models/order.model';
import { ordenStatusLabel } from '../../core/models/order-status';

@Component({
  imports: [CurrencyPipe, DatePipe],
  selector: 'app-receipt',
  styleUrl: './receipt.css',
  templateUrl: './receipt.html',
})
export class Receipt {
  readonly order = input.required<Order>();
  /** Solo se pasa en la vista del panel admin, que ve pedidos de cualquier cliente. */
  readonly customerName = input<string | null>(null);

  protected readonly ordenStatusLabel = ordenStatusLabel;

  protected imprimir(): void {
    window.print();
  }
}
