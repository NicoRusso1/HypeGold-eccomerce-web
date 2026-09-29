import { Component, inject, signal } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { CartService } from '../../core/services/cart.service';
import { CategoryService } from '../../core/services/category.service';
import { Category } from '../../core/models/category.model';

@Component({
  imports: [RouterLink],
  selector: 'app-header',
  styleUrl: './header.css',
  templateUrl: './header.html',
})
export class Header {
  private readonly router = inject(Router);
  private readonly categoryService = inject(CategoryService);
  protected readonly authService = inject(AuthService);
  protected readonly cartService = inject(CartService);

  protected readonly categories = signal<Category[]>([]);

  constructor() {
    this.categoryService.getCategories().subscribe({
      next: (categories) => this.categories.set(categories),
      // Si falla, el menú simplemente queda sin categorías (no rompe la página).
      error: () => this.categories.set([]),
    });
  }

  protected logout(): void {
    this.authService.logout().subscribe(() => this.router.navigateByUrl('/'));
  }
}
