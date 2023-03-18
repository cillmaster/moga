module outlines {

  class VectorPrintController {

    static $inject = ['popupService'];

    constructor(private popupService) { }

    openPaymentDialog(vectorId, daysMax) {
      this.popupService.open(PopupViews.VECTOR_PAYMENT, {vectorId: vectorId, daysMax: daysMax});
    }
  }

  angular.module('outlines').controller('vectorPrintController', VectorPrintController);

  /*
  ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   */

  class PaymentController {

    static $inject = ['popupService'];

    processing:boolean = false;
    paymentType:string = null;

    get vectorId() {
      return this.popupService.scope.vectorId;
    }

      get daysMax() {
          return this.popupService.scope.daysMax;
      }

      constructor(public popupService) { }

    pay(paymentType) {
      this.paymentType = paymentType;
      this.processing = true;
    }
  }

  angular.module('outlines').controller('paymentController', PaymentController);
}
