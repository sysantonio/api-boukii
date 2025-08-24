export type UserStatus = 'active' | 'inactive' | 'pending';

export interface UserListItem {
  id: number;
  name: string;
  email: string;
  status: UserStatus;
  roles: string[];
  createdAt: string;
}

export interface UserDetail {
  id: number;
  name: string;
  email: string;
  phone?: string;
  status: UserStatus;
  roles: string[];
  permissions?: string[];
}

export interface UsersListResponse {
  data: UserListItem[];
  meta: {
    total: number;
    page: number;
    perPage: number;
    lastPage: number;
  };
}

export interface UsersFilters {
  search?: string;
  role?: string;
  status?: UserStatus;
  page?: number;
  perPage?: number;
}