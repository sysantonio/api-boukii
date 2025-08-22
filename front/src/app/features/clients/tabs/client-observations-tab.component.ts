import { Component, Input, Output, EventEmitter, OnInit, inject, signal, ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';

import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { ToastService } from '@core/services/toast.service';
import { ClientDetail, ClientObservation } from '../client-detail.page';

@Component({
  selector: 'app-client-observations-tab',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslatePipe],
  template: `
    <div class="client-observations-tab">
      <div class="tab-header">
        <div>
          <h2>{{ 'clients.tabs.observaciones' | translate }}</h2>
          <p class="tab-description">{{ 'clients.tabs.observacionesDescription' | translate }}</p>
        </div>
        
        <button type="button" class="btn btn--primary" (click)="toggleQuickAdd()">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
          </svg>
          {{ 'clients.observations.add' | translate }}
        </button>
      </div>

      <!-- Quick Add Form -->
      @if (showQuickAdd()) {
        <div class="quick-add-form" #quickAddForm>
          <form [formGroup]="quickObservationForm" (ngSubmit)="onQuickSubmit()" class="observation-form">
            <div class="form-field">
              <label for="quick_title">{{ 'clients.observations.title' | translate }} *</label>
              <input
                id="quick_title"
                type="text"
                formControlName="title"
                [placeholder]="'clients.observations.titlePlaceholder' | translate"
                [class.is-invalid]="isQuickFieldInvalid('title')"
                [attr.aria-describedby]="isQuickFieldInvalid('title') ? 'quick_title_error' : null" />
              @if (isQuickFieldInvalid('title')) {
                <small id="quick_title_error" class="field-error">{{ getQuickFieldError('title') }}</small>
              }
            </div>

            <div class="form-field">
              <label for="quick_content">{{ 'clients.observations.content' | translate }} *</label>
              <textarea
                id="quick_content"
                formControlName="content"
                rows="4"
                [placeholder]="'clients.observations.contentPlaceholder' | translate"
                [class.is-invalid]="isQuickFieldInvalid('content')"
                [attr.aria-describedby]="isQuickFieldInvalid('content') ? 'quick_content_error' : null"></textarea>
              @if (isQuickFieldInvalid('content')) {
                <small id="quick_content_error" class="field-error">{{ getQuickFieldError('content') }}</small>
              }
            </div>

            <div class="form-actions">
              <button type="button" class="btn btn--outline" (click)="cancelQuickAdd()">
                {{ 'common.cancel' | translate }}
              </button>
              <button type="submit" class="btn btn--primary" [disabled]="quickObservationForm.invalid || saving()">
                @if (saving()) {
                  <div class="loading-spinner"></div>
                }
                {{ 'clients.observations.addQuick' | translate }}
              </button>
            </div>
          </form>
        </div>
      }

      @if (observations().length === 0 && !showQuickAdd()) {
        <div class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 2 2h8c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
            </svg>
          </div>
          <h3>{{ 'clients.observations.empty.title' | translate }}</h3>
          <p>{{ 'clients.observations.empty.message' | translate }}</p>
          <button type="button" class="btn btn--primary" (click)="toggleQuickAdd()">
            {{ 'clients.observations.addFirst' | translate }}
          </button>
        </div>
      }

      @if (observations().length > 0) {
        <div class="observations-list">
          @for (observation of observations(); track observation.id) {
            <div class="observation-card" [class.expanded]="isExpanded(observation.id)">
              <div class="observation-header">
                <div class="observation-meta">
                  <h4 class="observation-title">{{ observation.title }}</h4>
                  <span class="observation-date">{{ formatDate(observation.created_at) }}</span>
                </div>
                <div class="observation-controls">
                  <button 
                    type="button" 
                    class="expand-btn" 
                    (click)="toggleExpand(observation.id)"
                    [attr.aria-label]="isExpanded(observation.id) ? ('common.collapse' | translate) : ('common.expand' | translate)">
                    <svg viewBox="0 0 24 24" fill="currentColor" [class.rotated]="isExpanded(observation.id)">
                      <path d="M7 10l5 5 5-5z"/>
                    </svg>
                  </button>
                  <button type="button" class="action-btn" (click)="openEditModal(observation)" [attr.aria-label]="'common.edit' | translate">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                      <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                    </svg>
                  </button>
                  <button type="button" class="action-btn action-btn--danger" (click)="confirmDelete(observation)" [attr.aria-label]="'common.delete' | translate">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                      <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                    </svg>
                  </button>
                </div>
              </div>
              
              <div class="observation-content" [class.collapsed]="!isExpanded(observation.id)">
                <p>{{ observation.content }}</p>
              </div>
            </div>
          }
        </div>
      }

      <!-- Edit Modal -->
      @if (showEditModal()) {
        <div class="modal-overlay" (click)="closeEditModal()">
          <div class="modal-content" (click)="$event.stopPropagation()" #modalContent>
            <div class="modal-header">
              <h3>{{ 'clients.observations.edit' | translate }}</h3>
              <button type="button" class="close-btn" (click)="closeEditModal()" [attr.aria-label]="'common.close' | translate">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
              </button>
            </div>

            <form [formGroup]="observationForm" (ngSubmit)="onSubmit()" class="modal-form">
              <div class="form-field">
                <label for="observation_title">{{ 'clients.observations.title' | translate }} *</label>
                <input
                  id="observation_title"
                  type="text"
                  formControlName="title"
                  [class.is-invalid]="isFieldInvalid('title')"
                  [attr.aria-describedby]="isFieldInvalid('title') ? 'title_error' : null" />
                @if (isFieldInvalid('title')) {
                  <small id="title_error" class="field-error">{{ getFieldError('title') }}</small>
                }
              </div>

              <div class="form-field">
                <label for="observation_content">{{ 'clients.observations.content' | translate }} *</label>
                <textarea
                  id="observation_content"
                  formControlName="content"
                  rows="6"
                  [class.is-invalid]="isFieldInvalid('content')"
                  [attr.aria-describedby]="isFieldInvalid('content') ? 'content_error' : null"></textarea>
                @if (isFieldInvalid('content')) {
                  <small id="content_error" class="field-error">{{ getFieldError('content') }}</small>
                }
              </div>

              <div class="modal-actions">
                <button type="button" class="btn btn--outline" (click)="closeEditModal()">
                  {{ 'common.cancel' | translate }}
                </button>
                <button type="submit" class="btn btn--primary" [disabled]="observationForm.invalid || saving()">
                  @if (saving()) {
                    <div class="loading-spinner"></div>
                  }
                  {{ 'common.save' | translate }}
                </button>
              </div>
            </form>
          </div>
        </div>
      }

      <!-- Delete Confirmation Modal -->
      @if (showDeleteModal()) {
        <div class="modal-overlay" (click)="cancelDelete()">
          <div class="modal-content modal-content--small" (click)="$event.stopPropagation()">
            <div class="modal-header">
              <h3>{{ 'clients.observations.confirmDelete' | translate }}</h3>
            </div>

            <div class="modal-body">
              <p>
                {{ 'clients.observations.confirmDeleteMessage' | translate }}
                <strong>{{ observationToDelete()?.title }}</strong>?
              </p>
              <p class="warning-text">{{ 'clients.observations.deleteWarning' | translate }}</p>
            </div>

            <div class="modal-actions">
              <button type="button" class="btn btn--outline" (click)="cancelDelete()">
                {{ 'common.cancel' | translate }}
              </button>
              <button type="button" class="btn btn--danger" (click)="deleteObservation()" [disabled]="saving()">
                @if (saving()) {
                  <div class="loading-spinner"></div>
                }
                {{ 'common.delete' | translate }}
              </button>
            </div>
          </div>
        </div>
      }
    </div>
  `,
  styleUrls: ['./client-observations-tab.component.scss']
})
export class ClientObservationsTabComponent implements OnInit {
  @Input() client!: ClientDetail;
  @Output() observationsUpdated = new EventEmitter<ClientObservation[]>();

  @ViewChild('modalContent', { static: false }) modalContent!: ElementRef;
  @ViewChild('quickAddForm', { static: false }) quickAddForm!: ElementRef;

  private readonly fb = inject(FormBuilder);
  private readonly toastService = inject(ToastService);

  readonly observations = signal<ClientObservation[]>([]);
  readonly showQuickAdd = signal(false);
  readonly showEditModal = signal(false);
  readonly showDeleteModal = signal(false);
  readonly editingObservation = signal<ClientObservation | null>(null);
  readonly observationToDelete = signal<ClientObservation | null>(null);
  readonly saving = signal(false);
  readonly expandedObservations = signal<Set<number>>(new Set());

  quickObservationForm!: FormGroup;
  observationForm!: FormGroup;

  ngOnInit(): void {
    this.observations.set(this.client.observations || []);
    this.initializeForms();
  }

  private initializeForms(): void {
    this.quickObservationForm = this.fb.group({
      title: ['', [Validators.required, Validators.minLength(3)]],
      content: ['', [Validators.required, Validators.minLength(10)]]
    });

    this.observationForm = this.fb.group({
      title: ['', [Validators.required, Validators.minLength(3)]],
      content: ['', [Validators.required, Validators.minLength(10)]]
    });
  }

  toggleQuickAdd(): void {
    this.showQuickAdd.set(!this.showQuickAdd());
    
    if (this.showQuickAdd()) {
      this.quickObservationForm.reset();
      setTimeout(() => {
        const titleInput = this.quickAddForm?.nativeElement?.querySelector('#quick_title') as HTMLInputElement;
        titleInput?.focus();
      }, 100);
    }
  }

  cancelQuickAdd(): void {
    this.showQuickAdd.set(false);
    this.quickObservationForm.reset();
  }

  onQuickSubmit(): void {
    if (this.quickObservationForm.invalid) {
      this.markAllFieldsAsTouched(this.quickObservationForm);
      return;
    }

    this.saving.set(true);
    const formValue = this.quickObservationForm.value;

    // Simulate API call - replace with actual service
    setTimeout(() => {
      const newObservation: ClientObservation = {
        id: Date.now(), // Temporary ID
        client_id: this.client.id,
        title: formValue.title,
        content: formValue.content,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
      };

      const updated = [newObservation, ...this.observations()]; // Add to top for chronological order
      this.observations.set(updated);
      this.observationsUpdated.emit(updated);
      
      this.saving.set(false);
      this.showQuickAdd.set(false);
      this.quickObservationForm.reset();
      
      this.toastService.success('clients.observations.created');
      
      // Expand the newly created observation
      this.expandedObservations.update(expanded => new Set([...expanded, newObservation.id]));
    }, 800);
  }

  isExpanded(observationId: number): boolean {
    return this.expandedObservations().has(observationId);
  }

  toggleExpand(observationId: number): void {
    this.expandedObservations.update(expanded => {
      const newSet = new Set(expanded);
      if (newSet.has(observationId)) {
        newSet.delete(observationId);
      } else {
        newSet.add(observationId);
      }
      return newSet;
    });
  }

  openEditModal(observation: ClientObservation): void {
    this.editingObservation.set(observation);
    this.observationForm.patchValue({
      title: observation.title,
      content: observation.content
    });
    this.showEditModal.set(true);
    
    setTimeout(() => {
      const titleInput = this.modalContent?.nativeElement?.querySelector('#observation_title') as HTMLInputElement;
      titleInput?.focus();
    }, 100);
  }

  closeEditModal(): void {
    this.showEditModal.set(false);
    this.editingObservation.set(null);
    this.observationForm.reset();
  }

  onSubmit(): void {
    if (this.observationForm.invalid) {
      this.markAllFieldsAsTouched(this.observationForm);
      return;
    }

    const editingObservation = this.editingObservation();
    if (!editingObservation) return;

    this.saving.set(true);
    const formValue = this.observationForm.value;

    // Simulate API call - replace with actual service
    setTimeout(() => {
      const updatedObservation: ClientObservation = {
        ...editingObservation,
        title: formValue.title,
        content: formValue.content,
        updated_at: new Date().toISOString()
      };

      const updated = this.observations().map(obs => 
        obs.id === editingObservation.id ? updatedObservation : obs
      );
      this.observations.set(updated);
      this.observationsUpdated.emit(updated);
      
      this.saving.set(false);
      this.closeEditModal();
      
      this.toastService.success('clients.observations.updated');
    }, 800);
  }

  confirmDelete(observation: ClientObservation): void {
    this.observationToDelete.set(observation);
    this.showDeleteModal.set(true);
  }

  cancelDelete(): void {
    this.showDeleteModal.set(false);
    this.observationToDelete.set(null);
  }

  deleteObservation(): void {
    const observationToDelete = this.observationToDelete();
    if (!observationToDelete) return;

    this.saving.set(true);

    // Simulate API call - replace with actual service
    setTimeout(() => {
      const updated = this.observations().filter(obs => obs.id !== observationToDelete.id);
      this.observations.set(updated);
      this.observationsUpdated.emit(updated);
      
      // Remove from expanded set
      this.expandedObservations.update(expanded => {
        const newSet = new Set(expanded);
        newSet.delete(observationToDelete.id);
        return newSet;
      });
      
      this.saving.set(false);
      this.showDeleteModal.set(false);
      this.observationToDelete.set(null);
      
      this.toastService.success('clients.observations.deleted');
    }, 800);
  }

  isQuickFieldInvalid(fieldName: string): boolean {
    const control = this.quickObservationForm.get(fieldName);
    return !!(control && control.invalid && (control.dirty || control.touched));
  }

  getQuickFieldError(fieldName: string): string {
    const control = this.quickObservationForm.get(fieldName);
    if (control && control.errors && (control.dirty || control.touched)) {
      if (control.errors['required']) {
        return `clients.validation.${fieldName}Required`;
      }
      if (control.errors['minlength']) {
        return `clients.validation.${fieldName}MinLength`;
      }
    }
    return '';
  }

  isFieldInvalid(fieldName: string): boolean {
    const control = this.observationForm.get(fieldName);
    return !!(control && control.invalid && (control.dirty || control.touched));
  }

  getFieldError(fieldName: string): string {
    const control = this.observationForm.get(fieldName);
    if (control && control.errors && (control.dirty || control.touched)) {
      if (control.errors['required']) {
        return `clients.validation.${fieldName}Required`;
      }
      if (control.errors['minlength']) {
        return `clients.validation.${fieldName}MinLength`;
      }
    }
    return '';
  }

  formatDate(dateString: string): string {
    if (!dateString) return '';
    try {
      const date = new Date(dateString);
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } catch {
      return dateString;
    }
  }

  private markAllFieldsAsTouched(form: FormGroup): void {
    Object.keys(form.controls).forEach(key => {
      const control = form.get(key);
      control?.markAsTouched();
    });
  }
}