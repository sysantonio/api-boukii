import { inject, Injectable } from '@angular/core';
import { Observable, from } from 'rxjs';
import { ApiService } from '../../../../core/services/api.service';
import { 
  UserPermissionMatrix,
  PermissionMatrixResponse,
  UserSchoolRole,
  BulkPermissionAssignment,
  PermissionAssignmentFilters,
  SchoolRoleAssignment
} from '../types/school-permissions.types';

@Injectable({
  providedIn: 'root'
})
export class SchoolPermissionsService {
  private readonly api = inject(ApiService);

  /**
   * Get permission matrix for all users across all schools
   */
  getPermissionMatrix(filters?: PermissionAssignmentFilters): Observable<PermissionMatrixResponse> {
    const params = this.buildParams(filters);
    return from(this.api.get<PermissionMatrixResponse>('/admin/permissions/matrix', params));
  }

  /**
   * Get user permissions across all schools
   */
  getUserSchoolPermissions(userId: number): Observable<UserPermissionMatrix> {
    return from(this.api.get<UserPermissionMatrix>(`/admin/users/${userId}/school-permissions`));
  }

  /**
   * Get all role assignments for a specific school
   */
  getSchoolRoleAssignments(schoolId: number): Observable<SchoolRoleAssignment> {
    return from(this.api.get<SchoolRoleAssignment>(`/admin/schools/${schoolId}/role-assignments`));
  }

  /**
   * Assign roles to user in specific school
   */
  assignUserSchoolRoles(assignment: Omit<UserSchoolRole, 'id'>): Observable<UserSchoolRole> {
    return from(this.api.post<UserSchoolRole>('/admin/permissions/assign', assignment));
  }

  /**
   * Update user school roles
   */
  updateUserSchoolRoles(assignmentId: number, assignment: Partial<UserSchoolRole>): Observable<UserSchoolRole> {
    return from(this.api.put<UserSchoolRole>(`/admin/permissions/${assignmentId}`, assignment));
  }

  /**
   * Remove user school roles
   */
  removeUserSchoolRoles(assignmentId: number): Observable<void> {
    return from(this.api.delete<void>(`/admin/permissions/${assignmentId}`));
  }

  /**
   * Bulk assign roles to multiple users
   */
  bulkAssignRoles(assignment: BulkPermissionAssignment): Observable<{ successful: number; failed: number; errors: string[] }> {
    return from(this.api.post<{ successful: number; failed: number; errors: string[] }>('/admin/permissions/bulk-assign', assignment));
  }

  /**
   * Get effective permissions for user in school context
   */
  getUserEffectivePermissions(userId: number, schoolId: number): Observable<string[]> {
    return from(this.api.get<string[]>(`/admin/users/${userId}/effective-permissions?school_id=${schoolId}`));
  }

  /**
   * Validate permission assignment
   */
  validatePermissionAssignment(assignment: Omit<UserSchoolRole, 'id'>): Observable<{ valid: boolean; warnings: string[]; errors: string[] }> {
    return from(this.api.post<{ valid: boolean; warnings: string[]; errors: string[] }>('/admin/permissions/validate', assignment));
  }

  /**
   * Get permission history for user
   */
  getUserPermissionHistory(userId: number, schoolId?: number): Observable<{
    changes: Array<{
      id: number;
      action: 'assigned' | 'removed' | 'modified';
      roles: string[];
      permissions: string[];
      schoolId: number;
      schoolName: string;
      changedBy: string;
      changedAt: string;
      reason?: string;
    }>;
  }> {
    const params = schoolId ? { school_id: schoolId } : undefined;
    return from(this.api.get<{
      changes: Array<{
        id: number;
        action: 'assigned' | 'removed' | 'modified';
        roles: string[];
        permissions: string[];
        schoolId: number;
        schoolName: string;
        changedBy: string;
        changedAt: string;
        reason?: string;
      }>;
    }>(`/admin/users/${userId}/permission-history`, params));
  }

  /**
   * Export permission matrix
   */
  exportPermissionMatrix(filters?: PermissionAssignmentFilters, format: 'csv' | 'xlsx' = 'xlsx'): Observable<Blob> {
    const params = { ...this.buildParams(filters), format };
    return from(this.api.getBlob('/admin/permissions/export', params));
  }

  /**
   * Build query parameters from filters
   */
  private buildParams(filters?: PermissionAssignmentFilters): Record<string, string | number | boolean> | undefined {
    if (!filters) return undefined;

    const params: Record<string, string | number | boolean> = {};
    
    if (filters.schoolId) params['school_id'] = filters.schoolId;
    if (filters.userId) params['user_id'] = filters.userId;
    if (filters.role) params['role'] = filters.role;
    if (filters.status) params['status'] = filters.status;
    if (filters.search?.trim()) params['search'] = filters.search.trim();
    if (filters.startDate) params['start_date'] = filters.startDate;
    if (filters.endDate) params['end_date'] = filters.endDate;
    if (filters.page) params['page'] = filters.page;
    if (filters.perPage) params['per_page'] = filters.perPage;

    return Object.keys(params).length > 0 ? params : undefined;
  }
}