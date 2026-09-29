import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { CouponValidation } from '../models/order.model';

@Injectable({ providedIn: 'root' })
export class CouponService {
  private readonly http = inject(HttpClient);

  validate(code: string): Observable<CouponValidation> {
    return this.http
      .post<{ data: CouponValidation }>(`${environment.apiUrl}/coupons/validate`, { code })
      .pipe(map((response) => response.data));
  }
}
