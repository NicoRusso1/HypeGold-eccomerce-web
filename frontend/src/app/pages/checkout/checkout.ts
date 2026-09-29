import { CurrencyPipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { Address } from '../../core/models/address.model';
import { CouponValidation, Order } from '../../core/models/order.model';
import { AddressService } from '../../core/services/address.service';
import { CartService } from '../../core/services/cart.service';
import { CouponService } from '../../core/services/coupon.service';
import { OrderService } from '../../core/services/order.service';

@Component({
  imports: [CurrencyPipe, RouterLink],
  selector: 'app-checkout',
  styleUrl: './checkout.css',
  templateUrl: './checkout.html',
})
export class CheckoutPage {
  protected readonly cartService = inject(CartService);
  private readonly addressService = inject(AddressService);
  private readonly orderService = inject(OrderService);
  private readonly couponService = inject(CouponService);

  protected readonly addresses = signal<Address[]>([]);
  protected readonly loading = signal(true);
  protected readonly selectedAddressId = signal<number | null>(null);
  protected readonly confirmando = signal(false);
  protected readonly error = signal<string | null>(null);
  protected readonly order = signal<Order | null>(null);

  protected readonly cuponInput = signal('');
  protected readonly cuponAplicado = signal<CouponValidation | null>(null);
  protected readonly validandoCupon = signal(false);
  protected readonly errorCupon = signal<string | null>(null);

  constructor() {
    this.cartService.loadCart().subscribe();

    this.addressService.getAddresses().subscribe({
      next: (addresses) => {
        this.addresses.set(addresses);
        const predeterminada = addresses.find((a) => a.is_default) ?? addresses[0];
        this.selectedAddressId.set(predeterminada?.id ?? null);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  protected seleccionarDireccion(id: number): void {
    this.selectedAddressId.set(id);
  }

  protected aplicarCupon(): void {
    const code = this.cuponInput().trim();

    if (!code) {
      return;
    }

    this.validandoCupon.set(true);
    this.errorCupon.set(null);

    this.couponService.validate(code).subscribe({
      next: (validacion) => {
        this.validandoCupon.set(false);
        this.cuponAplicado.set(validacion);
      },
      error: (error: HttpErrorResponse) => {
        this.validandoCupon.set(false);
        this.cuponAplicado.set(null);
        this.errorCupon.set(
          error.status === 422
            ? (error.error?.errors?.code?.[0] ?? 'Ese cupón no es válido.')
            : 'No pudimos validar el cupón. Probá de nuevo en un momento.',
        );
      },
    });
  }

  protected quitarCupon(): void {
    this.cuponAplicado.set(null);
    this.cuponInput.set('');
    this.errorCupon.set(null);
  }

  protected confirmarCompra(): void {
    const addressId = this.selectedAddressId();

    if (!addressId) {
      return;
    }

    this.confirmando.set(true);
    this.error.set(null);

    this.orderService.checkout(addressId, this.cuponAplicado()?.code).subscribe({
      next: (order) => {
        this.confirmando.set(false);
        this.order.set(order);
        // El carrito ya se vació en el backend; se refresca el estado local.
        this.cartService.loadCart().subscribe();
      },
      error: (error: HttpErrorResponse) => {
        this.confirmando.set(false);
        this.error.set(this.mensajeError(error));
      },
    });
  }

  private mensajeError(error: HttpErrorResponse): string {
    if (error.status === 422) {
      const errores = error.error?.errors ?? {};
      const primerMensaje = Object.values(errores)[0] as string[] | undefined;
      return primerMensaje?.[0] ?? 'No pudimos confirmar la compra.';
    }

    return 'No pudimos confirmar la compra. Probá de nuevo en un momento.';
  }
}
