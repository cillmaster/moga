(function () {
    function NavController($scope, userService) {
        this.$inject = ['$scope', 'userService'];

        $scope.isAuthentificated = userService.isAuthentificated();
    }

    angular.module('outlines').controller('NavController', NavController);
})();