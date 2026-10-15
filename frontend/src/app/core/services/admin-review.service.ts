import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AdminReview, ReviewPayload } from '../models/admin-review.model';

@Injectable({ providedIn: 'root' })
export class AdminReviewService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiUrl}/admin/reviews`;

  getReviews(productId?: number): Observable<AdminReview[]> {
    const params: Record<string, number> = {};

    if (productId) {
      params['product_id'] = productId;
    }

    return this.http.get<{ data: AdminReview[] }>(this.baseUrl, { params }).pipe(map((r) => r.data));
  }

  createReview(payload: ReviewPayload): Observable<AdminReview> {
    return this.http.post<{ data: AdminReview }>(this.baseUrl, payload).pipe(map((r) => r.data));
  }

  updateReview(id: number, payload: ReviewPayload): Observable<AdminReview> {
    return this.http.put<{ data: AdminReview }>(`${this.baseUrl}/${id}`, payload).pipe(map((r) => r.data));
  }

  deleteReview(id: number): Observable<void> {
    return this.http.delete<void>(`${this.baseUrl}/${id}`);
  }
}
