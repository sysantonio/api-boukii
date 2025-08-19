import { NgModule } from "@angular/core";
import { RouterModule } from "@angular/router";
import { VexRoutes } from "src/@vex/interfaces/vex-route.interface";
import { BookingsV2Component } from "./bookings.component";
import { BookingsCreateUpdateV2Component } from "./bookings-create-update/bookings-create-update.component";
import { BookingDetailV2Component } from "./booking-detail/booking-detail.component";

const routes: VexRoutes = [
  {
    path: "",
    component: BookingsV2Component,
    data: {
      toolbarShadowEnabled: true,
    },
  },
  {
    path: "create",
    component: BookingsCreateUpdateV2Component,
    data: {
      toolbarShadowEnabled: true,
    },
  },
  {
    path: "update/:id",
    component: BookingDetailV2Component,
    data: {
      toolbarShadowEnabled: true,
    },
  },
  {
    path: "edit/:id",
    component: BookingsCreateUpdateV2Component,
    data: {
      toolbarShadowEnabled: true,
    },
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class BookingsRoutingModule { }
