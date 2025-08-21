import type { Meta, StoryObj } from '@storybook/angular';
import { applicationConfig, componentWrapperDecorator } from '@storybook/angular';
import { AuthShellComponent } from './auth-shell.component';
import { TranslationService } from '@core/services/translation.service';
import { TranslatePipe } from '../../../../shared/pipes/translate.pipe';

class MockTranslationService {
  currentLanguage() {
    return 'en';
  }
  get(key: string): string {
    const translations: Record<string, string> = {
      'auth.login.title': 'Sign In',
      'auth.login.subtitle': 'Enter your credentials to continue',
      'auth.register.title': 'Create Account',
      'auth.register.subtitle': 'Join Boukii V5',
      'auth.forgot.title': 'Reset Password',
      'auth.forgot.subtitle': 'We\'ll send you a recovery link to your email',
      'auth.hero.feature1': 'Complete Management',
      'auth.hero.feature1Desc': 'Management, bookings and more.',
      'auth.hero.feature2': 'Multi-Season',
      'auth.hero.feature2Desc': 'Organize by seasons.',
      'auth.hero.feature3': 'Professionalization',
      'auth.hero.feature3Desc': 'Professional tools.',
      'common.loading': 'Loading...'
    };
    return translations[key] || key;
  }
}

const themeDecorator = (theme: 'light' | 'dark') =>
  componentWrapperDecorator(
    (story) => `
      <div data-theme="${theme}" style="min-height:100vh;background:var(--bg);padding:1rem;">
        ${story}
      </div>
    `
  );

const defaultFeatures = [
  { icon: 'i-grid', titleKey: 'auth.hero.feature1', descKey: 'auth.hero.feature1Desc' },
  { icon: 'i-clock', titleKey: 'auth.hero.feature2', descKey: 'auth.hero.feature2Desc' },
  { icon: 'i-trending-up', titleKey: 'auth.hero.feature3', descKey: 'auth.hero.feature3Desc' }
];

const meta: Meta<AuthShellComponent & { showFeatures: boolean }> = {
  title: 'Features/Auth/AuthShell',
  component: AuthShellComponent,
  decorators: [
    applicationConfig({
      providers: [
        { provide: TranslationService, useClass: MockTranslationService },
        TranslatePipe
      ]
    })
  ],
  parameters: {
    layout: 'fullscreen'
  },
  argTypes: {
    showFeatures: { control: { type: 'boolean' } }
  },
  args: {
    titleKey: 'auth.login.title',
    subtitleKey: 'auth.login.subtitle',
    features: defaultFeatures,
    showFeatures: true
  }
};
export default meta;

type Story = StoryObj<AuthShellComponent & { showFeatures: boolean }>;

const renderTemplate = (content: string) => (args: any) => ({
  props: args,
  template: `
    <bk-auth-shell
      [titleKey]="titleKey"
      [subtitleKey]="subtitleKey"
      [features]="showFeatures ? features : []">
      ${content}
    </bk-auth-shell>
  `
});

export const LoginLight: Story = {
  decorators: [themeDecorator('light')],
  render: renderTemplate(`
    <form class="auth-form">
      <label>Email <input type="email" /></label>
      <label>Password <input type="password" /></label>
      <button type="submit">Login</button>
    </form>
  `)
};

export const LoginDark: Story = {
  decorators: [themeDecorator('dark')],
  render: LoginLight.render
};

export const Register: Story = {
  decorators: [themeDecorator('light')],
  args: {
    titleKey: 'auth.register.title',
    subtitleKey: 'auth.register.subtitle'
  },
  render: renderTemplate(`
    <form class="auth-form">
      <label>Email <input type="email" /></label>
      <label>Password <input type="password" /></label>
      <label>Confirm Password <input type="password" /></label>
      <button type="submit">Register</button>
    </form>
  `)
};

export const ForgotPassword: Story = {
  decorators: [themeDecorator('light')],
  args: {
    titleKey: 'auth.forgot.title',
    subtitleKey: 'auth.forgot.subtitle'
  },
  render: renderTemplate(`
    <form class="auth-form">
      <label>Email <input type="email" /></label>
      <button type="submit">Send Reset Link</button>
    </form>
  `)
};

export const ValidationErrors: Story = {
  decorators: [themeDecorator('light')],
  render: renderTemplate(`
    <form class="auth-form">
      <label>Email
        <input type="email" class="invalid" />
        <div class="error">Email is required</div>
      </label>
      <label>Password
        <input type="password" class="invalid" />
        <div class="error">Password is too short</div>
      </label>
      <button type="submit" disabled>Login</button>
    </form>
  `)
};

export const LoadingState: Story = {
  decorators: [themeDecorator('light')],
  render: renderTemplate(`
    <form class="auth-form">
      <label>Email <input type="email" /></label>
      <label>Password <input type="password" /></label>
      <button type="submit" disabled>
        <span class="spinner" style="margin-right:8px;"></span>
        {{ 'common.loading' | translate }}
      </button>
    </form>
  `)
};

export const MobileView: Story = {
  decorators: [themeDecorator('light')],
  parameters: {
    viewport: { defaultViewport: 'mobile1' }
  },
  render: LoginLight.render
};

