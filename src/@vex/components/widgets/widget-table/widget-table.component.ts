import { AfterViewInit, Component, Input, OnChanges, OnInit, SimpleChanges, ViewChild } from '@angular/core';
import { MatTableDataSource } from '@angular/material/table';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { TableColumn } from '../../../interfaces/table-column.interface';
import { ApiCrudService } from 'src/service/crud.service';
import { MOCK_COUNTRIES } from 'src/app/static-data/countries-data';

@Component({
  selector: 'vex-widget-table',
  templateUrl: './widget-table.component.html'
})
export class WidgetTableComponent<T> implements OnInit, OnChanges, AfterViewInit {

  @Input() data: any;
  @Input() columns: TableColumn<T>[];
  @Input() pageSize = 6;
  @Input()
  with: any = '';

  visibleColumns: Array<keyof T | string>;
  dataSource = new MatTableDataSource<T>();

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;
  countries = MOCK_COUNTRIES;

  user: any;
  sports: any = [];
  languages: any = [];
  clients: any = [];

  constructor(private crudService: ApiCrudService) { }

  ngOnInit() {
    this.user = JSON.parse(localStorage.getItem('boukiiUser'));
/*    this.getClients();*/
    this.getLanguages();
    this.getSports();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes.columns) {
      this.visibleColumns = this.columns.map(column => column.property);
    }

    if (changes.data) {
      this.dataSource.data = this.data;
    }
  }

  getCountry(id: any) {
    const country = this.countries.find((c) => c.id == +id);
    return country ? country.name : 'NDF';
  }

  calculateAge(birthDateString) {
    if(birthDateString && birthDateString !== null) {
      const today = new Date();
      const birthDate = new Date(birthDateString);
      let age = today.getFullYear() - birthDate.getFullYear();
      const m = today.getMonth() - birthDate.getMonth();

      if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
          age--;
      }

      return age;
    } else {
      return 0;
    }

  }

  getLanguages() {
    this.crudService.list('/languages', 1, 1000)
      .subscribe((data) => {
        this.languages = data.data.reverse();

      })
  }

  getLanguage(id: any) {
    const lang = this.languages.find((c) => c.id == +id);
    return lang ? lang.code.toUpperCase() : 'NDF';
  }

  checkIfCourseIdIsSame(data: any[]): boolean {
    if (data.length === 0) {
      return true; // o false, según tu lógica de negocio
    }

    const firstCourseId = data[0].course.id;
    return data.every(item => item.course.id === firstCourseId);
  }

  getBookingCourse(data: any) {
    if (data.length === 1 || this.checkIfCourseIdIsSame(data)) {
      return data[0].course.name;
    } else {
      return 'MULTIPLE';
    }
  }

  ngAfterViewInit() {
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }

  getSports() {
    this.crudService.list('/sports', 1, 10000, 'asc', 'name', '&school_id='+this.user.schools[0].id)
      .subscribe((data) => {
        this.sports = data.data;

      })
  }

  getClients() {
    this.crudService.list('/clients', 1, 10000, 'desc', 'id', '&school_id='+this.user.schools[0].id)
      .subscribe((data: any) => {
        this.clients = data.data;

      })
  }

  trackByProperty<T>(index: number, column: TableColumn<T>) {
    return column.property;
  }

  getBookingType(data: any) {
    //if (data.length === 1) {
      return data.course.course_type === 1 ? 'collectif' : 'prive'
    /*} else {
      return 'MULTIPLE';
    }*/
  }

  getBookingImage(data: any) {
    //if (data.length === 1) {
      const ret = this.sports.find((s) => s.id === data.course.sport_id);
      return ret ? ret.name.toLowerCase() : '';
   /* } else {
      return 'MULTIPLE';
    }*/
  }
}
