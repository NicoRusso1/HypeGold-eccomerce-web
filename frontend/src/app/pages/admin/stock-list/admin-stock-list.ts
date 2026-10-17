import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { AdminStockItem, AdminStockMeta } from '../../../core/models/admin-stock.model';
import { AdminStockService } from '../../../core/services/admin-stock.service';
import { AdminNav } from '../../../shared/admin-nav/admin-nav';

@Component({
  imports: [FormsModule, AdminNav],
  selector: 'app-admin-stock-list',
  styleUrl: './admin-stock-list.css',
  templateUrl: './admin-stock-list.html',
})
export class AdminStockListPage {
  private readonly stockService = inject(AdminStockService);

  protected readonly items = signal<AdminStockItem[]>([]);
  protected readonly meta = signal<AdminStockMeta | null>(null);
  protected readonly loading = signal(true);
  protected readonly soloStockBajo = signal(false);
  protected readonly savingId = signal<number | null>(null);
  protected readonly busqueda = signal('');
  protected readonly editValues = new Map<number, number>();

  protected get itemsVisibles(): AdminStockItem[] {
    const termino = this.busqueda().trim().toLowerCase();

    return this.items().filter((item) => {
      if (this.soloStockBajo() && !item.low_stock) {
        return false;
      }

      if (!termino) {
        return true;
      }

      return item.product.name.toLowerCase().includes(termino) || item.sku.toLowerCase().includes(termino);
    });
  }

  constructor() {
    this.cargar();
  }

  private cargar(): void {
    this.loading.set(true);

    this.stockService.getStock().subscribe({
      next: ({ items, meta }) => {
        this.items.set(items);
        this.meta.set(meta);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  protected valorEditado(item: AdminStockItem): number {
    return this.editValues.get(item.id) ?? item.stock;
  }

  protected onCambioValor(item: AdminStockItem, valor: number): void {
    this.editValues.set(item.id, valor);
  }

  protected huboCambio(item: AdminStockItem): boolean {
    return this.editValues.has(item.id) && this.editValues.get(item.id) !== item.stock;
  }

  protected guardar(item: AdminStockItem): void {
    const nuevoStock = this.editValues.get(item.id);

    if (nuevoStock === undefined || nuevoStock < 0) {
      return;
    }

    this.savingId.set(item.id);

    this.stockService.updateStock(item.id, nuevoStock).subscribe({
      next: (actualizado) => {
        this.items.update((lista) => lista.map((i) => (i.id === actualizado.id ? actualizado : i)));
        this.editValues.delete(item.id);
        this.savingId.set(null);
        this.recalcularLowStockCount();
      },
      error: () => this.savingId.set(null),
    });
  }

  private recalcularLowStockCount(): void {
    const meta = this.meta();

    if (!meta) {
      return;
    }

    this.meta.set({ ...meta, low_stock_count: this.items().filter((i) => i.low_stock).length });
  }
}
