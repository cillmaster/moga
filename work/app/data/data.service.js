(function() {
    angular.module('outlines').factory('dataService', [dataService]);

    function dataService() {
        var session = window.outlinesSession;
        var locationQuery;

        return {
            getFromSession: function(key) {
                return session[key];
            },
            getFromLocationQuery: function(key) {
                
            }
        }
    }
})();