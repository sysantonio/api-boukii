import type { Preview } from "@storybook/angular";

// No need for Angular Material CSS import - we use our own design tokens

const preview: Preview = {
  parameters: {
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/,
      },
    },
    docs: {
      story: {
        inline: true,
      },
    },
    backgrounds: {
      disable: true, // We use our own theme system
    },
  },
  globalTypes: {
    theme: {
      description: "Global theme for components",
      defaultValue: "light",
      toolbar: {
        title: "Theme",
        icon: "paintbrush",
        items: [
          { value: "light", title: "Light", icon: "sun" },
          { value: "dark", title: "Dark", icon: "moon" },
          { value: "system", title: "System", icon: "browser" },
        ],
        dynamicTitle: true,
      },
    },
  },
  decorators: [
    (story, context) => {
      const theme = context.globals['theme'] || "light";
      
      // Apply theme to document
      document.documentElement.setAttribute("data-theme", theme);
      
      // Inject CSS tokens if not already present
      if (!document.getElementById('storybook-tokens')) {
        const styleElement = document.createElement('style');
        styleElement.id = 'storybook-tokens';
        const cssContent = `:root {
--color-primary-50: #eff6ff;
--color-primary-500: #3b82f6;
--color-primary-600: #2563eb;
--color-primary-700: #1d4ed8;
--color-success-500: #22c55e;
--color-warning-500: #f59e0b;
--color-error-500: #ef4444;
--color-info-500: #3b82f6;
--color-neutral-0: #ffffff;
--color-neutral-50: #fafafa;
--color-neutral-100: #f5f5f5;
--color-neutral-200: #e5e5e5;
--color-neutral-300: #d4d4d4;
--color-neutral-500: #737373;
--color-neutral-700: #404040;
--color-neutral-900: #171717;
--color-neutral-950: #0a0a0a;
--color-background: var(--color-neutral-0);
--color-text-primary: var(--color-neutral-900);
--color-text-secondary: var(--color-neutral-700);
--font-family-sans: ui-sans-serif, system-ui, sans-serif;
--font-size-sm: 0.875rem;
--font-size-base: 1rem;
--font-size-lg: 1.125rem;
--font-size-xl: 1.25rem;
--spacing-1: 0.25rem;
--spacing-2: 0.5rem;
--spacing-3: 0.75rem;
--spacing-4: 1rem;
--spacing-6: 1.5rem;
--spacing-8: 2rem;
}
[data-theme='dark'] {
--color-background: var(--color-neutral-950);
--color-text-primary: var(--color-neutral-50);
--color-text-secondary: var(--color-neutral-300);
}
body {
background-color: var(--color-background);
color: var(--color-text-primary);
font-family: var(--font-family-sans);
transition: background-color 0.2s ease, color 0.2s ease;
}
.storybook-wrapper {
background-color: var(--color-background);
color: var(--color-text-primary);
min-height: 100vh;
padding: 1rem;
transition: background-color 0.2s ease, color 0.2s ease;
}`;
        styleElement.innerHTML = cssContent;
        document.head.appendChild(styleElement);
      }
      
      // Return the original story - let Storybook handle the wrapping
      return story();
    },
  ],
};

export default preview;
