import { Meta, StoryObj } from '@storybook/angular';
import { ButtonComponent } from './button.component';

const meta: Meta<ButtonComponent> = {
  title: 'UI/Atoms/Button',
  component: ButtonComponent,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: 'A customizable button component with various variants, sizes, and states.'
      }
    }
  },
  argTypes: {
    variant: {
      control: { type: 'select' },
      options: ['primary', 'secondary', 'outline', 'ghost', 'danger']
    },
    size: {
      control: { type: 'select' },
      options: ['sm', 'md', 'lg']
    },
    type: {
      control: { type: 'select' },
      options: ['button', 'submit', 'reset']
    },
    disabled: {
      control: { type: 'boolean' }
    },
    loading: {
      control: { type: 'boolean' }
    },
    iconLeft: {
      control: { type: 'boolean' }
    },
    iconRight: {
      control: { type: 'boolean' }
    }
  }
};

export default meta;
type Story = StoryObj<ButtonComponent>;

export const Primary: Story = {
  args: {
    variant: 'primary'
  },
  render: (args) => ({
    props: args,
    template: `<ui-button [variant]="variant" [size]="size" [disabled]="disabled" [loading]="loading">Primary Button</ui-button>`
  })
};

export const Secondary: Story = {
  args: {
    variant: 'secondary'
  },
  render: (args) => ({
    props: args,
    template: `<ui-button [variant]="variant" [size]="size" [disabled]="disabled" [loading]="loading">Secondary Button</ui-button>`
  })
};

export const Outline: Story = {
  args: {
    variant: 'outline'
  },
  render: (args) => ({
    props: args,
    template: `<ui-button [variant]="variant" [size]="size" [disabled]="disabled" [loading]="loading">Outline Button</ui-button>`
  })
};

export const Ghost: Story = {
  args: {
    variant: 'ghost'
  },
  render: (args) => ({
    props: args,
    template: `<ui-button [variant]="variant" [size]="size" [disabled]="disabled" [loading]="loading">Ghost Button</ui-button>`
  })
};

export const Danger: Story = {
  args: {
    variant: 'danger'
  },
  render: (args) => ({
    props: args,
    template: `<ui-button [variant]="variant" [size]="size" [disabled]="disabled" [loading]="loading">Danger Button</ui-button>`
  })
};

export const WithIcons: Story = {
  args: {
    variant: 'primary',
    iconLeft: true,
    iconRight: true
  },
  render: (args) => ({
    props: args,
    template: `
      <ui-button [variant]="variant" [size]="size" [iconLeft]="iconLeft" [iconRight]="iconRight">
        <svg slot="icon-left" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
          <path d="M12 2L2 7l10 5 10-5-10-5z"/>
          <path d="m2 17 10 5 10-5"/>
          <path d="m2 12 10 5 10-5"/>
        </svg>
        Button with Icons
        <svg slot="icon-right" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
          <path d="M9 18l6-6-6-6"/>
        </svg>
      </ui-button>
    `
  })
};

export const Loading: Story = {
  args: {
    variant: 'primary',
    loading: true
  },
  render: (args) => ({
    props: args,
    template: `<ui-button [variant]="variant" [size]="size" [loading]="loading">Loading...</ui-button>`
  })
};

export const Disabled: Story = {
  args: {
    variant: 'primary',
    disabled: true
  },
  render: (args) => ({
    props: args,
    template: `<ui-button [variant]="variant" [size]="size" [disabled]="disabled">Disabled Button</ui-button>`
  })
};

export const Sizes: Story = {
  render: () => ({
    template: `
      <div style="display: flex; gap: 16px; align-items: center;">
        <ui-button variant="primary" size="sm">Small</ui-button>
        <ui-button variant="primary" size="md">Medium</ui-button>
        <ui-button variant="primary" size="lg">Large</ui-button>
      </div>
    `
  })
};

export const AllVariants: Story = {
  render: () => ({
    template: `
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <ui-button variant="primary">Primary</ui-button>
        <ui-button variant="secondary">Secondary</ui-button>
        <ui-button variant="outline">Outline</ui-button>
        <ui-button variant="ghost">Ghost</ui-button>
        <ui-button variant="danger">Danger</ui-button>
      </div>
    `
  })
};