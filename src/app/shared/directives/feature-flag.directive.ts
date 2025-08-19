import { Directive, Input, TemplateRef, ViewContainerRef, inject, effect } from '@angular/core';
import { EnvironmentService } from '@core/services/environment.service';
import { FeatureFlags } from '@core/models/environment.models';

/**
 * Feature Flag Structural Directive
 * Usage: *appFeatureFlag="'darkTheme'"
 * Usage: *appFeatureFlag="'analytics'; inverse: true"
 */
@Directive({
  selector: '[appFeatureFlag]',
  standalone: true,
})
export class FeatureFlagDirective {
  private readonly templateRef = inject(TemplateRef<unknown>);
  private readonly viewContainer = inject(ViewContainerRef);
  private readonly environmentService = inject(EnvironmentService);

  private feature: keyof FeatureFlags | null = null;
  private inverse = false;

  constructor() {
    // Watch for feature flag changes and update view
    effect(() => {
      this.updateView();
    });
  }

  @Input() set appFeatureFlag(feature: keyof FeatureFlags) {
    this.feature = feature;
    this.updateView();
  }

  @Input() set appFeatureFlagInverse(value: boolean) {
    this.inverse = value;
    this.updateView();
  }

  private updateView(): void {
    if (!this.feature) return;

    const isEnabled = this.environmentService.isFeatureEnabled(this.feature);
    const shouldShow = this.inverse ? !isEnabled : isEnabled;

    this.viewContainer.clear();
    if (shouldShow) {
      this.viewContainer.createEmbeddedView(this.templateRef);
    }
  }
}
