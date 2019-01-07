<?php
/**
 * @package       RSform!Pro
 * @copyright (C) 2018 extensions.perfectwebteam.com
 * @license       GPL, http://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;


defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class PlgAjaxRsfppostcodeapi extends CMSPlugin
{
	/**
	 * @return array|bool
	 * @throws Exception
	 * @since 1.0.0
	 */
	function onAjaxRsfppostcodeapi()
	{
		$input = Factory::getApplication()->input;

		$data = $input->get('data');

		$postcode = strtoupper((string) $data[0]);
		$number   = (int) $data[1];

		// get API key from given FormID
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName('SettingValue'))
			->from($db->quoteName('#__rsform_config'))
			->where($db->quoteName('SettingName') . '=' . $db->quote('postcodeapi.code'));
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
				$addressdata = $data->_embedded->addresses[0];

				$city     = $addressdata->city->label;
				$street   = $addressdata->street;
				$province = $addressdata->province->label;
				$lat      = $addressdata->geo->center->wgs84->coordinates[1];
				$lon      = $addressdata->geo->center->wgs84->coordinates[0];

				$return_data = array(
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
				$return_data = array(
					'error' => 'De combinatie van postcode en huisnummer kan niet worden gevonden'
				);
			}
		}
		else
		{
			$return_data = array(
				'error' => 'Er zijn geen postcode of huisnummer bij ons binnen gekomen'
			);
		}

		return $return_data;
	}
}