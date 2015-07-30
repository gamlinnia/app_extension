app.controller('indexController', function($scope, restService, $window, Utils, $filter) {

    $scope.getQueryParam = function(param) {
        var found;
        $window.location.search.substr(1).split("&").forEach(function(item) {
            if (param ==  item.split("=")[0]) {
                found = item.split("=")[1];
            }
        });
        return found;
    };

    $scope.initValue = function () {
        /*page setup ↓*/
        $scope.bigCurrentPage = 1;
        $scope.rowsPerPage = 100;

        $scope.setPage = function (pageNo) {
            $scope.bigCurrentPage = pageNo;
        };

        $scope.pageChanged = function() {
            $scope.listAllAssets();
        };
        /*page setup ↑*/

        $scope.authenticated = false;
        var host = 'rwpim.silksoftware.net';
        var magentoDirectory = '';
        /*var host = $window.location.hostname;*/
        /*var magentoDirectory = '/magento';*/
        var callbackUrl = $window.location.origin + $window.location.pathname;
        $scope.authObject = {
            callbackUrl: callbackUrl,
            apiUrl: 'http://' + host + magentoDirectory + '/api/rest',
            temporaryCredentialsRequestUrl: 'http://' + host + magentoDirectory + '/index.php/oauth/initiate?oauth_callback=' + encodeURIComponent(callbackUrl),
            adminAuthorizationUrl: 'http://' + host + magentoDirectory + '/index.php/admin/oauth_authorize',
            accessTokenRequestUrl: 'http://' + host + magentoDirectory + '/index.php/oauth/token',
            consumerKey: 'eefc539175f5024958c657c1aa93c879',
            consumerSecret: 'a9df3a118519c28ca36007f70e039240'
            /*consumerKey: 'f5d00e14c41ee9632d528f59b243d2e1',*/
            /*consumerSecret: 'bdbd0843217e0189a4f812961ed6b52e'*/
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
        $scope.startTimeStamp = new Date().valueOf();
        $scope.params = {
            getRwProductList: {
                ItemCreationDateFrom: '2015-01-01'
            }
        }
    };
    $scope.initValue();

    $scope.destroySession = function () {
        restService.destroySession().success(function (data) {
            $window.location.reload();
        });
    };

    $scope.checkSessionState = function () {
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
                        break;
                }
            }
        });
    };
    $scope.checkSessionState();

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

    $scope.resetProductList = function () {
        $scope.productList = [];
    };

    $scope.getRwProductList = function () {
        $scope.authObject.action = 'getRwProductList';
        restService.getRwProductList($scope.params.getRwProductList).success(function (response) {
            $scope.rwFilteredItemList = response.DataCollection;
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
        var dateArray = javascriptDateObj.match(/[0-9]+[-]{1}[0-9]+/);
        var javascriptDate = parseInt(dateArray[0]);
        return new Date(javascriptDate).toLocaleDateString();
    };

    $scope.checkServerItemStatus = function () {
        restService.checkServerItemStatus($scope.rwFilteredItemList).success(function (response) {
            $scope.rwFilteredItemList = response.DataCollection;
            $scope.alert.msg = "count: " + response.count + ", notExistsCount: " + response.notExistsCount;
        })
    };

});

