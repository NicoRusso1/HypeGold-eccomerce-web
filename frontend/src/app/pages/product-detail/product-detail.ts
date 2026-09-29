import { Component, computed, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { CurrencyPipe } from '@angular/common';
import { ProductDetail, ProductVariant } from '../../core/models/product.model';
import { ProductService } from '../../core/services/product.service';

@Component({
  imports: [CurrencyPipe, RouterLink],
  selector: 'app-product-detail',
  styleUrl: './product-detail.css',
  templateUrl: './product-detail.html',
})
export class ProductDetailPage {
  private readonly route = inject(ActivatedRoute);
  private readonly productService = inject(ProductService);

  protected readonly product = signal<ProductDetail | null>(null);
  protected readonly loading = signal(true);
  protected readonly notFound = signal(false);
  protected readonly activeImageIndex = signal(0);
  protected readonly selectedVariantId = signal<number | null>(null);

  protected readonly selectedVariant = computed<ProductVariant | null>(() => {
    const product = this.product();
    const variantId = this.selectedVariantId();

    if (!product || variantId === null) {
      return null;
    }

    return product.variants.find((variant) => variant.id === variantId) ?? null;
  });

  constructor() {
    this.route.paramMap.subscribe((params) => {
      const slug = params.get('slug');

      if (!slug) {
        this.notFound.set(true);
        this.loading.set(false);
        return;
      }

      this.cargarProducto(slug);
    });
  }

  protected seleccionarImagen(index: number): void {
    this.activeImageIndex.set(index);
  }

  protected seleccionarVariante(variantId: number): void {
    this.selectedVariantId.set(variantId);
  }

  private cargarProducto(slug: string): void {
    this.loading.set(true);
    this.notFound.set(false);
    this.activeImageIndex.set(0);

    this.productService.getProduct(slug).subscribe({
      next: (product) => {
        this.product.set(product);
        this.selectedVariantId.set(product.variants[0]?.id ?? null);
        this.loading.set(false);
      },
      error: () => {
        this.product.set(null);
        this.notFound.set(true);
        this.loading.set(false);
      },
    });
  }
}
