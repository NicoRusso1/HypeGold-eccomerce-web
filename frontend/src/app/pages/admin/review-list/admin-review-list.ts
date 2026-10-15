import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormBuilder, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { AdminProduct } from '../../../core/models/admin-product.model';
import { AdminReview } from '../../../core/models/admin-review.model';
import { AdminProductService } from '../../../core/services/admin-product.service';
import { AdminReviewService } from '../../../core/services/admin-review.service';
import { AdminNav } from '../../../shared/admin-nav/admin-nav';
import { StarRating } from '../../../shared/star-rating/star-rating';

interface ValidationErrors {
  [field: string]: string[];
}

@Component({
  imports: [ReactiveFormsModule, FormsModule, AdminNav, StarRating],
  selector: 'app-admin-review-list',
  styleUrl: './admin-review-list.css',
  templateUrl: './admin-review-list.html',
})
export class AdminReviewListPage {
  private readonly fb = inject(FormBuilder);
  private readonly reviewService = inject(AdminReviewService);
  private readonly productService = inject(AdminProductService);

  protected readonly productos = signal<AdminProduct[]>([]);
  protected readonly reviews = signal<AdminReview[]>([]);
  protected readonly loading = signal(true);
  protected readonly error = signal(false);
  protected readonly saving = signal(false);
  protected readonly errors = signal<ValidationErrors>({});
  protected readonly editingId = signal<number | null>(null);
  protected readonly filtroProductoId = signal<number | null>(null);

  protected readonly form = this.fb.nonNullable.group({
    product_id: [null as number | null, Validators.required],
    author_name: ['', [Validators.required, Validators.maxLength(100)]],
    rating: [5, [Validators.required, Validators.min(1), Validators.max(5)]],
    comment: [''],
  });

  constructor() {
    this.productService.getProducts(1).subscribe({
      next: (response) => this.productos.set(response.data),
      error: () => this.productos.set([]),
    });

    this.cargarReviews();
  }

  protected onCambioFiltro(productId: string): void {
    this.filtroProductoId.set(productId ? Number(productId) : null);
    this.cargarReviews();
  }

  protected editar(review: AdminReview): void {
    this.editingId.set(review.id);
    this.errors.set({});
    this.form.setValue({
      product_id: review.product.id,
      author_name: review.author_name,
      rating: review.rating,
      comment: review.comment ?? '',
    });
  }

  protected cancelarEdicion(): void {
    this.editingId.set(null);
    this.errors.set({});
    this.form.reset({ product_id: this.filtroProductoId(), author_name: '', rating: 5, comment: '' });
  }

  protected guardar(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.saving.set(true);
    this.errors.set({});

    const raw = this.form.getRawValue();
    const payload = {
      product_id: raw.product_id as number,
      author_name: raw.author_name,
      rating: Number(raw.rating),
      comment: raw.comment || null,
    };
    const id = this.editingId();
    const request = id ? this.reviewService.updateReview(id, payload) : this.reviewService.createReview(payload);

    request.subscribe({
      next: () => {
        this.saving.set(false);
        this.cancelarEdicion();
        this.cargarReviews();
      },
      error: (error: HttpErrorResponse) => {
        this.saving.set(false);
        this.errors.set(error.status === 422 ? (error.error?.errors ?? {}) : {});
      },
    });
  }

  protected eliminar(review: AdminReview): void {
    this.reviewService.deleteReview(review.id).subscribe(() => this.cargarReviews());
  }

  private cargarReviews(): void {
    this.loading.set(true);
    this.error.set(false);

    this.reviewService.getReviews(this.filtroProductoId() ?? undefined).subscribe({
      next: (reviews) => {
        this.reviews.set(reviews);
        this.loading.set(false);
      },
      error: () => {
        this.loading.set(false);
        this.error.set(true);
      },
    });
  }
}
