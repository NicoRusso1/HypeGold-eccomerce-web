import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AdminOrder } from '../models/admin-order.model';
import { PaginatedResponse } from '../models/product.model';

@Injectable({ providedIn: 'root' })
export class AdminOrderService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiUrl}/admin/orders`;

  getOrders(page = 1, status?: string, search?: string): Observable<PaginatedResponse<AdminOrder>> {
    const params: Record<string, string | number> = { page };

    if (status) {
      params['status'] = status;
    }

    if (search) {
      params['search'] = search;
    }

    return this.http.get<PaginatedResponse<AdminOrder>>(this.baseUrl, { params });
  }

  getOrder(id: number): Observable<AdminOrder> {
    return this.http.get<{ data: AdminOrder }>(`${this.baseUrl}/${id}`).pipe(map((r) => r.data));
  }

  updateStatus(id: number, status: string): Observable<AdminOrder> {
    return this.http.put<{ data: AdminOrder }>(`${this.baseUrl}/${id}/status`, { status }).pipe(map((r) => r.data));
  }
}
