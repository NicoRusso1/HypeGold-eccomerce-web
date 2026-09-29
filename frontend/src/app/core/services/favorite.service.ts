import { HttpClient } from '@angular/common/http';
import { Injectable, computed, effect, inject, signal } from '@angular/core';
import { Observable, map, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Product } from '../models/product.model';
import { AuthService } from './auth.service';

@Injectable({ providedIn: 'root' })
export class FavoriteService {
  private readonly http = inject(HttpClient);
  private readonly authService = inject(AuthService);

  private readonly favoritesSignal = signal<Product[]>([]);

  readonly favorites = this.favoritesSignal.asReadonly();
  readonly favoriteIds = computed(() => new Set(this.favoritesSignal().map((p) => p.id)));

  constructor() {
    effect(() => {
      if (this.authService.isLoggedIn()) {
        this.loadFavorites().subscribe();
      } else {
        this.favoritesSignal.set([]);
      }
    });
  }

  loadFavorites(): Observable<Product[]> {
    return this.http.get<{ data: Product[] }>(`${environment.apiUrl}/favorites`).pipe(
      map((response) => response.data),
      tap((productos) => this.favoritesSignal.set(productos)),
    );
  }

  isFavorite(productId: number): boolean {
    return this.favoriteIds().has(productId);
  }

  toggle(product: Product): Observable<unknown> {
    return this.isFavorite(product.id) ? this.remove(product.id) : this.add(product);
  }

  private add(product: Product): Observable<unknown> {
    return this.http.post(`${environment.apiUrl}/favorites`, { product_id: product.id }).pipe(
      tap(() => this.favoritesSignal.update((productos) => [...productos, product])),
    );
  }

  private remove(productId: number): Observable<unknown> {
    return this.http.delete(`${environment.apiUrl}/favorites/${productId}`).pipe(
      tap(() => this.favoritesSignal.update((productos) => productos.filter((p) => p.id !== productId))),
    );
  }
}
