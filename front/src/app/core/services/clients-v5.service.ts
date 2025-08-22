import { Injectable, inject } from '@angular/core';
import { Observable, from, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { ApiService } from './api.service';

export interface Client {
  id: number;
  [key: string]: any;
}

export interface ClientsResponse {
  data: Client[];
  meta: {
    pagination: {
      page: number;
      limit: number;
      total: number;
      totalPages: number;
    };
  };
}

export interface GetClientsParams {
  school_id: number;
  q?: string;
  sport_id?: number;
  active?: boolean;
  page?: number;
}

@Injectable({
  providedIn: 'root'
})
export class ClientsV5Service {
  private readonly apiHttp = inject(ApiService);

  /**
   * Get clients list
   * Falls back to v4 endpoint if v5 is unavailable
   */
  getClients(params: GetClientsParams): Observable<ClientsResponse> {
    const queryParams: Record<string, string | number | boolean> = {};
    Object.entries(params).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        queryParams[key] = value;
      }
    });

    return from(this.apiHttp.get<ClientsResponse>('/api/v5/clients', queryParams)).pipe(
      catchError(err => {
        if (err?.status === 404) {
          return from(this.apiHttp.get<any>('/api/v4/clients', queryParams)).pipe(
            map((v4Res: any) => {
              const p = v4Res?.meta?.pagination || v4Res?.meta || {};
              const pagination = {
                page: p.page ?? p.current_page ?? 1,
                limit: p.limit ?? p.per_page ?? 0,
                total: p.total ?? 0,
                totalPages: p.totalPages ?? p.total_pages ?? p.last_page ?? 0,
              };
              return { data: v4Res.data, meta: { pagination } } as ClientsResponse;
            })
          );
        }
        return throwError(() => err);
      })
    );
  }
}

