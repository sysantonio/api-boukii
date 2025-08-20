import type { StorybookConfig } from '@storybook/angular';

const config: StorybookConfig = {
  framework: { name: '@storybook/angular', options: {} },
  core: { builder: '@storybook/builder-vite' },
  stories: ['../src/**/*.stories.@(ts|mdx)'],
  addons: ['@storybook/addon-essentials', '@storybook/addon-a11y'],
  viteFinal: async (config) => {
    const { default: tsconfigPaths } = await import('vite-tsconfig-paths');
    config.plugins = [...(config.plugins ?? []), tsconfigPaths()];
    return config;
  },
};
export default config;
