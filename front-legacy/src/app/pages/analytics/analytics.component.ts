import { AfterViewChecked, AfterViewInit, ChangeDetectorRef, Component, OnInit } from '@angular/core';
import Plotly from 'plotly.js-dist-min';
import { MatTabChangeEvent } from '@angular/material/tabs';
import { MonitorsCreateUpdateComponent } from '../monitors/monitors-create-update/monitors-create-update.component';
import { TableColumn } from '../../../@vex/interfaces/table-column.interface';
import { Router } from '@angular/router';
import { ApiCrudService } from '../../../service/crud.service';
import { Observable } from 'rxjs';
import { TranslateService } from '@ngx-translate/core';
import moment from 'moment/moment';
import { MOCK_COUNTRIES } from '../../static-data/countries-data';
import { MOCK_PROVINCES } from '../../static-data/province-data';

@Component({
  selector: 'vex-analytics',
  templateUrl: './analytics.component.html',
  styleUrls: ['./analytics.component.scss']
})
export class AnalyticsComponent implements AfterViewInit, AfterViewChecked {
  courseTypeHoursData = [
    { name: this.translateService.instant('course_colective'), value: 0, max_value: 0, color: '#ff5733' },
    { name: this.translateService.instant('course_private'), value: 0, max_value: 0, color: '#33c7ff' },
    { name: this.translateService.instant('activity'), value: 0, max_value: 0, color: '#ff33b8' }
  ];
  allSports: any[] = []; // Array para almacenar todos los deportes

  countries = MOCK_COUNTRIES;
  provinces = MOCK_PROVINCES;
  filter = '';
  monitor = null;
  imageAvatar = '../../../assets/img/avatar.png';
  user: any;
  icon = '../../../assets/img/icons/monitores.svg';
  totalHours = this.courseTypeHoursData.reduce((acc, course) => acc + course.value, 0);
  createComponent = MonitorsCreateUpdateComponent;
  showDetail: boolean = false;
  entity = '/admin/statistics/bookings/monitors';
  entitySales = '/admin/statistics/bookings/sells';
  selectedId = null;
  deleteEntity = '/monitors';
  totalSportHours: number = 0;
  totalPriceSell = 0;
  totalMonitorPriceSell = 0;
  currency = '';
  sportHoursData: any[] = [];
  selectedFrom = null;
  selectedSport = null;
  selectedTo = null;
  tabActive = 'general';
  areGraphsVisible: boolean = true;
  private resizePending = false;

  columns: TableColumn<any>[] = [
    { label: 'monitor', property: 'monitor', type: 'monitor', visible: true, cssClasses: ['font-medium'] },
    { label: 'sport', property: 'sport', type: 'sport', visible: true, cssClasses: ['font-medium'] },
    { label: 'hours_collective', property: 'hours_collective', type: 'text', visible: true },
    { label: 'hours_private', property: 'hours_private', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'hours_activities', property: 'hours_activities', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'hours_nwd_payed', property: 'hours_nwd_payed', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'base_price', property: 'hour_price', type: 'price', visible: true },
    { label: 'total_hours', property: 'total_hours', type: 'text', visible: true },
    { label: 'total', property: 'total_cost', type: 'price', visible: true },
  ];

  columnsSales: TableColumn<any>[] = [
    { label: 'type', property: 'icon', type: 'booking_users_image', visible: true },
    { label: 'name', property: 'name', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'availability', property: 'available_places', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'sold', property: 'booked_places', type: 'text', visible: true },
    { label: 'cash', property: 'cash', type: 'price', visible: true, cssClasses: ['font-medium'] },
    { label: 'other', property: 'other', type: 'price', visible: true, cssClasses: ['font-medium'] },
    { label: 'T.Boukii', property: 'boukii', type: 'price', visible: true, cssClasses: ['font-medium'] },
    { label: 'T.Boukii Web', property: 'boukii_web', type: 'price', visible: true, cssClasses: ['font-medium'] },
    { label: 'Link', property: 'online', type: 'price', visible: true, cssClasses: ['font-medium'] },
    { label: 'no_paid', property: 'no_paid', type: 'price', visible: true, cssClasses: ['font-medium'] },
    { label: 'admin', property: 'admin', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'web', property: 'web', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'vouchers', property: 'vouchers', type: 'price', visible: true, cssClasses: ['font-medium'] },
    { label: 'extras', property: 'extras', type: 'price', visible: true, cssClasses: ['font-medium'] },
    /*{ label: 'gift_vouchers', property: 'sell_voucher', type: 'price', visible: true, cssClasses: ['font-medium'] },*/
    //{label: 'discount_code', property: 'vouchers_gift', type: 'price', visible: true, cssClasses: ['font-medium']},
    { label: 'total', property: 'total_cost', type: 'price', visible: true },
  ];

  columnsDetail: TableColumn<any>[] = [
    { label: 'date', property: 'date', type: 'date', visible: true, cssClasses: ['font-medium'] },
    { label: 'sport', property: 'sport', type: 'sport', visible: true, cssClasses: ['font-medium'] },
    { label: 'hours_collective', property: 'hours_collective', type: 'text', visible: true },
    { label: 'hours_private', property: 'hours_private', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'hours_activities', property: 'hours_activities', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'hours_nwd_payed', property: 'hours_nwd_payed', type: 'text', visible: true, cssClasses: ['font-medium'] },
    { label: 'base_price', property: 'hour_price', type: 'price', visible: true },
    { label: 'total_hours', property: 'total_hours', type: 'text', visible: true },
    { label: 'total', property: 'total_cost', type: 'price', visible: true },

  ];

  constructor(private crudService: ApiCrudService, private router: Router, public translateService: TranslateService,
    private cdr: ChangeDetectorRef) {
    this.user = JSON.parse(localStorage.getItem('boukiiUser'));
  }

  ngAfterViewInit(): void {
    let voucherText = {
      'title': this.translateService.instant('gift_vouchers'),
      'subtitle': this.translateService.instant('sales'),
      'price': '0 CHF',
      'subprice': this.translateService.instant('occupation'),
    }
    this.setPlotly('#E91E63', voucherText, 'voucher', 0, 0);
    this.getBookingsByDate().subscribe(res => {
      this.setUserSessionAnalytics(false, res.data);
    })

    this.getBookingsByDateSport().subscribe(res => {
      this.updateChartBySport(res.data);
    })
    this.getSchoolSports().subscribe(res => {
      this.allSports = res.data;
      let settings = JSON.parse(this.user.schools[0].settings);
      this.currency = settings?.taxes?.currency;
      this.reloadData(false);
    })


  }

  getSchoolSports() {
    return this.crudService.list('/school-sports', 1, 10000, 'desc', 'id',
      '&school_id=' + this.user.schools[0].id, '', null, null, ['sport']);
  }

  updateTotalHours() {
    this.totalHours = this.courseTypeHoursData.reduce((acc, course) => acc + course.max_value, 0);
  }

  toggleGraphsVisibility(): void {
    this.areGraphsVisible = !this.areGraphsVisible;
    this.resizePending = true;
    this.cdr.detectChanges(); // Forzar la detección de cambios
  }

  updateCourseValue(index: number, newValue: number, newMaxValue: number = 0) {
    this.courseTypeHoursData[index].value = newValue;
    this.courseTypeHoursData[index].max_value = newMaxValue;
    this.updateTotalHours();

    // Crear una nueva referencia al array
    this.courseTypeHoursData = [...this.courseTypeHoursData];
    this.cdr.detectChanges();
  }

  getBookingsTotalByType(type): Observable<any> {
    return this.crudService.list('/admin/statistics/bookings', 1, 10000,
      'desc', 'id', '&school_id=' + this.user.schools[0].id + '&type=' + type + this.filter);
  }

  getBookingsTotal(): Observable<any> {
    return this.crudService.list('/admin/statistics/total', 1, 10000,
      'desc', 'id', '&school_id=' + this.user.schools[0].id + this.filter);
  }

  getMonitorsTotal(): Observable<any> {
    return this.crudService.list('/admin/statistics/monitors/total', 1, 10000,
      'desc', 'id', '&school_id=' + this.user.schools[0].id + this.filter);
  }


  getBookingsByDate(): Observable<any> {
    return this.crudService.list('/admin/statistics/bookings/dates', 1, 10000,
      'desc', 'id', '&school_id=' + this.user.schools[0].id + this.filter);
  }

  getBookingsByDateSport(): Observable<any> {
    return this.crudService.list('/admin/statistics/bookings/sports', 1, 10000,
      'desc', 'id', '&school_id=' + this.user.schools[0].id + this.filter);
  }

  getActiveMonitors(): Observable<any> {
    return this.crudService.list('/admin/statistics/bookings/monitors/active', 1, 10000,
      'desc', 'id', '&school_id=' + this.user.schools[0].id + this.filter);
  }

  getTotalHours(): Observable<any> {
    return this.crudService.list('/admin/statistics/bookings/monitors/hours', 1, 10000,
      'desc', 'id', '&school_id=' + this.user.schools[0].id + this.filter);
  }

  getTotalHoursBySport(): Observable<any> {
    return this.crudService.list('/admin/statistics/bookings/monitors/sports', 1, 10000,
      'desc', 'id', '&school_id=' + this.user.schools[0].id + this.filter);
  }

  showDetailEvent(event: any) {
    this.showDetail = true;
    this.selectedId = event.item.id;
    this.getMonitor().subscribe(res => {
      this.monitor = res.data;
      this.filter += '&monitor_id=' + this.selectedId;
      let settings = JSON.parse(this.user.schools[0].settings);
      this.courseTypeHoursData = [
        { name: this.translateService.instant('course_colective'), value: 0, max_value: 0, color: '#ff5733' },
        { name: this.translateService.instant('course_private'), value: 0, max_value: 0, color: '#33c7ff' },
        { name: this.translateService.instant('activity'), value: 0, max_value: 0, color: '#ff33b8' }
      ];
      this.reloadData(true);
    })

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

  getMonitor() {
    return this.crudService.get('/monitors/' + this.selectedId, ['language1']);
  }

  closeDetailEvent(event: any) {
    this.showDetail = false;
    this.selectedId = null;
    this.reloadData(true);
  }

  goTo(route: string) {
    this.router.navigate([route]);
  }

  filterData() {

    this.reloadData(true);
  }

  reloadData(monitors) {
    let filter = '';

    if (this.selectedFrom) {
      filter = filter + '&start_date=' + moment(this.selectedFrom).format('YYYY-MM-DD');
    }
    if (this.selectedTo) {
      filter = filter + '&end_date=' + moment(this.selectedTo).format('YYYY-MM-DD');
    }
    if (this.selectedSport) {
      filter = filter + '&sport_id=' + this.selectedSport;
    }

    if (this.selectedId) {
      filter = filter + '&monitor_id=' + this.selectedId;
    }

    this.filter = filter;
    this.totalPriceSell = 0;
    this.courseTypeHoursData = [
      { name: this.translateService.instant('course_colective'), value: 0, max_value: 0, color: '#ff5733' },
      { name: this.translateService.instant('course_private'), value: 0, max_value: 0, color: '#33c7ff' },
      { name: this.translateService.instant('activity'), value: 0, max_value: 0, color: '#ff33b8' }
    ];

    this.getBookingsTotal().subscribe(res => {
      this.totalPriceSell = res.data;
    });
      this.getBookingsTotalByType(1).subscribe(res => {
    const collectiveText = {
        'title': this.translateService.instant('course_colective'),
        'subtitle': this.translateService.instant('sales'),
        'price': `${res.data.total_price}${this.currency}`,
        'subprice': this.translateService.instant('occupation'),
      };
      this.updateCourseValue(0, res.data.total_reservations_hours,
        res.data.total_hours);
      if (!monitors) {
        this.setPlotly('#FA9917', collectiveText, 'collective',
          res.data.total_reservations_places, res.data.total_available_places);
      }




    });

    this.getBookingsTotalByType(2).subscribe(res => {
      const collectiveText = {
        'title': this.translateService.instant('course_private'),
        'subtitle': this.translateService.instant('sales'),
        'price': `${res.data.total_price}${this.currency}`,
        'subprice': this.translateService.instant('occupation'),
      };
      this.updateCourseValue(1, res.data.total_reservations_hours,
        res.data.total_hours);
      if (!monitors) {
        this.setPlotly('#2AC940', collectiveText, 'prive',
          res.data.total_reservations_places, res.data.total_places);

      }
  //    this.totalPriceSell += res.data.total_price;

    });

    this.getBookingsTotalByType(3).subscribe(res => {
      const collectiveText = {
        'title': this.translateService.instant('activity'),
        'subtitle': this.translateService.instant('sales'),
        'price': `${res.data.total_price}${this.currency}`,
        'subprice': this.translateService.instant('occupation'),
      };
      this.updateCourseValue(2, res.data.total_reservations_hours,
        res.data.total_hours);
      if (!monitors) {
        this.setPlotly('#9747FF', collectiveText, 'activity',
          res.data.total_reservations_places, res.data.total_places);
      }
    //  this.totalPriceSell += res.data.total_price;

    });


    this.getTotalHoursBySport().subscribe(res => {
      this.processSportData(res.data);
    });
    let plotActiveMonitors = 'activeMonitors';
    let plotTotalHours = 'totalHours';
    if (monitors) {
      plotActiveMonitors = 'activeMonitorsFiltered';
      plotTotalHours = 'totalHoursFiltered';
    }
    if (!this.showDetail && this.tabActive != 'sells') {
      this.getActiveMonitors().subscribe(res => {
        let collectiveText = {
          'title': this.translateService.instant('monitors'),
          'subtitle': this.translateService.instant('sales'),
          'price': res.data.busy + '/' + res.data.total,
          'subprice': this.translateService.instant('occupation'),
        }
        const chartElement = document.getElementById(plotActiveMonitors);
        if (chartElement) {
          this.setPlotly('#0547ED', collectiveText, plotActiveMonitors, res.data.busy, res.data.total)
        }
      });
    }
    if (this.tabActive == 'sells') {
      this.loadSellData();
    }

    if (this.tabActive == 'monitors') {
      this.getMonitorsTotal().subscribe(res => {
        this.totalMonitorPriceSell = res.data;
      });
    }
    this.getTotalHours().subscribe(res => {
      let collectiveText = {
        'title': this.translateService.instant('hours_worked'),
        'subtitle': this.translateService.instant('hours_worked_total'),
        'price': res.data.totalWorkedHours + 'h',
        'subprice': this.translateService.instant('hours_worked') + '/' + this.translateService.instant('blocks'),
      }
      const chartElement = document.getElementById(plotTotalHours);
      if (chartElement) {
        this.setPlotly('#3A57A7', collectiveText, plotTotalHours,
          res.data.totalNwdHours + res.data.totalBookingHours, res.data.totalMonitorHours)
      }
    });

    this.cdr.detectChanges();
  }

  loadSellData() {
   /* this.getBookingsTotal(1).subscribe(res => {
      this.totalPriceSell = 0;
      const collectiveText = {
        'title': this.translateService.instant('course_colective'),
        'subtitle': this.translateService.instant('sales'),
        'price': `${res.data.total_price}${this.currency}`,
        'subprice': this.translateService.instant('occupation'),
      };

      this.setPlotly('#FA9917', collectiveText, 'collectiveSales',
        res.data.total_places - res.data.total_available_places, res.data.total_available_places);


      this.totalPriceSell += res.data.total_price;

    });

    this.getBookingsTotal(2).subscribe(res => {
      const collectiveText = {
        'title': this.translateService.instant('course_private'),
        'subtitle': this.translateService.instant('sales'),
        'price': `${res.data.total_price}${this.currency}`,
        'subprice': this.translateService.instant('occupation'),
      };

      this.setPlotly('#2AC940', collectiveText, 'priveSales',
        res.data.total_places - res.data.total_available_places, res.data.total_places);


      this.totalPriceSell += res.data.total_price;

    });

    this.getBookingsTotal(3).subscribe(res => {
      const collectiveText = {
        'title': this.translateService.instant('activity'),
        'subtitle': this.translateService.instant('sales'),
        'price': `${res.data.total_price}${this.currency}`,
        'subprice': this.translateService.instant('occupation'),
      };

      this.setPlotly('#9747FF', collectiveText, 'activitySales',
        res.data.total_places - res.data.total_available_places, res.data.total_places);

      this.totalPriceSell += res.data.total_price;

    });

    let voucherText = {
      'title': this.translateService.instant('gift_vouchers'),
      'subtitle': this.translateService.instant('sales'),
      'price': '0 CHF',
      'subprice': this.translateService.instant('occupation'),
    }
    this.setPlotly('#E91E63', voucherText, 'voucherSales', 0, 0);*/
  }


  onTabChange(event: MatTabChangeEvent) {
    if (event.index === 2) {
      this.tabActive = 'monitors';
      this.reloadData(true);
      this.getBookingsByDate().subscribe(res => {
        this.setUserSessionAnalytics(true, res.data);
      })


    } else if (event.index === 0) {
      this.tabActive = 'general';
      this.filter = '';
      this.showDetail = false;
      this.reloadData(false);
      this.getBookingsByDate().subscribe(res => {
        this.setUserSessionAnalytics(false, res.data);
      })
    } else if (event.index === 1) {
      this.filter = '';
      this.tabActive = 'sells';
      this.showDetail = false;
/*      this.getBookingsByDate().subscribe(res => {
        this.setUserSessionAnalytics(false, res.data);
      })*/
      this.loadSellData();
    }
  }

  ngAfterViewChecked() {
    if (this.resizePending && this.areGraphsVisible) {
      this.resizePending = false;
      this.resizeAllCharts();
    }
  }

  resizeAllCharts() {
    const chartIds = [
      'collectiveSales', 'priveSales', 'activitySales', 'voucher', 'voucherSales', 'user-session-analytics',
      'activeMonitorsFiltered', 'totalHoursFiltered', 'user-session-analytics2', 'collectiveSales', 'privateSales'
    ];

    chartIds.forEach(id => {
      const chartElement = document.getElementById(id);
      if (chartElement) {
        Plotly.Plots.resize(chartElement);
      }
    });
  }


  getGraphHeight() {
    const screenWidth = window.innerWidth;

    if (screenWidth > 1024) {  // Pantallas pequeñas (móviles, tabletas)
      return 250;
    } else {  // Pantallas grandes (escritorios)
      return 350;
    }
  }

  setPlotly(color, text, id, value, maxValue) {

    // Calcular el porcentaje
    let percent: any = (value / maxValue) * 100;

    if (isNaN(percent)) {
      percent = 0;
    }

    const data = [
      {
        domain: { x: [0, 1], y: [0, 1] },
        value: percent,  // Valor del medidor
        title: { text: "Medidor Semi Circular" },
        type: "indicator",
        mode: "gauge",
        gauge: {
          shape: "angular",
          bar: {
            color: color,
            thickness: 1  // Ajusta el grosor de la barra
          },
          axis: {
            range: [null, 100],  // Rango del eje
            showticklabels: false,  // Oculta las etiquetas de las marcas
            ticks: "",  // Oculta los ticks
            showline: true,  // Muestra la línea del eje
            linecolor: "black",  // Color de la línea del eje
            linewidth: 1  // Grosor de la línea del eje
          },
          steps: [
            {
              range: [0, percent],
              color: color
            },
            {
              range: [percent, 100],
              color: "lightgrey"
            }
          ],
          bgcolor: "rgba(0,0,0,0)",  // Fondo transparente
          borderwidth: 0,  // Grosor del borde
          bordercolor: "black"  // Color del borde
        }
      }
    ];

    /* *** layout *** */

    const layout = {
      height: window.innerWidth > 1440 ? 250 : 350,
      margin: { t: 20, b: 10, l: window.innerWidth > 1440 ? 65 : 50, r: window.innerWidth > 1339 ? 50 : 50 },  // Aumentamos el margen superior (t) para dar espacio al texto
      paper_bgcolor: "rgba(0,0,0,0)",  // Fondo del gráfico transparente
      plot_bgcolor: "rgba(0,0,0,0)",   // Fondo del área del gráfico transparente
      annotations: [{
        text: percent.toFixed(2) + "%",  // Mostrar el porcentaje con dos decimales
        font: {
          family: "Dinamit, sans-serif",
          weight: 500,
          size: 25,
          color: color
        },
        showarrow: false,
        x: .5,
        y: .4,
        xref: 'paper',
        yref: 'paper',
        xanchor: 'center',
        yanchor: 'middle',
        align: 'center',
        pad: { t: 10, r: 10, b: 10, l: 10 }  // Espacio alrededor del texto
      },
      {
        text: text.title,
        font: {
          family: "Dinamit, sans-serif",
          weight: 700,
          size: 17,
          color: color
        },
        showarrow: false,
        x: -.13,
        y: .96,  // Ajustamos el valor de y para que el texto esté más arriba
        xref: 'paper',
        yref: 'paper',
        xanchor: 'left',
        yanchor: 'bottom',
        align: 'left',
        pad: { t: 10, r: 10, b: 10, l: 10 }  // Espacio alrededor del texto
      },
      {
        text: text.subtitle,
        font: {
          family: "Dinamit, sans-serif",
          weight: 400,
          size: 10,
          color: '#87888C'
        },
        showarrow: false,
        x: -.13,
        y: .94,
        xref: 'paper',
        yref: 'paper',
        xanchor: 'left',
        yanchor: 'top',
        align: 'left',
        pad: { t: 10, r: 10, b: 10, l: 10 }  // Espacio alrededor del texto
      },
      {
        text: text.price,
        font: {
          family: "Dinamit, sans-serif",
          weight: 700,
          size: 20,
          color: '#424242'
        },
        showarrow: false,
        x: -.13,
        y: .865,  // Ajustamos el valor de y para que el texto esté más arriba
        xref: 'paper',
        yref: 'paper',
        xanchor: 'left',
        yanchor: 'top',
        align: 'left',
        pad: { t: 10, r: 10, b: 10, l: 10 }  // Espacio alrededor del texto
      },
      {
        text: text.subprice,
        font: {
          family: "Dinamit, sans-serif",
          size: 11,
          weight: 400,
          color: '#87888C'
        },
        showarrow: false,
        x: -.13,
        y: .76,
        xref: 'paper',
        yref: 'paper',
        xanchor: 'left',
        yanchor: 'top',
        align: 'left',
        pad: { t: 10, r: 10, b: 10, l: 10 }  // Espacio alrededor del texto
      }
      ],

    };
    var config = {
      displayModeBar: false,  // Oculta el modo de visualización que incluye el botón de descarga
      displaylogo: false,
      responsive: true,
      modeBarButtonsToRemove: ['pan2d', 'lasso2d']// Oculta la etiqueta "Produced by Plotly"
    }

    Plotly.newPlot(id, data, layout, config);

    window.onresize = function () {
      Plotly.Plots.resize(document.getElementById(id));
    };
  }

  processSportData(data) {
    this.totalSportHours = 0;
    this.sportHoursData = Object.keys(data).map(sportId => {
      const sportData = data[sportId];
      this.totalSportHours += sportData.hours;
      return {
        id: sportData.sport.id,
        name: sportData.sport.name,
        value: sportData.hours,
        icon: sportData.sport.icon_selected,
        color: '#FCB859'  // Puedes ajustar para usar colores específicos
      };
    });
  }

  setUserSessionAnalytics(monitors, data) {
    const dates = Object.keys(data);
    const courseType1 = dates.map(date => data[date][1] || 0);
    const courseType2 = dates.map(date => data[date][2] || 0);
    const courseType3 = dates.map(date => data[date][3] || 0);

    const trace1 = {
      x: dates,
      y: courseType1,
      mode: 'lines+markers',
      name: this.translateService.instant('course_colective'),
      line: { color: '#FAC710' }
    };

    const trace2 = {
      x: dates,
      y: courseType2,
      mode: 'lines+markers',
      name: this.translateService.instant('course_private'),
      line: { color: '#8FD14F' }
    };

    const trace3 = {
      x: dates,
      y: courseType3,
      mode: 'lines+markers',
      name: this.translateService.instant('activity'),
      line: { color: '#00beff' }
    };

    const chartData = [trace1, trace2, trace3];

    const layout = {
      title: this.translateService.instant('hours_by_type'),
      xaxis: {
        title: this.translateService.instant('dates'),
        type: 'date',
        tickformat: '%Y-%m', // Formato para mostrar solo el año y el mes
      },
      yaxis: {
        title: this.translateService.instant('total'),
      },
      paper_bgcolor: 'rgba(0,0,0,0)',
      plot_bgcolor: 'rgba(0,0,0,0)',
      showlegend: true,
      height: 250
    };

    if (this.tabActive == 'monitors') {
      Plotly.newPlot('user-session-analytics4', chartData, layout);
    } else if (this.tabActive == 'general') {
      Plotly.newPlot('user-session-analytics', chartData, layout);
    } else if (this.tabActive == 'sells') {
      Plotly.newPlot('user-session-analytics-sales', chartData, layout);
    }
  }


  updateChartBySport(data, id = 'hours_by_sport') {
    const dates = Object.keys(data); // Obtener todas las fechas

    const sports = {};

    // Organizar los datos por deporte
    dates.forEach(date => {
      const dayData = data[date];
      Object.keys(dayData).forEach(sport => {
        if (!sports[sport]) {
          sports[sport] = [];
        }
        sports[sport].push(dayData[sport]);
      });

      // Si algún deporte no tiene entrada para esta fecha, agregar 0
      Object.keys(sports).forEach(sport => {
        if (!dayData[sport]) {
          sports[sport].push(0);
        }
      });
    });

    const traces = Object.keys(sports).map(sport => ({
      x: dates,
      y: sports[sport],
      mode: 'lines+markers',
      name: sport,
    }));

    const layout = {
      title: this.translateService.instant(id),
      xaxis: {
        title: this.translateService.instant('date'),
        type: 'date',
        tickformat: '%Y-%m', // Formato para mostrar solo el año y el mes
      },
      yaxis: {
        title: this.translateService.instant('total'),
      },
      paper_bgcolor: 'rgba(0,0,0,0)',
      plot_bgcolor: 'rgba(0,0,0,0)',
      showlegend: true,
    };

    Plotly.newPlot('user-session-analytics2', traces, layout);
  }


  getCountry(id: any) {
    const country = this.countries.find((c) => c.id == +id);
    return country ? country.name : 'NDF';
  }

  getPercentage(value: number, maxValue: number): number {
    if (maxValue === 0) {
      return 0; // Si max_value es 0, no se necesita hacer el cálculo
    }
    return (value / maxValue) * 100;
  }

}
