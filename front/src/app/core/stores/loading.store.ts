import { Injectable, signal, computed } from '@angular/core';

export interface LoadingRequest {
  id: string;
  url: string;
  method: string;
  startTime: Date;
  description?: string;
}

@Injectable({ providedIn: 'root' })
export class LoadingStore {
  // Private signals
  private readonly _activeRequests = signal<Map<string, LoadingRequest>>(new Map());
  private readonly _globalLoading = signal(false);
  private readonly _minimumLoadingDuration = 300; // ms - prevent flashing

  // Public readonly signals
  readonly activeRequests = this._activeRequests.asReadonly();
  readonly globalLoading = this._globalLoading.asReadonly();

  // Computed signals
  readonly isLoading = computed(() => this._activeRequests().size > 0);
  readonly loadingCount = computed(() => this._activeRequests().size);
  readonly longestRunningRequest = computed(() => {
    const requests = Array.from(this._activeRequests().values());
    if (requests.length === 0) return null;

    return requests.reduce((longest, current) =>
      current.startTime < longest.startTime ? current : longest
    );
  });

  // Loading states for different request types
  readonly isApiLoading = computed(() =>
    Array.from(this._activeRequests().values()).some((req) => req.url.startsWith('/api'))
  );

  readonly isAuthLoading = computed(() =>
    Array.from(this._activeRequests().values()).some((req) => req.url.includes('/auth/'))
  );

  // Methods
  /**
   * Start loading for a request
   */
  startLoading(id: string, url: string, method: string, description?: string): void {
    const newRequest: LoadingRequest = {
      id,
      url,
      method,
      startTime: new Date(),
      description,
    };

    const currentRequests = new Map(this._activeRequests());
    currentRequests.set(id, newRequest);
    this._activeRequests.set(currentRequests);

    // Update global loading state
    this.updateGlobalLoading();
  }

  /**
   * Stop loading for a request
   */
  stopLoading(id: string): void {
    const currentRequests = new Map(this._activeRequests());
    const request = currentRequests.get(id);

    if (request) {
      const duration = Date.now() - request.startTime.getTime();

      // If request was very fast, add a small delay to prevent flashing
      if (duration < this._minimumLoadingDuration) {
        setTimeout(() => {
          this.removeRequest(id);
        }, this._minimumLoadingDuration - duration);
      } else {
        this.removeRequest(id);
      }
    }
  }

  /**
   * Force stop all loading (useful for navigation or errors)
   */
  stopAllLoading(): void {
    this._activeRequests.set(new Map());
    this._globalLoading.set(false);
  }

  /**
   * Get loading state for specific URL pattern
   */
  isLoadingUrl(urlPattern: string): boolean {
    return Array.from(this._activeRequests().values()).some((req) => req.url.includes(urlPattern));
  }

  /**
   * Get loading requests by method
   */
  getRequestsByMethod(method: string): LoadingRequest[] {
    return Array.from(this._activeRequests().values()).filter(
      (req) => req.method.toLowerCase() === method.toLowerCase()
    );
  }

  /**
   * Check if specific request type is loading
   */
  isRequestTypeLoading(predicate: (req: LoadingRequest) => boolean): boolean {
    return Array.from(this._activeRequests().values()).some(predicate);
  }

  private removeRequest(id: string): void {
    const currentRequests = new Map(this._activeRequests());
    currentRequests.delete(id);
    this._activeRequests.set(currentRequests);
    this.updateGlobalLoading();
  }

  private updateGlobalLoading(): void {
    this._globalLoading.set(this._activeRequests().size > 0);
  }

  /**
   * Generate unique request ID
   */
  generateRequestId(): string {
    return `req_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  /**
   * Get debug information about current loading state
   */
  getDebugInfo(): {
    activeCount: number;
    requests: LoadingRequest[];
    longestDuration: number | null;
  } {
    const requests = Array.from(this._activeRequests().values());
    const longestDuration =
      requests.length > 0
        ? Math.max(...requests.map((req) => Date.now() - req.startTime.getTime()))
        : null;

    return {
      activeCount: requests.length,
      requests,
      longestDuration,
    };
  }
}
