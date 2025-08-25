import { TestBed } from '@angular/core/testing';
import { SchoolPermissionsService } from './school-permissions.service';
import { ApiService } from '../../../../core/services/api.service';

describe('SchoolPermissionsService', () => {
  let service: SchoolPermissionsService;
  let api: jest.Mocked<ApiService>;

  beforeEach(() => {
    const apiSpy = {
      get: jest.fn().mockResolvedValue({}),
      post: jest.fn().mockResolvedValue({}),
      put: jest.fn().mockResolvedValue({}),
      delete: jest.fn().mockResolvedValue({}),
      getBlob: jest.fn().mockResolvedValue(new Blob()),
    } as unknown as jest.Mocked<ApiService>;

    TestBed.configureTestingModule({
      providers: [
        SchoolPermissionsService,
        { provide: ApiService, useValue: apiSpy },
      ],
    });

    service = TestBed.inject(SchoolPermissionsService);
    api = TestBed.inject(ApiService) as jest.Mocked<ApiService>;
  });

  it('calls getPermissionMatrix with /admin/permissions/matrix', () => {
    service.getPermissionMatrix();
    expect(api.get).toHaveBeenCalledWith('/admin/permissions/matrix', undefined);
  });

  it('calls getUserSchoolPermissions with /admin/users/:id/school-permissions', () => {
    service.getUserSchoolPermissions(1);
    expect(api.get).toHaveBeenCalledWith('/admin/users/1/school-permissions');
  });

  it('calls assignUserSchoolRoles with /admin/permissions/assign', () => {
    const payload = { userId: 1, schoolId: 2, roles: ['admin'] } as any;
    service.assignUserSchoolRoles(payload);
    expect(api.post).toHaveBeenCalledWith('/admin/permissions/assign', payload);
  });

  it('calls updateUserSchoolRoles with /admin/permissions/:id', () => {
    const payload = { roles: ['coach'] } as any;
    service.updateUserSchoolRoles(3, payload);
    expect(api.put).toHaveBeenCalledWith('/admin/permissions/3', payload);
  });

  it('calls removeUserSchoolRoles with /admin/permissions/:id', () => {
    service.removeUserSchoolRoles(4);
    expect(api.delete).toHaveBeenCalledWith('/admin/permissions/4');
  });

  it('calls bulkAssignRoles with /admin/permissions/bulk-assign', () => {
    const payload = { assignments: [] } as any;
    service.bulkAssignRoles(payload);
    expect(api.post).toHaveBeenCalledWith('/admin/permissions/bulk-assign', payload);
  });

  it('calls getUserEffectivePermissions with /admin/users/:id/effective-permissions', () => {
    service.getUserEffectivePermissions(5, 10);
    expect(api.get).toHaveBeenCalledWith('/admin/users/5/effective-permissions?school_id=10');
  });

  it('calls validatePermissionAssignment with /admin/permissions/validate', () => {
    const payload = { userId: 1, schoolId: 2, roles: ['admin'] } as any;
    service.validatePermissionAssignment(payload);
    expect(api.post).toHaveBeenCalledWith('/admin/permissions/validate', payload);
  });

  it('calls getUserPermissionHistory with /admin/users/:id/permission-history', () => {
    service.getUserPermissionHistory(7, 8);
    expect(api.get).toHaveBeenCalledWith('/admin/users/7/permission-history', { school_id: 8 });
  });

  it('calls exportPermissionMatrix with /admin/permissions/export', () => {
    service.exportPermissionMatrix();
    expect(api.getBlob).toHaveBeenCalledWith('/admin/permissions/export', { format: 'xlsx' });
  });
});
