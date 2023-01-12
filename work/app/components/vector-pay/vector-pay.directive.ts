module outlines {

  var vectorPayDirective:ng.IDirective = {
    restrict: 'E',
    templateUrl: '/nox-themes/default/templates/app/vector-pay.html',
    controller: outlines.VectorPayController,
    controllerAs: 'vectorPayCtrl',
    scope: {
      vectorId: '=',
      daysMax: '=',
      large: '=?',
      invertColors: '=?'
    },
    bindToController: true
  };

  angular.module(MODULE).directive('vectorPay', () => vectorPayDirective);
}
