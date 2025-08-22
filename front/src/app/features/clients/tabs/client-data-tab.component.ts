import { Component, Input, Output, EventEmitter, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';

import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { ClientsV5Service } from '@core/services/clients-v5.service';
import { ToastService } from '@core/services/toast.service';
import { ClientDetail } from '../client-detail.page';

@Component({
  selector: 'app-client-data-tab',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslatePipe],
  template: `
    <div class="client-data-tab">
      <div class="tab-header">
        <h2>{{ 'clients.tabs.datos' | translate }}</h2>
        <p class="tab-description">{{ 'clients.tabs.datosDescription' | translate }}</p>
      </div>

      <form [formGroup]="dataForm" (ngSubmit)="onSubmit()" class="data-form">
        <!-- Personal Information Section -->
        <section class="form-section">
          <h3 class="section-title">{{ 'clients.sections.personalInfo' | translate }}</h3>
          
          <div class="form-grid">
            <div class="form-field">
              <label for="first_name">{{ 'clients.fields.firstName' | translate }} *</label>
              <div class="field-wrapper">
                @if (isEditing('first_name')) {
                  <input
                    id="first_name"
                    type="text"
                    formControlName="first_name"
                    [class.is-invalid]="isFieldInvalid('first_name')"
                    (blur)="saveField('first_name')"
                    (keydown.enter)="saveField('first_name')"
                    (keydown.escape)="cancelEdit('first_name')"
                    #firstNameInput />
                } @else {
                  <div class="field-value" (click)="startEdit('first_name')">
                    <span>{{ dataForm.get('first_name')?.value || '—' }}</span>
                    <button type="button" class="edit-btn" aria-label="Edit first name">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                      </svg>
                    </button>
                  </div>
                }
                @if (isFieldInvalid('first_name')) {
                  <small class="field-error">{{ getFieldError('first_name') }}</small>
                }
              </div>
            </div>

            <div class="form-field">
              <label for="last_name">{{ 'clients.fields.lastName' | translate }} *</label>
              <div class="field-wrapper">
                @if (isEditing('last_name')) {
                  <input
                    id="last_name"
                    type="text"
                    formControlName="last_name"
                    [class.is-invalid]="isFieldInvalid('last_name')"
                    (blur)="saveField('last_name')"
                    (keydown.enter)="saveField('last_name')"
                    (keydown.escape)="cancelEdit('last_name')"
                    #lastNameInput />
                } @else {
                  <div class="field-value" (click)="startEdit('last_name')">
                    <span>{{ dataForm.get('last_name')?.value || '—' }}</span>
                    <button type="button" class="edit-btn" aria-label="Edit last name">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                      </svg>
                    </button>
                  </div>
                }
                @if (isFieldInvalid('last_name')) {
                  <small class="field-error">{{ getFieldError('last_name') }}</small>
                }
              </div>
            </div>

            <div class="form-field">
              <label for="email">{{ 'clients.fields.email' | translate }}</label>
              <div class="field-wrapper">
                @if (isEditing('email')) {
                  <input
                    id="email"
                    type="email"
                    formControlName="email"
                    [class.is-invalid]="isFieldInvalid('email')"
                    (blur)="saveField('email')"
                    (keydown.enter)="saveField('email')"
                    (keydown.escape)="cancelEdit('email')"
                    #emailInput />
                } @else {
                  <div class="field-value" (click)="startEdit('email')">
                    <span>{{ dataForm.get('email')?.value || '—' }}</span>
                    <button type="button" class="edit-btn" aria-label="Edit email">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                      </svg>
                    </button>
                  </div>
                }
                @if (isFieldInvalid('email')) {
                  <small class="field-error">{{ getFieldError('email') }}</small>
                }
              </div>
            </div>

            <div class="form-field">
              <label for="birth_date">{{ 'clients.fields.birthDate' | translate }}</label>
              <div class="field-wrapper">
                @if (isEditing('birth_date')) {
                  <input
                    id="birth_date"
                    type="date"
                    formControlName="birth_date"
                    [class.is-invalid]="isFieldInvalid('birth_date')"
                    (blur)="saveField('birth_date')"
                    (keydown.enter)="saveField('birth_date')"
                    (keydown.escape)="cancelEdit('birth_date')" />
                } @else {
                  <div class="field-value" (click)="startEdit('birth_date')">
                    <span>{{ formatDate(dataForm.get('birth_date')?.value) || '—' }}</span>
                    <button type="button" class="edit-btn" aria-label="Edit birth date">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                      </svg>
                    </button>
                  </div>
                }
                @if (isFieldInvalid('birth_date')) {
                  <small class="field-error">{{ getFieldError('birth_date') }}</small>
                }
              </div>
            </div>
          </div>
        </section>

        <!-- Contact Information Section -->
        <section class="form-section">
          <h3 class="section-title">{{ 'clients.sections.contactInfo' | translate }}</h3>
          
          <div class="form-grid">
            <div class="form-field">
              <label for="phone">{{ 'clients.fields.phone' | translate }}</label>
              <div class="field-wrapper">
                @if (isEditing('phone')) {
                  <input
                    id="phone"
                    type="tel"
                    formControlName="phone"
                    [class.is-invalid]="isFieldInvalid('phone')"
                    (blur)="saveField('phone')"
                    (keydown.enter)="saveField('phone')"
                    (keydown.escape)="cancelEdit('phone')" />
                } @else {
                  <div class="field-value" (click)="startEdit('phone')">
                    <span>{{ dataForm.get('phone')?.value || '—' }}</span>
                    <button type="button" class="edit-btn" aria-label="Edit phone">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                      </svg>
                    </button>
                  </div>
                }
                @if (isFieldInvalid('phone')) {
                  <small class="field-error">{{ getFieldError('phone') }}</small>
                }
              </div>
            </div>

            <div class="form-field">
              <label for="telephone">{{ 'clients.fields.telephone' | translate }}</label>
              <div class="field-wrapper">
                @if (isEditing('telephone')) {
                  <input
                    id="telephone"
                    type="tel"
                    formControlName="telephone"
                    [class.is-invalid]="isFieldInvalid('telephone')"
                    (blur)="saveField('telephone')"
                    (keydown.enter)="saveField('telephone')"
                    (keydown.escape)="cancelEdit('telephone')" />
                } @else {
                  <div class="field-value" (click)="startEdit('telephone')">
                    <span>{{ dataForm.get('telephone')?.value || '—' }}</span>
                    <button type="button" class="edit-btn" aria-label="Edit telephone">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                      </svg>
                    </button>
                  </div>
                }
                @if (isFieldInvalid('telephone')) {
                  <small class="field-error">{{ getFieldError('telephone') }}</small>
                }
              </div>
            </div>
          </div>
        </section>

        <!-- Address Information Section -->
        <section class="form-section">
          <h3 class="section-title">{{ 'clients.sections.addressInfo' | translate }}</h3>
          
          <div class="form-grid">
            <div class="form-field form-field--full">
              <label for="address">{{ 'clients.fields.address' | translate }}</label>
              <div class="field-wrapper">
                @if (isEditing('address')) {
                  <input
                    id="address"
                    type="text"
                    formControlName="address"
                    [class.is-invalid]="isFieldInvalid('address')"
                    (blur)="saveField('address')"
                    (keydown.enter)="saveField('address')"
                    (keydown.escape)="cancelEdit('address')" />
                } @else {
                  <div class="field-value" (click)="startEdit('address')">
                    <span>{{ dataForm.get('address')?.value || '—' }}</span>
                    <button type="button" class="edit-btn" aria-label="Edit address">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                      </svg>
                    </button>
                  </div>
                }
                @if (isFieldInvalid('address')) {
                  <small class="field-error">{{ getFieldError('address') }}</small>
                }
              </div>
            </div>

            <div class="form-field">
              <label for="cp">{{ 'clients.fields.postalCode' | translate }}</label>
              <div class="field-wrapper">
                @if (isEditing('cp')) {
                  <input
                    id="cp"
                    type="text"
                    formControlName="cp"
                    [class.is-invalid]="isFieldInvalid('cp')"
                    (blur)="saveField('cp')"
                    (keydown.enter)="saveField('cp')"
                    (keydown.escape)="cancelEdit('cp')" />
                } @else {
                  <div class="field-value" (click)="startEdit('cp')">
                    <span>{{ dataForm.get('cp')?.value || '—' }}</span>
                    <button type="button" class="edit-btn" aria-label="Edit postal code">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                      </svg>
                    </button>
                  </div>
                }
                @if (isFieldInvalid('cp')) {
                  <small class="field-error">{{ getFieldError('cp') }}</small>
                }
              </div>
            </div>

            <div class="form-field">
              <label for="city">{{ 'clients.fields.city' | translate }}</label>
              <div class="field-wrapper">
                @if (isEditing('city')) {
                  <input
                    id="city"
                    type="text"
                    formControlName="city"
                    [class.is-invalid]="isFieldInvalid('city')"
                    (blur)="saveField('city')"
                    (keydown.enter)="saveField('city')"
                    (keydown.escape)="cancelEdit('city')" />
                } @else {
                  <div class="field-value" (click)="startEdit('city')">
                    <span>{{ dataForm.get('city')?.value || '—' }}</span>
                    <button type="button" class="edit-btn" aria-label="Edit city">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                      </svg>
                    </button>
                  </div>
                }
                @if (isFieldInvalid('city')) {
                  <small class="field-error">{{ getFieldError('city') }}</small>
                }
              </div>
            </div>

            <div class="form-field">
              <label for="province">{{ 'clients.fields.province' | translate }}</label>
              <div class="field-wrapper">
                @if (isEditing('province')) {
                  <input
                    id="province"
                    type="text"
                    formControlName="province"
                    [class.is-invalid]="isFieldInvalid('province')"
                    (blur)="saveField('province')"
                    (keydown.enter)="saveField('province')"
                    (keydown.escape)="cancelEdit('province')" />
                } @else {
                  <div class="field-value" (click)="startEdit('province')">
                    <span>{{ dataForm.get('province')?.value || '—' }}</span>
                    <button type="button" class="edit-btn" aria-label="Edit province">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                      </svg>
                    </button>
                  </div>
                }
                @if (isFieldInvalid('province')) {
                  <small class="field-error">{{ getFieldError('province') }}</small>
                }
              </div>
            </div>

            <div class="form-field">
              <label for="country">{{ 'clients.fields.country' | translate }}</label>
              <div class="field-wrapper">
                @if (isEditing('country')) {
                  <input
                    id="country"
                    type="text"
                    formControlName="country"
                    [class.is-invalid]="isFieldInvalid('country')"
                    (blur)="saveField('country')"
                    (keydown.enter)="saveField('country')"
                    (keydown.escape)="cancelEdit('country')" />
                } @else {
                  <div class="field-value" (click)="startEdit('country')">
                    <span>{{ dataForm.get('country')?.value || '—' }}</span>
                    <button type="button" class="edit-btn" aria-label="Edit country">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                      </svg>
                    </button>
                  </div>
                }
                @if (isFieldInvalid('country')) {
                  <small class="field-error">{{ getFieldError('country') }}</small>
                }
              </div>
            </div>
          </div>
        </section>

        @if (hasUnsavedChanges()) {
          <div class="form-actions">
            <div class="unsaved-changes">
              <span class="changes-indicator">{{ 'clients.unsavedChanges' | translate }}</span>
              <div class="actions">
                <button type="button" class="btn btn--outline" (click)="resetForm()">
                  {{ 'common.cancel' | translate }}
                </button>
                <button type="submit" class="btn btn--primary" [disabled]="dataForm.invalid || saving()">
                  @if (saving()) {
                    <div class="loading-spinner"></div>
                  }
                  {{ 'common.save' | translate }}
                </button>
              </div>
            </div>
          </div>
        }
      </form>
    </div>
  `,
  styleUrls: ['./client-data-tab.component.scss']
})
export class ClientDataTabComponent implements OnInit {
  @Input() client!: ClientDetail;
  @Output() clientUpdated = new EventEmitter<ClientDetail>();

  private readonly fb = inject(FormBuilder);
  private readonly clientsService = inject(ClientsV5Service);
  private readonly toastService = inject(ToastService);

  readonly editingField = signal<string | null>(null);
  readonly saving = signal(false);

  dataForm!: FormGroup;
  private originalValues: any = {};

  ngOnInit(): void {
    this.initializeForm();
  }

  private initializeForm(): void {
    this.dataForm = this.fb.group({
      first_name: [this.client.first_name || '', [Validators.required, Validators.minLength(2)]],
      last_name: [this.client.last_name || '', [Validators.required, Validators.minLength(2)]],
      email: [this.client.email || '', [Validators.email]],
      birth_date: [this.client.birth_date || ''],
      phone: [this.client.phone || ''],
      telephone: [this.client.telephone || ''],
      address: [this.client.address || ''],
      cp: [this.client.cp || ''],
      city: [this.client.city || ''],
      province: [this.client.province || ''],
      country: [this.client.country || '']
    });

    // Store original values for comparison
    this.originalValues = { ...this.dataForm.value };
  }

  isEditing(fieldName: string): boolean {
    return this.editingField() === fieldName;
  }

  startEdit(fieldName: string): void {
    this.editingField.set(fieldName);
    
    // Focus the input after DOM update
    setTimeout(() => {
      const input = document.getElementById(fieldName) as HTMLInputElement;
      input?.focus();
    }, 0);
  }

  saveField(fieldName: string): void {
    const control = this.dataForm.get(fieldName);
    if (control && control.valid) {
      this.editingField.set(null);
    }
  }

  cancelEdit(fieldName: string): void {
    const control = this.dataForm.get(fieldName);
    if (control) {
      control.setValue(this.originalValues[fieldName]);
      this.editingField.set(null);
    }
  }

  isFieldInvalid(fieldName: string): boolean {
    const control = this.dataForm.get(fieldName);
    return !!(control && control.invalid && (control.dirty || control.touched));
  }

  getFieldError(fieldName: string): string {
    const control = this.dataForm.get(fieldName);
    if (control && control.errors && (control.dirty || control.touched)) {
      if (control.errors['required']) {
        return `clients.validation.${fieldName}Required`;
      }
      if (control.errors['email']) {
        return 'clients.validation.emailInvalid';
      }
      if (control.errors['minlength']) {
        return `clients.validation.${fieldName}MinLength`;
      }
    }
    return '';
  }

  hasUnsavedChanges(): boolean {
    return JSON.stringify(this.originalValues) !== JSON.stringify(this.dataForm.value);
  }

  formatDate(dateString: string): string {
    if (!dateString) return '';
    try {
      return new Date(dateString).toLocaleDateString();
    } catch {
      return dateString;
    }
  }

  resetForm(): void {
    this.dataForm.patchValue(this.originalValues);
    this.editingField.set(null);
  }

  onSubmit(): void {
    if (this.dataForm.invalid) {
      this.markAllFieldsAsTouched();
      return;
    }

    this.saving.set(true);

    // Simulate API call - replace with actual service call
    setTimeout(() => {
      const updatedClient: ClientDetail = {
        ...this.client,
        ...this.dataForm.value,
        updated_at: new Date().toISOString()
      };

      this.originalValues = { ...this.dataForm.value };
      this.clientUpdated.emit(updatedClient);
      this.saving.set(false);
      this.toastService.success('clients.data.updated');
    }, 1000);
  }

  private markAllFieldsAsTouched(): void {
    Object.keys(this.dataForm.controls).forEach(key => {
      const control = this.dataForm.get(key);
      control?.markAsTouched();
    });
  }
}