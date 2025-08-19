import { Component } from '@angular/core';
import { MonitorsCreateUpdateComponent } from './monitors-create-update/monitors-create-update.component';
import { TableColumn } from 'src/@vex/interfaces/table-column.interface';
import { LEVELS } from 'src/app/static-data/level-data';
import { MOCK_PROVINCES } from 'src/app/static-data/province-data';
import { MOCK_COUNTRIES } from 'src/app/static-data/countries-data';
import { ApiCrudService } from 'src/service/crud.service';
import { Router } from '@angular/router';

@Component({
  selector: 'vex-monitors',
  templateUrl: './monitors.component.html',
  styleUrls: ['./monitors.component.scss']
})
export class MonitorsComponent {

  icon = '../../../assets/img/icons/monitores.svg';
  showDetail: boolean = false;
  monitorSport: any;
  detailData: any;
  sports = [];
  user: any;
  imageAvatar = '../../../assets/img/avatar.png';
  skiImage = 'https://api.boukii.com/storage/apps/sports/Ski.png';
  groupedByColor = {};
  colorKeys: string[] = []; // Aquí almacenaremos las claves de colores
  mockLevelData = LEVELS;
  countries = MOCK_COUNTRIES;
  provinces = MOCK_PROVINCES;
  languages = [];

  constructor(private crudService: ApiCrudService, private router: Router) {
    this.getLanguages();
    this.mockLevelData.forEach(level => {
      if (!this.groupedByColor[level.color]) {
        this.groupedByColor[level.color] = [];
      }
      this.groupedByColor[level.color].push(level);
    });

    this.colorKeys = Object.keys(this.groupedByColor);
    this.user = JSON.parse(localStorage.getItem('boukiiUser'));


    this.crudService.list('/sports', 1, 10000, 'desc', 'id', '&school_id=' + this.user.schools[0].id)
      .subscribe((sport) => {
        this.sports = sport.data;
      })
  }

  createComponent = MonitorsCreateUpdateComponent;
  entity = '/monitors';
  deleteEntity = '/monitors';

  columns: TableColumn<any>[] = [
    { label: 'Id', property: 'id', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'monitor', property: 'first_name', type: 'monitor', visible: true, cssClasses: ['font-medium'] },
    { label: 'mobile', property: 'phone', type: 'text', visible: true },
    { label: 'Email', property: 'email', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'level', property: 'monitor_sports_degrees', type: 'monitor_sports_degrees', visible: true, cssClasses: ['font-medium'] },
    { label: 'sports', property: 'sports', type: 'sports_monitor', visible: true },
    { label: 'register', property: 'created_at', type: 'date', visible: true },
    { label: "status", property: 'monitors_schools', type: 'light_monitors_schools', visible: true },
    { label: 'Actions', property: 'actions', type: 'button', visible: true }
  ];

  showDetailEvent(event: any) {

    if (event.showDetail || (!event.showDetail && this.detailData !== null && this.detailData.id !== event.item.id)) {
      this.detailData = event.item;
      this.detailData.monitor_sports_degrees.forEach(element => {

        element.monitor_sport_authorized_degrees = element.monitor_sport_authorized_degrees.reverse();

        this.crudService.get('/sports/' + element.sport_id)
          .subscribe((sport) => {
            element.name = sport.data.name;
            element.icon_selected = sport.data.icon_selected;
            element.icon_unselected = sport.data.icon_unselected;
          });

        this.crudService.get('/degrees/' + element.degree_id)
          .subscribe((level) => {
            element.level = level.data;

          });
      });

      this.showDetail = true;

    } else {
      this.showDetail = event.showDetail;
    }

  }

  getCountry(id: any) {
    const country = this.countries.find((c) => c.id == +id);
    return country ? country.name : 'NDF';
  }


  findHighestDegreeIdElement(data: any) {
    if (!data || data.length === 0) {
      return null;
    }

    let highestDegree = null;


    const highestInCurrent = data.reduce((prev, current) =>
      (prev.degree.degree_order > current.degree.degree_order) ? prev : current
    );
    if (!highestDegree || highestInCurrent.degree.degree_order > highestDegree.degree.degree_order) {
      highestDegree = highestInCurrent;
    }



    if (highestDegree) {
      return highestDegree.degree;
    }

    return null;
  }

  getProvince(id: any) {
    const province = this.provinces.find((c) => c.id == +id);
    return province ? province.name : 'NDF';
  }

  calculateAge(birthDateString) {
    if (birthDateString && birthDateString !== null) {
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

  getLanguage(id: any) {
    const lang = this.languages.find((c) => c.id == +id);
    return lang ? lang.code.toUpperCase() : 'NDF';
  }

  getLanguages() {
    this.crudService.list('/languages', 1, 1000)
      .subscribe((data) => {
        this.languages = data.data.reverse();

      })
  }

  goTo(route: string) {
    this.router.navigate([route]);
  }
}
