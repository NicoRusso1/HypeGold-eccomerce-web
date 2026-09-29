import { CurrencyPipe } from '@angular/common';
import { Component, Input, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { Product } from '../../core/models/product.model';
import { AuthService } from '../../core/services/auth.service';
import { FavoriteService } from '../../core/services/favorite.service';

@Component({
  imports: [CurrencyPipe, RouterLink],
  selector: 'app-product-card',
  styleUrl: './product-card.css',
  templateUrl: './product-card.html',
})
export class ProductCard {
  @Input({ required: true }) product!: Product;

  protected readonly authService = inject(AuthService);
  protected readonly favoriteService = inject(FavoriteService);
  private readonly router = inject(Router);

  protected toggleFavorito(event: Event): void {
    event.preventDefault();
    event.stopPropagation();

    if (!this.authService.isLoggedIn()) {
      this.router.navigateByUrl('/cuenta/ingresar');
      return;
    }

    this.favoriteService.toggle(this.product).subscribe();
  }
}
