import { Component, Input, Output, EventEmitter, OnInit, inject, signal, ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';

import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { ToastService } from '@core/services/toast.service';
import { ClientDetail, ClientSport, ClientUtilizador } from '../client-detail.page';

interface SchoolSport {
  id: number;
  name: string;
  description?: string;
}

interface SportDegree {
  id: number;
  name: string;
  level: number;
  color?: string;
}

interface PersonOption {
  id: string;
  type: 'client' | 'utilizador';
  name: string;
}

@Component({
  selector: 'app-client-sports-tab',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslatePipe],
  template: `
    <div class="client-sports-tab">
      <div class="tab-header">
        <div>
          <h2>{{ 'clients.tabs.deportes' | translate }}</h2>
          <p class="tab-description">{{ 'clients.tabs.deportesDescription' | translate }}</p>
        </div>
        
        <button type="button" class="btn btn--primary" (click)="openModal('create')">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
          </svg>
          {{ 'clients.sports.add' | translate }}
        </button>
      </div>

      @if (clientSports().length === 0) {
        <div class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
          </div>
          <h3>{{ 'clients.sports.empty.title' | translate }}</h3>
          <p>{{ 'clients.sports.empty.message' | translate }}</p>
          <button type="button" class="btn btn--primary" (click)="openModal('create')">
            {{ 'clients.sports.add' | translate }}
          </button>
        </div>
      }

      @if (clientSports().length > 0) {
        <!-- Sports grouped by person -->
        @for (person of getPersonsWithSports(); track person.id) {
          <div class="person-sports-group">
            <div class="person-header">
              <div class="person-info">
                <h3 class="person-name">{{ person.name }}</h3>
                <span class="person-type">
                  @if (person.type === 'client') {
                    {{ 'clients.sports.mainClient' | translate }}
                  } @else {
                    {{ 'clients.sports.utilizador' | translate }}
                  }
                </span>
              </div>
              <span class="sports-count">{{ person.sports.length }} {{ 'clients.sports.sports' | translate }}</span>
            </div>
            
            <div class="sports-grid">
              @for (sport of person.sports; track sport.id) {
                <div class="sport-card">
                  <div class="sport-main">
                    <div class="sport-info">
                      <h4 class="sport-name">{{ sport.sport?.name || 'Sport' }}</h4>
                      @if (sport.degree) {
                        <div class="sport-level" [style.color]="sport.degree.color || 'var(--text-2)'">
                          <span class="level-indicator">●</span>
                          {{ sport.degree.name }}
                        </div>
                      } @else {
                        <div class="sport-level no-level">
                          {{ 'clients.sports.noLevel' | translate }}
                        </div>
                      }
                    </div>
                    
                    <div class="sport-actions">
                      <button type="button" class="action-btn" (click)="openModal('edit', sport)" [attr.aria-label]="'common.edit' | translate">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                          <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                      </button>
                      <button type="button" class="action-btn action-btn--danger" (click)="confirmDelete(sport)" [attr.aria-label]="'common.delete' | translate">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                          <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <div class="sport-meta">
                    <span class="added-date">
                      {{ 'clients.addedOn' | translate }}: {{ formatDate(sport.created_at) }}
                    </span>
                  </div>
                </div>
              }
            </div>
          </div>
        }
      }

      <!-- Add/Edit Sport Modal -->
      @if (showModal()) {
        <div class="modal-overlay" (click)="closeModal()">
          <div class="modal-content" (click)="$event.stopPropagation()" #modalContent>
            <div class="modal-header">
              <h3>
                @if (modalMode() === 'create') {
                  {{ 'clients.sports.addSport' | translate }}
                } @else {
                  {{ 'clients.sports.editSport' | translate }}
                }
              </h3>
              <button type="button" class="close-btn" (click)="closeModal()" [attr.aria-label]="'common.close' | translate">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
              </button>
            </div>

            <form [formGroup]="sportForm" (ngSubmit)="onSubmit()" class="modal-form">
              <div class="form-grid">
                <div class="form-field">
                  <label for="person_select">{{ 'clients.sports.selectPerson' | translate }} *</label>
                  <select 
                    id="person_select" 
                    formControlName="person_id"
                    [class.is-invalid]="isFieldInvalid('person_id')"
                    [attr.aria-describedby]="isFieldInvalid('person_id') ? 'person_error' : null">
                    <option value="">{{ 'clients.sports.selectPersonPlaceholder' | translate }}</option>
                    @for (person of getPersonOptions(); track person.id) {
                      <option [value]="person.id">{{ person.name }}</option>
                    }
                  </select>
                  @if (isFieldInvalid('person_id')) {
                    <small id="person_error" class="field-error">{{ getFieldError('person_id') }}</small>
                  }
                </div>

                <div class="form-field">
                  <label for="sport_select">{{ 'clients.sports.selectSport' | translate }} *</label>
                  <select 
                    id="sport_select" 
                    formControlName="sport_id"
                    [class.is-invalid]="isFieldInvalid('sport_id')"
                    [attr.aria-describedby]="isFieldInvalid('sport_id') ? 'sport_error' : null"
                    (change)="onSportChange()">
                    <option value="">{{ 'clients.sports.selectSportPlaceholder' | translate }}</option>
                    @for (sport of availableSports(); track sport.id) {
                      <option [value]="sport.id">{{ sport.name }}</option>
                    }
                  </select>
                  @if (isFieldInvalid('sport_id')) {
                    <small id="sport_error" class="field-error">{{ getFieldError('sport_id') }}</small>
                  }
                </div>

                <div class="form-field form-field--full">
                  <label for="degree_select">{{ 'clients.sports.selectLevel' | translate }}</label>
                  <select 
                    id="degree_select" 
                    formControlName="degree_id"
                    [class.is-invalid]="isFieldInvalid('degree_id')"
                    [attr.aria-describedby]="isFieldInvalid('degree_id') ? 'degree_error' : null"
                    [disabled]="!selectedSportId() || loadingDegrees()">
                    <option value="">{{ 'clients.sports.selectLevelPlaceholder' | translate }}</option>
                    @for (degree of availableDegrees(); track degree.id) {
                      <option [value]="degree.id">{{ degree.name }}</option>
                    }
                  </select>
                  @if (loadingDegrees()) {
                    <small class="field-note">{{ 'clients.sports.loadingLevels' | translate }}</small>
                  }
                  @if (isFieldInvalid('degree_id')) {
                    <small id="degree_error" class="field-error">{{ getFieldError('degree_id') }}</small>
                  }
                </div>
              </div>

              <div class="modal-actions">
                <button type="button" class="btn btn--outline" (click)="closeModal()">
                  {{ 'common.cancel' | translate }}
                </button>
                <button type="submit" class="btn btn--primary" [disabled]="sportForm.invalid || saving()">
                  @if (saving()) {
                    <div class="loading-spinner"></div>
                  }
                  @if (modalMode() === 'create') {
                    {{ 'common.create' | translate }}
                  } @else {
                    {{ 'common.save' | translate }}
                  }
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
              <h3>{{ 'clients.sports.confirmDelete' | translate }}</h3>
            </div>

            <div class="modal-body">
              <p>
                {{ 'clients.sports.confirmDeleteMessage' | translate }}
                <strong>{{ sportToDelete()?.sport?.name }}</strong>?
              </p>
              <p class="warning-text">{{ 'clients.sports.deleteWarning' | translate }}</p>
            </div>

            <div class="modal-actions">
              <button type="button" class="btn btn--outline" (click)="cancelDelete()">
                {{ 'common.cancel' | translate }}
              </button>
              <button type="button" class="btn btn--danger" (click)="deleteSport()" [disabled]="saving()">
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
  styleUrls: ['./client-sports-tab.component.scss']
})
export class ClientSportsTabComponent implements OnInit {
  @Input() client!: ClientDetail;
  @Output() sportsUpdated = new EventEmitter<ClientSport[]>();

  @ViewChild('modalContent', { static: false }) modalContent!: ElementRef;

  private readonly fb = inject(FormBuilder);
  private readonly toastService = inject(ToastService);

  readonly clientSports = signal<ClientSport[]>([]);
  readonly showModal = signal(false);
  readonly modalMode = signal<'create' | 'edit'>('create');
  readonly editingSport = signal<ClientSport | null>(null);
  readonly saving = signal(false);
  readonly showDeleteModal = signal(false);
  readonly sportToDelete = signal<ClientSport | null>(null);
  
  readonly availableSports = signal<SchoolSport[]>([]);
  readonly availableDegrees = signal<SportDegree[]>([]);
  readonly selectedSportId = signal<number | null>(null);
  readonly loadingDegrees = signal(false);

  sportForm!: FormGroup;

  ngOnInit(): void {
    this.clientSports.set(this.client.client_sports || []);
    this.initializeForm();
    this.loadAvailableSports();
  }

  private initializeForm(): void {
    this.sportForm = this.fb.group({
      person_id: ['', [Validators.required]],
      sport_id: ['', [Validators.required]],
      degree_id: ['']
    });
  }

  private loadAvailableSports(): void {
    // Mock data - replace with actual service call
    const mockSports: SchoolSport[] = [
      { id: 1, name: 'Fútbol', description: 'Football/Soccer' },
      { id: 2, name: 'Baloncesto', description: 'Basketball' },
      { id: 3, name: 'Tenis', description: 'Tennis' },
      { id: 4, name: 'Natación', description: 'Swimming' },
      { id: 5, name: 'Atletismo', description: 'Athletics' },
    ];
    this.availableSports.set(mockSports);
  }

  private loadDegreesForSport(sportId: number): void {
    this.loadingDegrees.set(true);
    
    // Mock API call - replace with actual service
    setTimeout(() => {
      const mockDegrees: SportDegree[] = [
        { id: 1, name: 'Principiante', level: 1, color: '#10b981' },
        { id: 2, name: 'Intermedio', level: 2, color: '#f59e0b' },
        { id: 3, name: 'Avanzado', level: 3, color: '#ef4444' },
        { id: 4, name: 'Experto', level: 4, color: '#8b5cf6' },
      ];
      this.availableDegrees.set(mockDegrees);
      this.loadingDegrees.set(false);
    }, 500);
  }

  getPersonOptions(): PersonOption[] {
    const options: PersonOption[] = [
      {
        id: 'client',
        type: 'client',
        name: `${this.client.first_name} ${this.client.last_name}`.trim()
      }
    ];

    if (this.client.utilizadores) {
      this.client.utilizadores.forEach(utilizador => {
        options.push({
          id: `utilizador_${utilizador.id}`,
          type: 'utilizador',
          name: `${utilizador.first_name} ${utilizador.last_name}`.trim()
        });
      });
    }

    return options;
  }

  getPersonsWithSports() {
    const persons: Array<{ id: string; type: 'client' | 'utilizador'; name: string; sports: ClientSport[] }> = [];
    const sports = this.clientSports();

    // Group sports by person
    const clientSports = sports.filter(s => s.person_type === 'client');
    if (clientSports.length > 0) {
      persons.push({
        id: 'client',
        type: 'client',
        name: `${this.client.first_name} ${this.client.last_name}`.trim(),
        sports: clientSports
      });
    }

    if (this.client.utilizadores) {
      this.client.utilizadores.forEach(utilizador => {
        const utilizadorSports = sports.filter(s => s.person_type === 'utilizador' && s.person_id === utilizador.id);
        if (utilizadorSports.length > 0) {
          persons.push({
            id: `utilizador_${utilizador.id}`,
            type: 'utilizador',
            name: `${utilizador.first_name} ${utilizador.last_name}`.trim(),
            sports: utilizadorSports
          });
        }
      });
    }

    return persons;
  }

  openModal(mode: 'create' | 'edit', sport?: ClientSport): void {
    this.modalMode.set(mode);
    this.editingSport.set(sport || null);
    
    if (mode === 'edit' && sport) {
      const personId = sport.person_type === 'client' ? 'client' : `utilizador_${sport.person_id}`;
      
      this.sportForm.patchValue({
        person_id: personId,
        sport_id: sport.sport_id,
        degree_id: sport.degree_id || ''
      });
      
      if (sport.sport_id) {
        this.selectedSportId.set(sport.sport_id);
        this.loadDegreesForSport(sport.sport_id);
      }
    } else {
      this.sportForm.reset();
      this.selectedSportId.set(null);
      this.availableDegrees.set([]);
    }

    this.showModal.set(true);
    
    // Focus first select after modal opens
    setTimeout(() => {
      const firstSelect = this.modalContent?.nativeElement?.querySelector('select') as HTMLSelectElement;
      firstSelect?.focus();
    }, 100);
  }

  closeModal(): void {
    this.showModal.set(false);
    this.editingSport.set(null);
    this.sportForm.reset();
    this.selectedSportId.set(null);
    this.availableDegrees.set([]);
  }

  onSportChange(): void {
    const sportId = this.sportForm.get('sport_id')?.value;
    if (sportId) {
      this.selectedSportId.set(+sportId);
      this.loadDegreesForSport(+sportId);
      // Reset degree selection when sport changes
      this.sportForm.patchValue({ degree_id: '' });
    } else {
      this.selectedSportId.set(null);
      this.availableDegrees.set([]);
    }
  }

  confirmDelete(sport: ClientSport): void {
    this.sportToDelete.set(sport);
    this.showDeleteModal.set(true);
  }

  cancelDelete(): void {
    this.showDeleteModal.set(false);
    this.sportToDelete.set(null);
  }

  deleteSport(): void {
    const sportToDelete = this.sportToDelete();
    if (!sportToDelete) return;

    this.saving.set(true);

    // Simulate API call - replace with actual service
    setTimeout(() => {
      const updated = this.clientSports().filter(s => s.id !== sportToDelete.id);
      this.clientSports.set(updated);
      this.sportsUpdated.emit(updated);
      
      this.saving.set(false);
      this.showDeleteModal.set(false);
      this.sportToDelete.set(null);
      
      this.toastService.success('clients.sports.deleted');
    }, 800);
  }

  onSubmit(): void {
    if (this.sportForm.invalid) {
      this.markAllFieldsAsTouched();
      return;
    }

    this.saving.set(true);
    const formValue = this.sportForm.value;

    // Parse person info
    const personId = formValue.person_id;
    const isClient = personId === 'client';
    const utilizadorId = isClient ? null : parseInt(personId.replace('utilizador_', ''));
    
    // Simulate API call - replace with actual service
    setTimeout(() => {
      if (this.modalMode() === 'create') {
        const selectedSport = this.availableSports().find(s => s.id === +formValue.sport_id);
        const selectedDegree = this.availableDegrees().find(d => d.id === +formValue.degree_id);
        
        const newSport: ClientSport = {
          id: Date.now(), // Temporary ID
          client_id: this.client.id,
          person_type: isClient ? 'client' : 'utilizador',
          person_id: isClient ? this.client.id : utilizadorId!,
          sport_id: +formValue.sport_id,
          degree_id: formValue.degree_id ? +formValue.degree_id : undefined,
          sport: selectedSport,
          degree: selectedDegree,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        };
        
        const updated = [...this.clientSports(), newSport];
        this.clientSports.set(updated);
        this.sportsUpdated.emit(updated);
        this.toastService.success('clients.sports.created');
      } else {
        const editingSport = this.editingSport();
        if (editingSport) {
          const selectedSport = this.availableSports().find(s => s.id === +formValue.sport_id);
          const selectedDegree = formValue.degree_id ? this.availableDegrees().find(d => d.id === +formValue.degree_id) : undefined;
          
          const updatedSport: ClientSport = {
            ...editingSport,
            person_type: isClient ? 'client' : 'utilizador',
            person_id: isClient ? this.client.id : utilizadorId!,
            sport_id: +formValue.sport_id,
            degree_id: formValue.degree_id ? +formValue.degree_id : undefined,
            sport: selectedSport,
            degree: selectedDegree,
            updated_at: new Date().toISOString()
          };

          const updated = this.clientSports().map(s => 
            s.id === editingSport.id ? updatedSport : s
          );
          this.clientSports.set(updated);
          this.sportsUpdated.emit(updated);
          this.toastService.success('clients.sports.updated');
        }
      }

      this.saving.set(false);
      this.closeModal();
    }, 800);
  }

  isFieldInvalid(fieldName: string): boolean {
    const control = this.sportForm.get(fieldName);
    return !!(control && control.invalid && (control.dirty || control.touched));
  }

  getFieldError(fieldName: string): string {
    const control = this.sportForm.get(fieldName);
    if (control && control.errors && (control.dirty || control.touched)) {
      if (control.errors['required']) {
        return `clients.validation.${fieldName}Required`;
      }
    }
    return '';
  }

  formatDate(dateString: string): string {
    if (!dateString) return '';
    try {
      return new Date(dateString).toLocaleDateString();
    } catch {
      return dateString;
    }
  }

  private markAllFieldsAsTouched(): void {
    Object.keys(this.sportForm.controls).forEach(key => {
      const control = this.sportForm.get(key);
      control?.markAsTouched();
    });
  }
}