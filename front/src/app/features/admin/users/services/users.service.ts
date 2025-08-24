import { inject, Injectable } from '@angular/core';
import { Observable, from } from 'rxjs';
import { ApiService } from '../../../../core/services/api.service';
import { 
  UserListItem, 
  UserDetail, 
  UsersListResponse, 
  UsersFilters 
} from '../types/user.types';

@Injectable({
  providedIn: 'root'
})
export class UsersService {
  private readonly api = inject(ApiService);

  /**
   * Get users list with optional filters
   */
  getUsers(filters?: UsersFilters): Observable<UsersListResponse> {
    const params = this.buildParams(filters);
    return from(this.api.get<UsersListResponse>('/api/v5/users', params));
  }

  /**
   * Get user details by ID
   */
  getUserById(id: number): Observable<UserDetail> {
    return from(this.api.get<UserDetail>(`/api/v5/users/${id}`));
  }

  /**
   * Update user roles
   */
  updateUserRoles(userId: number, roleIds: number[]): Observable<void> {
    return from(this.api.put<void>(`/api/v5/users/${userId}/roles`, { roleIds }));
  }

  /**
   * Update user status
   */
  updateUserStatus(userId: number, status: 'active' | 'inactive'): Observable<UserDetail> {
    return from(this.api.patch<UserDetail>(`/api/v5/users/${userId}`, { status }));
  }

  /**
   * Build query parameters from filters
   */
  private buildParams(filters?: UsersFilters): Record<string, string | number | boolean> | undefined {
    if (!filters) return undefined;

    const params: Record<string, string | number | boolean> = {};
    
    if (filters.search?.trim()) {
      params['search'] = filters.search.trim();
    }
    
    if (filters.role) {
      params['role'] = filters.role;
    }
    
    if (filters.status) {
      params['status'] = filters.status;
    }
    
    if (filters.page) {
      params['page'] = filters.page;
    }
    
    if (filters.perPage) {
      params['per_page'] = filters.perPage;
    }

    return Object.keys(params).length > 0 ? params : undefined;
  }
}