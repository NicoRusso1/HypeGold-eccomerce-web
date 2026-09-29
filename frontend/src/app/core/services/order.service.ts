import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { PaginatedResponse } from '../models/product.model';
import { Order } from '../models/order.model';

interface OrderResponse {
  data: Order;
}

@Injectable({ providedIn: 'root' })
export class OrderService {
  private readonly http = inject(HttpClient);

  checkout(addressId: number, couponCode?: string | null): Observable<Order> {
    return this.http
      .post<OrderResponse>(`${environment.apiUrl}/orders`, {
        address_id: addressId,
        coupon_code: couponCode || undefined,
      })
      .pipe(map((response) => response.data));
  }

  getOrders(page = 1): Observable<PaginatedResponse<Order>> {
    return this.http.get<PaginatedResponse<Order>>(`${environment.apiUrl}/orders`, {
      params: new HttpParams().set('page', page),
    });
  }

  getOrder(id: number): Observable<Order> {
    return this.http
      .get<OrderResponse>(`${environment.apiUrl}/orders/${id}`)
      .pipe(map((response) => response.data));
  }
}
