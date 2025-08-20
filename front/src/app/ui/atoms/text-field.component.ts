import { Component, Input, Output, EventEmitter, ChangeDetectionStrategy, forwardRef, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ControlValueAccessor, NG_VALUE_ACCESSOR } from '@angular/forms';

export type TextFieldType = 'text' | 'email' | 'password' | 'tel' | 'url' | 'search';
export type TextFieldSize = 'sm' | 'md' | 'lg';

@Component({
  selector: 'ui-text-field',
  standalone: true,
  imports: [CommonModule],
  changeDetection: ChangeDetectionStrategy.OnPush,
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => TextFieldComponent),
      multi: true
    }
  ],
  template: `
    <div [class]="containerClasses">
      @if (label) {
        <label [for]="inputId" class="field-label">
          {{ label }}
          @if (required) {
            <span class="required-indicator" aria-label="Required">*</span>
          }
        </label>
      }
      
      <div class="input-container">
        @if (prefixIcon) {
          <div class="input-prefix">
            <ng-content select="[slot=prefix-icon]"></ng-content>
          </div>
        }
        
        <input
          [id]="inputId"
          [type]="type"
          [placeholder]="placeholder"
          [disabled]="disabled"
          [readonly]="readonly"
          [required]="required"
          [maxlength]="maxLength"
          [minlength]="minLength"
          [class]="inputClasses"
          [value]="value()"
          (input)="onInput($event)"
          (blur)="onBlur()"
          (focus)="onFocus()"
          [attr.aria-describedby]="ariaDescribedBy"
          [attr.aria-invalid]="hasError"
        />
        
        @if (suffixIcon || showClearButton) {
          <div class="input-suffix">
            @if (showClearButton && value() && !disabled && !readonly) {
              <button 
                type="button"
                class="clear-button"
                (click)="clearValue()"
                aria-label="Clear input"
              >
                <svg viewBox="0 0 24 24" width="16" height="16">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            }
            @if (suffixIcon) {
              <ng-content select="[slot=suffix-icon]"></ng-content>
            }
          </div>
        }
      </div>
      
      @if (hint || errorMessage) {
        <div class="field-message" [id]="messageId">
          @if (hasError && errorMessage) {
            <span class="error-message">{{ errorMessage }}</span>
          } @else if (hint) {
            <span class="hint-message">{{ hint }}</span>
          }
        </div>
      }
    </div>
  `,
  styles: [`
    .field-container {
      display: flex;
      flex-direction: column;
      gap: var(--space-2);
    }

    .field-label {
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-medium);
      color: var(--color-text-primary);
      display: flex;
      align-items: center;
      gap: var(--space-1);
    }

    .required-indicator {
      color: var(--color-error);
      font-weight: var(--font-weight-bold);
    }

    .input-container {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-field {
      width: 100%;
      border: 1px solid var(--input-border);
      border-radius: var(--radius-md);
      background-color: var(--input-background);
      color: var(--color-text-primary);
      font-family: var(--font-family-sans);
      transition: all var(--duration-fast) var(--ease-in-out);
      outline: none;
    }

    .input-field::placeholder {
      color: var(--color-text-tertiary);
    }

    .input-field:focus {
      border-color: var(--input-border-focus);
      box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primary) 12%, transparent);
    }

    .input-field:disabled {
      background-color: var(--color-surface-secondary);
      color: var(--color-text-tertiary);
      cursor: not-allowed;
    }

    .input-field:readonly {
      background-color: var(--color-surface-secondary);
    }

    /* Sizes */
    .input-sm {
      padding: var(--space-2) var(--space-3);
      font-size: var(--font-size-sm);
      line-height: var(--line-height-tight);
    }

    .input-md {
      padding: var(--space-3) var(--space-4);
      font-size: var(--font-size-base);
      line-height: var(--line-height-tight);
    }

    .input-lg {
      padding: var(--space-4) var(--space-5);
      font-size: var(--font-size-lg);
      line-height: var(--line-height-tight);
    }

    /* State variations */
    .field-error .input-field {
      border-color: var(--color-error);
    }

    .field-error .input-field:focus {
      border-color: var(--color-error);
      box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-error) 12%, transparent);
    }

    .input-with-prefix .input-field {
      padding-left: var(--space-10);
    }

    .input-with-suffix .input-field {
      padding-right: var(--space-10);
    }

    .input-prefix,
    .input-suffix {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      display: flex;
      align-items: center;
      color: var(--color-text-tertiary);
      pointer-events: none;
    }

    .input-prefix {
      left: var(--space-3);
    }

    .input-suffix {
      right: var(--space-3);
      gap: var(--space-1);
    }

    .clear-button {
      background: none;
      border: none;
      padding: var(--space-1);
      cursor: pointer;
      color: var(--color-text-tertiary);
      border-radius: var(--radius-sm);
      pointer-events: auto;
      transition: all var(--duration-fast) var(--ease-in-out);
    }

    .clear-button:hover {
      background-color: var(--color-surface-elevated);
      color: var(--color-text-secondary);
    }

    .field-message {
      font-size: var(--font-size-sm);
      line-height: var(--line-height-tight);
    }

    .error-message {
      color: var(--color-error);
    }

    .hint-message {
      color: var(--color-text-tertiary);
    }
  `]
})
export class TextFieldComponent implements ControlValueAccessor {
  @Input() public label: string = '';
  @Input() public placeholder: string = '';
  @Input() public type: TextFieldType = 'text';
  @Input() public size: TextFieldSize = 'md';
  @Input() public disabled: boolean = false;
  @Input() public readonly: boolean = false;
  @Input() public required: boolean = false;
  @Input() public maxLength: number | null = null;
  @Input() public minLength: number | null = null;
  @Input() public hint: string = '';
  @Input() public errorMessage: string = '';
  @Input() public prefixIcon: boolean = false;
  @Input() public suffixIcon: boolean = false;
  @Input() public showClearButton: boolean = false;

  @Output() public valueChange = new EventEmitter<string>();
  @Output() public focus = new EventEmitter<FocusEvent>();
  @Output() public blur = new EventEmitter<FocusEvent>();

  public value = signal<string>('');
  public inputId = `text-field-${Math.random().toString(36).substring(2, 15)}`;
  public messageId = `${this.inputId}-message`;

  private onChange = (_value: string) => {};
  private onTouched = () => {};

  public get containerClasses(): string {
    return [
      'field-container',
      this.hasError ? 'field-error' : ''
    ].filter(Boolean).join(' ');
  }

  public get inputClasses(): string {
    return [
      'input-field',
      `input-${this.size}`,
      this.prefixIcon ? 'input-with-prefix' : '',
      this.suffixIcon || this.showClearButton ? 'input-with-suffix' : ''
    ].filter(Boolean).join(' ');
  }

  public get hasError(): boolean {
    return !!this.errorMessage;
  }

  public get ariaDescribedBy(): string | null {
    return (this.hint || this.errorMessage) ? this.messageId : null;
  }

  public onInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    const newValue = target.value;
    this.value.set(newValue);
    this.onChange(newValue);
    this.valueChange.emit(newValue);
  }

  public onFocus(): void {
    this.focus.emit();
  }

  public onBlur(): void {
    this.onTouched();
    this.blur.emit();
  }

  public clearValue(): void {
    this.value.set('');
    this.onChange('');
    this.valueChange.emit('');
  }

  // ControlValueAccessor implementation
  public writeValue(value: string): void {
    this.value.set(value || '');
  }

  public registerOnChange(fn: (value: string) => void): void {
    this.onChange = fn;
  }

  public registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  public setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
  }
}