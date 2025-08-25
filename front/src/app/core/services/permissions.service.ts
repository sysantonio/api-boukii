import { Injectable, inject, computed } from '@angular/core';
import { AuthV5Service } from './auth-v5.service';
import { ContextService } from './context.service';
import { Role, Permission, SchoolPermission } from '../models/auth-v5.models';

export interface PermissionCheck {
  resource: string;
  action: string;
  school_id?: number;
}

@Injectable({
  providedIn: 'root'
})
export class PermissionsService {
  private readonly auth = inject(AuthV5Service);
  private readonly context = inject(ContextService);

  /**
   * Get all permissions for current user in current context
   */
  readonly currentPermissions = computed(() => {
    return this.auth.permissions();
  });

  /**
   * Get effective roles for current user in current school
   */
  readonly currentRoles = computed(() => {
    const user = this.auth.user();
    const schoolId = this.context.schoolId();
    
    if (!user?.roles || !schoolId) return [];
    
    return user.roles
      .filter(userRole => userRole.school_id === schoolId)
      .map(userRole => userRole.role);
  });

  /**
   * Check if user has specific permission
   */
  hasPermission(permission: string): boolean {
    const permissions = this.currentPermissions();
    return permissions.includes(permission);
  }

  /**
   * Check if user has permission with resource and action
   */
  hasResourcePermission(resource: string, action: string): boolean {
    const permissionKey = `${resource}.${action}`;
    return this.hasPermission(permissionKey);
  }

  /**
   * Check if user has any of the specified permissions
   */
  hasAnyPermission(permissions: string[]): boolean {
    const userPermissions = this.currentPermissions();
    return permissions.some(permission => userPermissions.includes(permission));
  }

  /**
   * Check if user has all specified permissions
   */
  hasAllPermissions(permissions: string[]): boolean {
    const userPermissions = this.currentPermissions();
    return permissions.every(permission => userPermissions.includes(permission));
  }

  /**
   * Check if user has specific role
   */
  hasRole(roleSlug: string): boolean {
    const roles = this.currentRoles();
    return roles.some(role => role.slug === roleSlug);
  }

  /**
   * Check if user has any of the specified roles
   */
  hasAnyRole(rolesSlugs: string[]): boolean {
    const roles = this.currentRoles();
    return rolesSlugs.some(roleSlug => 
      roles.some(role => role.slug === roleSlug)
    );
  }

  /**
   * Check if user can manage the current school
   */
  canManageSchool(): boolean {
    return this.hasAnyRole(['admin', 'manager', 'owner']) || 
           this.hasPermission('school.manage');
  }

  /**
   * Check if user can administrate the current school
   */
  canAdministrateSchool(): boolean {
    return this.hasAnyRole(['admin', 'owner']) || 
           this.hasPermission('school.administrate');
  }

  /**
   * Check if user can access seasons management
   */
  canManageSeasons(): boolean {
    return this.hasResourcePermission('seasons', 'manage') ||
           this.canManageSchool();
  }

  /**
   * Check if user can manage clients
   */
  canManageClients(): boolean {
    return this.hasResourcePermission('clients', 'manage') ||
           this.hasResourcePermission('clients', 'create') ||
           this.hasResourcePermission('clients', 'edit');
  }

  /**
   * Check if user can view clients
   */
  canViewClients(): boolean {
    return this.hasResourcePermission('clients', 'view') ||
           this.canManageClients();
  }

  /**
   * Check if user can manage bookings
   */
  canManageBookings(): boolean {
    return this.hasResourcePermission('bookings', 'manage') ||
           this.hasResourcePermission('bookings', 'create') ||
           this.hasResourcePermission('bookings', 'edit');
  }

  /**
   * Check if user can manage courses
   */
  canManageCourses(): boolean {
    return this.hasResourcePermission('courses', 'manage') ||
           this.hasResourcePermission('courses', 'create') ||
           this.hasResourcePermission('courses', 'edit');
  }

  /**
   * Check if user can manage monitors (instructors)
   */
  canManageMonitors(): boolean {
    return this.hasResourcePermission('monitors', 'manage') ||
           this.hasResourcePermission('monitors', 'create') ||
           this.hasResourcePermission('monitors', 'edit');
  }

  /**
   * Check if user can access reports
   */
  canAccessReports(): boolean {
    return this.hasResourcePermission('reports', 'view') ||
           this.canManageSchool();
  }

  /**
   * Check if user can manage users and roles
   */
  canManageUsers(): boolean {
    return this.hasResourcePermission('users', 'manage') ||
           this.canAdministrateSchool();
  }

  /**
   * Get permissions for a specific resource
   */
  getResourcePermissions(resource: string): string[] {
    const permissions = this.currentPermissions();
    return permissions.filter(permission => permission.startsWith(`${resource}.`));
  }

  /**
   * Get a formatted list of current user permissions for debugging
   */
  getPermissionsSummary(): {
    roles: string[];
    permissions: string[];
    school_management: {
      can_manage: boolean;
      can_administrate: boolean;
    };
    modules: {
      seasons: boolean;
      clients: boolean;
      bookings: boolean;
      courses: boolean;
      monitors: boolean;
      reports: boolean;
      users: boolean;
    };
  } {
    const roles = this.currentRoles();
    const permissions = this.currentPermissions();

    return {
      roles: roles.map(role => role.slug),
      permissions,
      school_management: {
        can_manage: this.canManageSchool(),
        can_administrate: this.canAdministrateSchool()
      },
      modules: {
        seasons: this.canManageSeasons(),
        clients: this.canViewClients(),
        bookings: this.canManageBookings(),
        courses: this.canManageCourses(),
        monitors: this.canManageMonitors(),
        reports: this.canAccessReports(),
        users: this.canManageUsers()
      }
    };
  }

  /**
   * Common permission constants
   */
  static readonly PERMISSIONS = {
    // School management
    SCHOOL_VIEW: 'school.view',
    SCHOOL_MANAGE: 'school.manage',
    SCHOOL_ADMINISTRATE: 'school.administrate',

    // Seasons
    SEASONS_VIEW: 'seasons.view',
    SEASONS_CREATE: 'seasons.create',
    SEASONS_EDIT: 'seasons.edit',
    SEASONS_DELETE: 'seasons.delete',
    SEASONS_MANAGE: 'seasons.manage',

    // Clients
    CLIENTS_VIEW: 'clients.view',
    CLIENTS_CREATE: 'clients.create',
    CLIENTS_EDIT: 'clients.edit',
    CLIENTS_DELETE: 'clients.delete',
    CLIENTS_MANAGE: 'clients.manage',

    // Bookings
    BOOKINGS_VIEW: 'bookings.view',
    BOOKINGS_CREATE: 'bookings.create',
    BOOKINGS_EDIT: 'bookings.edit',
    BOOKINGS_DELETE: 'bookings.delete',
    BOOKINGS_MANAGE: 'bookings.manage',

    // Courses
    COURSES_VIEW: 'courses.view',
    COURSES_CREATE: 'courses.create',
    COURSES_EDIT: 'courses.edit',
    COURSES_DELETE: 'courses.delete',
    COURSES_MANAGE: 'courses.manage',

    // Monitors
    MONITORS_VIEW: 'monitors.view',
    MONITORS_CREATE: 'monitors.create',
    MONITORS_EDIT: 'monitors.edit',
    MONITORS_DELETE: 'monitors.delete',
    MONITORS_MANAGE: 'monitors.manage',

    // Users
    USERS_VIEW: 'users.view',
    USERS_CREATE: 'users.create',
    USERS_EDIT: 'users.edit',
    USERS_DELETE: 'users.delete',
    USERS_MANAGE: 'users.manage',

    // Reports
    REPORTS_VIEW: 'reports.view',
    REPORTS_EXPORT: 'reports.export',

    // Settings
    SETTINGS_VIEW: 'settings.view',
    SETTINGS_EDIT: 'settings.edit'
  } as const;

  /**
   * Common roles constants
   */
  static readonly ROLES = {
    OWNER: 'owner',
    ADMIN: 'admin', 
    MANAGER: 'manager',
    INSTRUCTOR: 'instructor',
    RECEPTIONIST: 'receptionist',
    VIEWER: 'viewer'
  } as const;
}