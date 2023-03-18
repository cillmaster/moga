module outlines {

  export class PopupService {

    static $inject = ['$timeout'];

    private isOpen:boolean = false;
    private scope:Object = null;
    private template:string = null;

    constructor(private $timeout: ng.ITimeoutService) {
      document.addEventListener('keyup', function(e:KeyboardEvent) {
        if(this.isOpen) {
          return true;
        }
        switch(e.keyCode) {
          case 27:
            $timeout(this.close.bind(this));
            return false;
          default:
            return true;
        }
      });
    }

    open(template, scope) {
      if(typeof template === 'string') this.template = template;
      this.isOpen = true;
      this.scope = (scope) ? scope : null;
    }

    close() {
      this.isOpen = false;
    }
  }

  angular.module('outlines').service('popupService', PopupService);
}

var PopupViews = {
    VECTOR_PAYMENT: '/nox-themes/default/templates/app/payment-type.html'
};
