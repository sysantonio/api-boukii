// src/app/core/services/config.service.ts
import { Injectable } from '@angular/core';
import type { RuntimeEnvironment } from '../models/environment.models';
// TIP: Ensure your tsconfig has baseUrl 'src' so this import resolves.
// If not, adjust the path to '../../environments/environment' or your alias.
import { environment } from '@environments/environment';

@Injectable({ providedIn: 'root' })
export class ConfigService {
  private runtime?: RuntimeEnvironment;

  /** Called from APP_INITIALIZER once runtime-config.json is loaded (optional) */
  setRuntimeConfig(cfg: RuntimeEnvironment): void {
    this.runtime = cfg;
  }

  /** Versioned API base URL (e.g. http://api-boukii.test/api/v5) */
  getApiBaseUrl(): string {
    const fromRuntime = this.runtime?.api?.baseUrl;
    const fromEnv = (environment as any)?.apiUrl || (environment as any)?.api?.baseUrl;
    const raw = (fromRuntime || fromEnv || 'http://localhost:8000').toString();
    const base = this.stripTrailing(raw);

    // If the base already includes '/api/vX', respect it as-is.
    if (/\/api\/v\d+$/i.test(base)) {
      return base;
    }

    const version =
      this.runtime?.api?.version ||
      (environment as any)?.apiVersion ||
      (environment as any)?.api?.version ||
      'v5';

    return `${base}/api/${this.stripSlashes(version)}`;
  }

  getApiTimeout(): number {
    return (
      this.runtime?.api?.timeout ||
      (environment as any)?.apiTimeout ||
      30000
    );
  }

  // --- helpers ---
  private stripTrailing(v: string): string {
    return v.replace(/\/+$/, '');
  }
  private stripLeading(v: string): string {
    return v.replace(/^\/+/, '');
  }
  private stripSlashes(v: string): string {
    return this.stripLeading(this.stripTrailing(v));
  }
}
