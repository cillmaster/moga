var AuthEvents = {
    // Listen
    AuthMe: 'AuthMe'
};

(function() {
    angular.module('outlines').directive('auth', [authDirective]);

    function AuthCtrl(userService, $scope) {
        $scope.showDialog = false;

        this.show = function() {
            $scope.showDialog = true;
        };
        this.hide = function() {
            $scope.showDialog = false;
        };

        $scope.$on(AuthEvents.AuthMe, function() {

        });
    }

    function authDirective() {
        return {
            restrict: 'E',
            controllerAs: 'authCtrl',
            templateUrl: '/nox-themes/default/templates/app/auth.html',
            controller: ['userService', '$scope', AuthCtrl]
        }
    }
})();



