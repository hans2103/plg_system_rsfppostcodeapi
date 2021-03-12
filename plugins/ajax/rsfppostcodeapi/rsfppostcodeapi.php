<?php
/**
 * @package    RSform!Pro
 *
 * @author     Perfect Web Team <hallo@perfectwebteam.nl>
 * @copyright  Copyright (C) 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://perfectwebteam.nl
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;

/**
 * AJAX plugin for RSForm! Postcode API check.
 *
 * @package     RSform!Pro
 * @since       1.0.0
 */
class PlgAjaxRsfppostcodeapi extends CMSPlugin
{
	/**
	 * Do the postcode check.
	 *
	 * @return  array  The result of the check.
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function onAjaxRsfppostcodeapi()
	{
		$input = Factory::getApplication()->input;

		$data = $input->get('data');

		$postcode = strtoupper((string) $data[0]);
		$number   = '';

		// Check if a house number is supplied
		if (isset($data[1]))
		{
			$number = (int) $data[1];
		}

		// Get API key from given FormID
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName('SettingValue'))
			->from($db->quoteName('#__rsform_config'))
			->where($db->quoteName('SettingName') . '=' . $db->quote('postcodeapi.code'));
		$db->setQuery($query);
		$db->execute();
		$rows = $db->getNumRows();

		if (!$rows > 1)
		{
			return false;
		}

		$result = $db->loadObject();
		$apiKey = $result->SettingValue;

		if (strlen($postcode) == 7)
		{
			$postcode = substr($postcode, 0, 4) . substr($postcode, 5, 2);
		}

		if ($postcode !== '')
		{
//			$headers   = array();
//			$headers[] = 'X-Api-Key: ' . $apiKey;
//
//			// De URL naar de API call
//			$url = 'https://postcode-api.apiwise.nl/v2/addresses/?postcode=' . $postcode;
//
//			if ($number)
//			{
//				$url .= '&number=' . $number;
//			}
//
//			$curl = curl_init($url);
//
//			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//
//			$response = curl_exec($curl);
//			$data     = json_decode($response);
//
//			curl_close($curl);

			$token = '5f8f57d5-d153-4f99-8926-019f6e304340';
			$client = new \ApiPostcode\Client\PostcodeClient($token);

			$address = $client->fetchAddress($postcode, $number);

			if (isset($data->_embedded->addresses) && is_array($data->_embedded->addresses))
			{
				$addressdata = array_shift($data->_embedded->addresses);

				$city     = $addressdata->city->label;
				$street   = $addressdata->street;
				$province = $addressdata->province->label;
				$lat      = $addressdata->geo->center->wgs84->coordinates[1];
				$lon      = $addressdata->geo->center->wgs84->coordinates[0];

				$returnData = array(
					"city"     => $city,
					"street"   => $street,
					"province" => $province,
					"lat"      => $lat,
					"lon"      => $lon
				);

				header('Content-type:application/json;charset=utf-8');
			}
			else
			{
				$returnData = array(
					'error' => 'De combinatie van postcode en huisnummer kan niet worden gevonden'
				);
			}
		}
		else
		{
			$returnData = array(
				'error' => 'Er zijn geen postcode of huisnummer bij ons binnen gekomen'
			);
		}

		return $returnData;
	}
}
