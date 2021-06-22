/**
 * @package       RSform!Pro - Postcode API
 * @copyright (C) 2018 extensions.perfectwebteam.com
 * @license       GPL, http://www.gnu.org/copyleft/gpl.html
 *
 */

document.addEventListener("DOMContentLoaded", function () {

        // Get all elements with class "postcode"
        var postcodes = document.getElementsByClassName("js-postcode");

        for (var i = 0; i < postcodes.length; i++) {
            document.getElementById(postcodes[i].id).addEventListener("blur", function (){ getData(this) }, false);
        }

        // Get all elements with class "huisnummer"
        var huisnummers = document.getElementsByClassName("js-huisnummer");

        for (var j = 0; j < huisnummers.length; j++) {
            document.getElementById(huisnummers[j].id).addEventListener("blur", function (){ getData(this) }, false);
        }

        function getData(element) {
            var count = element.dataset.count - 1;
            var inputPostcode = document.getElementsByClassName('js-postcode')[count];
            var inputHuisnummer = document.getElementsByClassName('js-huisnummer')[count];

            if (inputPostcode.value !== '') {
                var data = {};

                if (inputHuisnummer && inputHuisnummer.value === '') {
                    return;
                }

                if (inputPostcode.value !== '') {
                    data.postcode = inputPostcode.value;
                }

                if (inputHuisnummer && inputHuisnummer.value !== '') {
                    data.number = inputHuisnummer.value;
                }

                var request = {
                        'option': 'com_ajax',
                        'plugin': 'rsfppostcodeapi',
                        'format': 'json',
                        'postcode': data.postcode,
                        'number': data.number
                    },
                    inputStraat = document.getElementsByClassName('js-straat')[count],
                    inputPlaats = document.getElementsByClassName('js-plaats')[count],
                    inputProvincie = document.getElementsByClassName('js-provincie')[count],
                    inputLat = document.getElementsByClassName('js-lat')[count],
                    inputLon = document.getElementsByClassName('js-lon')[count];

                getJSON(request, function (json) {
                        if (json.success === true) {
                            if (inputStraat && json.data[0].street !== null && json.data[0].street !== undefined) {
                                inputStraat.value = json.data[0].street;
                            }

                            if (inputPlaats && json.data[0].city !== null && json.data[0].city !== undefined) {
                                inputPlaats.value = json.data[0].city;
                            }

                            if (inputProvincie && json.data[0].province !== null && json.data[0].province !== undefined) {
                                inputProvincie.value = json.data[0].province;
                            }

                            if (inputLat && json.data[0].lat !== null && json.data[0].lat !== undefined) {
                                inputLat.value = json.data[0].lat;
                            }

                            if (inputLon && json.data[0].lon !== null && json.data[0].lon !== undefined) {
                                inputLon.value = json.data[0].lon;
                            }
                        }

                        if (inputStraat) {
                            inputStraat.disabled = false;
                        }

                        if (inputPlaats) {
                            inputPlaats.disabled = false;
                        }

                        if (inputProvincie) {
                            inputProvincie.disabled = false;
                        }

                        if (inputLat) {
                            inputLat.disabled = false;
                        }

                        if (inputLon) {
                            inputLon.disabled = false;
                        }
                    }
                );
            }
        }

        function getJSON(url, callback) {
            jQuery.ajax({
                    type: 'GET',
                    data: url,
                    dataType: 'json',
                    success: function (response) {
                        callback(response)
                    }
                }
            );
        }
    }
);
