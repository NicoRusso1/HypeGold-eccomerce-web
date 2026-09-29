import { Component, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { ProductCard } from '../../shared/product-card/product-card';
import { Category } from '../../core/models/category.model';
import { Product } from '../../core/models/product.model';
import { CategoryService } from '../../core/services/category.service';
import { ProductService } from '../../core/services/product.service';

const ICONOS_POR_CATEGORIA: Record<string, string> = {
  cadenas: '⛓️',
  pulseras: '✨',
  anillos: '💍',
  aros: '💎',
};

@Component({
  imports: [RouterLink, ProductCard],
  selector: 'app-home',
  styleUrl: './home.css',
  templateUrl: './home.html',
})
export class Home {
  private readonly categoryService = inject(CategoryService);
  private readonly productService = inject(ProductService);

  protected readonly categorias = signal<Category[]>([]);
  protected readonly destacados = signal<Product[]>([]);

  constructor() {
    this.categoryService.getCategories().subscribe({
      next: (categorias) => this.categorias.set(categorias),
      error: () => this.categorias.set([]),
    });

    this.productService.getProducts({ sort: 'recientes', per_page: 3 }).subscribe({
      next: (response) => this.destacados.set(response.data),
      error: () => this.destacados.set([]),
    });
  }

  protected iconoDe(categoria: Category): string {
    return ICONOS_POR_CATEGORIA[categoria.slug] ?? '💫';
  }
}
