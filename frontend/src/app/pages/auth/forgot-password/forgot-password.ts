import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  imports: [ReactiveFormsModule, RouterLink],
  selector: 'app-forgot-password',
  styleUrl: './forgot-password.css',
  templateUrl: './forgot-password.html',
})
export class ForgotPassword {
  private readonly fb = inject(FormBuilder);
  private readonly authService = inject(AuthService);

  protected readonly submitting = signal(false);
  protected readonly sent = signal(false);

  protected readonly form = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
  });

  protected fieldError(field: string): string | null {
    const control = this.form.get(field);

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

    // El backend siempre responde igual (exista o no el email), así que
    // solo necesitamos mostrar el mensaje genérico al terminar.
    this.authService.forgotPassword(this.form.getRawValue()).subscribe({
      next: () => {
        this.submitting.set(false);
        this.sent.set(true);
      },
      error: () => {
        this.submitting.set(false);
        this.sent.set(true);
      },
    });
  }
}
