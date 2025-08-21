import { TestBed } from '@angular/core/testing';
import { of } from 'rxjs';
import { SchoolService, SchoolsResponse, GetSchoolsParams } from './school.service';
import { ApiHttpService } from './api-http.service';
import { School } from './context.service';

describe('SchoolService', () => {
  let service: SchoolService;
  let mockApiHttp: jasmine.SpyObj<ApiHttpService>;

  const mockSchool: School = {
    id: 1,
    name: 'Test School',
    slug: 'test-school',
    active: true,
    createdAt: '2024-01-01T00:00:00Z',
    updatedAt: '2024-03-01T00:00:00Z'
  };

  const mockSchoolsResponse: SchoolsResponse = {
    data: [mockSchool],
    meta: {
      total: 1,
      page: 1,
      perPage: 20,
      lastPage: 1,
      from: 1,
      to: 1
    }
  };

  beforeEach(() => {
    const apiHttpSpy = jasmine.createSpyObj('ApiHttpService', ['get']);

    TestBed.configureTestingModule({
      providers: [
        SchoolService,
        { provide: ApiHttpService, useValue: apiHttpSpy }
      ]
    });

    service = TestBed.inject(SchoolService);
    mockApiHttp = TestBed.inject(ApiHttpService) as jasmine.SpyObj<ApiHttpService>;
  });

  describe('Service Creation', () => {
    it('should be created', () => {
      expect(service).toBeTruthy();
    });
  });

  describe('getMySchools', () => {
    beforeEach(() => {
      mockApiHttp.get.and.returnValue(of(mockSchoolsResponse));
    });

    it('should call API with default parameters', () => {
      service.getMySchools().subscribe();

      expect(mockApiHttp.get).toHaveBeenCalledWith(
        '/api/v5/schools?page=1&perPage=20&active=true&orderBy=name&orderDirection=asc'
      );
    });

    it('should call API with custom parameters', () => {
      const params: GetSchoolsParams = {
        page: 2,
        perPage: 10,
        search: 'test',
        active: false,
        orderBy: 'createdAt',
        orderDirection: 'desc'
      };

      service.getMySchools(params).subscribe();

      expect(mockApiHttp.get).toHaveBeenCalledWith(
        '/api/v5/schools?page=2&perPage=10&search=test&active=false&orderBy=createdAt&orderDirection=desc'
      );
    });

    it('should omit empty search parameter', () => {
      const params: GetSchoolsParams = {
        search: '',
        page: 1
      };

      service.getMySchools(params).subscribe();

      expect(mockApiHttp.get).toHaveBeenCalledWith(
        '/api/v5/schools?page=1&perPage=20&active=true&orderBy=name&orderDirection=asc'
      );
    });

    it('should handle partial parameters', () => {
      const params: GetSchoolsParams = {
        search: 'swimming',
        perPage: 5
      };

      service.getMySchools(params).subscribe();

      expect(mockApiHttp.get).toHaveBeenCalledWith(
        '/api/v5/schools?page=1&perPage=5&search=swimming&active=true&orderBy=name&orderDirection=asc'
      );
    });

    it('should return schools response', (done) => {
      service.getMySchools().subscribe(response => {
        expect(response).toEqual(mockSchoolsResponse);
        done();
      });
    });

    it('should handle undefined parameters', () => {
      const params: GetSchoolsParams = {
        page: undefined,
        search: undefined
      };

      service.getMySchools(params).subscribe();

      expect(mockApiHttp.get).toHaveBeenCalledWith(
        '/api/v5/schools?page=1&perPage=20&active=true&orderBy=name&orderDirection=asc'
      );
    });
  });

  describe('getSchoolById', () => {
    beforeEach(() => {
      mockApiHttp.get.and.returnValue(of(mockSchool));
    });

    it('should call API with correct school ID', () => {
      service.getSchoolById(123).subscribe();

      expect(mockApiHttp.get).toHaveBeenCalledWith('/api/v5/schools/123');
    });

    it('should return school data', (done) => {
      service.getSchoolById(1).subscribe(school => {
        expect(school).toEqual(mockSchool);
        done();
      });
    });
  });

  describe('getAllMySchools', () => {
    beforeEach(() => {
      mockApiHttp.get.and.returnValue(of([mockSchool]));
    });

    it('should call API without parameters', () => {
      service.getAllMySchools().subscribe();

      expect(mockApiHttp.get).toHaveBeenCalledWith('/api/v5/schools/all');
    });

    it('should return schools array', (done) => {
      service.getAllMySchools().subscribe(schools => {
        expect(schools).toEqual([mockSchool]);
        done();
      });
    });
  });

  describe('searchSchools', () => {
    beforeEach(() => {
      mockApiHttp.get.and.returnValue(of([mockSchool]));
    });

    it('should call API with search query and default limit', () => {
      service.searchSchools('swimming').subscribe();

      expect(mockApiHttp.get).toHaveBeenCalledWith(
        '/api/v5/schools/search?search=swimming&perPage=10&active=true'
      );
    });

    it('should call API with custom limit', () => {
      service.searchSchools('tennis', 5).subscribe();

      expect(mockApiHttp.get).toHaveBeenCalledWith(
        '/api/v5/schools/search?search=tennis&perPage=5&active=true'
      );
    });

    it('should return schools array', (done) => {
      service.searchSchools('test').subscribe(schools => {
        expect(schools).toEqual([mockSchool]);
        done();
      });
    });

    it('should handle empty search query', () => {
      service.searchSchools('').subscribe();

      expect(mockApiHttp.get).toHaveBeenCalledWith(
        '/api/v5/schools/search?search=&perPage=10&active=true'
      );
    });
  });

  describe('Error Handling', () => {
    it('should propagate API errors from getMySchools', (done) => {
      const error = new Error('Network error');
      mockApiHttp.get.and.throwError(error);

      service.getMySchools().subscribe({
        error: (err) => {
          expect(err).toEqual(error);
          done();
        }
      });
    });

    it('should propagate API errors from getSchoolById', (done) => {
      const error = new Error('School not found');
      mockApiHttp.get.and.throwError(error);

      service.getSchoolById(999).subscribe({
        error: (err) => {
          expect(err).toEqual(error);
          done();
        }
      });
    });

    it('should propagate API errors from getAllMySchools', (done) => {
      const error = new Error('Unauthorized');
      mockApiHttp.get.and.throwError(error);

      service.getAllMySchools().subscribe({
        error: (err) => {
          expect(err).toEqual(error);
          done();
        }
      });
    });

    it('should propagate API errors from searchSchools', (done) => {
      const error = new Error('Search failed');
      mockApiHttp.get.and.throwError(error);

      service.searchSchools('test').subscribe({
        error: (err) => {
          expect(err).toEqual(error);
          done();
        }
      });
    });
  });

  describe('URL Building', () => {
    it('should build correct URLs with special characters in search', () => {
      const params: GetSchoolsParams = {
        search: 'test & school',
        page: 1
      };

      service.getMySchools(params).subscribe();

      expect(mockApiHttp.get).toHaveBeenCalledWith(
        '/api/v5/schools?page=1&perPage=20&search=test%20%26%20school&active=true&orderBy=name&orderDirection=asc'
      );
    });

    it('should handle boolean parameters correctly', () => {
      const params: GetSchoolsParams = {
        active: false
      };

      service.getMySchools(params).subscribe();

      const calledUrl = mockApiHttp.get.calls.mostRecent().args[0];
      expect(calledUrl).toContain('active=false');
    });
  });
});