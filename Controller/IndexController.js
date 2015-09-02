app.controller('indexController', function($scope, restService, $window, Utils, $filter) {

    /*page setup ↓*/
    $scope.bigCurrentPage = 1;
    $scope.rowsPerPage = 100;

    $scope.setPage = function (pageNo) {
        $scope.bigCurrentPage = pageNo;
    };
    /*page setup ↑*/

    $scope.pageSetup = {
        debugMode: false,
        loading: false,
        enableSubmit: false
    };

    $scope.authenticated = false;
    $scope.startTimeStamp = new Date().valueOf();

    $scope.getConfigJson = function (modifyObject) {
        restService.getConfigJson(modifyObject).success(function (response) {
            Utils.assignObjToAntoher($scope, response);
            if (Utils.isDefinedAndNotNull($scope.host)) {
                Utils.assignObjToAntoher($scope, $scope.magentoConfig[$scope.host]);
            }
            $scope.pageSetup.enableSubmit = true;
        });
    };
    $scope.getConfigJson();

    $scope.changeMagentoHost = function () {
        $scope.postConfigJson({host: $scope.host});
        Utils.assignObjToAntoher($scope, $scope.magentoConfig[$scope.host]);
    };

    $scope.postConfigJson = function (modifyObject, type) {
        switch (type) {
            case 'dateString' :
                var theDateString = Utils.downToLastElement(modifyObject);
                if (theDateString.match(/^[0-9]{4}-[0-1][0-9]-[0-1][0-9]$/)) {
                    restService.postConfigJson(modifyObject).success(function (response) {
                        console.log(response);
                    });
                }
                break;
            default :
                restService.postConfigJson(modifyObject).success(function (response) {
                    console.log(response);
                });
        }
    };

    $scope.getQueryParam = function(param) {
        var found;
        $window.location.search.substr(1).split("&").forEach(function(item) {
            if (param ==  item.split("=")[0]) {
                found = item.split("=")[1];
            }
        });
        return found;
    };

    $scope.retrieveAttributeSetmappingTable = function () {
        restService.retrieveAttributeSetmappingTable().success(function (response) {
            $scope.attributeSetTable = response;
        })
    };

    $scope.checkSessionState = function () {
        $scope.parseAuthParams();
        restService.checkSessionState($scope.authObject).success(function (data) {
            if (data.status == 'success') {
                switch (data.state) {
                    case 1:
                        $window.location.assign(data.location);
                        break;
                    case 2:
                        $window.location.assign(data.location);
                        break;
                    case 'verified':
                        $scope.authenticated = true;
                        $scope.alert = {
                            type: 'success',
                            msg: 'AUTHENTICATION SUCCESS'
                        };
                        $scope.retrieveAttributeSetmappingTable();
                        break;
                }
            }
        });
    };

    $scope.parseAuthParams = function () {
        var callbackUrl = $window.location.origin + $window.location.pathname;
        $scope.authObject = {
            callbackUrl: callbackUrl,
            apiUrl: 'http://' + $scope.host + $scope.magentoDirectory + '/api/rest',
            temporaryCredentialsRequestUrl: 'http://' + $scope.host + $scope.magentoDirectory + '/index.php/oauth/initiate?oauth_callback=' + encodeURIComponent(callbackUrl),
            adminAuthorizationUrl: 'http://' + $scope.host + $scope.magentoDirectory + '/index.php/admin/oauth_authorize',
            accessTokenRequestUrl: 'http://' + $scope.host + $scope.magentoDirectory + '/index.php/oauth/token',
            consumerKey: $scope.consumerKey,
            consumerSecret: $scope.consumerSecret
        };
        var oauth_token = $scope.getQueryParam('oauth_token');
        var oauth_verifier = $scope.getQueryParam('oauth_verifier');
        if (oauth_token) {
            $scope.authObject.oauth_token = oauth_token;
        }
        if (oauth_verifier) {
            $scope.authObject.oauth_verifier = oauth_verifier;
        }
        $scope.responseData = {
            intelligence: []
        };
    };

    $scope.destroySession = function () {
        restService.destroySession().success(function (data) {
            $window.alert('Session Destroyed');
        });
    };

    $scope.backHome = function () {
        $window.location.assign($scope.authObject.callbackUrl);
    };

    $scope.getProductList = function () {
        $scope.resetProductList();
        $scope.authObject.action = 'getProductList';
        $scope.authObject.method = 'GET';
        $scope.authObject.restPostfix = '/products';
        restService.proceedRestData($scope.authObject, $scope.bigCurrentPage, $scope.rowsPerPage).success(function (response) {
            $scope.count = {
                update: 0,
                getInfo: 0,
                uploadImage: {
                    success: 0,
                    fail: 0
                }
            };
            $scope.productList = response.DataCollection;
        });
    };

    $scope.getProductImages = function () {
        $scope.authObject.action = 'getProductImages';
        $scope.authObject.method = 'GET';
//        $scope.authObject.restPostfix = ''/products/' . $itemObj['entity_id'] . '/images'';
        restService.proceedRestData($scope.authObject, $scope.bigCurrentPage, $scope.rowsPerPage).success(function (response) {
            $scope.count = {
                update: 0,
                getInfo: 0,
                uploadImage: {
                    success: 0,
                    fail: 0
                }
            };
            $scope.productList = response.DataCollection;
        });
    };

    $scope.createNewProduct = function () {
        $scope.authObject.action = 'createNewProduct';
    };

    $scope.closeAlert = function () {
        delete $scope.alert;
    };

    $scope.isNotEmpty = function (value) {
        return Utils.isDefinedAndNotNull(value);
    };

    $scope.getInformationFromIntelligence = function () {
        angular.forEach($scope.productList, function (obj, key) {
            restService.getInformationFromIntelligence(obj.sku).success(function (response) {
                $scope.productList[key].intelligenceInfo = $scope.productList[key].intelligenceInfo || {};
                $scope.productList[key].intelligenceInfo = response.DataCollection;
                $scope.count.getInfo++;
                $scope.alert = {
                    type: 'success',
                    msg: $scope.count.getInfo
                };
                if ($scope.count.getInfo == $scope.rowsPerPage) {
                    $scope.alert.msg = 'GET INFO DONE';
                }
            });
        });
    };

    $scope.updateInfo = function (updateId, productInfo) {
        var UpdateObj = {
            action: 'updateProductList',
            method: 'PUT',
            restPostfix: '/products/' + updateId,
            requestBody: {
                description: productInfo.intelligenceInfo.Introduction ? productInfo.intelligenceInfo.Introduction : ' ',
                ne_description: productInfo.intelligenceInfo.Introduction ? productInfo.intelligenceInfo.Introduction : ' ',
                ne_highlight: productInfo.intelligenceInfo.Intelligence ? productInfo.intelligenceInfo.Intelligence : ' '
            },
            apiUrl: $scope.authObject.apiUrl,
            consumerKey: $scope.authObject.consumerKey,
            consumerSecret: $scope.authObject.consumerSecret
        };
        restService.proceedRestData(UpdateObj).success(function (response) {
            $scope.count.update++;
            $scope.alert = {
                type: 'success',
                msg: response.restPostfix + ' ' + response.http_code + ' ' + $scope.count.update
            };
        });
    };

    $scope.updateAllInfo = function () {
        angular.forEach($scope.productList, function (obj) {
            if (Utils.isUndefinedOrNull(obj.intelligenceInfo)) {
                $window.alert('NO INFO TO UPDATE');
                return;
            }
            $scope.updateInfo(obj.entity_id, obj);
        });
    };

    $scope.proceedUploading = function (obj) {
        var uploadObj = {
            apiUrl: $scope.authObject.apiUrl,
            consumerKey: $scope.authObject.consumerKey,
            consumerSecret: $scope.authObject.consumerSecret,
            itemObj: {
                entity_id: obj.entity_id,
                sku: obj.sku
            }
        };
        restService.uploadProductImages(uploadObj).success(function (response) {
            if (response.length > 0) {
                $scope.count.uploadImage.success++;
            } else {
                if (response.message && response.message == 'NO IMAGE NEED TO BE UPLOADED') {
                    $scope.count.uploadImage.success++;
                } else {
                    $scope.count.uploadImage.fail++;
                    $scope.errorObj.push({
                        entity_id: obj.entity_id,
                        sku: obj.sku
                    });
                }
            }
            $scope.alert = {
                type: 'success',
                msg: 'sucess: ' + $scope.count.uploadImage.success + ', fail: ' + $scope.count.uploadImage.fail + ', count: ' + ($scope.count.uploadImage.count +1)
            };
            if ($scope.count.uploadImage.count < $scope.rowsPerPage) {
                $scope.count.uploadImage.count++;
                $scope.proceedUploading($scope.productList[$scope.count.uploadImage.count]);
            } else {
                console.log('DONE');
                $scope.alert = {
                    type: 'success',
                    msg: 'DONE UPLOADING' + ', sucess: ' + $scope.count.uploadImage.success + ', fail: ' + $scope.count.uploadImage.fail
                };
                $window.alert('DONE');
            }
        }).error(function () {
            $scope.count.uploadImage.fail++;
            $scope.errorObj.push({
                entity_id: obj.entity_id,
                sku: obj.sku
            });

            $scope.alert = {
                type: 'success',
                msg: 'sucess: ' + $scope.count.uploadImage.success + ', fail: ' + $scope.count.uploadImage.fail + ', count: ' + ($scope.count.uploadImage.count +1)
            };

            if ($scope.count.uploadImage.count < $scope.rowsPerPage) {
                $scope.proceedUploading($scope.productList[$scope.count.uploadImage.count]);
            } else {
                $scope.alert = {
                    type: 'success',
                    msg: 'DONE UPLOADING' + ', sucess: ' + $scope.count.uploadImage.success + ', fail: ' + $scope.count.uploadImage.fail
                };
                $window.alert('DONE');
            }
        });
    };

    $scope.uploadProductImages = function () {
        $scope.count.uploadImage = {
            count: 0,
            success: 0,
            fail: 0
        };
        $scope.errorObj = [];
        $scope.proceedUploading($scope.productList[$scope.count.uploadImage.count]);
    };

    $scope.uploadSingleProductImage = function (rwItem) {
        var uploadObj = {
            apiUrl: $scope.authObject.apiUrl,
            consumerKey: $scope.authObject.consumerKey,
            consumerSecret: $scope.authObject.consumerSecret,
            itemObj: {
                entity_id: rwItem.entity_id,
                sku: rwItem.sku
            }
        };
        console.log(rwItem);
        console.log(uploadObj);
        return;
        restService.uploadProductImages(uploadObj).success(function (response) {
            console.log(response);
            if (response.length > 0) {
                $scope.count.uploadImage.success++;
            } else {
                if (response.message && response.message == 'NO IMAGE NEED TO BE UPLOADED') {
                    $scope.count.uploadImage.success++;
                } else {
                    $scope.count.uploadImage.fail++;
                    $scope.errorObj.push({
                        entity_id: obj.entity_id,
                        sku: obj.sku
                    });
                }
            }
            $scope.alert = {
                type: 'success',
                msg: 'sucess: ' + $scope.count.uploadImage.success + ', fail: ' + $scope.count.uploadImage.fail + ', count: ' + ($scope.count.uploadImage.count +1)
            };
            if ($scope.count.uploadImage.count < $scope.rowsPerPage) {
                $scope.count.uploadImage.count++;
                $scope.proceedUploading($scope.productList[$scope.count.uploadImage.count]);
            } else {
                console.log('DONE');
                $scope.alert = {
                    type: 'success',
                    msg: 'DONE UPLOADING' + ', sucess: ' + $scope.count.uploadImage.success + ', fail: ' + $scope.count.uploadImage.fail
                };
                $window.alert('DONE');
            }
        }).error(function () {
            $scope.count.uploadImage.fail++;
            $scope.errorObj.push({
                entity_id: obj.entity_id,
                sku: obj.sku
            });

            $scope.alert = {
                type: 'success',
                msg: 'sucess: ' + $scope.count.uploadImage.success + ', fail: ' + $scope.count.uploadImage.fail + ', count: ' + ($scope.count.uploadImage.count +1)
            };

            if ($scope.count.uploadImage.count < $scope.rowsPerPage) {
                $scope.proceedUploading($scope.productList[$scope.count.uploadImage.count]);
            } else {
                $scope.alert = {
                    type: 'success',
                    msg: 'DONE UPLOADING' + ', sucess: ' + $scope.count.uploadImage.success + ', fail: ' + $scope.count.uploadImage.fail
                };
                $window.alert('DONE');
            }
        });
    };

    $scope.resetProductList = function () {
        $scope.productList = [];
    };

    $scope.getRwProductList = function () {
        $scope.parseAuthParams();
        $scope.authObject.action = 'getRwProductList';
        restService.getRwProductList($scope.params.getRwProductList).success(function (response) {
            $scope.rwFilteredItemList = response.DataCollection;
            $scope.checkServerItemStatus();
        });
    };

    $scope.toggleDebugMode = function () {
        $scope.pageSetup.debug = !$scope.pageSetup.debug;
    };

    var orderBy = $filter('orderBy');
    $scope.order = function(predicate, reverse, objName) {
        $scope[objName] = orderBy($scope[objName], predicate, reverse);
    };

    $scope.filterJavascriptDate = function (javascriptDateObj) {
        if (!javascriptDateObj) {
            return typeof javascriptDateObj;
        }
        var dateArray = javascriptDateObj.match(/[0-9]+[-]{1}[0-9]+/);
        var javascriptDate = parseInt(dateArray[0]);
        return new Date(javascriptDate).toLocaleDateString();
    };

    $scope.checkServerItemStatus = function () {
        restService.checkServerItemStatus($scope.rwFilteredItemList).success(function (response) {
            $scope.rwFilteredItemList = response.DataCollection;
            angular.forEach($scope.rwFilteredItemList, function (obj, index) {
                if (Utils.isDefinedAndNotNull(obj.attribute_set_id)) {
//                    console.log(Utils.mapAttributeSetIdToName(obj.attribute_set_id, $scope.attributeSetTable));
                    $scope.rwFilteredItemList[index].attribute_set_name = Utils.mapAttributeSetIdToName(obj.attribute_set_id, $scope.attributeSetTable);
                }
            });
            $scope.alert = {
                type: 'success',
                msg: "count: " + response.count + ", notExistsCount: " + response.notExistsCount
            };
        });
    };

    $scope.uploadExcelAndImport = function (uploadFile) {
        $scope.authObject.action = 'importXlsx';
        $scope.deleteObject($scope, 'response');
        restService.uploadExcelAndImport(uploadFile).success(function (response) {
            $scope.response = response.DataCollection;
            $scope.test = {};
            for (var key in $scope.response) {
                console.log($scope.response[key]['NE Item Maintain Subcategory\n(by Code & Name)'], $scope.response[key]['RWPIM Subcategory Name']);
                $scope.test[$scope.response[key]['NE Item Maintain Subcategory\n(by Code & Name)']] = $scope.response[key]['RWPIM Subcategory Name'];
            }
        });
    };

    $scope.deleteObject = function (obj, key) {
        if (Utils.isDefinedAndNotNull(obj[key])) {
            delete obj[key];
        }
    };

    $scope.createProduct = function (createProductObj) {
        var createObj = {
            action: 'createProduct',
            method: 'POST',
            restPostfix: '/products',
            apiUrl: $scope.authObject.apiUrl,
            consumerKey: $scope.authObject.consumerKey,
            consumerSecret: $scope.authObject.consumerSecret,
            requestBody: createProductObj
        };
        restService.proceedRestData(createObj).success(function (response) {
            console.log(response);
            if (Utils.isDefinedAndNotNull(response.http_code) && response.http_code == 200) {
                $window.alert('Product ' + createProductObj.sku + ' has been Inserted.');
            } else {
                $window.alert('Error occur');
            }
        });
    };

    $scope.getInfoThroughAPI = function ($event, rwItem, idx) {
        $scope.clickedTr($event);
        restService.getCombinationInfo(rwItem).success(function (response) {
            $scope.rwFilteredItemList[idx] = response;
            if (Utils.isDefinedAndNotNull($scope.rwFilteredItemList[idx]['baseinfo'])) {
                if (Utils.isDefinedAndNotNull($scope.rwFilteredItemList[idx]['baseinfo']['SubcategoryName'])) {
                    var subCategoryObj = Utils.neSubcategoryToMagentoSubcategory($scope.rwFilteredItemList[idx].baseinfo.SubcategoryName);
                }
            } else {
                alert('no subcategory' + $scope.rwFilteredItemList[idx].ItemNumber);
            }
            $scope.rwFilteredItemList[idx].attribute_set_name = subCategoryObj.status == 'success' ? subCategoryObj.attr_names : ['No Matching'];
//            console.log($scope.rwFilteredItemList[idx]);
//            Utils.formatProductCreateObjectFromApi(response, $scope.attributeSetTable);
//            console.log(response, idx);
        });
    };

    $scope.clickedTr = function ($event) {
        $($event.target).parents('tr').css({
            'background-color': 'yellow'
        });
    };

    $scope.chooseAttrAndUploadNewProduct = function (idx, attr_name, rwItem) {
        if (rwItem.exists) {
            return;
        }
        if (confirm(
                'Sure to choose ' + attr_name + '\n' +
                'for\n' +
                rwItem.Description + '?'
        )) {
            rwItem.attribute_set_name = attr_name;
            rwItem.attribute_set_id = Utils.mapAttributeSetNameToId(rwItem.attribute_set_name, $scope.attributeSetTable);
            restService.getAttributesById(rwItem.attribute_set_id).success(function (response) {
                var is_visible_on_frontAttributesArray = response.is_visible_on_front;
                var specAttributesValueMappingObject = Utils.mapMagentoSpecAndProperty(rwItem.property, is_visible_on_frontAttributesArray);
                Utils.mapMagentoOptions(specAttributesValueMappingObject, function(specAttributesOptionMappingObject) {
                    var parseProcess = Utils.formatProductCreateObjectFromApi(idx, rwItem, $scope.attributeSetTable, specAttributesOptionMappingObject);
                    console.log(parseProcess);
                    if (!$scope.pageSetup.debugMode) {
                        $scope.createProduct(parseProcess.transformedData);
                    }
                });
            });
        }
    };

    $scope.attrClass = function (attrName, existsInLocal) {
        if (attrName != 'No Matching' && !existsInLocal) {
            return 'attrNameClickable';
        }
    };

    $scope.containsComma = function (attr_name) {
        return typeof attr_name == 'string' && attr_name.indexOf(',') > -1;
    };

    $scope.createCustomer = function () {
        var createObj = {
            action: 'createCustomer',
            method: 'POST',
            restPostfix: '/customer',
            apiUrl: $scope.authObject.apiUrl,
            consumerKey: $scope.authObject.consumerKey,
            consumerSecret: $scope.authObject.consumerSecret,
            requestBody: {
                "firstname": "fdsjlkfds",
                "lastname": "djfkldsjdkfls",
                "email": "gamlinnia@icloud.com",
                "password": "fjdkfdsjf"
            }
        };
        restService.proceedRestData(createObj).success(function (response) {
            console.log(response);
        });
    };

    $scope.retrieveCustomer = function () {
        var createObj = {
            action: 'retrieveCustomer',
            method: 'GET',
            restPostfix: '/customer/1',
            apiUrl: $scope.authObject.apiUrl,
            consumerKey: $scope.authObject.consumerKey,
            consumerSecret: $scope.authObject.consumerSecret
        };
        restService.proceedRestData(createObj).success(function (response) {
            console.log(response);
        });
    };

    $scope.exportAction = function(){
        $scope.export_action = 'excel';
        switch($scope.export_action){
            case 'pdf':
                $scope.$broadcast('export-pdf', {});
                break;
            case 'excel':
                console.log('export to excel');
                $scope.$broadcast('export-excel', {});
                break;
            case 'doc':
                $scope.$broadcast('export-doc', {});
                break;
            default:
                console.log('no event caught');
        }
    };

});

