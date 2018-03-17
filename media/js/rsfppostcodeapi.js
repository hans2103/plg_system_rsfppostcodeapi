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
            var apiURL = "/media/plg_system_rsfppostcodeapi/rsfppostcodeapi.php?postcode=" + inputPostcode.value + "&number=" + inputHuisnummer.value,
                inputStraat = document.getElementById("straat"),
                inputPlaats = document.getElementById("plaats"),
                inputProvincie = document.getElementById("provincie"),
                inputLat = document.getElementById("lat"),
                inputLon = document.getElementById("lon");

            getJSON(apiURL, function (json) {
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

    // Remember XHMLHTTP requests are asynchronous!!
    function getJSON(url, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", url, true);
        xhr.onload = function (e) {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    var res = xhr.responseText;
                    // Executes your callback with the
                    // response already parsed into JSON
                    callback(JSON.parse(res));
                } else { // Server responded with some error
                    console.error(xhr.statusText);
                } // End of verifying response status
            } // Please read: http://www.w3schools.com/ajax/...
              // .../ajax_xmlhttprequest_onreadystatechange.asp
        }; // End of what to do when the response is answered

        // What to do if there's an error with the request
        xhr.onerror = function (e) {
            console.error(xhr.statusText);
        }; // End of error handling

        // Send the request to the server
        xhr.send(null);
    } // End of getJSON function
});