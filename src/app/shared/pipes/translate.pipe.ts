import { Pipe, PipeTransform, inject, OnDestroy } from '@angular/core';
import { TranslationService } from '@core/services/translation.service';
import { TranslationParams, TranslationContext } from '@core/models/i18n.models';
import { Subscription } from 'rxjs';

/**
 * Translation pipe that automatically updates when language changes
 *
 * Usage:
 * {{ 'nav.dashboard' | translate }}
 * {{ 'validation.minLength' | translate:{ min: 5 } }}
 * {{ 'messages.itemCount' | translate:{ count: items.length }:{ count: items.length } }}
 */
@Pipe({
  name: 'translate',
  standalone: true,
  pure: false, // Impure pipe to react to language changes
})
export class TranslatePipe implements PipeTransform, OnDestroy {
  private readonly translationService = inject(TranslationService);
  private lastKey = '';
  private lastParams: TranslationParams | undefined;
  private lastContext: TranslationContext | undefined;
  private lastLanguage = '';
  private lastValue = '';
  private readonly subscription: Subscription | null = null;

  transform(key: string, params?: TranslationParams, context?: TranslationContext): string {
    if (!key) {
      return '';
    }

    const currentLanguage = this.translationService.currentLanguage();

    // Check if we need to re-translate
    const shouldUpdate =
      this.lastKey !== key ||
      this.lastParams !== params ||
      this.lastContext !== context ||
      this.lastLanguage !== currentLanguage;

    if (shouldUpdate) {
      this.lastKey = key;
      this.lastParams = params;
      this.lastContext = context;
      this.lastLanguage = currentLanguage;
      this.lastValue = this.translationService.get(key, params, context);
    }

    return this.lastValue;
  }

  ngOnDestroy(): void {
    if (this.subscription) {
      this.subscription.unsubscribe();
    }
  }
}

/**
 * Instant translation pipe (pure pipe, doesn't auto-update)
 * Use when you're sure the language won't change during component lifecycle
 */
@Pipe({
  name: 'translateInstant',
  standalone: true,
  pure: true,
})
export class TranslateInstantPipe implements PipeTransform {
  private readonly translationService = inject(TranslationService);

  transform(key: string, params?: TranslationParams, context?: TranslationContext): string {
    if (!key) {
      return '';
    }

    return this.translationService.instant(key, params, context);
  }
}

/**
 * Date formatting pipe that respects current language
 */
@Pipe({
  name: 'translateDate',
  standalone: true,
  pure: false,
})
export class TranslateDatePipe implements PipeTransform {
  private readonly translationService = inject(TranslationService);
  private lastDate: Date | null = null;
  private lastFormat = '';
  private lastLanguage = '';
  private lastValue = '';

  transform(
    date: Date | string | number | null | undefined,
    format: 'short' | 'medium' | 'long' = 'medium'
  ): string {
    if (!date) {
      return '';
    }

    const dateObj = date instanceof Date ? date : new Date(date);
    const currentLanguage = this.translationService.currentLanguage();

    // Check if we need to re-format
    const shouldUpdate =
      this.lastDate?.getTime() !== dateObj.getTime() ||
      this.lastFormat !== format ||
      this.lastLanguage !== currentLanguage;

    if (shouldUpdate) {
      this.lastDate = dateObj;
      this.lastFormat = format;
      this.lastLanguage = currentLanguage;
      this.lastValue = this.translationService.formatDate(dateObj, format);
    }

    return this.lastValue;
  }
}

/**
 * Number formatting pipe that respects current language
 */
@Pipe({
  name: 'translateNumber',
  standalone: true,
  pure: false,
})
export class TranslateNumberPipe implements PipeTransform {
  private readonly translationService = inject(TranslationService);
  private lastValue: number | null = null;
  private lastType = '';
  private lastLanguage = '';
  private lastFormattedValue = '';

  transform(
    value: number | string | null | undefined,
    type: 'decimal' | 'currency' | 'percentage' = 'decimal'
  ): string {
    if (value === null || value === undefined || value === '') {
      return '';
    }

    const numValue = typeof value === 'number' ? value : parseFloat(String(value));
    if (isNaN(numValue)) {
      return String(value);
    }

    const currentLanguage = this.translationService.currentLanguage();

    // Check if we need to re-format
    const shouldUpdate =
      this.lastValue !== numValue ||
      this.lastType !== type ||
      this.lastLanguage !== currentLanguage;

    if (shouldUpdate) {
      this.lastValue = numValue;
      this.lastType = type;
      this.lastLanguage = currentLanguage;
      this.lastFormattedValue = this.translationService.formatNumber(numValue, type);
    }

    return this.lastFormattedValue;
  }
}

/**
 * Plural-aware translation pipe
 */
@Pipe({
  name: 'translatePlural',
  standalone: true,
  pure: false,
})
export class TranslatePluralPipe implements PipeTransform {
  private readonly translationService = inject(TranslationService);

  transform(key: string, count: number, params?: TranslationParams): string {
    if (!key) {
      return '';
    }

    const context: TranslationContext = { count };
    const allParams = { ...params, count };

    return this.translationService.get(key, allParams, context);
  }
}

/**
 * Safe translation pipe that never throws errors
 */
@Pipe({
  name: 'translateSafe',
  standalone: true,
  pure: false,
})
export class TranslateSafePipe implements PipeTransform {
  private readonly translationService = inject(TranslationService);

  transform(
    key: string,
    params?: TranslationParams,
    context?: TranslationContext,
    fallback?: string
  ): string {
    if (!key) {
      return fallback || '';
    }

    try {
      const translation = this.translationService.get(key, params, context);

      // If translation is the same as the key, it means translation wasn't found
      if (translation === key && fallback) {
        return fallback;
      }

      return translation;
    } catch (error) {
      console.warn('Translation error for key:', key, error);
      return fallback || key;
    }
  }
}
