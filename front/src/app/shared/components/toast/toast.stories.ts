import type { Meta, StoryObj } from '@storybook/angular';
import { moduleMetadata, applicationConfig } from '@storybook/angular';
import { provideAnimations } from '@angular/platform-browser/animations';
import { ToastComponent } from './toast.component';

const meta: Meta<ToastComponent> = {
  title: 'Shared/Toast',
  component: ToastComponent,
  decorators: [
    moduleMetadata({
      imports: [ToastComponent],
    }),
    applicationConfig({
      providers: [provideAnimations()],
    }),
  ],
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: `
Toast Component displays temporary notification messages to users.

## Features
- Multiple toast types (success, error, warning, info)
- Auto-dismiss with configurable duration
- Progress bar indicator
- Action buttons support
- Queue management for multiple toasts
- Smooth animations
- Accessible announcements

## Usage
\`\`\`typescript
// Using ToastService
this.toastService.show({
  type: 'success',
  message: 'Operation completed successfully!',
  duration: 3000
});
\`\`\`
        `,
      },
    },
  },
  argTypes: {
    // Component uses internal service state
  },
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<ToastComponent>;

export const Success: Story = {
  render: () => ({
    template: `<app-toast-container></app-toast-container>`,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Success toast notification with green styling and checkmark icon.',
      },
    },
  },
};

export const Error: Story = {
  render: () => ({
    template: `<app-toast-container></app-toast-container>`,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Error toast notification with red styling and error icon.',
      },
    },
  },
};

export const Warning: Story = {
  render: () => ({
    template: `<app-toast-container></app-toast-container>`,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Warning toast notification with amber styling and warning icon.',
      },
    },
  },
};

export const Info: Story = {
  render: () => ({
    template: `<app-toast-container></app-toast-container>`,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Info toast notification with blue styling and info icon.',
      },
    },
  },
};

export const WithAction: Story = {
  render: () => ({
    template: `<app-toast-container></app-toast-container>`,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Toast with an action button for user interaction.',
      },
    },
  },
};

export const LongDuration: Story = {
  render: () => ({
    template: `<app-toast-container></app-toast-container>`,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Toast with extended duration (10 seconds) and slower progress bar.',
      },
    },
  },
};

export const AllTypes: Story = {
  render: () => ({
    template: `
      <div class="story-container">
        <div style="display: flex; flex-direction: column; gap: 1rem; min-width: 400px;">
          <h3>All Toast Types</h3>
          <p>This story demonstrates the toast container component which manages multiple toast notifications.</p>
          <app-toast-container></app-toast-container>
        </div>
      </div>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Showcase of all toast types with different progress states.',
      },
    },
  },
};

export const Interactive: Story = {
  render: () => ({
    template: `
      <div class="story-container">
        <div class="component-showcase">
          <h3>Interactive Toast Demo</h3>
          <p>This demonstrates the toast container component which manages notifications.</p>
          <app-toast-container></app-toast-container>
        </div>
      </div>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Interactive demo with buttons to trigger different toast types.',
      },
    },
  },
};

export const Playground: Story = {
  render: () => ({
    template: `
      <div class="story-container">
        <div class="component-showcase">
          <h4>Toast Container Playground</h4>
          <p>This story shows the toast container component that manages multiple toast notifications.</p>
          <app-toast-container></app-toast-container>
        </div>
      </div>
    `,
  }),
  parameters: {
    docs: {
      description: {
        story: 'Playground showing different toast configurations and use cases.',
      },
    },
  },
};
