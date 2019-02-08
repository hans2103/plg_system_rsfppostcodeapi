# plg_system_rsfppostcodeapi
Joomla! 3 plugin to perform the Dutch postcode api check

Met dank aan [@renekreijveld](https://github.com/renekreijveld/) voor het maken van het [postcode api script](https://github.com/renekreijveld/postcodeapi)

## Usage
There are 2 fields that are important:
 - Postcode
 - Huisnummer (house number)
 
To identify these fields they must be given a class. The `Postcode` field must have the class `postcode`and 
the `Huisnummer` field must have a class `huisnummer`. These classes are required for the script to work. In addition
the data attributes are required as well.

The result of the zipcode check are several data items:
 - street
 - city
 - province
 - latitude
 - longitude
 
To identify these fields we must add a data attribute to the field as follows:
 - data-postcode="<FIELDNAME>"
 - data-huisnummer="<FIELDNAME>"
 - data-straat="<FIELDNAME>"
 - data-plaats="<FIELDNAME>"
 - data-provincie="<FIELDNAME>"
 - data-lat="<FIELDNAME>"
 - data-lng="<FIELDNAME>"
 
The fieldname is the HTML ID of the field. 