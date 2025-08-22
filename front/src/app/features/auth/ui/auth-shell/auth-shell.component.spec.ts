import { ComponentFixture, TestBed } from '@angular/core/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { CommonModule } from '@angular/common';
import { By } from '@angular/platform-browser';
import { Component } from '@angular/core';

import { AuthShellComponent } from './auth-shell.component';
import { TranslationService } from '../../../../core/services/translation.service';
import { TranslatePipe } from '../../../../shared/pipes/translate.pipe';

describe('AuthShellComponent', () => {
  let component: AuthShellComponent;
  let fixture: ComponentFixture<AuthShellComponent>;
  let mockTranslationService: any;

  const mockFeatures = [
    {
      icon: '<svg><path d="test"/></svg>',
      title: 'Test Feature 1',
      subtitle: 'Test subtitle 1'
    },
    {
      icon: '<svg><path d="test2"/></svg>',
      title: 'Test Feature 2', 
      subtitle: 'Test subtitle 2'
    }
  ];

  const mockFooterLinks = [
    { label: 'Test Link 1', routerLink: '/test1' },
    { label: 'Test Link 2', routerLink: '/test2' }
  ];

  beforeEach(async () => {
    mockTranslationService = {
      currentLanguage: jest.fn().mockReturnValue('es'),
      setLanguage: jest.fn(),
      get: jest.fn().mockReturnValue('Mocked translation'),
      instant: jest.fn().mockReturnValue('Mocked translation')
    };

    await TestBed.configureTestingModule({
      imports: [
        CommonModule,
        RouterTestingModule,
        AuthShellComponent,
        TranslatePipe
      ],
      providers: [
        { provide: TranslationService, useValue: mockTranslationService }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(AuthShellComponent);
    component = fixture.componentInstance;
  });

  describe('Component Initialization', () => {
    it('should create', () => {
      expect(component).toBeTruthy();
    });

    it('should have default language state', () => {
      expect(component.langOpen).toBe(false);
      expect(component.currentLang).toBe('es');
    });

    it('should have supported languages', () => {
      expect(component.supported).toEqual([
        { code: 'es', label: 'ES · Español' },
        { code: 'en', label: 'EN · English' },
        { code: 'fr', label: 'FR · Français' },
      ]);
    });
  });

  describe('Input Properties', () => {
    beforeEach(() => {
      component.title = 'Test Title';
      component.subtitle = 'Test Subtitle';
      component.brandLine = 'Test Brand Line';
      component.features = mockFeatures;
      component.footerLinks = mockFooterLinks;
      fixture.detectChanges();
    });

    it('should display title in hero section', () => {
      const titleElement = fixture.debugElement.query(By.css('.hero__title'));
      expect(titleElement.nativeElement.textContent).toBe('Test Title');
    });

    it('should display subtitle when provided', () => {
      const subtitleElement = fixture.debugElement.query(By.css('.hero__subtitle'));
      expect(subtitleElement.nativeElement.textContent).toBe('Test Subtitle');
    });

    it('should display brandLine when provided', () => {
      const brandLineElement = fixture.debugElement.query(By.css('.hero__brandline'));
      expect(brandLineElement.nativeElement.textContent).toBe('Test Brand Line');
    });

    it('should not display subtitle when not provided', () => {
      component.subtitle = undefined;
      fixture.detectChanges();
      
      const subtitleElement = fixture.debugElement.query(By.css('.hero__subtitle'));
      expect(subtitleElement).toBeFalsy();
    });

    it('should not display brandLine when not provided', () => {
      component.brandLine = undefined;
      fixture.detectChanges();
      
      const brandLineElement = fixture.debugElement.query(By.css('.hero__brandline'));
      expect(brandLineElement).toBeFalsy();
    });
  });

  describe('Features Display', () => {
    beforeEach(() => {
      component.features = mockFeatures;
      fixture.detectChanges();
    });

    it('should display features when provided', () => {
      const featuresContainer = fixture.debugElement.query(By.css('.hero__features'));
      expect(featuresContainer).toBeTruthy();

      const featureItems = fixture.debugElement.queryAll(By.css('.hero__features li'));
      expect(featureItems.length).toBe(2);
    });

    it('should render feature icons as innerHTML', () => {
      const featureIcons = fixture.debugElement.queryAll(By.css('.feature__icon'));
      expect(featureIcons[0].nativeElement.innerHTML).toContain('<svg><path d="test"/></svg>');
      expect(featureIcons[1].nativeElement.innerHTML).toContain('<svg><path d="test2"/></svg>');
    });

    it('should display feature titles and subtitles', () => {
      const featureTitles = fixture.debugElement.queryAll(By.css('.feature__title'));
      const featureSubtitles = fixture.debugElement.queryAll(By.css('.feature__subtitle'));

      expect(featureTitles[0].nativeElement.textContent).toBe('Test Feature 1');
      expect(featureSubtitles[0].nativeElement.textContent).toBe('Test subtitle 1');
      expect(featureTitles[1].nativeElement.textContent).toBe('Test Feature 2');
      expect(featureSubtitles[1].nativeElement.textContent).toBe('Test subtitle 2');
    });

    it('should not display features section when features not provided', () => {
      component.features = undefined;
      fixture.detectChanges();

      const featuresContainer = fixture.debugElement.query(By.css('.hero__features'));
      expect(featuresContainer).toBeFalsy();
    });

    it('should use trackBy function for features performance', () => {
      const trackResult = component.trackFeature(0, mockFeatures[0]);
      expect(trackResult).toBe('Test Feature 1');
    });
  });

  describe('Footer Links', () => {
    beforeEach(() => {
      component.footerLinks = mockFooterLinks;
      fixture.detectChanges();
    });

    it('should display footer links when provided', () => {
      const footerContainer = fixture.debugElement.query(By.css('.card__footer'));
      expect(footerContainer).toBeTruthy();

      const footerLinkElements = fixture.debugElement.queryAll(By.css('.footer-link'));
      expect(footerLinkElements.length).toBe(2);
    });

    it('should set correct routerLink attributes', () => {
      const footerLinkElements = fixture.debugElement.queryAll(By.css('.footer-link'));
      
      expect(footerLinkElements[0].nativeElement.getAttribute('ng-reflect-router-link')).toBe('/test1');
      expect(footerLinkElements[1].nativeElement.getAttribute('ng-reflect-router-link')).toBe('/test2');
    });

    it('should display correct link labels', () => {
      const footerLinkElements = fixture.debugElement.queryAll(By.css('.footer-link'));
      
      expect(footerLinkElements[0].nativeElement.textContent.trim()).toBe('Test Link 1');
      expect(footerLinkElements[1].nativeElement.textContent.trim()).toBe('Test Link 2');
    });

    it('should not display footer section when footerLinks not provided', () => {
      component.footerLinks = undefined;
      fixture.detectChanges();

      const footerContainer = fixture.debugElement.query(By.css('.card__footer'));
      expect(footerContainer).toBeFalsy();
    });

    it('should use trackBy function for footer links performance', () => {
      const trackResult = component.trackFooterLink(0, mockFooterLinks[0]);
      expect(trackResult).toBe('/test1');
    });
  });

  describe('Language Selection', () => {
    beforeEach(() => {
      fixture.detectChanges();
    });

    it('should display current language label', () => {
      const currentLangLabel = component.currentLangLabel;
      expect(currentLangLabel).toBe('ES · Español');
    });

    it('should toggle language dropdown', () => {
      expect(component.langOpen).toBe(false);
      
      component.toggleLang();
      expect(component.langOpen).toBe(true);
      
      component.toggleLang();
      expect(component.langOpen).toBe(false);
    });

    it('should set language and close dropdown', () => {
      component.langOpen = true;
      
      component.setLang('en');
      
      expect(mockTranslationService.setLanguage).toHaveBeenCalledWith('en');
      expect(component.langOpen).toBe(false);
    });

    it('should check current language correctly', () => {
      mockTranslationService.currentLanguage.mockReturnValue('en');
      
      expect(component.isLang('en')).toBe(true);
      expect(component.isLang('es')).toBe(false);
      expect(component.isLang('fr')).toBe(false);
    });

    it('should fallback to Spanish for unsupported languages', () => {
      mockTranslationService.currentLanguage.mockReturnValue('unsupported' as any);
      
      expect(component.currentLang).toBe('es');
    });

    it('should display language menu when open', () => {
      component.langOpen = true;
      fixture.detectChanges();

      const langMenu = fixture.debugElement.query(By.css('.langmenu'));
      expect(langMenu).toBeTruthy();

      const langButtons = fixture.debugElement.queryAll(By.css('.langmenu button'));
      expect(langButtons.length).toBe(3);
    });

    it('should not display language menu when closed', () => {
      component.langOpen = false;
      fixture.detectChanges();

      const langMenu = fixture.debugElement.query(By.css('.langmenu'));
      expect(langMenu).toBeFalsy();
    });
  });

  describe('Accessibility', () => {
    beforeEach(() => {
      fixture.detectChanges();
    });

    it('should have proper ARIA attributes on language toggle', () => {
      const langButton = fixture.debugElement.query(By.css('.lang'));
      
      expect(langButton.nativeElement.getAttribute('aria-haspopup')).toBe('menu');
      expect(langButton.nativeElement.getAttribute('aria-expanded')).toBe('false');
    });

    it('should update aria-expanded when language menu opens', () => {
      component.langOpen = true;
      fixture.detectChanges();

      const langButton = fixture.debugElement.query(By.css('.lang'));
      expect(langButton.nativeElement.getAttribute('aria-expanded')).toBe('true');
    });

    it('should have proper role attributes on language menu', () => {
      component.langOpen = true;
      fixture.detectChanges();

      const langMenu = fixture.debugElement.query(By.css('.langmenu'));
      expect(langMenu.nativeElement.getAttribute('role')).toBe('menu');
    });

    it('should have menuitemradio role on language options', () => {
      component.langOpen = true;
      fixture.detectChanges();

      const langButtons = fixture.debugElement.queryAll(By.css('.langmenu button'));
      langButtons.forEach(button => {
        expect(button.nativeElement.getAttribute('role')).toBe('menuitemradio');
      });
    });

    it('should have proper aria-checked attributes', () => {
      component.langOpen = true;
      fixture.detectChanges();

      const esButton = fixture.debugElement.query(By.css('button[aria-checked="true"]'));
      expect(esButton).toBeTruthy();
    });
  });

  describe('Content Projection', () => {
    it('should project content into card section', () => {
      const testContent = '<div class="test-content">Test Content</div>';
      
      // Create a test host component that includes the projected content
      @Component({
        template: `
          <bk-auth-shell>
            ${testContent}
          </bk-auth-shell>
        `
      })
      class TestHostComponent {}

      TestBed.configureTestingModule({
        imports: [AuthShellComponent, TranslatePipe],
        declarations: [TestHostComponent],
        providers: [
          { provide: TranslationService, useValue: mockTranslationService }
        ]
      });

      const hostFixture = TestBed.createComponent(TestHostComponent);
      hostFixture.detectChanges();

      const projectedContent = hostFixture.debugElement.query(By.css('.test-content'));
      expect(projectedContent).toBeTruthy();
      expect(projectedContent.nativeElement.textContent).toBe('Test Content');
    });
  });

  describe('Brand Elements', () => {
    beforeEach(() => {
      fixture.detectChanges();
    });

    it('should display brand logo', () => {
      const logoElements = fixture.debugElement.queryAll(By.css('.auth__logo, .hero__logo'));
      expect(logoElements.length).toBeGreaterThan(0);
    });

    it('should display V5 tag', () => {
      const tagElement = fixture.debugElement.query(By.css('.auth__tag'));
      expect(tagElement).toBeTruthy();
      expect(tagElement.nativeElement.textContent).toBe('V5');
    });

    it('should have proper logo alt attributes', () => {
      const headerLogo = fixture.debugElement.query(By.css('.auth__logo'));
      const heroLogo = fixture.debugElement.query(By.css('.hero__logo'));
      
      expect(headerLogo.nativeElement.getAttribute('alt')).toBe('boukii');
      expect(heroLogo.nativeElement.getAttribute('alt')).toBe('');
    });
  });
});