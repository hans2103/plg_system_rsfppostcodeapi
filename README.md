# plg_system_rsfppostcodeapi
Joomla! 3 plugin to perform the Dutch postcode api check

## Usage
There are 2 fields that are important:
 - Postcode
 - Huisnummer (house number)
 
To identify these fields they must be given a class. The `Postcode` field must have the class `js-postcode` and 
the `Huisnummer` field must have a class `js-huisnummer`. These classes are required for the script to work. 

The result of the zipcode check are several data items (with the clas the field must have for autocomplete to work):
 - street `js-straat`
 - city `js-plaats`
 - province `js-provincie`
 - latitude `js-lat`
 - longitude `js-lon`

These class name need to be added under the `Attributes` tab when editing a field in RSForm!
