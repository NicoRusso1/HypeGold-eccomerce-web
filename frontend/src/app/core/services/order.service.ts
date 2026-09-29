import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Order } from '../models/order.model';

interface OrderResponse {
  data: Order;
}

@Injectable({ providedIn: 'root' })
export class OrderService {
  private readonly http = inject(HttpClient);

  checkout(addressId: number): Observable<Order> {
    return this.http
      .post<OrderResponse>(`${environment.apiUrl}/orders`, { address_id: addressId })
      .pipe(map((response) => response.data));
  }
}
