import { Component, ChangeDetectorRef } from "@angular/core";
import { TranslateService } from "@ngx-translate/core";
import { MatDialog } from "@angular/material/dialog";
import { BookingDialogComponent } from "./components/booking-dialog/booking-dialog.component";
import { FormBuilder, FormGroup } from '@angular/forms';
import { BookingService } from '../../../../service/bookings.service';
import { ApiCrudService } from '../../../../service/crud.service';
import { Router } from '@angular/router';
import {MatSnackBar} from '@angular/material/snack-bar';

@Component({
  selector: "bookings-create-update-v2",
  templateUrl: "./bookings-create-update.component.html",
  styleUrls: ["./bookings-create-update.component.scss"],
})
export class BookingsCreateUpdateV2Component {
  currentStep = 0;
  currentBookingData = {};
  mainClient: any;
  utilizers: any;
  sport: any;
  sportLevel: any;
  forceStep!: any;
  forms: FormGroup[];
  dates: any;
  allLevels: any;
  normalizedDates: any[];
  course!: any;
  monitors!: any;
  clientObs!: any;
  schoolObs!: any;
  total!: any;
  subtotal!: any;
  extraPrice!: any;
  deleteModal: boolean = false
  deleteIndex: number = 1
  endModal: boolean = false
  isDetail = false;
  selectedIndexForm = null;
  selectedForm: FormGroup;
  payModal: boolean = false;
  paymentMethod: number = 1; // Valor por defecto
  step: number = 1;  // Paso inicial
  selectedPaymentOption: string = 'Tarjeta';
  isPaid = false;
  isConfirmingPayment = false;
  paymentOptions: any[] = [
    { type: 'Tarjeta', value: 4, translation: this.translateService.instant('credit_card') },
    { type: 'Efectivo', value: 1,  translation: this.translateService.instant('payment_cash') },
    { type: 'Boukii Pay', value: 2, translation: 'Boukii Pay' }
  ]; // Opciones de pago para "Pago directo"

  constructor(
    public translateService: TranslateService,
    public dialog: MatDialog,
    private cdr: ChangeDetectorRef,
    private fb: FormBuilder,
    public bookingService: BookingService,
    private crudService: ApiCrudService,
    private router: Router,
    private snackBar: MatSnackBar
  ) {
    this.normalizedDates = []
    this.forms = []
    this.getDegrees();
  }

  handleFormChange(formData: any) {
    const {
      step1: { client, mainClient },
      step2: { utilizers },
      step3: { sport, sportLevel },
      step4: { course },
      step5: { course_dates },
      step6: { clientObs, schoolObs },
    } = formData.value;

    this.mainClient = mainClient;
    this.utilizers = utilizers;
    this.sport = sport;
    this.sportLevel = sportLevel;
    this.course = course;
    this.dates = course_dates ? this.getSelectedDates(course_dates) : [];
    //this.monitors = MOCK_MONITORS;
    this.clientObs = clientObs;
    this.schoolObs = schoolObs;
    if (this.course && this.dates) {
      this.calculateTotal();
    }

    if (this.course && this.dates && formData.controls.step6.touched) {
      if (this.selectedIndexForm === null) {
        this.forms.push(formData);
      } else {
        this.forms[this.selectedIndexForm] = formData;
      }
      this.normalizeDates()
    }
  }

  editActivity(data: any, index: number) {
    this.isDetail = false;
    this.currentStep = data.step;
    this.selectedIndexForm = index;
    this.selectedForm = this.forms[index];
    const {
      step1: { client, mainClient },
      step2: { utilizers },
      step3: { sport, sportLevel },
      step4: { course },
      step5: { course_dates },
      step6: { clientObs, schoolObs },
    } = this.selectedForm.value;

    this.mainClient = mainClient;
    this.utilizers = utilizers;
    this.sport = sport;
    this.sportLevel = sportLevel;
    this.course = course;
    this.dates = course_dates ? this.getSelectedDates(course_dates) : [];
    //this.monitors = MOCK_MONITORS;
    this.clientObs = clientObs;
    this.schoolObs = schoolObs;
    this.forceStep = data.step;
    this.cdr.detectChanges();
  }

  addNewActivity() {
    this.isDetail = false;
    this.selectedIndexForm = null;
    this.currentStep = 1;
    const step1Controls = this.forms[0].get('step1').value;
    this.selectedForm = this.fb.group({
      step1: this.fb.group(step1Controls),
      step2: this.fb.group({}),
      step3: this.fb.group({}),
      step4: this.fb.group({}),
      step5: this.fb.group({}),
      step6: this.fb.group({})
    });
    this.utilizers = [];
    this.sport = null;
    this.sportLevel = null;
    this.course = null;
    this.dates = [];
    this.clientObs = null;
    this.schoolObs = null;
    this.forceStep = 1;
    this.calculateTotal();
    this.cdr.detectChanges();
  }

  getDegrees() {
    const user = JSON.parse(localStorage.getItem("boukiiUser"))
    this.crudService.list('/degrees', 1, 10000, 'asc', 'degree_order',
      '&school_id=' + user.schools[0].id + '&active=1')
      .subscribe((data) => this.allLevels = data.data)
  }

  calculateTotal() {
    let total = 0;
    if(!this.course) {
      this.total = null
      this.subtotal = null
      this.extraPrice = null
    } else {
      if (this.course.course_type === 1) {
        total = this.calculateColectivePrice();
      } else if (this.course.course_type === 2) {
        total = this.calculatePrivatePrice();
      }

      // Asegurarse de que el precio base no sea NaN
      if (isNaN(total) || total === undefined || total === null) {
        total = 0;
      }

      // Calcular el total de los extras
      const extrasTotal = this.dates.reduce((acc: number, date: any) => {
        if (date.extras && Array.isArray(date.extras) && date.extras.length > 0) {
          const extrasPrice = date.extras.reduce((extraAcc: number, extra: any) => {
            const price = parseFloat(extra.price) || 0;
            const quantity = parseFloat(extra.quantity) || 1;
            return extraAcc + price * quantity;
          }, 0);
          return acc + extrasPrice;
        }
        return acc;
      }, 0);

      // Asegurarse de que el total de extras sea un número válido
      const validExtrasTotal = isNaN(extrasTotal) ? 0 : extrasTotal;

      // Calcular el total final y asegurarse de que no sea NaN
      const totalSinExtras = total;
      total += validExtrasTotal;

      if (isNaN(total)) {
        total = 0;
      }

      // Formatear los resultados
      this.total = `${total.toFixed(2)} ${this.course.currency}`;
      this.subtotal = `${totalSinExtras.toFixed(2)}`;
      this.extraPrice = `${validExtrasTotal.toFixed(2)}`;
    }
  }


  deleteActivity(index: any) {
    this.forms.splice(index, 1);
    this.normalizedDates.splice(index, 1);
    this.selectedIndexForm = null;
    this.deleteModal = false;
    if (this.forms.length == 0) {
      this.currentStep = 0;
      this.isDetail = false;
      this.selectedForm = this.fb.group({
        step1: this.fb.group({}),
        step2: this.fb.group({}),
        step3: this.fb.group({}),
        step4: this.fb.group({}),
        step5: this.fb.group({}),
        step6: this.fb.group({})
      });
      this.forceStep = 0;
      this.cdr.detectChanges();
    }
  }

  private calculatePrivatePrice() {
    let total = 0;

    if (this.course.is_flexible) {
      // Calcula el precio basado en el intervalo y el número de utilizadores para cada fecha
      this.dates.forEach((date: any) => {
        const duration = date.duration; // Duración de cada fecha
        const selectedUtilizers = this.utilizers.length; // Número de utilizadores

        // Encuentra el intervalo de duración que se aplica
        const interval = this.course.price_range.find(range => {
          return range.intervalo === duration; // Comparar con la duración de la fecha
        });

        if (interval) {
          total += parseFloat(interval[selectedUtilizers]); // Precio por utilizador para cada fecha
        }

        // Suma el precio total de los extras para cada utilizador en esta fecha
        date.utilizers.forEach((utilizer: any) => {
          if (utilizer.extras && utilizer.extras.length) {
            const extrasTotal = utilizer.extras.reduce((acc, extra) => {
              const price = parseFloat(extra.price) || 0; // Convierte el precio del extra a un número
              return acc + price;
            }, 0);
            total += extrasTotal; // Suma el total de extras por cada utilizador
          }
        });

      });
    } else {
      // Si el curso no es flexible
      this.dates.forEach((date: any) => {
        const dateTotal = parseFloat(this.course.price) * this.utilizers.length; // Precio por número de utilizadores
        total += dateTotal;
        date.utilizers.forEach((utilizer: any) => {
          if (utilizer.extras && utilizer.extras.length) {
            const extrasTotal = utilizer.extras.reduce((acc, extra) => {
              const price = parseFloat(extra.price) || 0; // Convierte el precio del extra a un número
              return acc + price;
            }, 0);
            total += extrasTotal; // Suma el total de extras por cada utilizador
          }
        });
      });
    }

    return total;
  }

  private normalizeDates() {
    // Limpia el array normalizedDates antes de llenarlo
    this.normalizedDates = this.forms.map(form => {
      const {
        step1: { client, mainClient },
        step2: { utilizers },
        step3: { sport, sportLevel },
        step4: { course },
        step5: { course_dates },
        step6: { clientObs, schoolObs },
      } = form.value;

      const dates = course_dates ? this.getSelectedDates(course_dates) : [];

      // Calcular el total para cada actividad
      const { total, totalSinExtras, extrasTotal } = this.calculateIndividualTotal(course, dates, utilizers);


      return {
        utilizers,
        sport,
        sportLevel,
        course,
        dates,
        clientObs,
        schoolObs,
        total: `${total} ${course.currency}`, // Guardar el total calculado para esta actividad
        totalSinExtras: totalSinExtras, // Guardar el total sin extras
        extrasTotal: extrasTotal // Guardar el total de extras
      };
    });

    this.isDetail = true;
  }

  private calculateIndividualTotal(course, dates, utilizers) {
    let total = 0;

    // Calcula el precio base dependiendo del tipo de curso
    if (course.course_type === 1) {
      total = this.calculateColectivePriceForDates(course, dates);
    } else if (course.course_type === 2) {
      total = this.calculatePrivatePriceForDates(course, dates, utilizers);
    }

    // Calcula el total de los extras
    // Calcula el total de los extras
    const extrasTotal = dates.reduce((acc, date) => {
      // Para cursos colectivos
      if (course.course_type === 1) {
        if (date.extras && date.extras.length) {
          const extrasPrice = date.extras.reduce((extraAcc, extra) => {
            const price = parseFloat(extra.price) || 0; // Convierte el precio del extra a un número
            return extraAcc + (price * (extra.quantity || 1)); // Multiplica el precio del extra por la cantidad
          }, 0);
          return acc + extrasPrice;
        }
      }
      // Para cursos privados
      else if (course.course_type === 2) {
        // Asegúrate de que 'utilizers' está definido en la fecha
        if (date.utilizers && date.utilizers.length) {
          // Sumar el total de extras de cada utilizador
          date.utilizers.forEach(utilizer => {
            if (utilizer.extras && utilizer.extras.length) {
              const extrasPrice = utilizer.extras.reduce((extraAcc, extra) => {
                const price = parseFloat(extra.price) || 0; // Convierte el precio del extra a un número
                return extraAcc + (price * (extra.quantity || 1)); // Multiplica el precio del extra por la cantidad
              }, 0);
              acc += extrasPrice; // Suma el precio de los extras del utilizador al acumulador
            }
          });
        }
      }
      return acc; // Retorna el acumulador
    }, 0);

    // Total sin extras
    const totalSinExtras = total;

    // Suma el total de extras al total general
    total += extrasTotal;

    // Puedes retornar un objeto con ambos totales si lo prefieres
    return {
      total: total.toFixed(2),
      totalSinExtras: totalSinExtras.toFixed(2),
      extrasTotal: extrasTotal.toFixed(2),
      currency: course.currency // Incluye la moneda si es necesario
    };
  }

  private calculateColectivePriceForDates(course, dates) {
    let total = 0;
    if (course.is_flexible) {
      const selectedDatesCount = dates.length; // Número de fechas seleccionadas
      total = course.price * selectedDatesCount;

      const discounts = course.discounts ? JSON.parse(course.discounts) : [];
      discounts.forEach((discount: { date: number; percentage: number }) => {
        if (discount.date <= selectedDatesCount) {
          const discountAmount = (course.price * discount.percentage) / 100;
          total -= discountAmount;
        }
      });
    } else {
      total = parseFloat(course.price);
    }
    return total;
  }

  private calculatePrivatePriceForDates(course: any, dates: any, utilizers: any) {
    let total = 0;

    if (course.is_flexible) {
      dates.forEach((date: any) => {
        const duration = date.duration;
        const selectedUtilizers = utilizers.length;

        const interval = course.price_range.find(range => {
          return range.intervalo === duration;
        });

        if (interval) {
          total += parseFloat(interval[selectedUtilizers]);
        }

        date.utilizers.forEach(utilizer => {
          if (utilizer.extras && utilizer.extras.length) {
            const extrasTotal = utilizer.extras.reduce((acc, extra) => {
              const price = parseFloat(extra.price) || 0;
              return acc + price;
            }, 0);
            total += extrasTotal;
          }
        });
      });
    } else {
      dates.forEach((date: any) => {
        const dateTotal = parseFloat(course.price) * utilizers.length;
        total += dateTotal;
        date.utilizers.forEach(utilizer => {
          if (utilizer.extras && utilizer.extras.length) {
            const extrasTotal = utilizer.extras.reduce((acc, extra) => {
              const price = parseFloat(extra.price) || 0;
              return acc + price;
            }, 0);
            total += extrasTotal;
          }
        });
      });
    }

    return total;
  }

  // Método para obtener el intervalo de precios basado en la duración
  private getPriceInterval(duration: number) {
    const priceRanges = this.course.price_range;
    return priceRanges.find((interval: any) => {
      const intervalDuration = this.parseDuration(interval.intervalo);
      return duration <= intervalDuration;
    });
  }

  // Método para parsear la duración en formato de texto a minutos
  private parseDuration(durationStr: string): number {
    const parts = durationStr.split(' ');
    let totalMinutes = 0;

    parts.forEach(part => {
      if (part.includes('h')) {
        totalMinutes += parseInt(part, 10) * 60;
      } else if (part.includes('m')) {
        totalMinutes += parseInt(part, 10);
      }
    });

    return totalMinutes;
  }

  private calculateColectivePrice() {
    let total = 0;
    if (this.course.is_flexible) {
      // Si el curso es flexible
      const selectedDatesCount = this.dates.length; // Número de fechas seleccionadas
      total = this.course.price * selectedDatesCount;

      // Aplica los descuentos
      const discounts = this.course.discounts ? JSON.parse(this.course.discounts) : [];
      discounts.forEach((discount: { date: number; percentage: number }) => {
        if (discount.date <= selectedDatesCount) {
          const discountAmount = (this.course.price * discount.percentage) / 100;
          total -= discountAmount;
        }
      });
    } else {
      // Si el curso no es flexible
      total = parseFloat(this.course.price);
    }
    return total;
  }

  // Filtra las fechas seleccionadas
  getSelectedDates(dates: any) {
    return dates.filter((date: any) => date.selected);
  }

  openBookingDialog() {
    this.dialog.open(BookingDialogComponent, {
      width: "400px",
      panelClass: "customBookingDialog",
      position: {
        bottom: "24px",
        right: "24px",
      },
      data: {
        utilizers: this.utilizers,
        sport: this.sport,
        sportLevel: this.sportLevel,
        course: this.course,
        dates: this.dates,
        monitors: this.monitors,
        clientObs: this.clientObs,
        schoolObs: this.schoolObs,
        total: this.total,
        isDetail: this.isDetail,
        mainClient: this.mainClient,
        normalizedDates: this.normalizedDates
      },
    });
  }

  sumActivityTotal(): number {
    return this.normalizedDates.reduce((acc, item) => {
      const numericValue = parseFloat(item.total.replace(/[^\d.-]/g, '')); // Eliminar cualquier cosa que no sea un número o signo
      return acc + numericValue;
    }, 0);
  }

  forceChange(newStep: any) {
    this.forceStep = newStep;
    this.cdr.detectChanges();

  }

  changeStep(newStep: any) {
    this.forceStep = newStep;
    this.cdr.detectChanges();

  }


  onPaymentMethodChange(event: any) {
    // Lógica para manejar el cambio de método de pago
    if (event.value === 1) {
      // Si se selecciona 'Pago directo', establecer un valor predeterminado o comportamiento necesario
      this.selectedPaymentOption = null; // Resetear la opción de pago seleccionada si es necesario
    } else {
      // Para otros métodos de pago, puedes asignar flags específicos
      this.selectedPaymentOption = event.value; // Ejemplo: asignar el método seleccionado
    }
  }

  goToNextStep() {
    this.step = 2;  // Cambiar al paso 2 de confirmación de pago
  }

  cancelPaymentStep() {
    if(this.step == 1) {
      this.payModal = false;
    }
    this.step = 1;  // Regresar al paso 1
    this.isPaid = false;  // Resetear isPaid
  }

  // Método para finalizar la reserva
  finalizeBooking(): void {
    let bookingData = this.bookingService.getBookingData();
    bookingData.cart = this.bookingService.setCart(this.normalizedDates, bookingData);
    bookingData.payment_method_id = this.paymentMethod;

    if(this.paymentMethod === 1) {
      // Mapear la opción seleccionada con el método de pago
      if (this.selectedPaymentOption === 'Efectivo') {
        bookingData.payment_method_id = 1;
      } else if (this.selectedPaymentOption === 'Boukii Pay') {
        bookingData.payment_method_id = 2;
      } else if (this.selectedPaymentOption === 'Tarjeta') {
        bookingData.payment_method_id = 4;
      }
    }


    if (this.bookingService.calculatePendingPrice() === 0) {
      bookingData.paid = true;
      bookingData.paid_total = bookingData.price_total;
    }
    // Si es pago en efectivo o tarjeta, guardar si fue pagado
    if (bookingData.payment_method_id === 1 || bookingData.payment_method_id === 4) {
      if (this.isPaid) {
        bookingData.paid = true;
        bookingData.paid_total = bookingData.price_total;
      } else {
        bookingData.paid = false;
      }
    }

    const user = JSON.parse(localStorage.getItem("boukiiUser"));
    bookingData.selectedPaymentOption = this.selectedPaymentOption;
    bookingData.user_id = user.id;

    // Enviar la reserva a la API
    this.crudService.post('/admin/bookings', bookingData)
      .subscribe(
        (result: any) => {
          const bookingId = result.data.id;

          // Manejar pagos en línea
          if (bookingData.payment_method_id === 2 || bookingData.payment_method_id === 3) {
            this.crudService.post(`/admin/bookings/payments/${bookingId}`, result.data.basket)
              .subscribe(
                (paymentResult: any) => {
                  if (bookingData.payment_method_id === 2) {
                    window.open(paymentResult.data, "_self");
                  } else {
                    this.showErrorSnackbar("Error al procesar el pago en línea.");
                    this.router.navigate([`/bookings-v2/update/${bookingId}`]);
                  }
                },
                (error) => {
                  this.showErrorSnackbar("Error al procesar el pago en línea.");
                  this.router.navigate([`/bookings-v2/update/${bookingId}`]);
                }
              );
          } else {
            // Si no es pago online, llevar directamente a la página de actualización
            this.router.navigate([`/bookings/update/${bookingId}`]);
          }
        },
        (error) => {
          this.showErrorSnackbar("Error al crear la reserva.");
        }
      );
  }

  // Función para mostrar un Snackbar en caso de error
  showErrorSnackbar(message: string): void {
    this.snackBar.open(message, "Cerrar", {
      duration: 3000,
      panelClass: ['error-snackbar']
    });
  }
}
