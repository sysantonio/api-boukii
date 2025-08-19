import { addons } from '@storybook/manager-api';
import { create } from '@storybook/theming/create';

const theme = create({
  base: 'light',
  brandTitle: 'Boukii Admin V5',
  brandUrl: 'https://boukii.app',
  brandImage: undefined,
  brandTarget: '_self',

  colorPrimary: '#3b82f6',
  colorSecondary: '#6366f1',

  // UI
  appBg: '#ffffff',
  appContentBg: '#ffffff',
  appBorderColor: '#e5e7eb',
  appBorderRadius: 8,

  // Typography
  fontBase: '"Inter", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif',
  fontCode: '"Fira Code", "SF Mono", Monaco, Inconsolata, "Roboto Mono", monospace',

  // Text colors
  textColor: '#1f2937',
  textInverseColor: '#ffffff',

  // Toolbar default and active colors
  barTextColor: '#6b7280',
  barSelectedColor: '#3b82f6',
  barBg: '#f9fafb',

  // Form colors
  inputBg: '#ffffff',
  inputBorder: '#d1d5db',
  inputTextColor: '#1f2937',
  inputBorderRadius: 6,
});

addons.setConfig({
  theme,
  panelPosition: 'bottom',
  sidebar: {
    showRoots: true,
    collapsedRoots: ['setup'],
  },
  toolbar: {
    title: { hidden: false },
    zoom: { hidden: false },
    eject: { hidden: false },
    copy: { hidden: false },
    fullscreen: { hidden: false },
  },
});
