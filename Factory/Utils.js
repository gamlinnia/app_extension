
app.factory('Utils', ['restService', function(restService) {
    var Utils = {
        downToLastElement: function (obj) {
            var type = typeof obj;
            switch (type) {
                case 'object' :
                    var lastElementValue = '';
                    for (var key in obj) {
                        if (typeof obj[key] == 'object') {
                            lastElementValue = Utils.downToLastElement(obj[key]);
                        } else {
                            lastElementValue = obj[key];
                        }
                    }
                    return lastElementValue;
                    break;
                default :
                    return obj;
            }
        },
        assignObjToAntoher: function (obj, toBeAssignedObj) {
            for (var key in toBeAssignedObj) {
                if ( typeof toBeAssignedObj[key] == 'object') {
                    if (!obj.hasOwnProperty(obj[key])) {
                        obj[key] = {};
                    }
                    this.assignObjToAntoher(obj[key], toBeAssignedObj[key]);
                } else {
                    obj[key] = toBeAssignedObj[key];
                }
            }
            return obj;
        },
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
        },
        mapAttributeSetNameToId: function (name, attributeSetTable) {
            for (var key in attributeSetTable) {
                if (name == attributeSetTable[key].name) {
                    return attributeSetTable[key].id;
                }
            }
        },
        mapAttributeSetIdToName: function (attributeSetId, attributeSetTable) {
            for (var key in attributeSetTable) {
                if (attributeSetId == attributeSetTable[key].id) {
                    return attributeSetTable[key].name;
                }
            }
        },
        formatProductCreateObjectFromExcel: function (excelRowData, attributeSetTable) {
            return {
                name : excelRowData['Product Name'],
                type_id : "simple",
                length: excelRowData['Length(inch)'],
                width: excelRowData['Width(inch)'],
                height: excelRowData['Height(inch)'],
                ne_length_cm: excelRowData['Length(cm)'],
                ne_width_cm: excelRowData['Width(cm)'],
                ne_height_cm: excelRowData['Height(cm)'],
                brand: excelRowData['Brand'],
                sku : excelRowData['SKU'],

//                short_description : excelRowData['Description - Short'],
//                description : excelRowData['Description - Long'],

                name_long: 'name_long',
                model_number: 'model_number',
                attribute_set_id : this.mapAttributeSetNameToId( excelRowData['Attribute Set Name'], attributeSetTable ),
                price : 25.5,
                weight : 125,   // Weight-Gross
                weight_net: excelRowData['Net (Package) Weight (lbs)'],


                tax_class_id : "0",
                visibility : "4",   // Catalog, Search
                status : 1,
                enable_rma: "1",
                meta_description : "Test meta",
                meta_keyword : "Test keyword",
                meta_title : "Test title"
            };
        },
        formatProductCreateObjectFromApi: function (idx, apiData, attributeSetTable, specAttributesValueMappingObject) {
            parsedObject = {
                listIndex: idx,
                originalData: apiData,
                transformedData: {
                    type_id : "simple",
                    attribute_set_id : this.mapAttributeSetNameToId( apiData.attribute_set_name, attributeSetTable ),
                    ne_highlight: apiData.intelligence.hasOwnProperty('Intelligence') ? apiData.intelligence.Intelligence : null,
                    ne_description: apiData.intelligence.hasOwnProperty('Introduction') && apiData.intelligence.Introduction ? apiData.intelligence.Introduction : apiData.baseinfo.ViewDescription,
                    sku: apiData.baseinfo.ItemNumber,
                    status : 1,
                    visibility : "4",   // Catalog, Search
                    tax_class_id : "0",
                    enable_rma: "0",
                    //                    brand: this.lookforProperty(apiData.property, 'PropertyName', /brand/i, ['ValueName', 'UserInputted']),

                    length: apiData.dimension.Length,
                    width: apiData.dimension.Width,
                    height: apiData.dimension.Height,
                    weight : apiData.dimension.PackageWeight,   // Weight-Gross
                    ne_package_weight_kg: this.fixNumberToFixedPosition(parseFloat(apiData.dimension.PackageWeight) * 0.45359237, 7),
                    ne_length_cm: apiData.dimension.Length * 2.54,
                    ne_width_cm: apiData.dimension.Width * 2.54,
                    ne_height_cm: apiData.dimension.Height * 2.54,

                    //                    product_type: apiData.baseinfo.ProductType ? apiData.baseinfo.ProductType : null,
                    item_type: apiData.baseinfo.ItemType || null,
                    short_description : apiData.baseinfo.ShortDescription,

                    name: apiData.description.hasOwnProperty('WebDescription') ? apiData.description.WebDescription : apiData.description.Title,
                    name_long: apiData.description.hasOwnProperty('WebDescription') ? apiData.description.WebDescription : apiData.description.Title,
                    model_number: this.lookforProperty(apiData.property, 'PropertyName', /model/i, ['UserInputted', 'ValueName']),
                    price: apiData.price.UnitPrice,
                    msrp: apiData.price.UnitPrice,
                    mfrproductpagelink: apiData.ProductInfos.ManufacturerProductURL,
                    ne_manufacturer_part: apiData.ProductInfos.ManufacturerPartNumber,
                    upc_number: apiData.ProductInfos.UPCCode
                }
            };

            if (apiData.intelligence.hasOwnProperty('Introduction') && apiData.intelligence.Introduction) {
                parsedObject.description =  apiData.intelligence.Introduction;

            } else {
                parsedObject.description = apiData.baseinfo.ViewDescription ? apiData.baseinfo.ViewDescription : apiData.description.WebDescription;
            }

            for (var key in specAttributesValueMappingObject) {
                parsedObject.transformedData[key] = specAttributesValueMappingObject[key];
            }
            return parsedObject;
        },
        neSubcategoryToMagentoSubcategory: function (neSubcategory) {
            var mappingTable = {
                "Accessories - Case / Rackmount": "b01_Computer_Case",
                "Accessories - Hard Drive": "b45_hard-drive-adapters",
                "Accessories - Monitors": "c32_tv-brackets, c19_home-electronics-accessories",
                "Accessories - Mouse": "a05_gaming-keyboard, b37_cell-phone-chargers-&-cables, b70_mouse, a06_gaming-mouse",
                "Accessories - Projectors": "c32_tv-brackets",
                "Accessories - USB": "c20_humidifiers, c17_led-lightbulbs",
                "Accessories - Wireless": "b02_wireless, b38_cell-phone-mounts-&-holders",
                "Add-On Cards": "b51_network-interface-cards",
                "App Enabled Products": "b04_app-enabled-products",
                "Audio Adapters": "b65_video-adapters",
                "Audio/Video Splitters": "b65_video-adapters",
                "BreadMakers": "c06_breadmakers",
                "Cables - 3.5mm / 2.5mm Stereo Cables": "b07_cables-3.5mm/2.5mm-stereo-cables",
                "Cables - Coaxial RF (F-Type) Cables": "b52_network-network-antennas",
                "Cables - Computer Power Cords": "b10_cables-computer-power-cords",
                "Cables - DisplayPort Cables": "b12_cables-displayport-cables",
                "Cables - DVI Cables": "b13_cables-dvi-cables",
                "Cables - Firewire (IEEE 1394) Cables": "b15_cables-firewire-IEEE-1394-cables",
                "Cables - HDMI Cables": "b16_cables-hdmi-cables",
                "Cables - Internal Power Cables": "b17_cables-internal-power-cables",
                "Cables - Lightning Cables": "b18_cables-lightning-cables",
                "Cables - Mini DisplayPort Cables": "b12_cables-displayport-cables",
                "Cables - Mouse / Keyboard (PS2) Cables": "b30_cables-usb-cables",
                "Cables - Network Ethernet Cables": "b20_network-ethernet-cables",
                "Cables - Phone Cables": "b30_cables-usb-cables",
                "Cables - Printer (Parallel) Cables": "b41_data-adapters",
                "Cables - SATA / eSATA Cables": "b27_cables-sata/esata-cables",
                "Cables - Serial Cables": "b41_data-adapters",
                "Cables - USB Cables": "b30_cables-usb-cables",
                "Cables - VGA / SVGA Cables": "b31_cables-vga/svga-cables",
                "Card Reader/Adapter": "b32_card-reader/adapter",
                "Case Fans": "b33_case-fans",
                "Cases (Computer Cases - ATX Form)": "a01_Gaming_Case, b01_Computer_Case",
                "CCTV/Analog Cameras": "b48_ip/network-cameras, c07_cctv/analog-cameras",
                "CD/DVD R/RW Media": "b34_cd/dvd-r/rw-media",
                "CD/DVD ROM/RW Drives - External": "b35_cd/dvd-rom/rw-drives-external",
                "Cell Phone - Batteries": "b68_Power_Supplies, b37_cell-phone-chargers-&-cables",
                "Cell Phone - Mounts & Holders": "b38_cell-phone-mounts-&-holders",
                "Cell Phones - Bluetooth Headsets, Speakers, & Accessories": "b66_Headsets_and_Accessories, b06_bluetooth-cell-phone-accessories",
                "CPU Cooling": "b40_cpu-cooling",
                "Cutlery": "c08_cutlery",
                "Deep Fryers": "c09_deep-fryers",
                "Digital Camera Batteries & Chargers": "b42_digital-camera-batteries-&-chargers",
                "Digital Camera Cases": "b73_camera_bags",
                "Digital Media Remote": "c10_digital-media-remote",
                "Extenders & Repeaters": "b41_data-adapters",
                "External Enclosure": "b45_hard-drive-adapters, b44_external-enclosure",
                "Fans": "b33_case-fans",
                "Filament": "c02_filament",
                "Flashlights": "c12_flashlights",
                "Gaming Keyboard": "a05_gaming-keyboard",
                "Gaming Mice": "a06_gaming-mouse",
                "Hard Drive Adapters": "b45_hard-drive-adapters, b17_cables-internal-power-cables",
                "Hard Drive Controllers / RAID Cards": "b51_network-interface-cards",
                "Headphones and Accessories": "b66_Headsets_and_Accessories, a02_Gaming_Headsets_and_Accessories",
                "Headsets and Accessories": "b66_Headsets_and_Accessories, a02_Gaming_Headsets_and_Accessories",
                "Heater": "c13_heater",
                "HI - Gauges": "c14_gauges",
                "HI - Landscape Lighting": "c16_landscape-lighting",
                "HI - LED Light Bulbs": "c17_led-lightbulbs",
                "HI - Spot Lights": "c18_spotlights",
                "Home Electronics Accessories": "b05_audio-video-switch, b60_surge-suppressors",
                "Hubs - Network / USB / Firewire": "b47_hubs-network-usb-firewire",
                "Humidifiers": "c20_humidifiers",
                "Ice Cream Makers": "c21_ice-cream-makers",
                "IceMakers": "c33_ice-maker",
                "Induction Cookers": "c22_induction-cookers",
                "IP/Network Cameras": "b48_ip/network-cameras, c29_surveillance-video-monitoring-kits-all-in-one-systems",
                "Juicers & Extractors": "c23_juicers-&-extractors",
                "Keyboards": "b69_keyboard, a05_gaming-keyboard",
                "Kitchen Gadgets": "c14_gauges",
                "KVM (Keyboard - Video - Mouse) Switches": "b49_kvm-keyboard-video-mouse-switches",
                "Modems": "b50_modems",
                "Mouse": "b70_mouse, a06_gaming-mouse, a05_gaming-keyboard",
                "Network - Interface Cards": "b51_network-interface-cards, b54_network-wireless-adapters",
                "Network - Network Antennas": "b52_network-network-antennas, c28_satellite-tv-receivers-&-accessories",
                "Network - Switches": "b53_network-switches",
                "Network - Wireless Adapters": "b54_network-wireless-adapters, b55_network-wireless-routers",
                "Network - Wireless Routers": "b54_network-wireless-adapters, b55_network-wireless-routers",
                "Notebook Batteries / AC Adapters": "b08_ac-power-cords-for-laptop",
                "Notebook Cases": "b02_wireless",
                "OS - Office Furniture": "c03_os-office-furniture",
                "OS - Shredders & Shredder Supplies": "c25_shredders-&-shredder-supplies",
                "Popcorn Poppers": "c26_popcorn-poppers",
                "Power Strips - Inverters and Converters": "c19_home-electronics-accessories, b60_surge-suppressors",
                "Power Supplies": "a04_Gaming_Power_Supplies, b68_Power_Supplies",
                "Printer - Ink Cartridges - Aftermarket": "c04_printer-ink-cartridges-aftermarket",
                "Printer - Printer Ribbons": "c04_printer-ink-cartridges-aftermarket",
                "Printer / Fax - Toners - Aftermarket": "c04_printer-ink-cartridges-aftermarket",
                "Rice Cookers": "c27_rice-cookers",
                "Satellite TV - Receivers & Accessories": "b52_network-network-antennas",
                "Server - Accessories": "b27_cables-sata/esata-cables, b57_server-accessories, b45_hard-drive-adapters",
                "Server - Chassis": "b58_server-chassis",
                "Server - RAID Sub-Systems": "b46_hard-drive-controllers/raid-cards",
                "Sound Card": "b51_network-interface-cards",
                "Speakers": "b67_Speakers, a02_Gaming_Headsets_and_Accessories, b06_bluetooth-cell-phone-accessories",
                "Surge Suppressors": "b60_surge-suppressors",
                "Surveillance - Video Monitoring Kits / All in One Systems": "c29_surveillance-video-monitoring-kits-all-in-one-systems",
                "Thermo Pots": "c30_thermo-pots",
                "Toaster Ovens": "c31_toaster-ovens",
                "Tools - Network / PC Service / Acc.": "b61_tools-network/pc-service/acc",
                "TV Brackets": "c32_tv-brackets",
                "USB Converters": "b45_hard-drive-adapters",
                "Video Adapters": "b13_cables-dvi-cables, b12_cables-displayport-cables, b65_video-adapters",
                "OS - Staplers & Hole Punchers" : "c39_staplers_hole_punchers",
                "Teakettles" : "c36_electric_kettles",
                "Water Dispensers" : "c30_thermo-pots",
                "Food Processors" : "c21_ice-cream-makers",
                "Steamers" : "c37_food_steamers",
                "Pressure Cookers" : "c35_pressure_cookers",
                "Massagers" : "c38_massagers",
                "Accessories - General" : "b72_cable_ties",
                "HI - Portable Generators" : "c40_portable_generators"
            };
            if (this.isDefinedAndNotNull(mappingTable[neSubcategory])) {
                return {
                    status: 'success',
                    attr_names: mappingTable[neSubcategory]
                };
            } else {
                return {
                    status: 'fail',
                    original_attr_name: neSubcategory
                };
            }
        },
        lookforProperty: function (propertyArray, lookforname, regex, valueKeyArray) {
            for (var key in propertyArray) {
                if (propertyArray[key].hasOwnProperty(lookforname) && propertyArray[key][lookforname].match(regex) != null) {
                    for (var i = 0; i < valueKeyArray.length; i++) {
                        var valueKey = valueKeyArray[i];
                        if (propertyArray[key][valueKey]) {
                            return propertyArray[key][valueKey];
                        }
                    }
                }
            }
            return null;
        },
        fixNumberToFixedPosition: function (numberToFix, fixedPosition) {
            var num = new Number(numberToFix);
            return parseFloat(num.toFixed(fixedPosition));
        },
        generateAttributeCodeArray: function (attribute_codeArray, frontend_label) {
            frontend_label = frontend_label.replace(/[\(].+[\)]/, '').trim();     // 有括號則不考慮
            var frontend_labelArray = frontend_label.split(' ');
            // array 相加
            var mapWordArray = attribute_codeArray.concat(frontend_labelArray);
            for (var x = mapWordArray.length -1; x >= 0; x--) {
                mapWordArray[x] = mapWordArray[x].replace(/[".]/, '');      // 有 " . 等符號直接去掉
                if ( !mapWordArray[x] ) {                                  // null的值delete掉
                    mapWordArray.splice(x, 1);
                } else {
                    // deduplicate
                    for (var y = mapWordArray.length -1; y >= 0; y--) {
                        if (y != x) {
                            if (mapWordArray[x].indexOf('+') > -1) {
                                var regexp = new RegExp('\\' + mapWordArray[x], 'i');
                            } else {
                                var regexp = new RegExp(mapWordArray[x], 'i');
                            }
                            if ( mapWordArray[y].match(regexp) ) {
                                mapWordArray.splice(x, 1);
                                break;
                            }
                        }
                    }
                }
            }
            return mapWordArray;
        },
        lookforPropertyByAttribute: function (propertyArray, attributeObject) {
            var attribute_codeArray = this.generateAttributeCodeArray(attributeObject.attribute_code.split('_'), attributeObject.frontend_label);
            // 例外字串置換
            var splitableString = {
                lightbulb: 'light bulb'
            };
            var clonePropertyArray = JSON.parse(JSON.stringify(propertyArray));
            console.log(attribute_codeArray);
            var regexp;
            for (var i = attribute_codeArray.length -1; i > 0 ; i--) {
                if (splitableString.hasOwnProperty(attribute_codeArray[i])) {
                    if (splitableString[attribute_codeArray[i]].indexOf('+') > -1) {
                        console.log('has plus inside');
                        regexp = new RegExp('\\' + splitableString[attribute_codeArray[i]], 'i');
                    } else {
                        regexp = new RegExp(splitableString[attribute_codeArray[i]], 'i');
                    }
                } else {
                    if (attribute_codeArray[i].indexOf('+') > -1) {
                        console.log('has plus inside');
                        regexp = new RegExp('\\' + attribute_codeArray[i], 'i')
                    } else {
                        regexp = new RegExp(attribute_codeArray[i], 'i')
                    }
                }
                for (var key = clonePropertyArray.length -1; key >= 0; key--) {
                    if (clonePropertyArray.length < 1) {
                        return null;
                    }
                    console.log(regexp, clonePropertyArray[key].PropertyName);
                    var match = clonePropertyArray[key].PropertyName.match(regexp);
                    if (!match) {
                        clonePropertyArray.splice(key, 1);
                    } else {
                        console.log(clonePropertyArray[key].PropertyName + ' left behind');
                    }
                }
            }

            if (clonePropertyArray.length == 1) {
                for (var key in clonePropertyArray) {
                    if (clonePropertyArray[key].hasOwnProperty('UserInputted') && clonePropertyArray[key].UserInputted) {
                        console.log(clonePropertyArray[key].UserInputted);
                        return clonePropertyArray[key].UserInputted;
                    } else if (clonePropertyArray[key].hasOwnProperty('ValueName') && clonePropertyArray[key].ValueName) {
                        console.log(clonePropertyArray[key].ValueName);
                        return clonePropertyArray[key].ValueName;
                    }
                }
            } else if (clonePropertyArray.length > 1) {
                console.log(clonePropertyArray);
                return clonePropertyArray;
            } else {
                return null;
            }
        },
        mapMagentoSpecAndProperty: function (propertyArray, is_visible_on_frontAttributesArray) {
            var specAttributesValueMapping = {};
            var response;
            for (var key in is_visible_on_frontAttributesArray) {
                var attribute_code = is_visible_on_frontAttributesArray[key].attribute_code;
                var frontend_label = is_visible_on_frontAttributesArray[key].frontend_label;
                var match = attribute_code.match(/[a-z][0-9]{5}/);
                if (match) {
                    response = this.lookforPropertyByAttribute(propertyArray, is_visible_on_frontAttributesArray[key]);
                    if (response) {
                        specAttributesValueMapping[ is_visible_on_frontAttributesArray[key].attribute_code ] = response;
                    }
                }
            }
            return specAttributesValueMapping;
        },
        mapMagentoOptions: function (specAttributesValueMappingObject, authObject, callback) {
            console.log('specAttributesValueMappingObject', specAttributesValueMappingObject);
            var attributeCodeArrayString = Object.keys(specAttributesValueMappingObject).join();

            restService.getAttributeOptions(attributeCodeArrayString, authObject).success(function (response) {
                console.log(response);
                var magentoAttributeOptionsArray = response;
                var specAttributesOptionMappingObject = {};
                var attributeValue = '';
                var attributeKey = '';
                var attributeType = '';
                var regexMatchValue = null;
                var regex;
                for (var i = 0; i < magentoAttributeOptionsArray.length; i++) {
                    attributeKey = magentoAttributeOptionsArray[i].attributeCode;
                    attributeValue = specAttributesValueMappingObject[attributeKey];
                    console.log(attributeKey, attributeValue);
                    attributeType = magentoAttributeOptionsArray[i].frontend_input;
                    switch (attributeType) {
                        case 'select' :
                            regexMatchValue = null;
                            console.log(attributeKey + ' need to get option of value: ' + attributeValue);
                            for (var x = 0; x < magentoAttributeOptionsArray[i].options.length; x++) {
                                if (attributeValue == magentoAttributeOptionsArray[i].options[x].label) {
                                    specAttributesOptionMappingObject[attributeKey] = magentoAttributeOptionsArray[i].options[x].value;
                                    console.log(attributeValue + ' equal ' + magentoAttributeOptionsArray[i].options[x].label + ', ' + magentoAttributeOptionsArray[i].options[x].value);
                                    regexMatchValue = null;
                                    break;
                                } else {
                                    console.log(magentoAttributeOptionsArray[i].options[x].label);
                                    magentoAttributeOptionsArray[i].options[x].label = magentoAttributeOptionsArray[i].options[x].label.replace(/\+/g, '\\+');
                                    regex = new RegExp(magentoAttributeOptionsArray[i].options[x].label, 'i');
                                    if (typeof attributeValue != 'object') {
                                        if (attributeValue.match(regex)) {
                                            regexMatchValue = magentoAttributeOptionsArray[i].options[x].value;
                                            console.log(attributeValue + ' match ' + regex + ', ' + magentoAttributeOptionsArray[i].options[x].value);
                                        }
                                    } else {
                                        console.log('------------------ attributeValue is not string ------------------' + typeof attributeValue);
                                    }
                                }
                            }
                            if (regexMatchValue) {
                                specAttributesOptionMappingObject[attributeKey] = regexMatchValue;
                            }
                            break;
                        case 'text' :
                        case 'textarea' :
                            specAttributesOptionMappingObject[attributeKey] = regexMatchValue;
                            break;
                        default :
                            console.log('type ' + attributeType, 'value ' + attributeValue, 'not inputted');
                    }
                }
                callback(specAttributesOptionMappingObject);
            });
        }
    };  // return close bracket
    return Utils;
}]);