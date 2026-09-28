import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';

interface ValidationErrors {
  [field: string]: string[];
}

@Component({
  imports: [ReactiveFormsModule, RouterLink],
  selector: 'app-login',
  styleUrl: './login.css',
  templateUrl: './login.html',
})
export class Login {
  private readonly fb = inject(FormBuilder);
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);

  protected readonly submitting = signal(false);
  protected readonly serverErrors = signal<ValidationErrors>({});

  protected readonly form = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required]],
  });

  protected fieldError(field: string): string | null {
    const control = this.form.get(field);

    if (this.serverErrors()[field]?.length) {
      return this.serverErrors()[field][0];
    }

    if (!control || !control.touched || control.valid) {
      return null;
    }

    if (control.hasError('required')) return 'Este campo es obligatorio.';
    if (control.hasError('email')) return 'Ingresá un email válido.';

    return 'Revisá este campo.';
  }

  protected submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.submitting.set(true);
    this.serverErrors.set({});

    this.authService.login(this.form.getRawValue()).subscribe({
      next: () => {
        this.submitting.set(false);
        this.router.navigateByUrl('/');
      },
      error: (error: HttpErrorResponse) => {
        this.submitting.set(false);

        if (error.status === 422) {
          this.serverErrors.set(error.error?.errors ?? {});
          return;
        }

        this.serverErrors.set({
          general: ['No pudimos iniciar sesión. Probá de nuevo en un momento.'],
        });
      },
    });
  }
}
