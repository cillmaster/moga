module outlines {

  var vectorPreviewDirective:ng.IDirective = {
    restrict: 'E',
    templateUrl: '/nox-themes/default/templates/app/vector-preview.html',
    controller: VectorPreviewController,
    controllerAs: 'vectorPreviewCtrl',
    link: function(scope, element, attrs, ctrl: VectorPreviewController) {
      ctrl.vector = <Vector>JSON.parse(base64_decode(attrs['vector']));
    }
  };

  angular.module(MODULE).directive('vectorPreview', () => vectorPreviewDirective);
}
