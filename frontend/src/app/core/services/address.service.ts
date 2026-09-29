import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Address, AddressPayload } from '../models/address.model';

interface AddressResponse {
  data: Address;
}

interface AddressListResponse {
  data: Address[];
}

@Injectable({ providedIn: 'root' })
export class AddressService {
  private readonly http = inject(HttpClient);

  getAddresses(): Observable<Address[]> {
    return this.http
      .get<AddressListResponse>(`${environment.apiUrl}/addresses`)
      .pipe(map((response) => response.data));
  }

  createAddress(payload: AddressPayload): Observable<Address> {
    return this.http
      .post<AddressResponse>(`${environment.apiUrl}/addresses`, payload)
      .pipe(map((response) => response.data));
  }

  updateAddress(id: number, payload: AddressPayload): Observable<Address> {
    return this.http
      .put<AddressResponse>(`${environment.apiUrl}/addresses/${id}`, payload)
      .pipe(map((response) => response.data));
  }

  deleteAddress(id: number): Observable<void> {
    return this.http.delete<void>(`${environment.apiUrl}/addresses/${id}`);
  }
}
