import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, OnInit, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';

interface ValidationErrors {
  [field: string]: string[];
}

@Component({
  imports: [ReactiveFormsModule, RouterLink],
  selector: 'app-reset-password',
  styleUrl: './reset-password.css',
  templateUrl: './reset-password.html',
})
export class ResetPassword implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly authService = inject(AuthService);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);

  protected readonly submitting = signal(false);
  protected readonly success = signal(false);
  protected readonly serverErrors = signal<ValidationErrors>({});
  protected readonly linkInvalido = signal(false);

  protected readonly form = this.fb.nonNullable.group({
    token: ['', [Validators.required]],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(8)]],
    password_confirmation: ['', [Validators.required]],
  });

  ngOnInit(): void {
    const { token, email } = this.route.snapshot.queryParams;

    if (!token || !email) {
      this.linkInvalido.set(true);
      return;
    }

    this.form.patchValue({ token, email });
  }

  protected fieldError(field: string): string | null {
    const control = this.form.get(field);

    if (this.serverErrors()[field]?.length) {
      return this.serverErrors()[field][0];
    }

    if (!control || !control.touched || control.valid) {
      return null;
    }

    if (control.hasError('required')) return 'Este campo es obligatorio.';
    if (control.hasError('minlength')) return 'Debe tener al menos 8 caracteres.';

    return 'Revisá este campo.';
  }

  protected submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.submitting.set(true);
    this.serverErrors.set({});

    this.authService.resetPassword(this.form.getRawValue()).subscribe({
      next: () => {
        this.submitting.set(false);
        this.success.set(true);
        setTimeout(() => this.router.navigateByUrl('/cuenta/ingresar'), 2500);
      },
      error: (error: HttpErrorResponse) => {
        this.submitting.set(false);

        if (error.status === 422) {
          this.serverErrors.set(error.error?.errors ?? {});
          return;
        }

        this.serverErrors.set({
          general: ['No pudimos restablecer tu contraseña. Probá de nuevo en un momento.'],
        });
      },
    });
  }
}
