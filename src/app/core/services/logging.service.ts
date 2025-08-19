import { Injectable, inject } from '@angular/core';
import { AuthStore } from '../stores/auth.store';
import { ErrorLogEntry, ErrorContext, ErrorSeverity, ErrorResponse } from '../models/error.models';
import { LogLevel } from '../models/environment.models';
import { ApiService } from './api.service';

@Injectable({ providedIn: 'root' })
export class LoggingService {
  private readonly auth = inject(AuthStore);
  private readonly logBuffer: ErrorLogEntry[] = [];
  private readonly maxBufferSize = 100;
  private currentLogLevel: LogLevel = 'info';
  private readonly api = inject(ApiService);

  /**
   * Log an error with full context
   */
  logError(
    message: string,
    error: ErrorResponse,
    context: Partial<ErrorContext>,
    userFriendlyMessage?: string
  ): void {
    const logEntry: ErrorLogEntry = {
      id: this.generateId(),
      timestamp: new Date(),
      level: 'error',
      message,
      context: this.buildErrorContext(context),
      error,
      handled: true,
      userFriendlyMessage,
    };

    this.addToBuffer(logEntry);
    this.writeToConsole(logEntry);

    // In production, you would send this to your logging service
    if (this.isProduction() && this.shouldSendToServer(logEntry)) {
      this.sendToLoggingService(logEntry);
    }
  }

  /**
   * Log a warning
   */
  logWarning(message: string, context?: Partial<ErrorContext>): void {
    const logEntry: ErrorLogEntry = {
      id: this.generateId(),
      timestamp: new Date(),
      level: 'warn',
      message,
      context: this.buildErrorContext(context || {}),
      error: { type: 'warning', title: 'Warning', detail: message, code: 'WARNING' },
      handled: true,
    };

    this.addToBuffer(logEntry);
    console.warn(`[${logEntry.timestamp.toISOString()}] ${message}`, logEntry.context);
  }

  /**
   * Log info message
   */
  logInfo(message: string, context?: Partial<ErrorContext>): void {
    const logEntry: ErrorLogEntry = {
      id: this.generateId(),
      timestamp: new Date(),
      level: 'info',
      message,
      context: this.buildErrorContext(context || {}),
      error: { type: 'info', title: 'Info', detail: message, code: 'INFO' },
      handled: true,
    };

    this.addToBuffer(logEntry);
    console.info(`[${logEntry.timestamp.toISOString()}] ${message}`, logEntry.context);
  }

  /**
   * Get recent logs (for debugging or error reports)
   */
  getRecentLogs(count = 50): ErrorLogEntry[] {
    return this.logBuffer.slice(-count);
  }

  /**
   * Clear log buffer
   */
  clearLogs(): void {
    this.logBuffer.length = 0;
  }

  /**
   * Export logs for support/debugging
   */
  exportLogs(): string {
    return JSON.stringify(this.logBuffer, null, 2);
  }

  private buildErrorContext(partialContext: Partial<ErrorContext>): ErrorContext {
    const currentUser = this.auth.user();

    return {
      url: partialContext.url || window.location.href,
      method: partialContext.method || 'UNKNOWN',
      userId: currentUser?.id,
      sessionId: this.getSessionId(),
      userAgent: navigator.userAgent,
      timestamp: new Date(),
      requestId: partialContext.requestId || this.generateId(),
      severity: partialContext.severity || ErrorSeverity.MEDIUM,
    };
  }

  private addToBuffer(logEntry: ErrorLogEntry): void {
    this.logBuffer.push(logEntry);

    // Keep buffer size manageable
    if (this.logBuffer.length > this.maxBufferSize) {
      this.logBuffer.shift();
    }
  }

  private writeToConsole(logEntry: ErrorLogEntry): void {
    const timestamp = logEntry.timestamp.toISOString();
    const prefix = `[${timestamp}] [${logEntry.level.toUpperCase()}]`;

    switch (logEntry.level) {
      case 'error':
        console.error(`${prefix} ${logEntry.message}`, {
          error: logEntry.error,
          context: logEntry.context,
          userMessage: logEntry.userFriendlyMessage,
        });
        break;
      case 'warn':
        console.warn(`${prefix} ${logEntry.message}`, logEntry.context);
        break;
      case 'info':
        console.info(`${prefix} ${logEntry.message}`, logEntry.context);
        break;
    }
  }

  private shouldSendToServer(logEntry: ErrorLogEntry): boolean {
    // Only send errors and high-severity warnings to server
    return (
      logEntry.level === 'error' ||
      (logEntry.level === 'warn' && logEntry.context.severity === ErrorSeverity.HIGH)
    );
  }

  private async sendToLoggingService(logEntry: ErrorLogEntry): Promise<void> {
    try {
      // In a real app, send to your logging endpoint
      await this.api.post('/logs', JSON.stringify(logEntry))

    } catch (error) {
      // Fallback: don't create infinite loops
      console.error('Failed to send log to server:', error);
    }
  }

  private generateId(): string {
    return `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
  }

  private getSessionId(): string {
    // In a real app, this might come from a session service
    let sessionId = sessionStorage.getItem('session_id');
    if (!sessionId) {
      sessionId = this.generateId();
      sessionStorage.setItem('session_id', sessionId);
    }
    return sessionId;
  }

  private isProduction(): boolean {
    return false;
/*
    return !['development', 'local'].includes(window.location.hostname);
*/
  }

  /**
   * Set the current log level
   */
  setLogLevel(level: LogLevel): void {
    this.currentLogLevel = level;
    console.log(`Log level set to: ${level}`);
  }

  /**
   * Get the current log level
   */
  getLogLevel(): LogLevel {
    return this.currentLogLevel;
  }

  /**
   * Check if a log level should be output
   */
  private shouldLog(level: 'debug' | 'info' | 'warn' | 'error'): boolean {
    const levels: Record<LogLevel, number> = {
      debug: 0,
      info: 1,
      warn: 2,
      error: 3,
      none: 4,
    };

    const currentLevelNumber = levels[this.currentLogLevel];
    const logLevelNumber = levels[level];

    return logLevelNumber >= currentLevelNumber;
  }
}
