import { Injectable, signal, computed } from '@angular/core';
import {
  Toast,
  ToastConfig,
  ToastPosition,
  DEFAULT_TOAST_CONFIG,
  ToastQueueConfig,
} from '../models/toast.models';

@Injectable({ providedIn: 'root' })
export class ToastService {
  // Private signals
  private readonly _toasts = signal<Toast[]>([]);
  private readonly _queueConfig = signal<ToastQueueConfig>({
    maxToasts: 5,
    removeOldest: true,
    stackNewest: 'top',
  });

  // Public readonly signals
  readonly toasts = this._toasts.asReadonly();
  readonly queueConfig = this._queueConfig.asReadonly();

  // Computed signals
  readonly toastsByPosition = computed(() => {
    const toastMap = new Map<ToastPosition, Toast[]>();

    this._toasts().forEach((toast) => {
      const position = toast.position;
      if (!toastMap.has(position)) {
        toastMap.set(position, []);
      }
      toastMap.get(position)!.push(toast);
    });

    // Sort toasts within each position
    toastMap.forEach((toasts, position) => {
      const isTop = position.includes('top');
      const stackNewest = this._queueConfig().stackNewest;

      toasts.sort((a, b) => {
        const timeA = a.createdAt.getTime();
        const timeB = b.createdAt.getTime();

        if (stackNewest === 'top') {
          return isTop ? timeB - timeA : timeA - timeB;
        } else {
          return isTop ? timeA - timeB : timeB - timeA;
        }
      });
    });

    return toastMap;
  });

  readonly hasToasts = computed(() => this._toasts().length > 0);
  readonly toastCount = computed(() => this._toasts().length);

  // Private timers for auto-dismiss
  private readonly timers = new Map<string, number>();

  // Public methods
  /**
   * Show a success toast
   */
  success(message: string, config?: Partial<ToastConfig>): string {
    return this.show({
      ...DEFAULT_TOAST_CONFIG.success,
      ...config,
      message,
      type: 'success',
    });
  }

  /**
   * Show an error toast
   */
  error(message: string, config?: Partial<ToastConfig>): string {
    return this.show({
      ...DEFAULT_TOAST_CONFIG.error,
      ...config,
      message,
      type: 'error',
    });
  }

  /**
   * Show a warning toast
   */
  warning(message: string, config?: Partial<ToastConfig>): string {
    return this.show({
      ...DEFAULT_TOAST_CONFIG.warning,
      ...config,
      message,
      type: 'warning',
    });
  }

  /**
   * Show an info toast
   */
  info(message: string, config?: Partial<ToastConfig>): string {
    return this.show({
      ...DEFAULT_TOAST_CONFIG.info,
      ...config,
      message,
      type: 'info',
    });
  }

  /**
   * Show a toast with full configuration
   */
  show(config: ToastConfig): string {
    const defaultConfig = DEFAULT_TOAST_CONFIG[config.type];
    const fullConfig = { ...defaultConfig, ...config };

    const id = fullConfig.id || this.generateId();

    // Check for duplicates
    if (fullConfig.preventDuplicates && this.findToast(id)) {
      return id;
    }

    // Remove existing toast with same ID
    this.remove(id);

    const toast: Toast = {
      id,
      message: fullConfig.message,
      type: fullConfig.type,
      title: fullConfig.title,
      duration: fullConfig.duration ?? DEFAULT_TOAST_CONFIG[config.type].duration ?? 4000,
      closeable: fullConfig.closeable ?? true,
      position: fullConfig.position ?? 'top-right',
      actions: fullConfig.actions,
      html: fullConfig.html ?? false,
      className: fullConfig.className,
      icon: fullConfig.icon,
      showProgress: fullConfig.showProgress ?? true,
      preventDuplicates: fullConfig.preventDuplicates ?? false,
      createdAt: new Date(),
      dismissing: false,
      paused: false,
    };

    // Add to queue
    this.addToQueue(toast);

    // Set up auto-dismiss timer
    if (toast.duration > 0) {
      this.setTimer(toast);
    }

    return id;
  }

  /**
   * Remove a specific toast
   */
  remove(id: string): boolean {
    const currentToasts = this._toasts();
    const toastIndex = currentToasts.findIndex((t) => t.id === id);

    if (toastIndex === -1) return false;

    // Clear timer
    this.clearTimer(id);

    // Start dismiss animation
    const updatedToasts = [...currentToasts];
    updatedToasts[toastIndex] = { ...updatedToasts[toastIndex], dismissing: true };
    this._toasts.set(updatedToasts);

    // Actually remove after animation
    setTimeout(() => {
      const finalToasts = this._toasts().filter((t) => t.id !== id);
      this._toasts.set(finalToasts);
    }, 300); // Animation duration

    return true;
  }

  /**
   * Remove all toasts
   */
  clear(): void {
    this.timers.forEach((timer) => {
      window.clearTimeout(timer);
    });
    this.timers.clear();

    // Start dismiss animation for all
    const dismissingToasts = this._toasts().map((t) => ({ ...t, dismissing: true }));
    this._toasts.set(dismissingToasts);

    // Clear after animation
    setTimeout(() => {
      this._toasts.set([]);
    }, 300);
  }

  /**
   * Pause auto-dismiss for a toast (useful for hover)
   */
  pauseToast(id: string): void {
    this.updateToast(id, { paused: true });
    this.clearTimer(id);
  }

  /**
   * Resume auto-dismiss for a toast
   */
  resumeToast(id: string): void {
    const toast = this.findToast(id);
    if (!toast || toast.duration === 0) return;

    this.updateToast(id, { paused: false });
    this.setTimer(toast);
  }

  /**
   * Update toast queue configuration
   */
  updateQueueConfig(config: Partial<ToastQueueConfig>): void {
    this._queueConfig.set({ ...this._queueConfig(), ...config });
  }

  /**
   * Get toast by ID
   */
  getToast(id: string): Toast | undefined {
    return this.findToast(id);
  }

  // Private methods
  private addToQueue(toast: Toast): void {
    const currentToasts = this._toasts();
    const config = this._queueConfig();

    let newToasts = [...currentToasts, toast];

    // Enforce max toasts limit
    if (newToasts.length > config.maxToasts) {
      if (config.removeOldest) {
        // Remove oldest toasts
        const toRemove = newToasts.slice(0, newToasts.length - config.maxToasts);
        toRemove.forEach((t) => this.clearTimer(t.id));
        newToasts = newToasts.slice(newToasts.length - config.maxToasts);
      } else {
        // Don't add new toast
        return;
      }
    }

    this._toasts.set(newToasts);
  }

  private setTimer(toast: Toast): void {
    if (toast.duration <= 0 || toast.paused) return;

    const timer = window.setTimeout(() => {
      this.remove(toast.id);
    }, toast.duration);

    this.timers.set(toast.id, timer);
  }

  private clearTimer(id: string): void {
    const timer = this.timers.get(id);
    if (timer) {
      window.clearTimeout(timer);
      this.timers.delete(id);
    }
  }

  private findToast(id: string): Toast | undefined {
    return this._toasts().find((t) => t.id === id);
  }

  private updateToast(id: string, updates: Partial<Toast>): void {
    const currentToasts = this._toasts();
    const toastIndex = currentToasts.findIndex((t) => t.id === id);

    if (toastIndex === -1) return;

    const updatedToasts = [...currentToasts];
    updatedToasts[toastIndex] = { ...updatedToasts[toastIndex], ...updates };
    this._toasts.set(updatedToasts);
  }

  private generateId(): string {
    return `toast_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  /**
   * Utility methods for common toast patterns
   */

  /**
   * Show loading toast (returns ID for later update)
   */
  loading(message: string, config?: Partial<ToastConfig>): string {
    return this.info(message, {
      ...config,
      duration: 0,
      closeable: false,
      showProgress: false,
      icon: 'loader',
    });
  }

  /**
   * Update loading toast to success
   */
  loadingSuccess(id: string, message: string): void {
    this.remove(id);
    this.success(message);
  }

  /**
   * Update loading toast to error
   */
  loadingError(id: string, message: string): void {
    this.remove(id);
    this.error(message);
  }

  /**
   * Show toast for HTTP errors
   */
  httpError(status: number, message?: string): string {
    const defaultMessage = this.getHttpErrorMessage(status);
    return this.error(message || defaultMessage, {
      title: `Error ${status}`,
      actions:
        status >= 500
          ? [
              {
                label: 'Retry',
                action: () => window.location.reload(),
                style: 'primary',
              },
            ]
          : undefined,
    });
  }

  /**
   * Show toast for validation errors
   */
  validationError(errors: Record<string, string[]>): string {
    const firstError = Object.values(errors)[0]?.[0];
    const errorCount = Object.keys(errors).length;

    const message =
      errorCount > 1
        ? `${firstError} (and ${errorCount - 1} more)`
        : firstError || 'Validation failed';

    return this.error(message, {
      title: 'Validation Error',
    });
  }

  private getHttpErrorMessage(status: number): string {
    switch (status) {
      case 400:
        return 'Bad request - please check your input';
      case 401:
        return 'You need to log in to continue';
      case 403:
        return "You don't have permission for this action";
      case 404:
        return 'The requested resource was not found';
      case 422:
        return 'The data provided is invalid';
      case 429:
        return 'Too many requests - please try again later';
      case 500:
        return 'Server error - please try again later';
      case 502:
        return 'Service temporarily unavailable';
      case 503:
        return 'Service temporarily unavailable';
      case 504:
        return 'Request timeout - please try again';
      default:
        return 'An unexpected error occurred';
    }
  }
}
