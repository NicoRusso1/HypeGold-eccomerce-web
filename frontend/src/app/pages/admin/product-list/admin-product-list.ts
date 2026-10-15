import { CurrencyPipe } from '@angular/common';
import { Component, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { AdminProduct } from '../../../core/models/admin-product.model';
import { PaginatedResponse } from '../../../core/models/product.model';
import { AdminProductService } from '../../../core/services/admin-product.service';
import { AdminNav } from '../../../shared/admin-nav/admin-nav';

@Component({
  imports: [CurrencyPipe, RouterLink, AdminNav],
  selector: 'app-admin-product-list',
  styleUrl: './admin-product-list.css',
  templateUrl: './admin-product-list.html',
})
export class AdminProductListPage {
  private readonly productService = inject(AdminProductService);

  protected readonly products = signal<AdminProduct[]>([]);
  protected readonly loading = signal(true);
  protected readonly meta = signal<PaginatedResponse<AdminProduct>['meta'] | null>(null);

  private page = 1;

  constructor() {
    this.cargar();
  }

  protected irAPagina(pagina: number): void {
    this.page = pagina;
    this.cargar();
  }

  private cargar(): void {
    this.loading.set(true);

    this.productService.getProducts(this.page).subscribe({
      next: (response) => {
        this.products.set(response.data);
        this.meta.set(response.meta);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }
}
