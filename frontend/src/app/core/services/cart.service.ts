import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Injectable, computed, effect, inject, signal } from '@angular/core';
import { Observable, catchError, map, of, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Cart } from '../models/cart.model';
import { AuthService } from './auth.service';

interface CartResponse {
  data: Cart;
}

@Injectable({ providedIn: 'root' })
export class CartService {
  private readonly http = inject(HttpClient);
  private readonly authService = inject(AuthService);

  private readonly cartSignal = signal<Cart | null>(null);

  readonly cart = this.cartSignal.asReadonly();
  readonly itemsCount = computed(() => this.cartSignal()?.items_count ?? 0);

  constructor() {
    effect(() => {
      if (this.authService.isLoggedIn()) {
        this.loadCart().subscribe();
      } else {
        this.cartSignal.set(null);
      }
    });
  }

  loadCart(): Observable<Cart | null> {
    return this.http.get<CartResponse>(`${environment.apiUrl}/cart`).pipe(
      map((response) => response.data),
      tap((cart) => this.cartSignal.set(cart)),
      catchError(() => {
        this.cartSignal.set(null);
        return of(null);
      }),
    );
  }

  addItem(productVariantId: number, quantity = 1): Observable<Cart> {
    return this.http
      .post<CartResponse>(`${environment.apiUrl}/cart/items`, { product_variant_id: productVariantId, quantity })
      .pipe(
        map((response) => response.data),
        tap((cart) => this.cartSignal.set(cart)),
      );
  }

  updateQuantity(itemId: number, quantity: number): Observable<Cart> {
    return this.http.put<CartResponse>(`${environment.apiUrl}/cart/items/${itemId}`, { quantity }).pipe(
      map((response) => response.data),
      tap((cart) => this.cartSignal.set(cart)),
    );
  }

  removeItem(itemId: number): Observable<Cart> {
    return this.http.delete<CartResponse>(`${environment.apiUrl}/cart/items/${itemId}`).pipe(
      map((response) => response.data),
      tap((cart) => this.cartSignal.set(cart)),
    );
  }

  clear(): Observable<Cart> {
    return this.http.delete<CartResponse>(`${environment.apiUrl}/cart`).pipe(
      map((response) => response.data),
      tap((cart) => this.cartSignal.set(cart)),
    );
  }

  /** Expone el mensaje de error de stock (422) tal como lo manda el backend, si existe. */
  static stockErrorMessage(error: HttpErrorResponse): string | null {
    return error.status === 422 ? (error.error?.errors?.quantity?.[0] ?? null) : null;
  }
}
