import { HttpErrorResponse } from '@angular/common/http';
import { Component, computed, inject, signal } from '@angular/core';
import { FormBuilder, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { Category } from '../../../core/models/category.model';
import { AdminCategoryService } from '../../../core/services/admin-category.service';
import { ConfirmDialogService } from '../../../core/services/confirm-dialog.service';
import { AdminNav } from '../../../shared/admin-nav/admin-nav';

interface ValidationErrors {
  [field: string]: string[];
}

@Component({
  imports: [ReactiveFormsModule, FormsModule, AdminNav],
  selector: 'app-admin-category-list',
  styleUrl: './admin-category-list.css',
  templateUrl: './admin-category-list.html',
})
export class AdminCategoryListPage {
  private readonly fb = inject(FormBuilder);
  private readonly categoryService = inject(AdminCategoryService);
  private readonly confirmDialog = inject(ConfirmDialogService);

  protected readonly categories = signal<Category[]>([]);
  protected readonly loading = signal(true);
  protected readonly saving = signal(false);
  protected readonly errors = signal<ValidationErrors>({});
  protected readonly editingId = signal<number | null>(null);
  protected readonly deleteError = signal<string | null>(null);
  protected readonly busqueda = signal('');

  protected readonly categoriesVisibles = computed(() => {
    const termino = this.busqueda().trim().toLowerCase();

    if (!termino) {
      return this.categories();
    }

    return this.categories().filter((categoria) => categoria.name.toLowerCase().includes(termino));
  });

  protected readonly form = this.fb.nonNullable.group({
    name: ['', [Validators.required, Validators.maxLength(255)]],
    description: [''],
  });

  constructor() {
    this.cargar();
  }

  private cargar(): void {
    this.loading.set(true);

    this.categoryService.getCategories().subscribe({
      next: (categories) => {
        this.categories.set(categories);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  protected editar(category: Category): void {
    this.editingId.set(category.id);
    this.errors.set({});
    this.form.setValue({ name: category.name, description: category.description ?? '' });
  }

  protected cancelarEdicion(): void {
    this.editingId.set(null);
    this.errors.set({});
    this.form.reset({ name: '', description: '' });
  }

  protected guardar(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.saving.set(true);
    this.errors.set({});

    const raw = this.form.getRawValue();
    const payload = { name: raw.name, description: raw.description || null };
    const id = this.editingId();
    const request = id ? this.categoryService.updateCategory(id, payload) : this.categoryService.createCategory(payload);

    request.subscribe({
      next: () => {
        this.saving.set(false);
        this.cancelarEdicion();
        this.cargar();
      },
      error: (error: HttpErrorResponse) => {
        this.saving.set(false);
        this.errors.set(error.status === 422 ? (error.error?.errors ?? {}) : {});
      },
    });
  }

  protected async eliminar(category: Category): Promise<void> {
    const confirmado = await this.confirmDialog.confirm({
      title: 'Eliminar categoría',
      message: `¿Seguro que querés eliminar "${category.name}"? Esta acción no se puede deshacer.`,
    });

    if (!confirmado) {
      return;
    }

    this.deleteError.set(null);

    this.categoryService.deleteCategory(category.id).subscribe({
      next: () => this.cargar(),
      error: (error: HttpErrorResponse) => {
        this.deleteError.set(
          error.status === 422
            ? (error.error?.errors?.category?.[0] ?? 'No se pudo eliminar la categoría.')
            : 'No se pudo eliminar la categoría.',
        );
      },
    });
  }
}
