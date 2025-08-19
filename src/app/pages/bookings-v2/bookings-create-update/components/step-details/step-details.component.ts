import { Component, Input, Output, EventEmitter } from "@angular/core";
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import { MOCK_COURSE_PRIVATE, MOCK_COURSE_COLECTIVE } from "../../mocks/course";
import { MOCK_USER1, MOCK_USER2 } from "../../mocks/user";

@Component({
  selector: "booking-step-details",
  templateUrl: "./step-details.component.html",
  styleUrls: ["./step-details.component.scss"],
})
export class StepDetailsComponent {
  @Input() initialData: any;
  @Input() course: any;
  @Input() date: any;
  @Input() utilizers: any;
  @Input() activitiesBooked: any;
  @Input() selectedForm: FormGroup;
  @Input() dateForm: any;
  @Input() sportLevel: any;
  @Output() stepCompleted = new EventEmitter<FormGroup>();
  @Output() prevStep = new EventEmitter();
  addDateEvent = false;
  addParticipantEvent = false;
  stepForm: FormGroup;
  utilizer;

  constructor(private fb: FormBuilder) {

  }

  ngOnInit(): void {
    this.utilizer = this.utilizers?.[0];
    if(!this.stepForm) {
      this.stepForm = this.fb.group({
        course: this.course
      });
    }
  }

  get isFormValid() {
    if(this.stepForm) {
      return this.stepForm.valid;
    }
    return false;
  }

  handlePrevStep() {
    this.prevStep.emit();
  }

  addPrivateDate() {
    this.addDateEvent = true;  setTimeout(() => this.addDateEvent = false, 0);
  }

  addParticipant() {
    this.addParticipantEvent = true;  setTimeout(() => this.addParticipantEvent = false, 0);
  }

  completeStep() {
    if (this.isFormValid) {
      this.stepCompleted.emit(this.stepForm);
    }
  }


}
