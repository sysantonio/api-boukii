import { Component, Input, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { TranslationService } from '../../../../core/services/translation.service';
import { TranslatePipe } from '../../../../shared/pipes/translate.pipe';

interface AuthShellFeature {
  icon: string;
  title: string;
  subtitle: string;
}

interface AuthShellFooterLink {
  label: string;
  routerLink: string;
}

@Component({
  selector: 'bk-auth-shell',
  standalone: true,
  imports: [CommonModule, TranslatePipe, RouterLink],
  templateUrl: './auth-shell.component.html',
  styleUrls: ['./auth-shell.component.scss'],
})
export class AuthShellComponent {
  @Input() title!: string;
  @Input() subtitle?: string;
  @Input() brandLine?: string;
  @Input() features?: Array<AuthShellFeature>;
  @Input() footerLinks?: Array<AuthShellFooterLink>;

  private readonly translation = inject(TranslationService);

  langOpen = false;
  supported: Array<{ code: 'es'|'en'|'fr'; label: string }> = [
    { code: 'es', label: 'ES · Español' },
    { code: 'en', label: 'EN · English' },
    { code: 'fr', label: 'FR · Français' },
  ];

  get currentLang(): 'es'|'en'|'fr' {
    const currentLang = this.translation.currentLanguage();
    return (['es','en','fr'].includes(currentLang) ? currentLang : 'es') as 'es'|'en'|'fr';
  }
  
  get currentLangLabel() {
    return this.supported.find(s => s.code === this.currentLang)?.label ?? 'ES · Español';
  }

  toggleLang() { 
    this.langOpen = !this.langOpen; 
  }
  
  isLang(code: 'es'|'en'|'fr') { 
    return this.currentLang === code; 
  }
  
  setLang(code: 'es'|'en'|'fr') {
    this.translation.setLanguage(code);
    this.langOpen = false;
  }

  // TrackBy functions for performance
  trackFeature(index: number, feature: AuthShellFeature): string {
    return feature.title;
  }

  trackFooterLink(index: number, link: AuthShellFooterLink): string {
    return link.routerLink;
  }
}