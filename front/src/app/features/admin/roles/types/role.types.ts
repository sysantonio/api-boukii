export interface Role {
  id: number;
  name: string;
  description: string;
  permissions: string[];
}

export interface RolesListResponse {
  data: Role[];
}