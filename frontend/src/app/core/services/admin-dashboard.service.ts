import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { DashboardMeta, DashboardResumen } from '../models/admin-dashboard.model';

@Injectable({ providedIn: 'root' })
export class AdminDashboardService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiUrl}/admin/dashboard`;

  getResumen(from?: string, to?: string): Observable<{ data: DashboardResumen; meta: DashboardMeta }> {
    const params: Record<string, string> = {};

    if (from) {
      params['from'] = from;
    }
    if (to) {
      params['to'] = to;
    }

    return this.http.get<{ data: DashboardResumen; meta: DashboardMeta }>(this.baseUrl, { params });
  }
}
