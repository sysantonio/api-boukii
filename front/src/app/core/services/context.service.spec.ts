import { TestBed } from '@angular/core/testing';
import { of, throwError } from 'rxjs';
import { ContextService, School, Season } from './context.service';
import { ApiHttpService } from './api-http.service';

describe('ContextService', () => {
  let service: ContextService;
  let mockApiHttp: jasmine.SpyObj<ApiHttpService>;

  const mockSchool: School = {
    id: 1,
    name: 'Test School',
    slug: 'test-school',
    active: true
  };

  const mockSeason: Season = {
    id: 1,
    name: 'Test Season',
    startDate: '2024-01-01',
    endDate: '2024-12-31',
    active: true,
    schoolId: 1
  };

  beforeEach(() => {
    const apiHttpSpy = jasmine.createSpyObj('ApiHttpService', ['post', 'get']);

    TestBed.configureTestingModule({
      providers: [
        ContextService,
        { provide: ApiHttpService, useValue: apiHttpSpy }
      ]
    });

    service = TestBed.inject(ContextService);
    mockApiHttp = TestBed.inject(ApiHttpService) as jasmine.SpyObj<ApiHttpService>;

    // Clear localStorage before each test
    localStorage.clear();
  });

  afterEach(() => {
    localStorage.clear();
  });

  describe('Initial State', () => {
    it('should be created', () => {
      expect(service).toBeTruthy();
    });

    it('should have null initial values', () => {
      expect(service.schoolId()).toBeNull();
      expect(service.seasonId()).toBeNull();
      expect(service.school()).toBeNull();
      expect(service.season()).toBeNull();
    });

    it('should have incomplete context initially', () => {
      expect(service.hasCompleteContext()).toBeFalse();
      expect(service.hasSchoolSelected()).toBeFalse();
    });
  });

  describe('setSchool', () => {
    beforeEach(() => {
      mockApiHttp.post.and.returnValue(of({}));
      mockApiHttp.get.and.returnValue(of(mockSchool));
    });

    it('should set school context successfully', async () => {
      await service.setSchool(1);

      expect(mockApiHttp.post).toHaveBeenCalledWith('/api/v5/context/school', { schoolId: 1 });
      expect(service.schoolId()).toBe(1);
      expect(service.hasSchoolSelected()).toBeTrue();
    });

    it('should store school ID in localStorage', async () => {
      await service.setSchool(1);

      expect(localStorage.getItem('context_schoolId')).toBe('1');
    });

    it('should reset season when school changes', async () => {
      // Set initial season
      localStorage.setItem('context_seasonId', '5');
      
      await service.setSchool(1);

      expect(service.seasonId()).toBeNull();
      expect(localStorage.getItem('context_seasonId')).toBeNull();
    });

    it('should load school details via /schools/{id}', async () => {
      await service.setSchool(1);

      const expectedPath = '/api/v5/schools/1';
      expect(mockApiHttp.get).toHaveBeenCalledWith(expectedPath);
      expect(service.school()).toEqual(mockSchool);
    });

    it('should handle API error', async () => {
      mockApiHttp.post.and.returnValue(throwError(() => new Error('API Error')));

      await expectAsync(service.setSchool(1)).toBeRejectedWithError('API Error');
    });
  });

  describe('setSeason', () => {
    beforeEach(() => {
      mockApiHttp.post.and.returnValue(of({}));
      mockApiHttp.get.and.returnValue(of(mockSeason));
    });

    it('should set season context successfully', async () => {
      await service.setSeason(1);

      expect(mockApiHttp.post).toHaveBeenCalledWith('/api/v5/context/season', { seasonId: 1 });
      expect(service.seasonId()).toBe(1);
    });

    it('should store season ID in localStorage', async () => {
      await service.setSeason(1);

      expect(localStorage.getItem('context_seasonId')).toBe('1');
    });

    it('should load season details', async () => {
      await service.setSeason(1);

      expect(mockApiHttp.get).toHaveBeenCalledWith('/api/v5/seasons/1');
      expect(service.season()).toEqual(mockSeason);
    });

    it('should handle API error', async () => {
      mockApiHttp.post.and.returnValue(throwError(() => new Error('Season API Error')));

      await expectAsync(service.setSeason(1)).toBeRejectedWithError('Season API Error');
    });
  });

  describe('clearContext', () => {
    it('should clear all context data', () => {
      // Set initial data
      localStorage.setItem('context_schoolId', '1');
      localStorage.setItem('context_seasonId', '2');

      service.clearContext();

      expect(service.schoolId()).toBeNull();
      expect(service.seasonId()).toBeNull();
      expect(service.school()).toBeNull();
      expect(service.season()).toBeNull();
      expect(localStorage.getItem('context_schoolId')).toBeNull();
      expect(localStorage.getItem('context_seasonId')).toBeNull();
    });
  });

  describe('context computed', () => {
    it('should return complete context object', async () => {
      mockApiHttp.post.and.returnValue(of({}));
      mockApiHttp.get.and.returnValue(of(mockSchool));

      await service.setSchool(1);

      const context = service.context();
      expect(context).toEqual({
        schoolId: 1,
        seasonId: null,
        school: mockSchool,
        season: undefined
      });
    });
  });

  describe('hasCompleteContext', () => {
    it('should return true when both school and season are selected', async () => {
      mockApiHttp.post.and.returnValue(of({}));
      mockApiHttp.get.and.returnValue(of(mockSchool));

      await service.setSchool(1);
      
      mockApiHttp.get.and.returnValue(of(mockSeason));
      await service.setSeason(1);

      expect(service.hasCompleteContext()).toBeTrue();
    });

    it('should return false when only school is selected', async () => {
      mockApiHttp.post.and.returnValue(of({}));
      mockApiHttp.get.and.returnValue(of(mockSchool));

      await service.setSchool(1);

      expect(service.hasCompleteContext()).toBeFalse();
    });
  });

  describe('validateContext', () => {
    it('should return false if context is incomplete', async () => {
      const result = await service.validateContext();

      expect(result).toBeFalse();
    });

    it('should validate complete context with server', async () => {
      // Setup complete context
      mockApiHttp.post.and.returnValue(of({}));
      mockApiHttp.get.and.returnValues(of(mockSchool), of(mockSeason));
      
      await service.setSchool(1);
      await service.setSeason(1);

      // Mock validation response
      mockApiHttp.get.and.returnValue(of({ valid: true }));

      const result = await service.validateContext();

      expect(result).toBeTrue();
      expect(mockApiHttp.get).toHaveBeenCalledWith('/api/v5/context/validate');
    });

    it('should clear context if validation fails', async () => {
      // Setup complete context
      mockApiHttp.post.and.returnValue(of({}));
      mockApiHttp.get.and.returnValues(of(mockSchool), of(mockSeason));
      
      await service.setSchool(1);
      await service.setSeason(1);

      // Mock invalid validation response
      mockApiHttp.get.and.returnValue(of({ valid: false }));

      const result = await service.validateContext();

      expect(result).toBeFalse();
      expect(service.schoolId()).toBeNull();
      expect(service.seasonId()).toBeNull();
    });

    it('should handle validation API error', async () => {
      // Setup complete context
      mockApiHttp.post.and.returnValue(of({}));
      mockApiHttp.get.and.returnValues(of(mockSchool), of(mockSeason));
      
      await service.setSchool(1);
      await service.setSeason(1);

      // Mock validation error
      mockApiHttp.get.and.returnValue(throwError(() => new Error('Validation Error')));

      const result = await service.validateContext();

      expect(result).toBeFalse();
      expect(service.schoolId()).toBeNull(); // Should clear on error
    });
  });

  describe('localStorage integration', () => {
    beforeEach(() => {
      mockApiHttp.get.and.returnValues(of(mockSchool), of(mockSeason));
    });

    it('should load stored school ID on initialization', () => {
      localStorage.setItem('context_schoolId', '5');
      
      // Create new service instance to test initialization
      const newService = new ContextService();
      
      expect(newService.schoolId()).toBe(5);
    });

    it('should load stored season ID on initialization', () => {
      localStorage.setItem('context_seasonId', '3');
      
      // Create new service instance to test initialization
      const newService = new ContextService();
      
      expect(newService.seasonId()).toBe(3);
    });

    it('should handle invalid localStorage values', () => {
      localStorage.setItem('context_schoolId', 'invalid');
      localStorage.setItem('context_seasonId', 'also-invalid');
      
      // Create new service instance to test initialization
      const newService = new ContextService();
      
      expect(newService.schoolId()).toBeNull();
      expect(newService.seasonId()).toBeNull();
    });
  });
});