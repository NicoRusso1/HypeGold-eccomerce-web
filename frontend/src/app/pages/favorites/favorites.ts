import { Component, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { ProductCard } from '../../shared/product-card/product-card';
import { FavoriteService } from '../../core/services/favorite.service';

@Component({
  imports: [ProductCard, RouterLink],
  selector: 'app-favorites',
  styleUrl: './favorites.css',
  templateUrl: './favorites.html',
})
export class FavoritesPage {
  protected readonly favoriteService = inject(FavoriteService);

  protected readonly loading = signal(true);

  constructor() {
    this.favoriteService.loadFavorites().subscribe({
      next: () => this.loading.set(false),
      error: () => this.loading.set(false),
    });
  }
}
