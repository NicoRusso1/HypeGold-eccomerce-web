import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Address } from '../../core/models/address.model';
import { AddressService } from '../../core/services/address.service';

interface ValidationErrors {
  [field: string]: string[];
}

@Component({
  imports: [ReactiveFormsModule],
  selector: 'app-addresses',
  styleUrl: './addresses.css',
  templateUrl: './addresses.html',
})
export class AddressesPage {
  private readonly fb = inject(FormBuilder);
  private readonly addressService = inject(AddressService);

  protected readonly addresses = signal<Address[]>([]);
  protected readonly loading = signal(true);
  protected readonly saving = signal(false);
  protected readonly formErrors = signal<ValidationErrors>({});
  protected readonly editingId = signal<number | null>(null);
  protected readonly showForm = signal(false);

  protected readonly form = this.fb.nonNullable.group({
    label: [''],
    street: ['', [Validators.required, Validators.maxLength(255)]],
    city: ['', [Validators.required, Validators.maxLength(100)]],
    province: ['', [Validators.required, Validators.maxLength(100)]],
    postal_code: ['', [Validators.required, Validators.maxLength(20)]],
    phone: ['', [Validators.required, Validators.maxLength(30)]],
    is_default: [false],
  });

  constructor() {
    this.cargarDirecciones();
  }

  protected fieldError(field: string): string | null {
    const control = this.form.get(field);

    if (this.formErrors()[field]?.length) {
      return this.formErrors()[field][0];
    }

    if (!control || !control.touched || control.valid) {
      return null;
    }

    return 'Este campo es obligatorio.';
  }

  protected nuevaDireccion(): void {
    this.editingId.set(null);
    this.form.reset({ label: '', street: '', city: '', province: '', postal_code: '', phone: '', is_default: false });
    this.formErrors.set({});
    this.showForm.set(true);
  }

  protected editarDireccion(address: Address): void {
    this.editingId.set(address.id);
    this.form.reset({
      label: address.label ?? '',
      street: address.street,
      city: address.city,
      province: address.province,
      postal_code: address.postal_code,
      phone: address.phone,
      is_default: address.is_default,
    });
    this.formErrors.set({});
    this.showForm.set(true);
  }

  protected cancelar(): void {
    this.showForm.set(false);
  }

  protected guardar(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.saving.set(true);
    this.formErrors.set({});

    const { label, street, city, province, postal_code, phone, is_default } = this.form.getRawValue();
    const payload = { label: label || null, street, city, province, postal_code, phone, is_default };
    const editingId = this.editingId();

    const request = editingId
      ? this.addressService.updateAddress(editingId, payload)
      : this.addressService.createAddress(payload);

    request.subscribe({
      next: () => {
        this.saving.set(false);
        this.showForm.set(false);
        this.cargarDirecciones();
      },
      error: (error: HttpErrorResponse) => {
        this.saving.set(false);
        this.formErrors.set(error.status === 422 ? (error.error?.errors ?? {}) : {});
      },
    });
  }

  protected eliminar(address: Address): void {
    this.addressService.deleteAddress(address.id).subscribe(() => this.cargarDirecciones());
  }

  private cargarDirecciones(): void {
    this.loading.set(true);
    this.addressService.getAddresses().subscribe({
      next: (addresses) => {
        this.addresses.set(addresses);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }
}
