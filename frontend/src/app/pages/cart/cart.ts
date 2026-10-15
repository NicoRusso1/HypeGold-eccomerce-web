import { CurrencyPipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { CartService } from '../../core/services/cart.service';
import { ConfirmDialogService } from '../../core/services/confirm-dialog.service';

@Component({
  imports: [CurrencyPipe, RouterLink],
  selector: 'app-cart',
  styleUrl: './cart.css',
  templateUrl: './cart.html',
})
export class CartPage {
  protected readonly cartService = inject(CartService);
  private readonly confirmDialog = inject(ConfirmDialogService);

  protected readonly loading = signal(true);
  protected readonly error = signal(false);
  protected readonly updatingItemId = signal<number | null>(null);
  protected readonly errorPorItem = signal<Record<number, string>>({});

  constructor() {
    this.cargarCarrito();
  }

  protected reintentar(): void {
    this.error.set(false);
    this.loading.set(true);
    this.cargarCarrito();
  }

  private cargarCarrito(): void {
    this.cartService.loadCart().subscribe({
      next: () => this.loading.set(false),
      error: () => {
        this.loading.set(false);
        this.error.set(true);
      },
    });
  }

  protected cambiarCantidad(itemId: number, cantidad: number): void {
    if (cantidad < 1) {
      return;
    }

    this.setErrorItem(itemId, null);
    this.updatingItemId.set(itemId);

    this.cartService.updateQuantity(itemId, cantidad).subscribe({
      next: () => this.updatingItemId.set(null),
      error: (error: HttpErrorResponse) => {
        this.updatingItemId.set(null);
        this.setErrorItem(itemId, this.mensajeError(error));
      },
    });
  }

  protected eliminarItem(itemId: number): void {
    this.updatingItemId.set(itemId);

    this.cartService.removeItem(itemId).subscribe({
      next: () => this.updatingItemId.set(null),
      error: () => this.updatingItemId.set(null),
    });
  }

  protected async vaciarCarrito(): Promise<void> {
    const confirmado = await this.confirmDialog.confirm({
      title: 'Vaciar carrito',
      message: '¿Seguro que querés vaciar tu carrito? Se van a quitar todos los productos.',
      confirmText: 'Vaciar',
    });

    if (!confirmado) {
      return;
    }

    this.cartService.clear().subscribe();
  }

  private setErrorItem(itemId: number, mensaje: string | null): void {
    this.errorPorItem.update((errores) => {
      const copia = { ...errores };

      if (mensaje) {
        copia[itemId] = mensaje;
      } else {
        delete copia[itemId];
      }

      return copia;
    });
  }

  private mensajeError(error: HttpErrorResponse): string {
    return error.status === 422
      ? (error.error?.errors?.quantity?.[0] ?? 'No pudimos actualizar la cantidad.')
      : 'No pudimos actualizar la cantidad. Probá de nuevo en un momento.';
  }
}
