(function() {
    angular.module('outlines').factory('userService', ['dataService', userService]);

    function userService(dataService) {
        var user = dataService.getFromSession('user');

        return {
            getUser: function() {
                return user;
            },
            isAuthentificated: function() {
                return !!user;
            },
            authentificate: function() {

            }
        }
    }
})();