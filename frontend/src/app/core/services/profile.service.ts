import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { User } from '../models/user.model';
import { AuthService } from './auth.service';

export interface UpdateProfilePayload {
  name: string;
  email: string;
  phone: string | null;
}

export interface UpdatePasswordPayload {
  current_password: string;
  password: string;
  password_confirmation: string;
}

@Injectable({ providedIn: 'root' })
export class ProfileService {
  private readonly http = inject(HttpClient);
  private readonly authService = inject(AuthService);

  getProfile(): Observable<User> {
    return this.http.get<User>(`${environment.apiUrl}/profile`);
  }

  updateProfile(payload: UpdateProfilePayload): Observable<User> {
    return this.http
      .put<User>(`${environment.apiUrl}/profile`, payload)
      .pipe(tap((user) => this.authService.updateStoredUser(user)));
  }

  updatePassword(payload: UpdatePasswordPayload): Observable<{ message: string }> {
    return this.http.put<{ message: string }>(`${environment.apiUrl}/profile/password`, payload);
  }
}
