import { Component, inject, signal, computed, Input, Output, EventEmitter, OnInit, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, FormArray, Validators } from '@angular/forms';
import { catchError, finalize, switchMap } from 'rxjs/operators';
import { of, forkJoin } from 'rxjs';
import { TranslatePipe } from '../../../../../shared/pipes/translate.pipe';
import { SchoolPermissionsService } from '../../services/school-permissions.service';
import { RolesService } from '../../../roles/services/roles.service';
import { 
  UserSchoolRole, 
  School,
  UserPermissionMatrix,
  SchoolPermission
} from '../../types/school-permissions.types';
import { Role } from '../../../roles/types/role.types';

@Component({
  selector: 'app-permission-assignment-modal',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslatePipe],
  template: `
    <div class="modal-overlay" *ngIf="isOpen" (click)="closeModal()" data-testid="permission-modal">
      <div class="modal-content" (click)="$event.stopPropagation()">
        <div class="modal-header">
          <h2>{{ isEditing() ? ('permissions.modal.editTitle' | translate) : ('permissions.modal.assignTitle' | translate) }}</h2>
          <button class="close-btn" (click)="closeModal()" aria-label="Close">
            <i class="icon-x"></i>
          </button>
        </div>

        <div class="modal-body">
          <!-- User & School Info -->
          <div class="assignment-info" *ngIf="userInfo() && schoolInfo()">
            <div class="info-section">
              <h3>{{ 'permissions.modal.user' | translate }}</h3>
              <div class="user-display">
                <div class="user-avatar">{{ getInitials(userInfo()!.name) }}</div>
                <div class="user-details">
                  <div class="user-name">{{ userInfo()!.name }}</div>
                  <div class="user-email">{{ userInfo()!.email }}</div>
                </div>
              </div>
            </div>

            <div class="info-section">
              <h3>{{ 'permissions.modal.school' | translate }}</h3>
              <div class="school-display">
                <div class="school-name">{{ schoolInfo()!.name }}</div>
                <div class="school-code">{{ schoolInfo()!.code }}</div>
              </div>
            </div>
          </div>

          <!-- Assignment Form -->
          <form [formGroup]="assignmentForm" (ngSubmit)="saveAssignment()">
            <!-- Role Selection -->
            <div class="form-section">
              <h3>{{ 'permissions.modal.roleAssignment' | translate }}</h3>
              <p class="section-description">{{ 'permissions.modal.roleDescription' | translate }}</p>
              
              <div class="roles-grid" *ngIf="availableRoles().length > 0">
                <div 
                  *ngFor="let role of availableRoles(); trackBy: trackByRoleId" 
                  class="role-option"
                  [class.selected]="isRoleSelected(role.name)"
                >
                  <label class="role-checkbox">
                    <input 
                      type="checkbox" 
                      [value]="role.name"
                      [checked]="isRoleSelected(role.name)"
                      (change)="toggleRole(role.name, $event)"
                      [attr.data-testid]="'role-option-' + role.name"
                    />
                    <div class="checkbox-custom"></div>
                    <div class="role-info">
                      <div class="role-name">{{ role.description }}</div>
                      <div class="role-identifier">{{ role.name }}</div>
                      <div class="role-permissions-count">
                        {{ role.permissions.length }} {{ 'permissions.modal.permissions' | translate }}
                      </div>
                    </div>
                  </label>
                </div>
              </div>
            </div>

            <!-- Additional Permissions -->
            <div class="form-section" *ngIf="showAdditionalPermissions()">
              <h3>{{ 'permissions.modal.additionalPermissions' | translate }}</h3>
              <p class="section-description">{{ 'permissions.modal.additionalDescription' | translate }}</p>
              
              <div class="permissions-grid">
                <div 
                  *ngFor="let permission of additionalPermissions(); trackBy: trackByPermission" 
                  class="permission-option"
                >
                  <label class="permission-checkbox">
                    <input 
                      type="checkbox" 
                      [value]="permission"
                      formControlName="additionalPermissions"
                      [attr.data-testid]="'permission-option-' + permission"
                    />
                    <div class="checkbox-custom"></div>
                    <span class="permission-name">{{ permission }}</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Date Range -->
            <div class="form-section">
              <h3>{{ 'permissions.modal.dateRange' | translate }}</h3>
              <div class="date-inputs">
                <div class="date-field">
                  <label for="startDate">{{ 'permissions.modal.startDate' | translate }}</label>
                  <input 
                    id="startDate"
                    type="date" 
                    formControlName="startDate"
                    class="form-input"
                  />
                </div>
                <div class="date-field">
                  <label for="endDate">{{ 'permissions.modal.endDate' | translate }}</label>
                  <input 
                    id="endDate"
                    type="date" 
                    formControlName="endDate"
                    class="form-input"
                  />
                  <small class="field-hint">{{ 'permissions.modal.endDateHint' | translate }}</small>
                </div>
              </div>
            </div>

            <!-- Status Toggle -->
            <div class="form-section">
              <div class="status-toggle">
                <label class="toggle-switch">
                  <input 
                    type="checkbox" 
                    formControlName="isActive"
                    data-testid="status-toggle"
                  />
                  <span class="toggle-slider"></span>
                  <span class="toggle-label">{{ 'permissions.modal.isActive' | translate }}</span>
                </label>
              </div>
            </div>
          </form>

          <!-- Validation Warnings -->
          <div class="validation-warnings" *ngIf="validationWarnings().length > 0">
            <div class="warning-header">
              <i class="icon-alert-triangle"></i>
              {{ 'permissions.modal.warnings' | translate }}
            </div>
            <ul class="warnings-list">
              <li *ngFor="let warning of validationWarnings()">{{ warning }}</li>
            </ul>
          </div>

          <!-- Error Messages -->
          <div class="error-messages" *ngIf="errors().length > 0">
            <div class="error-header">
              <i class="icon-alert-circle"></i>
              {{ 'permissions.modal.errors' | translate }}
            </div>
            <ul class="errors-list">
              <li *ngFor="let error of errors()">{{ error }}</li>
            </ul>
          </div>
        </div>

        <div class="modal-footer">
          <button 
            type="button" 
            class="btn btn-outline"
            (click)="closeModal()"
            [disabled]="saving()"
          >
            {{ 'common.cancel' | translate }}
          </button>
          
          <button 
            type="button"
            class="btn btn-outline"
            (click)="validateAssignment()"
            [disabled]="saving() || !assignmentForm.valid"
            data-testid="validate-btn"
          >
            <i class="icon-shield-check"></i>
            {{ 'permissions.modal.validate' | translate }}
          </button>
          
          <button 
            type="button"
            class="btn btn-primary"
            (click)="saveAssignment()"
            [disabled]="saving() || !assignmentForm.valid || errors().length > 0"
            data-testid="save-assignment-btn"
          >
            <span *ngIf="saving()" class="spinner"></span>
            {{ saving() ? ('common.saving' | translate) : (isEditing() ? ('common.save' | translate) : ('permissions.modal.assign' | translate)) }}
          </button>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }

    .modal-content {
      background: var(--color-surface);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-xl);
      max-width: 800px;
      max-height: 90vh;
      width: 90%;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: var(--space-lg);
      border-bottom: 1px solid var(--color-border);
    }

    .modal-header h2 {
      margin: 0;
      color: var(--color-text-primary);
      font-size: var(--font-size-xl);
      font-weight: var(--font-weight-semibold);
    }

    .close-btn {
      background: none;
      border: none;
      color: var(--color-text-secondary);
      cursor: pointer;
      padding: var(--space-sm);
      border-radius: var(--radius-sm);
      transition: all 0.2s ease;
    }

    .close-btn:hover {
      background: var(--color-surface-hover);
      color: var(--color-text-primary);
    }

    .modal-body {
      flex: 1;
      overflow-y: auto;
      padding: var(--space-lg);
    }

    .assignment-info {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--space-lg);
      margin-bottom: var(--space-xl);
      padding: var(--space-md);
      background: var(--color-surface-secondary);
      border-radius: var(--radius-md);
    }

    .info-section h3 {
      margin: 0 0 var(--space-sm) 0;
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-semibold);
      color: var(--color-text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .user-display, .school-display {
      display: flex;
      align-items: center;
      gap: var(--space-sm);
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--color-primary);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: var(--font-weight-semibold);
    }

    .user-name, .school-name {
      font-weight: var(--font-weight-medium);
      color: var(--color-text-primary);
    }

    .user-email, .school-code {
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
    }

    .form-section {
      margin-bottom: var(--space-xl);
    }

    .form-section h3 {
      margin: 0 0 var(--space-xs) 0;
      font-size: var(--font-size-lg);
      font-weight: var(--font-weight-semibold);
      color: var(--color-text-primary);
    }

    .section-description {
      margin: 0 0 var(--space-md) 0;
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
      line-height: 1.5;
    }

    .roles-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: var(--space-sm);
    }

    .role-option {
      border: 1px solid var(--color-border);
      border-radius: var(--radius-md);
      transition: all 0.2s ease;
    }

    .role-option:hover {
      border-color: var(--color-primary-light);
    }

    .role-option.selected {
      border-color: var(--color-primary);
      background: var(--color-primary-light);
    }

    .role-checkbox {
      display: flex;
      align-items: flex-start;
      gap: var(--space-md);
      padding: var(--space-md);
      cursor: pointer;
      width: 100%;
    }

    .role-checkbox input[type="checkbox"] {
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }

    .checkbox-custom {
      width: 20px;
      height: 20px;
      border: 2px solid var(--color-border);
      border-radius: var(--radius-sm);
      background: var(--color-surface);
      transition: all 0.2s ease;
      position: relative;
      flex-shrink: 0;
      margin-top: 2px;
    }

    .role-checkbox input[type="checkbox"]:checked + .checkbox-custom {
      background: var(--color-primary);
      border-color: var(--color-primary);
    }

    .role-checkbox input[type="checkbox"]:checked + .checkbox-custom::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 10px;
      height: 10px;
      background: white;
      mask: url('data:image/svg+xml,<svg viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>') no-repeat center center;
    }

    .role-info {
      flex: 1;
    }

    .role-name {
      font-weight: var(--font-weight-semibold);
      color: var(--color-text-primary);
      margin-bottom: var(--space-xs);
    }

    .role-identifier {
      font-size: var(--font-size-sm);
      color: var(--color-text-secondary);
      font-family: var(--font-family-mono);
      background: var(--color-surface-secondary);
      padding: 2px var(--space-xs);
      border-radius: var(--radius-xs);
      display: inline-block;
      margin-bottom: var(--space-xs);
    }

    .role-permissions-count {
      font-size: var(--font-size-xs);
      color: var(--color-primary);
      font-weight: var(--font-weight-medium);
    }

    .date-inputs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--space-md);
    }

    .date-field {
      display: flex;
      flex-direction: column;
      gap: var(--space-xs);
    }

    .date-field label {
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-medium);
      color: var(--color-text-secondary);
    }

    .form-input {
      padding: var(--space-sm);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-sm);
      background: var(--color-surface);
      color: var(--color-text-primary);
    }

    .form-input:focus {
      outline: none;
      border-color: var(--color-primary);
      box-shadow: 0 0 0 3px var(--color-primary-alpha);
    }

    .field-hint {
      font-size: var(--font-size-xs);
      color: var(--color-text-secondary);
      font-style: italic;
    }

    .status-toggle {
      display: flex;
      align-items: center;
      gap: var(--space-md);
    }

    .toggle-switch {
      display: flex;
      align-items: center;
      gap: var(--space-sm);
      cursor: pointer;
    }

    .toggle-switch input[type="checkbox"] {
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }

    .toggle-slider {
      width: 44px;
      height: 24px;
      background: var(--color-border);
      border-radius: 12px;
      position: relative;
      transition: background-color 0.2s ease;
    }

    .toggle-slider::after {
      content: '';
      position: absolute;
      width: 20px;
      height: 20px;
      background: white;
      border-radius: 50%;
      top: 2px;
      left: 2px;
      transition: transform 0.2s ease;
    }

    .toggle-switch input[type="checkbox"]:checked + .toggle-slider {
      background: var(--color-primary);
    }

    .toggle-switch input[type="checkbox"]:checked + .toggle-slider::after {
      transform: translateX(20px);
    }

    .toggle-label {
      font-weight: var(--font-weight-medium);
      color: var(--color-text-primary);
    }

    .validation-warnings, .error-messages {
      margin-top: var(--space-lg);
      padding: var(--space-md);
      border-radius: var(--radius-md);
    }

    .validation-warnings {
      background: var(--color-warning-light);
      border: 1px solid var(--color-warning);
    }

    .error-messages {
      background: var(--color-danger-light);
      border: 1px solid var(--color-danger);
    }

    .warning-header, .error-header {
      display: flex;
      align-items: center;
      gap: var(--space-sm);
      font-weight: var(--font-weight-semibold);
      margin-bottom: var(--space-sm);
    }

    .warning-header {
      color: var(--color-warning);
    }

    .error-header {
      color: var(--color-danger);
    }

    .warnings-list, .errors-list {
      margin: 0;
      padding-left: var(--space-lg);
    }

    .warnings-list li {
      color: var(--color-warning);
    }

    .errors-list li {
      color: var(--color-danger);
    }

    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: var(--space-sm);
      padding: var(--space-lg);
      border-top: 1px solid var(--color-border);
    }

    .btn {
      padding: var(--space-sm) var(--space-md);
      border-radius: var(--radius-sm);
      font-size: var(--font-size-sm);
      font-weight: var(--font-weight-medium);
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: var(--space-xs);
      transition: all 0.2s ease;
    }

    .btn-primary {
      background: var(--color-primary);
      color: white;
    }

    .btn-primary:hover:not(:disabled) {
      background: var(--color-primary-dark);
    }

    .btn-outline {
      background: transparent;
      color: var(--color-text-primary);
      border: 1px solid var(--color-border);
    }

    .btn-outline:hover:not(:disabled) {
      background: var(--color-surface-hover);
    }

    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .spinner {
      width: 16px;
      height: 16px;
      border: 2px solid transparent;
      border-top: 2px solid currentColor;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
      .modal-content {
        width: 95%;
        max-height: 95vh;
      }

      .assignment-info {
        grid-template-columns: 1fr;
      }

      .date-inputs {
        grid-template-columns: 1fr;
      }

      .modal-footer {
        flex-direction: column;
      }
    }
  `]
})
export class PermissionAssignmentModalComponent implements OnInit, OnChanges {
  @Input() isOpen = false;
  @Input() userId: number | null = null;
  @Input() schoolId: number | null = null;
  @Input() existingAssignment: UserSchoolRole | null = null;
  
  @Output() closed = new EventEmitter<void>();
  @Output() saved = new EventEmitter<UserSchoolRole>();

  private readonly permissionsService = inject(SchoolPermissionsService);
  private readonly rolesService = inject(RolesService);
  private readonly fb = inject(FormBuilder);

  // Reactive state using signals
  availableRoles = signal<Role[]>([]);
  additionalPermissions = signal<string[]>([]);
  userInfo = signal<{ name: string; email: string } | null>(null);
  schoolInfo = signal<{ name: string; code: string } | null>(null);
  saving = signal(false);
  validationWarnings = signal<string[]>([]);
  errors = signal<string[]>([]);

  // Computed values
  isEditing = computed(() => !!this.existingAssignment);
  showAdditionalPermissions = computed(() => this.availableRoles().length > 0);

  // Form
  assignmentForm: FormGroup;

  constructor() {
    this.assignmentForm = this.fb.group({
      roles: this.fb.array([]),
      additionalPermissions: [[]],
      startDate: [''],
      endDate: [''],
      isActive: [true, Validators.required]
    });
  }

  ngOnInit(): void {
    this.loadRoles();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['isOpen'] && this.isOpen) {
      this.loadAssignmentData();
    }
    
    if (changes['existingAssignment'] && this.existingAssignment) {
      this.populateForm();
    }
  }

  private loadRoles(): void {
    this.rolesService.getRoles().pipe(
      catchError(error => {
        console.error('Error loading roles:', error);
        return of({ data: [] as Role[] });
      })
    ).subscribe(response => {
      this.availableRoles.set(response.data);
    });
  }

  private loadAssignmentData(): void {
    if (!this.userId || !this.schoolId) return;

    // Load user and school info
    // This would typically come from parent component or separate service calls
    // For now, we'll mock it
    this.userInfo.set({ name: 'User Name', email: 'user@example.com' });
    this.schoolInfo.set({ name: 'School Name', code: 'SCH001' });
  }

  private populateForm(): void {
    if (!this.existingAssignment) return;

    this.assignmentForm.patchValue({
      additionalPermissions: this.existingAssignment.permissions || [],
      startDate: this.existingAssignment.startDate || '',
      endDate: this.existingAssignment.endDate || '',
      isActive: this.existingAssignment.isActive
    });

    // Set selected roles
    const rolesArray = this.assignmentForm.get('roles') as FormArray;
    rolesArray.clear();
    this.existingAssignment.roles.forEach(role => {
      rolesArray.push(this.fb.control(role));
    });
  }

  isRoleSelected(roleName: string): boolean {
    const rolesArray = this.assignmentForm.get('roles') as FormArray;
    return rolesArray.value.includes(roleName);
  }

  toggleRole(roleName: string, event: Event): void {
    const checkbox = event.target as HTMLInputElement;
    const rolesArray = this.assignmentForm.get('roles') as FormArray;
    
    if (checkbox.checked && !rolesArray.value.includes(roleName)) {
      rolesArray.push(this.fb.control(roleName));
    } else if (!checkbox.checked) {
      const index = rolesArray.value.indexOf(roleName);
      if (index > -1) {
        rolesArray.removeAt(index);
      }
    }
    
    this.assignmentForm.markAsDirty();
  }

  validateAssignment(): void {
    if (!this.userId || !this.schoolId) return;

    const assignment: Omit<UserSchoolRole, 'id'> = {
      userId: this.userId,
      schoolId: this.schoolId,
      roles: this.assignmentForm.get('roles')?.value || [],
      permissions: this.assignmentForm.get('additionalPermissions')?.value || [],
      startDate: this.assignmentForm.get('startDate')?.value || undefined,
      endDate: this.assignmentForm.get('endDate')?.value || undefined,
      isActive: this.assignmentForm.get('isActive')?.value || true
    };

    this.permissionsService.validatePermissionAssignment(assignment).pipe(
      catchError(error => {
        this.errors.set(['Validation failed']);
        return of({ valid: false, warnings: [], errors: ['Validation failed'] });
      })
    ).subscribe(result => {
      this.validationWarnings.set(result.warnings);
      this.errors.set(result.errors);
    });
  }

  saveAssignment(): void {
    if (!this.assignmentForm.valid || !this.userId || !this.schoolId) return;

    this.saving.set(true);
    this.errors.set([]);

    const assignment: Omit<UserSchoolRole, 'id'> = {
      userId: this.userId,
      schoolId: this.schoolId,
      roles: this.assignmentForm.get('roles')?.value || [],
      permissions: this.assignmentForm.get('additionalPermissions')?.value || [],
      startDate: this.assignmentForm.get('startDate')?.value || undefined,
      endDate: this.assignmentForm.get('endDate')?.value || undefined,
      isActive: this.assignmentForm.get('isActive')?.value || true
    };

    const saveOperation = this.existingAssignment 
      ? this.permissionsService.updateUserSchoolRoles(this.existingAssignment.id!, assignment)
      : this.permissionsService.assignUserSchoolRoles(assignment);

    saveOperation.pipe(
      catchError(error => {
        this.errors.set([error.message || 'Failed to save assignment']);
        return of(null);
      }),
      finalize(() => this.saving.set(false))
    ).subscribe(result => {
      if (result) {
        this.saved.emit(result);
        this.closeModal();
      }
    });
  }

  closeModal(): void {
    this.closed.emit();
    this.resetForm();
  }

  private resetForm(): void {
    this.assignmentForm.reset({ isActive: true });
    this.validationWarnings.set([]);
    this.errors.set([]);
  }

  getInitials(name: string): string {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .substring(0, 2);
  }

  trackByRoleId(index: number, role: Role): number {
    return role.id;
  }

  trackByPermission(index: number, permission: string): string {
    return permission;
  }
}