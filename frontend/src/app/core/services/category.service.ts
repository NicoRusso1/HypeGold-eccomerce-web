import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Category } from '../models/category.model';

interface CategoriesResponse {
  data: Category[];
}

@Injectable({ providedIn: 'root' })
export class CategoryService {
  private readonly http = inject(HttpClient);

  getCategories(): Observable<Category[]> {
    return this.http
      .get<CategoriesResponse>(`${environment.apiUrl}/categories`)
      .pipe(map((response) => response.data));
  }
}
