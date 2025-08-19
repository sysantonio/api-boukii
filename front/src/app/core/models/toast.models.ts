/**
 * Toast notification models
 */

export type ToastType = 'success' | 'error' | 'warning' | 'info';

export type ToastPosition =
  | 'top-left'
  | 'top-right'
  | 'top-center'
  | 'bottom-left'
  | 'bottom-right'
  | 'bottom-center';

export interface ToastAction {
  label: string;
  action: () => void;
  style?: 'primary' | 'secondary' | 'destructive';
}

export interface ToastConfig {
  /**
   * Toast message
   */
  message: string;

  /**
   * Toast type (affects styling and icon)
   */
  type: ToastType;

  /**
   * Optional title
   */
  title?: string;

  /**
   * Auto-dismiss duration in milliseconds (0 = no auto-dismiss)
   */
  duration?: number;

  /**
   * Whether the toast can be manually closed
   */
  closeable?: boolean;

  /**
   * Position on screen
   */
  position?: ToastPosition;

  /**
   * Optional action buttons
   */
  actions?: ToastAction[];

  /**
   * HTML content (use with caution)
   */
  html?: boolean;

  /**
   * Additional CSS classes
   */
  className?: string;

  /**
   * Toast icon (overrides default type icon)
   */
  icon?: string;

  /**
   * Whether to show progress bar
   */
  showProgress?: boolean;

  /**
   * Unique identifier for deduplication
   */
  id?: string;

  /**
   * Prevent duplicate toasts with same ID
   */
  preventDuplicates?: boolean;
}

export interface Toast
  extends Required<Omit<ToastConfig, 'actions' | 'title' | 'className' | 'icon'>> {
  /**
   * Unique toast ID
   */
  id: string;

  /**
   * Creation timestamp
   */
  createdAt: Date;

  /**
   * Optional title
   */
  title?: string;

  /**
   * Optional action buttons
   */
  actions?: ToastAction[];

  /**
   * Additional CSS classes
   */
  className?: string;

  /**
   * Toast icon
   */
  icon?: string;

  /**
   * Whether toast is being dismissed
   */
  dismissing: boolean;

  /**
   * Remaining duration for auto-dismiss (ms)
   */
  remainingDuration?: number;

  /**
   * Whether toast is paused (e.g., on hover)
   */
  paused: boolean;
}

/**
 * Default configurations for different toast types
 */
export const DEFAULT_TOAST_CONFIG: Record<ToastType, Partial<ToastConfig>> = {
  success: {
    type: 'success',
    duration: 5000,
    closeable: true,
    position: 'top-right',
    showProgress: true,
    icon: 'check-circle',
  },
  error: {
    type: 'error',
    duration: 0, // Don't auto-dismiss errors
    closeable: true,
    position: 'top-right',
    showProgress: false,
    icon: 'x-circle',
  },
  warning: {
    type: 'warning',
    duration: 7000,
    closeable: true,
    position: 'top-right',
    showProgress: true,
    icon: 'alert-triangle',
  },
  info: {
    type: 'info',
    duration: 4000,
    closeable: true,
    position: 'top-right',
    showProgress: true,
    icon: 'info',
  },
};

/**
 * Toast queue configuration
 */
export interface ToastQueueConfig {
  maxToasts: number;
  removeOldest: boolean;
  stackNewest: 'top' | 'bottom';
}
