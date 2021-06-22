# plg_system_rsfppostcodeapi
Joomla! 3 plugin to perform the Dutch postcode api check

## Usage
There are 2 fields that are important:
 - Postcode
 - Huisnummer (house number)
 
To identify these fields they must be given a class. The `Postcode` field must have the class `js-postcode` and 
the `Huisnummer` field must have a class `js-huisnummer`. These classes are required for the script to work. 

The result of the zipcode check are several data items (with the class the field must have for autocomplete to work):
 - street `js-straat`
 - city `js-plaats`
 - province `js-provincie`
 - latitude `js-lat`
 - longitude `js-lon`

The fields ``postcode`` and `huisnummer` also need the data attribute `data-count="number"`. Count starts at 1. The count attribute is used to target the right set of address classes. When you add another set of address fields to the form, you use `data-count="2"`.

These class names need to be added under the `Attributes` tab in the textarea when editing a field in RSForm! in order for the auto-complete to work. Below an example.

Example: 
``class="js-postcode" data-count="1"``
