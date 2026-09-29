import { CurrencyPipe, DatePipe } from '@angular/common';
import { Component, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { Order } from '../../core/models/order.model';
import { PaginatedResponse } from '../../core/models/product.model';
import { OrderService } from '../../core/services/order.service';
import { ordenStatusLabel } from '../../core/models/order-status';

@Component({
  imports: [CurrencyPipe, DatePipe, RouterLink],
  selector: 'app-orders',
  styleUrl: './orders.css',
  templateUrl: './orders.html',
})
export class OrdersPage {
  private readonly orderService = inject(OrderService);

  protected readonly orders = signal<Order[]>([]);
  protected readonly loading = signal(true);
  protected readonly meta = signal<PaginatedResponse<Order>['meta'] | null>(null);

  protected readonly ordenStatusLabel = ordenStatusLabel;

  private page = 1;

  constructor() {
    this.cargarPedidos();
  }

  protected irAPagina(pagina: number): void {
    this.page = pagina;
    this.cargarPedidos();
  }

  private cargarPedidos(): void {
    this.loading.set(true);

    this.orderService.getOrders(this.page).subscribe({
      next: (response) => {
        this.orders.set(response.data);
        this.meta.set(response.meta);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }
}
