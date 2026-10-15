import { HttpErrorResponse } from '@angular/common/http';
import { CurrencyPipe, DatePipe } from '@angular/common';
import { Component, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { AdminOrder } from '../../../core/models/admin-order.model';
import { ORDER_STATUS_TRANSITIONS, ordenStatusLabel } from '../../../core/models/order-status';
import { AdminOrderService } from '../../../core/services/admin-order.service';
import { AdminNav } from '../../../shared/admin-nav/admin-nav';
import { OrderTimeline } from '../../../shared/order-timeline/order-timeline';

@Component({
  imports: [CurrencyPipe, DatePipe, RouterLink, OrderTimeline, AdminNav],
  selector: 'app-admin-order-detail',
  styleUrl: './admin-order-detail.css',
  templateUrl: './admin-order-detail.html',
})
export class AdminOrderDetailPage {
  private readonly route = inject(ActivatedRoute);
  private readonly orderService = inject(AdminOrderService);

  protected readonly order = signal<AdminOrder | null>(null);
  protected readonly loading = signal(true);
  protected readonly notFound = signal(false);
  protected readonly changingStatus = signal(false);
  protected readonly statusError = signal<string | null>(null);

  protected readonly ordenStatusLabel = ordenStatusLabel;

  constructor() {
    const id = Number(this.route.snapshot.paramMap.get('id'));

    if (!id) {
      this.notFound.set(true);
      this.loading.set(false);
      return;
    }

    this.cargarPedido(id);
  }

  protected get transicionesDisponibles(): string[] {
    const pedido = this.order();
    return pedido ? (ORDER_STATUS_TRANSITIONS[pedido.status] ?? []) : [];
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

  protected cambiarEstado(nuevoEstado: string): void {
    const pedido = this.order();

    if (!pedido) {
      return;
    }

    this.changingStatus.set(true);
    this.statusError.set(null);

    this.orderService.updateStatus(pedido.id, nuevoEstado).subscribe({
      next: (actualizado) => {
        this.order.set(actualizado);
        this.changingStatus.set(false);
      },
      error: (error: HttpErrorResponse) => {
        this.statusError.set(error.error?.errors?.status?.[0] ?? 'No se pudo cambiar el estado.');
        this.changingStatus.set(false);
      },
    });
  }
}
