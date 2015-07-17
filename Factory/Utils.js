
app.factory('Utils', [function() {
    return {
        currentTimeStamp: function () {
            return new Date().valueOf();
        },
        unixTimeStamp: function () {
            return Math.floor(new Date().valueOf() / 1000);
        },
        isUndefinedOrNull: function(obj) {
            return angular.isUndefined(obj) || obj===null;
        },
        isDefinedAndNotNull: function(obj) {
            return angular.isDefined(obj) && obj !== null && obj !== '';
        },
        formatFloat: function (num, pos) {
            var size = Math.pow(10, pos);
            return Math.round(num * size) / size;
        },
        formatDate: function(timestamp) {
            if (!timestamp) {
                return '';
            }
            var datetime = new Date(parseInt(timestamp) * 1000);
            var month = (datetime.getMonth() + 1);
            if (month < 10) {
                month = '0' + month;
            }
            var date = datetime.getDate();
            if (date < 10) {
                date = '0' + date;
            }
            return datetime.getFullYear() + '-' + month + '-' + date;
        },
        formatDateTime: function(timestamp) {
            if (!timestamp) {
                return '';
            }
            var datetime = new Date(parseInt(timestamp) * 1000);
            var month = (datetime.getMonth() + 1);
            if (month < 10) {
                month = '0' + month;
            }
            var date = datetime.getDate();
            if (date < 10) {
                date = '0' + date;
            }
            var hour = datetime.getHours();
            if (hour < 10) {
                hour = '0' + hour;
            }
            var minute = datetime.getMinutes();
            if (minute < 10) {
                minute = '0' + minute;
            }
            return datetime.getFullYear() + '-' + month + '-' + date + ' ' + hour + ':' + minute;
        },
        isEmpty: function (obj) {

            /*null and undefined are "empty"*/
            if (obj == null) return true;

            /*Assume if it has a length property with a non-zero value*/
            /*that that property is correct.*/
            if (obj.length > 0)    return false;
            if (obj.length === 0)  return true;

            /*Otherwise, does it have any properties of its own?*/
            /*Note that this doesn't handle*/
            /*toString and valueOf enumeration bugs in IE < 9*/
            for (var key in obj) {
                if (Object.prototype.hasOwnProperty.call(obj, key)) return false;
            }
            return true;
        },
        deleteAttr: function (deleteAttrArray, obj) {
            for (var i = 0; i < deleteAttrArray.length; i++) {
                delete obj[deleteAttrArray[i]];
            }
        }
    };
}]);