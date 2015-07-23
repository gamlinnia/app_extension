app.service('restService', function($http) {

    var urlBase = 'rest/route.php/api/';

    this.destroySession = function () {
        return $http({
            method: 'GET',
            url: urlBase + 'destroySession'
        })
    };

    this.checkSessionState = function (authObject) {
        return $http({
            method: 'GET',
            url: urlBase + 'checkSessionState',
            params: authObject
        });
    };

    this.proceedRestData = function (authObject, page, rowsPerPage) {
        return $http({
            method: 'POST',
            url: urlBase + 'proceedRestData',
            params: {page: page, rowsPerPage: rowsPerPage},
            data: authObject
        });
    };

    this.getInformationFromIntelligence = function (itemNumber) {
        paramsObject = {itemNumber: itemNumber};
        return $http({
            method: 'GET',
            url: urlBase + 'getInformationFromIntelligence',
            params: paramsObject
        });
    };

    this.uploadProductImages = function (data) {
        return $http({
            method: 'POST',
            url: urlBase + 'uploadProductImages',
            data: data
        });
    };

    this.getRwProductList = function (queryStringObject) {
        return $http({
            method: 'GET',
            url: urlBase + 'getRwProductList',
            param: queryStringObject
        });
    }

});