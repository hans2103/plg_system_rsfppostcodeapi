/*!
 * @package       RSform!Pro - Postcode API
 * @copyright (C) 2018 extensions.perfectwebteam.com
 * @license       GPL, http://www.gnu.org/copyleft/gpl.html
 *
 */

document.addEventListener("DOMContentLoaded", function () {
    var inputPostcode = document.getElementById("postcode");
    var inputHuisnummer = document.getElementById("huisnummer");

    inputPostcode.addEventListener("blur", getData, false);
    inputHuisnummer.addEventListener("blur", getData, false);

    function getData() {
        if (inputPostcode.value !== '' && inputHuisnummer.value !== '') {
            var request = {
                    'option': 'com_ajax',
                    'plugin': 'rsfppostcodeapi',
                    'format': 'json',
                    'data': {'postcode': inputPostcode.value, 'number': inputHuisnummer.value}
                },
                inputStraat = document.getElementById("straat"),
                inputPlaats = document.getElementById("plaats"),
                inputProvincie = document.getElementById("provincie"),
                inputLat = document.getElementById("lat"),
                inputLon = document.getElementById("lon");

            getJSON(request, function (json) {
                if (json.success === true) {
                    if (inputStraat) {
                        inputStraat.setAttribute('value', (json.data[0].street));
                    }
                    if (inputPlaats) {
                        inputPlaats.setAttribute('value', (json.data[0].city));
                    }
                    if (inputProvincie) {
                        inputProvincie.setAttribute('value', (json.data[0].province));
                    }
                    if (inputLat) {
                        inputLat.setAttribute('value', (json.data[0].lat));
                    }
                    if (inputLon) {
                        inputLon.setAttribute('value', (json.data[0].lon));
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
            });
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
        });
    }
});