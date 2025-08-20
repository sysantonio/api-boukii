import { LanguageConfig, SupportedLanguage } from '../models/i18n.models';

/**
 * Language configurations with locale-specific settings
 */
export const LANGUAGE_CONFIGS: Record<SupportedLanguage, LanguageConfig> = {
  en: {
    code: 'en',
    name: 'English',
    nativeName: 'English',
    flag: '🇺🇸',
    direction: 'ltr',
    dateFormat: 'MM/dd/yyyy',
    timeFormat: 'h:mm a',
    currencyCode: 'USD',
    currencySymbol: '$',
    thousandsSeparator: ',',
    decimalSeparator: '.',
  },
  es: {
    code: 'es',
    name: 'Spanish',
    nativeName: 'Español',
    flag: '🇪🇸',
    direction: 'ltr',
    dateFormat: 'dd/MM/yyyy',
    timeFormat: 'HH:mm',
    currencyCode: 'EUR',
    currencySymbol: '€',
    thousandsSeparator: '.',
    decimalSeparator: ',',
  },
  fr: {
    code: 'fr',
    name: 'French',
    nativeName: 'Français',
    flag: '🇫🇷',
    direction: 'ltr',
    dateFormat: 'dd/MM/yyyy',
    timeFormat: 'HH:mm',
    currencyCode: 'EUR',
    currencySymbol: '€',
    thousandsSeparator: ' ',
    decimalSeparator: ',',
  },
  it: {
    code: 'it',
    name: 'Italian',
    nativeName: 'Italiano',
    flag: '🇮🇹',
    direction: 'ltr',
    dateFormat: 'dd/MM/yyyy',
    timeFormat: 'HH:mm',
    currencyCode: 'EUR',
    currencySymbol: '€',
    thousandsSeparator: '.',
    decimalSeparator: ',',
  },
  de: {
    code: 'de',
    name: 'German',
    nativeName: 'Deutsch',
    flag: '🇩🇪',
    direction: 'ltr',
    dateFormat: 'dd.MM.yyyy',
    timeFormat: 'HH:mm',
    currencyCode: 'EUR',
    currencySymbol: '€',
    thousandsSeparator: '.',
    decimalSeparator: ',',
  },
};

/**
 * Default language
 */
export const DEFAULT_LANGUAGE: SupportedLanguage = 'en';

/**
 * Fallback language for missing translations
 */
export const FALLBACK_LANGUAGE: SupportedLanguage = 'en';

/**
 * Storage key for selected language
 */
export const LANGUAGE_STORAGE_KEY = 'language';

/**
 * Get all available languages as array
 */
export function getAvailableLanguages(): LanguageConfig[] {
  return Object.values(LANGUAGE_CONFIGS);
}

/**
 * Get language config by code
 */
export function getLanguageConfig(code: SupportedLanguage): LanguageConfig {
  return LANGUAGE_CONFIGS[code] || LANGUAGE_CONFIGS[DEFAULT_LANGUAGE];
}

/**
 * Check if language is supported
 */
export function isLanguageSupported(code: string): code is SupportedLanguage {
  return Object.keys(LANGUAGE_CONFIGS).includes(code);
}

/**
 * Detect browser language and return closest supported match
 */
export function detectBrowserLanguage(): SupportedLanguage {
  if (typeof navigator === 'undefined') {
    return DEFAULT_LANGUAGE;
  }

  const browserLang = navigator.language;
  const browserLangCode = browserLang.split('-')[0];

  // Check exact match first
  if (isLanguageSupported(browserLang)) {
    return browserLang as SupportedLanguage;
  }

  // Check language code match
  if (isLanguageSupported(browserLangCode)) {
    return browserLangCode as SupportedLanguage;
  }

  // Check all browser languages
  for (const lang of navigator.languages) {
    const langCode = lang.split('-')[0];
    if (isLanguageSupported(langCode)) {
      return langCode as SupportedLanguage;
    }
  }

  return DEFAULT_LANGUAGE;
}
