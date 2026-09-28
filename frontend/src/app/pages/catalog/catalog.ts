import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { ProductCard } from '../../shared/product-card/product-card';
import { PaginatedResponse, Product, ProductSort } from '../../core/models/product.model';
import { ProductService } from '../../core/services/product.service';

@Component({
  imports: [ProductCard, ReactiveFormsModule],
  selector: 'app-catalog',
  styleUrl: './catalog.css',
  templateUrl: './catalog.html',
})
export class Catalog {
  private readonly route = inject(ActivatedRoute);
  private readonly productService = inject(ProductService);
  private readonly fb = inject(FormBuilder);

  protected readonly materiales = ['Oro 18k', 'Oro laminado', 'Plata 925', 'Acero quirúrgico'];

  protected readonly products = signal<Product[]>([]);
  protected readonly loading = signal(true);
  protected readonly error = signal(false);
  protected readonly meta = signal<PaginatedResponse<Product>['meta'] | null>(null);
  protected readonly categoriaSlug = signal<string | null>(null);

  private page = 1;

  protected readonly filterForm = this.fb.nonNullable.group({
    search: [''],
    material: [''],
    min_price: [''],
    max_price: [''],
    sort: ['recientes' as ProductSort],
  });

  constructor() {
    this.route.paramMap.subscribe((params) => {
      this.categoriaSlug.set(params.get('categoria'));
      this.page = 1;
      this.cargarProductos();
    });
  }

  protected aplicarFiltros(): void {
    this.page = 1;
    this.cargarProductos();
  }

  protected limpiarFiltros(): void {
    this.filterForm.reset({ search: '', material: '', min_price: '', max_price: '', sort: 'recientes' });
    this.page = 1;
    this.cargarProductos();
  }

  protected irAPagina(pagina: number): void {
    this.page = pagina;
    this.cargarProductos();
  }

  private cargarProductos(): void {
    this.loading.set(true);
    this.error.set(false);

    const { search, material, min_price, max_price, sort } = this.filterForm.getRawValue();

    this.productService
      .getProducts({
        category: this.categoriaSlug() ?? undefined,
        search: search || undefined,
        material: material || undefined,
        min_price: min_price ? Number(min_price) : undefined,
        max_price: max_price ? Number(max_price) : undefined,
        sort,
        page: this.page,
      })
      .subscribe({
        next: (response) => {
          this.products.set(response.data);
          this.meta.set(response.meta);
          this.loading.set(false);
        },
        error: () => {
          this.products.set([]);
          this.meta.set(null);
          this.loading.set(false);
          this.error.set(true);
        },
      });
  }
}
