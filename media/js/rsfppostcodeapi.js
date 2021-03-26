/**
 * @package       RSform!Pro - Postcode API
 * @copyright (C) 2018 extensions.perfectwebteam.com
 * @license       GPL, http://www.gnu.org/copyleft/gpl.html
 *
 */

document.addEventListener("DOMContentLoaded", function () {

        // Get all elements with class "postcode"
        let postcodes = document.getElementsByClassName("js-postcode");

        for (let i = 0; i < postcodes.length; i++) {
            document.getElementById(postcodes[i].id).addEventListener("blur", getData, false);
        }

        // Get all elements with class "huisnummer"
        let huisnummers = document.getElementsByClassName("js-huisnummer");

        for (let i = 0; i < huisnummers.length; i++) {
            document.getElementById(huisnummers[i].id).addEventListener("blur", getData, false);
        }

        function getData(element) {
            let inputPostcode = document.getElementsByClassName('js-postcode')[0];
            let inputHuisnummer = document.getElementsByClassName('js-huisnummer')[0];

            if (inputPostcode.value !== '') {
                let data = {};

                if (inputHuisnummer && inputHuisnummer.value === '') {
                    return;
                }

                if (inputPostcode.value !== '') {
                    data.postcode = inputPostcode.value;
                }

                if (inputHuisnummer && inputHuisnummer.value !== '') {
                    data.number = inputHuisnummer.value;
                }

                let request = {
                        'option': 'com_ajax',
                        'plugin': 'rsfppostcodeapi',
                        'format': 'json',
                        'postcode': data.postcode,
                        'number': data.number
                    },
                    inputStraat = document.getElementsByClassName('js-straat')[0],
                    inputPlaats = document.getElementsByClassName('js-plaats')[0],
                    inputProvincie = document.getElementsByClassName('js-provincie')[0],
                    inputLat = document.getElementsByClassName('js-lat')[0],
                    inputLon = document.getElementsByClassName('js-lon')[0];

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
