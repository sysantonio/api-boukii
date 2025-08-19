import type { Meta, StoryObj } from '@storybook/angular';
import { moduleMetadata, applicationConfig } from '@storybook/angular';
import { provideAnimations } from '@angular/platform-browser/animations';
import { provideHttpClient } from '@angular/common/http';
import { LanguageSelectorComponent } from './language-selector.component';

const meta: Meta<LanguageSelectorComponent> = {
  title: 'Shared/LanguageSelector',
  component: LanguageSelectorComponent,
  decorators: [
    moduleMetadata({
      imports: [LanguageSelectorComponent],
    }),
    applicationConfig({
      providers: [provideAnimations(), provideHttpClient()],
    }),
  ],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: `
Language Selector Component allows users to switch between available languages.

## Features
- Multi-language support (English/Spanish)
- Dropdown interface with flag icons
- Real-time language switching
- Loading states during language changes
- Accessible keyboard navigation
- Responsive design

## Usage
\`\`\`html
<app-language-selector></app-language-selector>
\`\`\`
        `,
      },
    },
  },
  argTypes: {
    // Component uses internal state management
  },
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<LanguageSelectorComponent>;

export const Default: Story = {
  render: () => ({
    template: `
      <div class="story-container">
        <div class="component-showcase">
          <h3>Language Selector</h3>
          <p>Select your preferred language</p>
          <app-language-selector></app-language-selector>
        </div>
      </div>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Default language selector with English and Spanish options.',
      },
    },
  },
};

export const InToolbar: Story = {
  render: () => ({
    template: `
      <div class="story-container">
        <div style="background: var(--color-surface-elevated); padding: 1rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; min-width: 400px;">
          <div>
            <h4 style="margin: 0; font-size: 1rem;">Boukii Admin</h4>
            <p style="margin: 0; font-size: 0.875rem; color: var(--color-text-secondary);">Admin Panel</p>
          </div>
          
          <div style="display: flex; gap: 1rem; align-items: center;">
            <app-language-selector></app-language-selector>
            <button style="padding: 0.5rem; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-surface); color: var(--color-text-primary);">
              Settings
            </button>
          </div>
        </div>
      </div>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Language selector integrated in a toolbar layout.',
      },
    },
  },
};

export const WithThemeToggle: Story = {
  render: () => ({
    template: `
      <div class="story-container">
        <div style="background: var(--color-surface); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--color-border);">
          <h3>User Preferences</h3>
          <div style="display: grid; gap: 1.5rem;">
            <div>
              <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--color-text-primary);">
                Language
              </label>
              <app-language-selector></app-language-selector>
            </div>
            
            <div>
              <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--color-text-primary);">
                Theme
              </label>
              <app-theme-toggle></app-theme-toggle>
            </div>
          </div>
        </div>
      </div>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Language selector combined with theme toggle in a settings panel.',
      },
    },
  },
};

export const States: Story = {
  render: () => ({
    template: `
      <div class="story-container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
          <div class="component-showcase">
            <h4>Default State</h4>
            <app-language-selector></app-language-selector>
          </div>
          
          <div class="component-showcase">
            <h4>Compact Layout</h4>
            <div style="max-width: 120px;">
              <app-language-selector></app-language-selector>
            </div>
          </div>
          
          <div class="component-showcase">
            <h4>In Dark Background</h4>
            <div style="background: var(--color-surface-elevated); padding: 1rem; border-radius: 8px;">
              <app-language-selector></app-language-selector>
            </div>
          </div>
        </div>
      </div>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Different states and layouts of the language selector.',
      },
    },
  },
};

export const Responsive: Story = {
  render: () => ({
    template: `
      <div class="story-container">
        <div style="resize: horizontal; overflow: auto; border: 2px dashed var(--color-border); padding: 1rem; min-width: 200px; max-width: 100%;">
          <p style="margin: 0 0 1rem 0; font-size: 0.875rem; color: var(--color-text-secondary);">
            Resize this container horizontally to see responsive behavior
          </p>
          <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
            <span>Language:</span>
            <app-language-selector></app-language-selector>
          </div>
        </div>
      </div>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Responsive behavior of the language selector in constrained spaces.',
      },
    },
  },
};

export const Interactive: Story = {
  render: () => ({
    template: `
      <div class="story-container">
        <div class="component-showcase">
          <h3>Interactive Language Demo</h3>
          <p>Change the language and observe the interface updates</p>
          
          <div style="margin: 1rem 0;">
            <app-language-selector></app-language-selector>
          </div>
          
          <div style="margin-top: 2rem; padding: 1rem; background: var(--color-surface-secondary); border-radius: 8px;">
            <h4>Sample Content</h4>
            <p>{{ 'dashboard.welcome' | translate }}</p>
            <p>{{ 'nav.dashboard' | translate }}</p>
            <p>{{ 'actions.save' | translate }} / {{ 'actions.cancel' | translate }}</p>
          </div>
          
          <div style="margin-top: 1rem; font-size: 0.875rem; color: var(--color-text-secondary);">
            Note: In a real application, all text would be translated based on the selected language.
          </div>
        </div>
      </div>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Interactive demo showing how language changes affect translated content.',
      },
    },
  },
};
