app.service('restService', function($http) {

    var local_url = 'rest/route.php/api/';
    var remote_url = 'http://114.34.186.208/rosewill/rest/route.php/api/';
    var urlBase = remote_url;


    this.destroySession = function () {
        return $http({
            method: 'GET',
            url: urlBase + 'destroySession'
        })
    };

    this.checkSessionState = function (authObject) {
        console.log(authObject);
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

    this.checkServerItemStatus = function (rwFilteredItemList, host) {
        return $http({
            method: 'POST',
            url: urlBase + 'checkServerItemStatus',
            params: {host: host},
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

    this.retrieveAttributeSetmappingTable = function (authObject) {
        return $http({
            method: 'GET',
            url: urlBase + 'retrieveAttributeSetmappingTable',
            params: authObject
        });
    };

    this.getCombinationInfo = function (rwItem) {
        return $http({
            method: 'POST',
            url: urlBase + 'getCombinationInfo',
            data: rwItem
        });
    };

    this.getAttributesById = function (attribute_set_id, authObject) {
        authObject.id = attribute_set_id;
        return $http({
            method: 'GET',
            url: urlBase + 'getAttributesById',
            params: authObject
        });
    };

    this.getAttributeOptions = function (attributeCodes, authObject) {
        authObject.attributeCodes = attributeCodes;
        return $http({
            method: 'GET',
            url: urlBase + 'getAttributeOptionsByAttrCode',
            params: authObject
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
    
        //20150918 add by tim
    this.getFormList = function (){
        return $http({
            method: 'GET',
            url: urlBase + 'testShowForm1'
        })
    }

    this.getFormDetail = function (form_name){
        return $http({
            method: 'GET',
            url: urlBase + 'getFormList/' + form_name
        })
    }

});