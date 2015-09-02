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
        var paramsObject = {itemNumber: itemNumber};
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
            params: queryStringObject
        });
    };

    this.checkServerItemStatus = function (rwFilteredItemList) {
        return $http({
            method: 'POST',
            url: urlBase + 'checkServerItemStatus',
            data: rwFilteredItemList
        });
    };

    this.uploadExcelAndImport = function (uploadFileInfo) {
        return $http({
            method: 'POST',
            url: urlBase + 'uploadExcelAndImport',
            data: uploadFileInfo
        });
    };

    this.retrieveAttributeSetmappingTable = function () {
        return $http({
            method: 'GET',
            url: urlBase + 'retrieveAttributeSetmappingTable'
        });
    };

    this.getCombinationInfo = function (rwItem) {
        return $http({
            method: 'POST',
            url: urlBase + 'getCombinationInfo',
            data: rwItem
        });
    };

    this.getAttributesById = function (attribute_set_id) {
        return $http({
            method: 'GET',
            url: urlBase + 'getAttributesById',
            params: {id: attribute_set_id}
        });
    };

    this.getAttributeOptions = function (attributeCodes) {
        return $http({
            method: 'GET',
            url: urlBase + 'getAttributeOptionsByAttrCode',
            params: {attributeCodes: attributeCodes}
        });
    };

    this.getConfigJson = function () {
        return $http({
            method: 'GET',
            url: urlBase + 'configJson'
        })
    };

    this.postConfigJson = function (postData) {
        return $http({
            method: 'POST',
            url: urlBase + 'configJson',
            data: postData
        })
    };

});