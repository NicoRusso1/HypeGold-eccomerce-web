import { Component, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';

@Component({
  imports: [RouterLink],
  selector: 'app-header',
  styleUrl: './header.css',
  templateUrl: './header.html',
})
export class Header {
  private readonly router = inject(Router);
  protected readonly authService = inject(AuthService);

  protected logout(): void {
    this.authService.logout().subscribe(() => this.router.navigateByUrl('/'));
  }
}
