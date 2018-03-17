/*!
 * @package       RSform!Pro - Postcode API
 * @copyright (C) 2018 extensions.perfectwebteam.com
 * @license       GPL, http://www.gnu.org/copyleft/gpl.html
 *
 */

jQuery(document).ready(function ($) {
    $('#postcode,#huisnummer').blur(function (event) {
        var postcode = $('#postcode').val();
        var huisnr = $('#huisnummer').val();

        if (postcode !== '' && huisnr !== '') {
            var url = "/media/plg_system_rsfppostcodeapi/rsfppostcodeapi.php?postcode=" + postcode + "&number=" + huisnr;
            $.getJSON(url, function (json) {
                if (json.length == 1) {
                    $("#straat").val(json[0].street).removeAttr('disabled');
                    $("#plaats").val(json[0].city).removeAttr('disabled');
                    $("#provincie").val(json[0].province).removeAttr('disabled');
                    $("#lat").val(json[0].lat).removeAttr('disabled');
                    $("#lon").val(json[0].lon).removeAttr('disabled');
                } else {
                    $("#straat").removeAttr('disabled');
                    $("#plaats").removeAttr('disabled');
                    $("#provincie").removeAttr('disabled');
                    $("#lat").removeAttr('disabled');
                    $("#lon").removeAttr('disabled');
                }
            });
        }
    });
});