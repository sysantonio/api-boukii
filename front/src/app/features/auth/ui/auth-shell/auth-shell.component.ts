import { Component, Input, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslationService } from '../../../../core/services/translation.service';
import { TranslatePipe } from '../../../../shared/pipes/translate.pipe';

/*interface AuthShellFeature {
  icon: string;
  titleKey: string;
  descKey: string;
}*/

@Component({
  selector: 'bk-auth-shell',
  standalone: true,
  imports: [CommonModule, TranslatePipe],
  templateUrl: './auth-shell.component.html',
  styleUrls: ['./auth-shell.component.scss'],
})
export class AuthShellComponent {
  @Input() titleKey = 'auth.welcome.title';
  @Input() subtitleKey = 'auth.welcome.subtitle';
  @Input() features: Array<{ title: string; subtitle: string }> = [];

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
}

