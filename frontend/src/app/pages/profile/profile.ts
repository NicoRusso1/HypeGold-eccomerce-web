import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, OnInit, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { ProfileService } from '../../core/services/profile.service';

interface ValidationErrors {
  [field: string]: string[];
}

@Component({
  imports: [ReactiveFormsModule, RouterLink],
  selector: 'app-profile',
  styleUrl: './profile.css',
  templateUrl: './profile.html',
})
export class Profile implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly profileService = inject(ProfileService);
  protected readonly authService = inject(AuthService);

  protected readonly savingProfile = signal(false);
  protected readonly profileSuccess = signal(false);
  protected readonly profileErrors = signal<ValidationErrors>({});

  protected readonly savingPassword = signal(false);
  protected readonly passwordSuccess = signal(false);
  protected readonly passwordErrors = signal<ValidationErrors>({});

  protected readonly profileForm = this.fb.nonNullable.group({
    name: ['', [Validators.required, Validators.maxLength(255)]],
    email: ['', [Validators.required, Validators.email]],
    phone: [''],
  });

  protected readonly passwordForm = this.fb.nonNullable.group({
    current_password: ['', [Validators.required]],
    password: ['', [Validators.required, Validators.minLength(8)]],
    password_confirmation: ['', [Validators.required]],
  });

  ngOnInit(): void {
    const user = this.authService.currentUser();

    if (user) {
      this.profileForm.patchValue({
        name: user.name,
        email: user.email,
        phone: user.phone ?? '',
      });
    }
  }

  protected fieldError(form: 'profile' | 'password', field: string): string | null {
    const errors = form === 'profile' ? this.profileErrors() : this.passwordErrors();
    const control =
      form === 'profile' ? this.profileForm.get(field) : this.passwordForm.get(field);

    if (errors[field]?.length) {
      return errors[field][0];
    }

    if (!control || !control.touched || control.valid) {
      return null;
    }

    if (control.hasError('required')) return 'Este campo es obligatorio.';
    if (control.hasError('email')) return 'Ingresá un email válido.';
    if (control.hasError('minlength')) return 'Debe tener al menos 8 caracteres.';

    return 'Revisá este campo.';
  }

  protected submitProfile(): void {
    if (this.profileForm.invalid) {
      this.profileForm.markAllAsTouched();
      return;
    }

    this.savingProfile.set(true);
    this.profileSuccess.set(false);
    this.profileErrors.set({});

    const { name, email, phone } = this.profileForm.getRawValue();

    this.profileService.updateProfile({ name, email, phone: phone || null }).subscribe({
      next: () => {
        this.savingProfile.set(false);
        this.profileSuccess.set(true);
      },
      error: (error: HttpErrorResponse) => {
        this.savingProfile.set(false);
        this.profileErrors.set(error.status === 422 ? (error.error?.errors ?? {}) : {
          general: ['No pudimos guardar los cambios. Probá de nuevo en un momento.'],
        });
      },
    });
  }

  protected submitPassword(): void {
    if (this.passwordForm.invalid) {
      this.passwordForm.markAllAsTouched();
      return;
    }

    this.savingPassword.set(true);
    this.passwordSuccess.set(false);
    this.passwordErrors.set({});

    this.profileService.updatePassword(this.passwordForm.getRawValue()).subscribe({
      next: () => {
        this.savingPassword.set(false);
        this.passwordSuccess.set(true);
        this.passwordForm.reset();
      },
      error: (error: HttpErrorResponse) => {
        this.savingPassword.set(false);
        this.passwordErrors.set(error.status === 422 ? (error.error?.errors ?? {}) : {
          general: ['No pudimos cambiar la contraseña. Probá de nuevo en un momento.'],
        });
      },
    });
  }
}
