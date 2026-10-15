import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormArray, FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { AdminProductImage } from '../../../core/models/admin-product.model';
import { Category } from '../../../core/models/category.model';
import { AdminProductService } from '../../../core/services/admin-product.service';
import { CategoryService } from '../../../core/services/category.service';
import { AdminNav } from '../../../shared/admin-nav/admin-nav';

interface ValidationErrors {
  [field: string]: string[];
}

@Component({
  imports: [ReactiveFormsModule, RouterLink, AdminNav],
  selector: 'app-admin-product-form',
  styleUrl: './admin-product-form.css',
  templateUrl: './admin-product-form.html',
})
export class AdminProductFormPage {
  private readonly fb = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly productService = inject(AdminProductService);
  private readonly categoryService = inject(CategoryService);

  protected readonly categories = signal<Category[]>([]);
  protected readonly loading = signal(true);
  protected readonly saving = signal(false);
  protected readonly errors = signal<ValidationErrors>({});
  protected readonly productId = signal<number | null>(null);
  protected readonly images = signal<AdminProductImage[]>([]);
  protected readonly subiendoImagen = signal(false);

  protected readonly form = this.fb.nonNullable.group({
    category_id: [null as number | null, Validators.required],
    name: ['', [Validators.required, Validators.maxLength(255)]],
    description: [''],
    material: ['', [Validators.required, Validators.maxLength(100)]],
    base_price: [0, [Validators.required, Validators.min(0)]],
    active: [true],
    variants: this.fb.array([this.nuevaVariante()]),
  });

  protected get variantsArray(): FormArray {
    return this.form.get('variants') as FormArray;
  }

  constructor() {
    this.categoryService.getCategories().subscribe({
      next: (categories) => this.categories.set(categories),
      error: () => this.categories.set([]),
    });

    const idParam = this.route.snapshot.paramMap.get('id');

    if (!idParam) {
      this.loading.set(false);
      return;
    }

    const id = Number(idParam);
    this.productId.set(id);

    this.productService.getProduct(id).subscribe({
      next: (product) => {
        this.form.patchValue({
          category_id: product.category?.id ?? null,
          name: product.name,
          description: product.description ?? '',
          material: product.material,
          base_price: product.base_price,
          active: product.active,
        });

        this.variantsArray.clear();
        (product.variants ?? []).forEach((variant) => this.variantsArray.push(this.nuevaVariante(variant)));
        if (this.variantsArray.length === 0) {
          this.variantsArray.push(this.nuevaVariante());
        }

        this.images.set(product.images ?? []);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  private nuevaVariante(valores?: { id?: number; talle: string | null; sku: string; stock: number; extra_price: number }) {
    return this.fb.nonNullable.group({
      id: [valores?.id ?? null],
      talle: [valores?.talle ?? ''],
      sku: [valores?.sku ?? '', Validators.required],
      stock: [valores?.stock ?? 0, [Validators.required, Validators.min(0)]],
      extra_price: [valores?.extra_price ?? 0, [Validators.min(0)]],
    });
  }

  protected agregarVariante(): void {
    this.variantsArray.push(this.nuevaVariante());
  }

  protected quitarVariante(index: number): void {
    if (this.variantsArray.length > 1) {
      this.variantsArray.removeAt(index);
    }
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
      category_id: raw.category_id as number,
      name: raw.name,
      description: raw.description || null,
      material: raw.material,
      base_price: Number(raw.base_price),
      active: raw.active,
      variants: raw.variants.map((v) => ({
        id: v.id ?? undefined,
        talle: v.talle || null,
        sku: v.sku,
        stock: Number(v.stock),
        extra_price: Number(v.extra_price),
      })),
    };

    const id = this.productId();
    const request = id ? this.productService.updateProduct(id, payload) : this.productService.createProduct(payload);

    request.subscribe({
      next: (product) => {
        this.saving.set(false);

        if (!id) {
          // Recién creado: vamos a su pantalla de edición para poder cargar fotos.
          this.router.navigate(['/admin/productos', product.id, 'editar']);
        }
      },
      error: (error: HttpErrorResponse) => {
        this.saving.set(false);
        this.errors.set(error.status === 422 ? (error.error?.errors ?? {}) : {});
      },
    });
  }

  protected onImagenSeleccionada(event: Event): void {
    const id = this.productId();
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!id || !file) {
      return;
    }

    this.subiendoImagen.set(true);

    this.productService.uploadImage(id, file).subscribe({
      next: (image) => {
        this.images.update((imgs) => [...imgs, image]);
        this.subiendoImagen.set(false);
      },
      error: () => this.subiendoImagen.set(false),
    });
  }

  protected eliminarImagen(image: AdminProductImage): void {
    const id = this.productId();

    if (!id) {
      return;
    }

    this.productService.deleteImage(id, image.id).subscribe(() => {
      this.images.update((imgs) => imgs.filter((img) => img.id !== image.id));
    });
  }
}
