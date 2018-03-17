<?php
/**
 * @package       RSform!Pro
 * @copyright (C) 2018 extensions.perfectwebteam.com
 * @license       GPL, http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Define the application's minimum supported PHP version as a constant so it can be referenced within the application.
 */
define('JOOMLA_MINIMUM_PHP', '5.3.10');

if (version_compare(PHP_VERSION, JOOMLA_MINIMUM_PHP, '<'))
{
	die('Your host needs to use PHP ' . JOOMLA_MINIMUM_PHP . ' or higher to run this version of Joomla!');
}

// Saves the start time and memory usage.
$startTime = microtime(1);
$startMem  = memory_get_usage();

/**
 * Constant that is checked in included files to prevent direct access.
 * define() is used in the installation folder rather than "const" to not error for PHP 5.2 and lower
 */
define('_JEXEC', 1);

if (file_exists(__DIR__ . '/defines.php'))
{
	include_once __DIR__ . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	//define('JPATH_BASE', __DIR__);
	define('JPATH_BASE', realpath(dirname(__FILE__) . '/../..'));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';

// Set profiler start time and memory usage and mark afterLoad in the profiler.
JDEBUG ? JProfiler::getInstance('Application')->setStart($startTime, $startMem)->mark('afterLoad') : null;

use Joomla\CMS\Factory;

// Instantiate the application.
$app      = Factory::getApplication('site');
$jinput   = $app->input;
$postcode = strtoupper($jinput->get('postcode', '', 'STRING'));
$number   = $jinput->get('number', '', 'STRING');

// get API key from given FormID
$formId = strtoupper($jinput->get('id', '', 'STRING'));;
$db     = Factory::getDbo();
$query  = $db->getQuery();
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
	$headers = array();
	// voorbeeld: $headers[] = 'X-Api-Key: sdfhksewhfsifhwkejfrbkfhskfHKkHKHKH';
	$headers[] = 'X-Api-Key: ' . $apikey;
	// De URL naar de API call
	$url  = 'https://postcode-api.apiwise.nl/v2/addresses/?postcode=' . $postcode . '&number=' . $number;
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	$response = curl_exec($curl);
	$data     = json_decode($response);
	curl_close($curl);
	$addressdata = $data->_embedded->addresses[0];
	if ($addressdata)
	{
		$city          = $addressdata->city->label;
		$street        = $addressdata->street;
		$province      = $addressdata->province->label;
		$lat           = $addressdata->geo->center->wgs84->coordinates[1];
		$lon           = $addressdata->geo->center->wgs84->coordinates[0];
		$return_data[] = array("city" => $city, "street" => $street, "province" => $province, "lat" => $lat, "lon" => $lon);
		header('Content-type:application/json;charset=utf-8');
		echo json_encode($return_data);
	}
}