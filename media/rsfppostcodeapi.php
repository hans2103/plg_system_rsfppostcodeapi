<?php
/**
 * @package       RSform!Pro
 * @copyright (C) 2018 extensions.perfectwebteam.com
 * @license       GPL, http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
define('_JEXEC', 1);

use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;

// defining the base path.
define('JPATH_BASE', realpath(dirname(__FILE__) . '/../..'));
define('DS', DIRECTORY_SEPARATOR);

// including the main joomla files
require_once(JPATH_BASE . DS . 'includes' . DS . 'defines.php');
require_once(JPATH_BASE . DS . 'includes' . DS . 'framework.php');

// Creating an app instance
$app   = Factory::getApplication('site');
$input = Factory::getApplication()->input;

$postcode = strtoupper($input->get('postcode', '', 'STRING'));
$number   = $input->get('number', '', 'STRING');

// get API key from given FormID
$formId = strtoupper($input->get('id', '', 'STRING'));;
$db    = Factory::getDbo();
$query = $db->getQuery();
$query
	->select('SettingValue')
	->from($db->qn('#__rsform_config'))
	->where($db->qn('SettingName') . '=' . $db->q('postcodeapi.code'));
$db->setQuery($query);
$db->execute();
$num_rows = $db->getNumRows();

if (!$num_rows > 1)
{
	return false;
}

$result = $db->loadObject();
$apikey = $result->SettingValue;


if (strlen($postcode) == 7)
{
	$postcode = substr($postcode, 0, 4) . substr($postcode, 5, 2);
}

if ($postcode !== '' && $number !== '')
{
	$headers   = array();
	$headers[] = 'X-Api-Key: ' . $apikey;

	// De URL naar de API call
	$url = 'https://postcode-api.apiwise.nl/v2/addresses/?postcode=' . $postcode . '&number=' . $number;

	$curl = curl_init($url);

	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($curl);
	$data     = json_decode($response);

	curl_close($curl);

	if (isset($data->_embedded->addresses))
	{
		$addressdata   = $data->_embedded->addresses[0];

		$city          = $addressdata->city->label;
		$street        = $addressdata->street;
		$province      = $addressdata->province->label;
		$lat           = $addressdata->geo->center->wgs84->coordinates[1];
		$lon           = $addressdata->geo->center->wgs84->coordinates[0];

		$return_data[] = array(
			"city" => $city,
			"street" => $street,
			"province" => $province,
			"lat" => $lat,
			"lon" => $lon
		);

		header('Content-type:application/json;charset=utf-8');
	}
	else
	{
		$return_data = array(
			'error' => 'De combinatie van postcode en huisnummer kan niet worden gevonden'
		);
	}

	echo new JsonResponse($return_data);
}