import { Component, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { AdminOrder } from '../../../core/models/admin-order.model';
import { AdminOrderService } from '../../../core/services/admin-order.service';
import { AdminNav } from '../../../shared/admin-nav/admin-nav';
import { Receipt } from '../../../shared/receipt/receipt';

@Component({
  imports: [RouterLink, Receipt, AdminNav],
  selector: 'app-admin-order-receipt',
  styleUrl: './admin-order-receipt.css',
  templateUrl: './admin-order-receipt.html',
})
export class AdminOrderReceiptPage {
  private readonly route = inject(ActivatedRoute);
  private readonly orderService = inject(AdminOrderService);

  protected readonly order = signal<AdminOrder | null>(null);
  protected readonly loading = signal(true);
  protected readonly notFound = signal(false);

  constructor() {
    const id = Number(this.route.snapshot.paramMap.get('id'));

    if (!id) {
      this.notFound.set(true);
      this.loading.set(false);
      return;
    }

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
