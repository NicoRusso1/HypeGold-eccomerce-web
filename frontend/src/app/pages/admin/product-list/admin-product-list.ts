import { CurrencyPipe } from '@angular/common';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { AdminProduct } from '../../../core/models/admin-product.model';
import { PaginatedResponse } from '../../../core/models/product.model';
import { AdminProductService } from '../../../core/services/admin-product.service';
import { ConfirmDialogService } from '../../../core/services/confirm-dialog.service';
import { AdminNav } from '../../../shared/admin-nav/admin-nav';

@Component({
  imports: [CurrencyPipe, RouterLink, FormsModule, AdminNav],
  selector: 'app-admin-product-list',
  styleUrl: './admin-product-list.css',
  templateUrl: './admin-product-list.html',
})
export class AdminProductListPage {
  private readonly productService = inject(AdminProductService);
  private readonly confirmDialog = inject(ConfirmDialogService);

  protected readonly products = signal<AdminProduct[]>([]);
  protected readonly loading = signal(true);
  protected readonly meta = signal<PaginatedResponse<AdminProduct>['meta'] | null>(null);
  protected readonly deleteError = signal<string | null>(null);
  protected readonly busqueda = signal('');

  private page = 1;

  constructor() {
    this.cargar();
  }

  protected buscar(): void {
    this.page = 1;
    this.cargar();
  }

  protected irAPagina(pagina: number): void {
    this.page = pagina;
    this.cargar();
  }

  protected async eliminar(product: AdminProduct): Promise<void> {
    const confirmado = await this.confirmDialog.confirm({
      title: 'Eliminar producto',
      message: `¿Seguro que querés eliminar "${product.name}"? Se van a borrar también sus variantes, fotos y reseñas. Esta acción no se puede deshacer.`,
    });

    if (!confirmado) {
      return;
    }

    this.deleteError.set(null);

    this.productService.deleteProduct(product.id).subscribe({
      next: () => this.cargar(),
      error: () => this.deleteError.set('No se pudo eliminar el producto.'),
    });
  }

  private cargar(): void {
    this.loading.set(true);

    this.productService.getProducts(this.page, this.busqueda() || undefined).subscribe({
      next: (response) => {
        this.products.set(response.data);
        this.meta.set(response.meta);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }
}
