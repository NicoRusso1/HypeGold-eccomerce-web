import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Category } from '../models/category.model';

export interface CategoryPayload {
  name: string;
  description: string | null;
}

@Injectable({ providedIn: 'root' })
export class AdminCategoryService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiUrl}/admin/categories`;

  getCategories(): Observable<Category[]> {
    return this.http.get<{ data: Category[] }>(this.baseUrl).pipe(map((r) => r.data));
  }

  createCategory(payload: CategoryPayload): Observable<Category> {
    return this.http.post<{ data: Category }>(this.baseUrl, payload).pipe(map((r) => r.data));
  }

  updateCategory(id: number, payload: CategoryPayload): Observable<Category> {
    return this.http.put<{ data: Category }>(`${this.baseUrl}/${id}`, payload).pipe(map((r) => r.data));
  }

  deleteCategory(id: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/${id}`);
  }
}
