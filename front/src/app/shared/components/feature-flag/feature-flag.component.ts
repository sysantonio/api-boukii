import { Component, Input, inject, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EnvironmentService } from '@core/services/environment.service';
import { FeatureFlags } from '@core/models/environment.models';

/**
 * Feature Flag Component
 * Conditionally shows/hides content based on feature flags
 */
@Component({
  selector: 'app-feature-flag',
  standalone: true,
  imports: [CommonModule],
  template: `
    @if (isEnabled()) {
      <ng-content></ng-content>
    } @else if (fallbackContent) {
      <div class="feature-flag-fallback">
        <ng-content select="[slot=fallback]"></ng-content>
      </div>
    }
  `,
  styles: [
    `
      .feature-flag-fallback {
        opacity: 0.6;
        pointer-events: none;
      }
    `,
  ],
})
export class FeatureFlagComponent {
  private readonly environmentService = inject(EnvironmentService);

  @Input({ required: true }) feature!: keyof FeatureFlags;
  @Input() fallbackContent = false;
  @Input() inverse = false;

  readonly isEnabled = computed(() => {
    const enabled = this.environmentService.isFeatureEnabled(this.feature);
    return this.inverse ? !enabled : enabled;
  });
}
