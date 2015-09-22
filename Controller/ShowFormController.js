/**
 * Created by th98 on 2015/9/18.
 */

app.controller('showFormController', function($scope, restService, $window, Utils, $filter) {

    $scope.formList = [];

    $scope.showFormDetail = false;
    $scope.formLoading = false;
    $scope.formDetail = [];

    $scope.getFormList = function() {
        $scope.formLoading = true;
        restService.getFormList()
            .success(function (response) {
                //console.log(response);
                $scope.formList = response.DataCollection;
                $scope.showFormDetail = false;
                $scope.formLoading = false;
                //console.log(typeof ($scope.formList[0]));
                //$scope.form_name = $scope.formList[0];
            })
            .error(function () {
                console.log("Error");
            })
    }

    $scope.getFormList();

    $scope.changeForm = function (formName){
        if (formName == null)
        {
            $scope.showFormDetail = false;
        }
        else{
            $scope.showFormDetail = false;
            $scope.formLoading = true;
            if (formName=='all')
            {
                formName = '*';
            }
            restService.getFormDetail(formName)
                .success( function(response){
                    console.log(response);
                    $scope.formDetail = response.DataCollection;
                    $scope.formLoading = false;
                    $scope.showFormDetail = true;
                })
                .error( function(){
                    $scope.showFormDetail = false;
                    console.log("Error 2");
                })
        }
    }


});
