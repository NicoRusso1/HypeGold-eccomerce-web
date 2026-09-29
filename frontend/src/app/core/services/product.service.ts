import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { PaginatedResponse, Product, ProductDetail, ProductFilters } from '../models/product.model';

interface ProductDetailResponse {
  data: ProductDetail;
}

@Injectable({ providedIn: 'root' })
export class ProductService {
  private readonly http = inject(HttpClient);

  getProducts(filters: ProductFilters = {}): Observable<PaginatedResponse<Product>> {
    let params = new HttpParams();

    for (const [key, value] of Object.entries(filters)) {
      if (value !== undefined && value !== null && value !== '') {
        params = params.set(key, value);
      }
    }

    return this.http.get<PaginatedResponse<Product>>(`${environment.apiUrl}/products`, { params });
  }

  getProduct(slug: string): Observable<ProductDetail> {
    return this.http
      .get<ProductDetailResponse>(`${environment.apiUrl}/products/${slug}`)
      .pipe(map((response) => response.data));
  }

  /** Sugerencias livianas para el autocompletado de búsqueda. */
  searchSuggestions(q: string): Observable<Product[]> {
    return this.http
      .get<{ data: Product[] }>(`${environment.apiUrl}/products/search`, { params: { q } })
      .pipe(map((response) => response.data));
  }
}
