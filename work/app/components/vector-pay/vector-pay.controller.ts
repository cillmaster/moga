module outlines {

  export class VectorPayController {

    static $inject = ['$attrs', '$sce', 'popupService'];

    vectorId:string;
    price:string;
    paytype:string;
    daysMax:string;
    large:boolean;
    invertColors:boolean;

    constructor($attrs:ng.IAttributes, $sce:ng.ISCEService, private popupService: PopupService) {
      this.price = $sce.trustAsHtml($attrs['price']);
      this.paytype = $sce.trustAsHtml($attrs['paytype']);
    }

    openDialog() {
      this.popupService.open(PopupViews.VECTOR_PAYMENT, {vectorId: this.vectorId, daysMax: this.daysMax})
    }
  }
}
