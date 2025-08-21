import { ComponentFixture, TestBed, fakeAsync, tick } from '@angular/core/testing';
import { Router } from '@angular/router';
import { of, throwError, Subject } from 'rxjs';
import { signal } from '@angular/core';

import { SelectSchoolPageComponent } from './select-school.page';
import { SchoolService, SchoolsResponse } from '@core/services/school.service';
import { ContextService, School } from '@core/services/context.service';
import { TranslationService } from '@core/services/translation.service';

describe('SelectSchoolPageComponent', () => {
  let component: SelectSchoolPageComponent;
  let fixture: ComponentFixture<SelectSchoolPageComponent>;
  let mockSchoolService: jasmine.SpyObj<SchoolService>;
  let mockContextService: jasmine.SpyObj<ContextService>;
  let mockTranslationService: jasmine.SpyObj<TranslationService>;
  let mockRouter: jasmine.SpyObj<Router>;

  const mockSchools: School[] = [
    {
      id: 1,
      name: 'Active School',
      slug: 'active-school',
      active: true
    },
    {
      id: 2,
      name: 'Inactive School',
      active: false
    }
  ];

  const mockSchoolsResponse: SchoolsResponse = {
    data: mockSchools,
    meta: {
      total: 2,
      page: 1,
      perPage: 20,
      lastPage: 1,
      from: 1,
      to: 2
    }
  };

  beforeEach(async () => {
    const schoolServiceSpy = jasmine.createSpyObj('SchoolService', ['getMySchools']);
    const contextServiceSpy = jasmine.createSpyObj('ContextService', ['setSchool'], {
      hasSchoolSelected: signal(false),
      hasCompleteContext: signal(false)
    });
    const translationServiceSpy = jasmine.createSpyObj('TranslationService', ['get']);
    const routerSpy = jasmine.createSpyObj('Router', ['navigate']);

    await TestBed.configureTestingModule({
      imports: [SelectSchoolPageComponent],
      providers: [
        { provide: SchoolService, useValue: schoolServiceSpy },
        { provide: ContextService, useValue: contextServiceSpy },
        { provide: TranslationService, useValue: translationServiceSpy },
        { provide: Router, useValue: routerSpy }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(SelectSchoolPageComponent);
    component = fixture.componentInstance;
    mockSchoolService = TestBed.inject(SchoolService) as jasmine.SpyObj<SchoolService>;
    mockContextService = TestBed.inject(ContextService) as jasmine.SpyObj<ContextService>;
    mockTranslationService = TestBed.inject(TranslationService) as jasmine.SpyObj<TranslationService>;
    mockRouter = TestBed.inject(Router) as jasmine.SpyObj<Router>;

    // Setup default mocks
    mockSchoolService.getMySchools.and.returnValue(of(mockSchoolsResponse));
    mockTranslationService.get.and.returnValue('Translated text');
  });

  describe('Component Initialization', () => {
    it('should create', () => {
      expect(component).toBeTruthy();
    });

    it('should load schools on init', () => {
      component.ngOnInit();
      
      expect(mockSchoolService.getMySchools).toHaveBeenCalledWith({
        page: 1,
        perPage: 20,
        search: '',
        active: true,
        orderBy: 'name',
        orderDirection: 'asc'
      });
    });

    it('should set initial loading state', () => {
      expect(component.isLoading()).toBeTruthy();
    });
  });

  describe('Loading Schools', () => {
    it('should display schools after successful load', fakeAsync(() => {
      component.ngOnInit();
      tick();

      expect(component.schools()).toEqual(mockSchools);
      expect(component.isLoading()).toBeFalsy();
      expect(component.hasError()).toBeFalsy();
    }));

    it('should handle loading error', fakeAsync(() => {
      const error = new Error('Failed to load schools');
      mockSchoolService.getMySchools.and.returnValue(throwError(() => error));

      component.ngOnInit();
      tick();

      expect(component.hasError()).toBeTruthy();
      expect(component.errorMessage()).toBe('Failed to load schools');
      expect(component.isLoading()).toBeFalsy();
    }));

    it('should show empty state when no schools', fakeAsync(() => {
      const emptyResponse: SchoolsResponse = {
        data: [],
        meta: {
          total: 0,
          page: 1,
          perPage: 20,
          lastPage: 1,
          from: 0,
          to: 0
        }
      };
      mockSchoolService.getMySchools.and.returnValue(of(emptyResponse));

      component.ngOnInit();
      tick();

      expect(component.schools().length).toBe(0);
      expect(component.isLoading()).toBeFalsy();
      expect(component.hasError()).toBeFalsy();
    }));
  });

  describe('Search Functionality', () => {
    beforeEach(() => {
      component.ngOnInit();
    });

    it('should trigger search after debounce delay', fakeAsync(() => {
      component.searchQuery = 'swimming';
      component.onSearchInput();

      // Verify searching state is set
      expect(component.isSearching()).toBeTruthy();

      // Fast-forward through debounce delay
      tick(300);

      expect(mockSchoolService.getMySchools).toHaveBeenCalledWith({
        page: 1,
        perPage: 20,
        search: 'swimming',
        active: true,
        orderBy: 'name',
        orderDirection: 'asc'
      });
    }));

    it('should not trigger search before debounce delay', fakeAsync(() => {
      const initialCallCount = mockSchoolService.getMySchools.calls.count();
      
      component.searchQuery = 'test';
      component.onSearchInput();

      // Before debounce
      tick(100);
      expect(mockSchoolService.getMySchools.calls.count()).toBe(initialCallCount);

      // After debounce
      tick(250);
      expect(mockSchoolService.getMySchools.calls.count()).toBe(initialCallCount + 1);
    }));

    it('should handle search with trimmed query', fakeAsync(() => {
      component.searchQuery = '  swimming  ';
      component.onSearchInput();
      tick(300);

      expect(mockSchoolService.getMySchools).toHaveBeenCalledWith(
        jasmine.objectContaining({ search: 'swimming' })
      );
    }));
  });

  describe('School Selection', () => {
    beforeEach(fakeAsync(() => {
      component.ngOnInit();
      tick();
    }));

    it('should select active school successfully', async () => {
      const activeSchool = mockSchools[0];
      mockContextService.setSchool.and.returnValue(Promise.resolve());

      await component.selectSchool(activeSchool);

      expect(mockContextService.setSchool).toHaveBeenCalledWith(1);
      expect(mockRouter.navigate).toHaveBeenCalledWith(['/select-season']);
    });

    it('should not select inactive school', async () => {
      const inactiveSchool = mockSchools[1];

      await component.selectSchool(inactiveSchool);

      expect(mockContextService.setSchool).not.toHaveBeenCalled();
      expect(mockRouter.navigate).not.toHaveBeenCalled();
    });

    it('should handle selection error', async () => {
      const activeSchool = mockSchools[0];
      const error = new Error('Selection failed');
      mockContextService.setSchool.and.returnValue(Promise.reject(error));
      mockTranslationService.get.and.returnValue('Error selecting school');

      await component.selectSchool(activeSchool);

      expect(component.hasError()).toBeTruthy();
      expect(component.errorMessage()).toBe('Error selecting school');
      expect(component.isSelecting()).toBeNull();
    });

    it('should prevent multiple simultaneous selections', async () => {
      const activeSchool = mockSchools[0];
      mockContextService.setSchool.and.returnValue(new Promise(() => {})); // Never resolves

      // Start selection
      component.selectSchool(activeSchool);
      expect(component.isSelecting()).toBe(1);

      // Try to select another school
      await component.selectSchool(activeSchool);

      expect(mockContextService.setSchool).toHaveBeenCalledTimes(1);
    });
  });

  describe('Pagination', () => {
    const paginatedResponse: SchoolsResponse = {
      data: mockSchools,
      meta: {
        total: 50,
        page: 2,
        perPage: 10,
        lastPage: 5,
        from: 11,
        to: 20
      }
    };

    beforeEach(fakeAsync(() => {
      mockSchoolService.getMySchools.and.returnValue(of(paginatedResponse));
      component.ngOnInit();
      tick();
    }));

    it('should display correct pagination info', () => {
      const pagination = component.pagination();
      expect(pagination.current).toBe(2);
      expect(pagination.total).toBe(5);
      expect(pagination.totalResults).toBe(50);
    });

    it('should navigate to different page', () => {
      component.goToPage(3);

      expect(mockSchoolService.getMySchools).toHaveBeenCalledWith(
        jasmine.objectContaining({ page: 3 })
      );
    });

    it('should ignore invalid page numbers', () => {
      const initialCallCount = mockSchoolService.getMySchools.calls.count();

      component.goToPage(0); // Invalid: too low
      component.goToPage(10); // Invalid: too high
      component.goToPage(2); // Invalid: current page

      expect(mockSchoolService.getMySchools.calls.count()).toBe(initialCallCount);
    });

    it('should generate correct page numbers for normal pagination', () => {
      const pages = component.getPageNumbers();
      expect(pages).toEqual([1, 2, 3, 4, 5]);
    });

    it('should generate correct page numbers with ellipsis', () => {
      const largePaginationResponse: SchoolsResponse = {
        data: mockSchools,
        meta: {
          total: 150,
          page: 5,
          perPage: 10,
          lastPage: 15,
          from: 41,
          to: 50
        }
      };
      
      mockSchoolService.getMySchools.and.returnValue(of(largePaginationResponse));
      component.loadSchools();

      const pages = component.getPageNumbers();
      expect(pages).toEqual([1, '...', 4, 5, 6, '...', 15]);
    });
  });

  describe('Component Lifecycle', () => {
    it('should complete destroy subject on destroy', () => {
      const destroySpy = spyOn(component['destroy$'], 'next');
      const completespy = spyOn(component['destroy$'], 'complete');

      component.ngOnDestroy();

      expect(destroySpy).toHaveBeenCalled();
      expect(completespy).toHaveBeenCalled();
    });

    it('should unsubscribe from observables on destroy', fakeAsync(() => {
      const searchSubject = new Subject<string>();
      spyOnProperty(component as any, 'searchSubject', 'get').and.returnValue(searchSubject);

      component.ngOnInit();
      
      const subscribedSpy = spyOn(searchSubject, 'pipe').and.callThrough();
      
      component.ngOnDestroy();
      
      // Verify takeUntil was used (indirectly by checking if pipe was called)
      expect(subscribedSpy).toHaveBeenCalled();
    }));
  });

  describe('Error Recovery', () => {
    it('should retry loading schools after error', fakeAsync(() => {
      // First call fails
      mockSchoolService.getMySchools.and.returnValue(throwError(() => new Error('First error')));
      component.ngOnInit();
      tick();

      expect(component.hasError()).toBeTruthy();

      // Retry with successful response
      mockSchoolService.getMySchools.and.returnValue(of(mockSchoolsResponse));
      component.loadSchools();
      tick();

      expect(component.hasError()).toBeFalsy();
      expect(component.schools()).toEqual(mockSchools);
    }));

    it('should clear error state when retrying', () => {
      // Set error state
      component['_hasError'].set(true);
      component['_errorMessage'].set('Previous error');

      component.loadSchools();

      expect(component.hasError()).toBeFalsy();
      expect(component.errorMessage()).toBeNull();
    });
  });
});