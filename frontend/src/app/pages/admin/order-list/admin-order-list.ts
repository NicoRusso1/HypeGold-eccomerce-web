import { CurrencyPipe, DatePipe } from '@angular/common';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { ordenStatusLabel } from '../../../core/models/order-status';
import { AdminOrder } from '../../../core/models/admin-order.model';
import { PaginatedResponse } from '../../../core/models/product.model';
import { AdminOrderService } from '../../../core/services/admin-order.service';
import { AdminNav } from '../../../shared/admin-nav/admin-nav';

@Component({
  imports: [CurrencyPipe, DatePipe, RouterLink, FormsModule, AdminNav],
  selector: 'app-admin-order-list',
  styleUrl: './admin-order-list.css',
  templateUrl: './admin-order-list.html',
})
export class AdminOrderListPage {
  private readonly orderService = inject(AdminOrderService);

  protected readonly ordenStatusLabel = ordenStatusLabel;
  protected readonly estados = ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'];

  protected readonly orders = signal<AdminOrder[]>([]);
  protected readonly loading = signal(true);
  protected readonly meta = signal<PaginatedResponse<AdminOrder>['meta'] | null>(null);
  protected readonly estadoFiltro = signal<string>('');

  private page = 1;

  constructor() {
    this.cargar();
  }

  protected onCambioFiltro(estado: string): void {
    this.estadoFiltro.set(estado);
    this.page = 1;
    this.cargar();
  }

  protected irAPagina(pagina: number): void {
    this.page = pagina;
    this.cargar();
  }

  private cargar(): void {
    this.loading.set(true);

    this.orderService.getOrders(this.page, this.estadoFiltro() || undefined).subscribe({
      next: (response) => {
        this.orders.set(response.data);
        this.meta.set(response.meta);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }
}
