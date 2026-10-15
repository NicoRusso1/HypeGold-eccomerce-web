import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AdminProduct, AdminProductImage, AdminProductPayload } from '../models/admin-product.model';
import { PaginatedResponse } from '../models/product.model';

@Injectable({ providedIn: 'root' })
export class AdminProductService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiUrl}/admin/products`;

  getProducts(page = 1): Observable<PaginatedResponse<AdminProduct>> {
    return this.http.get<PaginatedResponse<AdminProduct>>(this.baseUrl, { params: { page } });
  }

  getProduct(id: number): Observable<AdminProduct> {
    return this.http.get<{ data: AdminProduct }>(`${this.baseUrl}/${id}`).pipe(map((r) => r.data));
  }

  createProduct(payload: AdminProductPayload): Observable<AdminProduct> {
    return this.http.post<{ data: AdminProduct }>(this.baseUrl, payload).pipe(map((r) => r.data));
  }

  updateProduct(id: number, payload: AdminProductPayload): Observable<AdminProduct> {
    return this.http.put<{ data: AdminProduct }>(`${this.baseUrl}/${id}`, payload).pipe(map((r) => r.data));
  }

  uploadImage(productId: number, file: File): Observable<AdminProductImage> {
    const formData = new FormData();
    formData.append('image', file);

    return this.http
      .post<{ data: AdminProductImage }>(`${this.baseUrl}/${productId}/images`, formData)
      .pipe(map((r) => r.data));
  }

  deleteImage(productId: number, imageId: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/${productId}/images/${imageId}`);
  }

  deleteProduct(id: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/${id}`);
  }
}
