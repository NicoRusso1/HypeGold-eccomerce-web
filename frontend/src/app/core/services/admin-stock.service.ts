import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AdminStockItem, AdminStockMeta } from '../models/admin-stock.model';

@Injectable({ providedIn: 'root' })
export class AdminStockService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiUrl}/admin/stock`;

  getStock(): Observable<{ items: AdminStockItem[]; meta: AdminStockMeta }> {
    return this.http
      .get<{ data: AdminStockItem[]; meta: AdminStockMeta }>(this.baseUrl)
      .pipe(map((r) => ({ items: r.data, meta: r.meta })));
  }

  updateStock(variantId: number, stock: number): Observable<AdminStockItem> {
    return this.http.put<{ data: AdminStockItem }>(`${this.baseUrl}/${variantId}`, { stock }).pipe(map((r) => r.data));
  }
}
