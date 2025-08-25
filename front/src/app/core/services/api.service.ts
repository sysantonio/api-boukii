import { inject, Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';
import { ConfigService } from './config.service';

export interface ApiResponse<T = unknown> {
  success: boolean;
  message?: string;
  data?: T;
  meta?: {
    pagination?: {
      page: number;
      limit: number;
      total: number;
      totalPages: number;
    };
  };
}

export interface ApiError {
  type?: string;
  title?: string;
  status: number;
  detail: string;
  code?: string;
  errors?: Record<string, string[]>;
  request_id?: string;
}

@Injectable({ providedIn: 'root' })
export class ApiService {
  private readonly http = inject(HttpClient);
  private readonly config = inject(ConfigService);

  private get baseUrl(): string {
    return this.config.getApiBaseUrl();
  }

  /**
   * GET request helper
   */
  public async get<T = unknown>(
    url: string,
    params?: Record<string, string | number | boolean>
  ): Promise<T> {
    const httpParams = params ? new HttpParams({ fromObject: params }) : undefined;
    return firstValueFrom(this.http.get<T>(this.join(url), { params: httpParams }));
  }

  /**
   * POST request helper
   */
  public async post<T = unknown>(url: string, body?: unknown): Promise<T> {
    return firstValueFrom(this.http.post<T>(this.join(url), body));
  }

  /**
   * POST request with custom headers (for auth tokens)
   */
  public async postWithHeaders<T = unknown>(
    url: string,
    body?: unknown,
    headers?: Record<string, string>
  ): Promise<T> {
    return firstValueFrom(this.http.post<T>(this.join(url), body, { headers }));
  }

  /**
   * PUT request helper
   */
  public async put<T = unknown>(url: string, body?: unknown): Promise<T> {
    return firstValueFrom(this.http.put<T>(this.join(url), body));
  }

  /**
   * PATCH request helper
   */
  public async patch<T = unknown>(url: string, body?: unknown): Promise<T> {
    return firstValueFrom(this.http.patch<T>(this.join(url), body));
  }

  /**
   * DELETE request helper
   */
  public async delete<T = unknown>(url: string): Promise<T> {
    return firstValueFrom(this.http.delete<T>(this.join(url)));
  }

  /**
   * Upload file helper
   */
  public async uploadFile<T = unknown>(
    url: string,
    file: File,
    additionalData?: Record<string, string>
  ): Promise<T> {
    const formData = new FormData();
    formData.append('file', file);

    if (additionalData) {
      Object.keys(additionalData).forEach((key) => {
        formData.append(key, additionalData[key]);
      });
    }

    return firstValueFrom(this.http.post<T>(this.join(url), formData));
  }

  /**
   * Get blob data for file downloads/exports
   */
  public async getBlob(
    url: string,
    params?: Record<string, string | number | boolean>
  ): Promise<Blob> {
    const httpParams = params ? new HttpParams({ fromObject: params }) : undefined;
    return firstValueFrom(this.http.get(this.join(url), { 
      params: httpParams,
      responseType: 'blob'
    }));
  }

  /**
   * Download file helper
   */
  public async downloadFile(url: string, filename?: string): Promise<void> {
    const response = await firstValueFrom(
      this.http.get(this.join(url), {
        responseType: 'blob',
        observe: 'response',
      })
    );

    const blob = response.body;
    if (!blob) return;

    const downloadUrl = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = filename ?? this.extractFilename(response) ?? 'download';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    window.URL.revokeObjectURL(downloadUrl);
  }

  private extractFilename(response: {
    headers: { get(name: string): string | null };
  }): string | null {
    const contentDisposition = response.headers.get('content-disposition');
    if (!contentDisposition) return null;

    const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(contentDisposition);
    return matches?.[1]?.replace(/['"]/g, '') ?? null;
  }

  private join(path: string): string {
    const base = this.baseUrl.replace(/\/+$/, '');
    const rel = (path || '').toString().replace(/^\/+/, '');
    return `${base}/${rel}`;
  }
}
