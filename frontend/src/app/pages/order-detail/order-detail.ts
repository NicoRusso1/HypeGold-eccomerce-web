import { CurrencyPipe, DatePipe } from '@angular/common';
import { Component, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { Order } from '../../core/models/order.model';
import { ordenStatusLabel } from '../../core/models/order-status';
import { OrderService } from '../../core/services/order.service';
import { OrderTimeline } from '../../shared/order-timeline/order-timeline';

@Component({
  imports: [CurrencyPipe, DatePipe, RouterLink, OrderTimeline],
  selector: 'app-order-detail',
  styleUrl: './order-detail.css',
  templateUrl: './order-detail.html',
})
export class OrderDetailPage {
  private readonly route = inject(ActivatedRoute);
  private readonly orderService = inject(OrderService);

  protected readonly order = signal<Order | null>(null);
  protected readonly loading = signal(true);
  protected readonly notFound = signal(false);

  protected readonly ordenStatusLabel = ordenStatusLabel;

  constructor() {
    this.route.paramMap.subscribe((params) => {
      const id = Number(params.get('id'));

      if (!id) {
        this.notFound.set(true);
        this.loading.set(false);
        return;
      }

      this.cargarPedido(id);
    });
  }

  private cargarPedido(id: number): void {
    this.loading.set(true);
    this.notFound.set(false);

    this.orderService.getOrder(id).subscribe({
      next: (order) => {
        this.order.set(order);
        this.loading.set(false);
      },
      error: () => {
        this.notFound.set(true);
        this.loading.set(false);
      },
    });
  }
}
