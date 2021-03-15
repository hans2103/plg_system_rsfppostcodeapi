<?php
/**
 * @package    RSform!Pro
 *
 * @author     Perfect Web Team <hallo@perfectwebteam.nl>
 * @copyright  Copyright (C) 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://perfectwebteam.nl
 */

defined('_JEXEC') or die;

use ApiPostcode\Client\PostcodeClient;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

require_once JPATH_LIBRARIES . "/Postcode/vendor/autoload.php";

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
	 * @since   1.0.0
	 * @throws  Exception
	 *
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

		if (!($rows > 1))
		{
			return false;
		}

		$result = $db->loadObject();
		$token  = $result->SettingValue;

		if (strlen($postcode) == 7)
		{
			$postcode = substr($postcode, 0, 4) . substr($postcode, 5, 2);
		}

		if ($postcode !== '')
		{
			$client = new PostcodeClient($token);

			try
			{
				$address = $client->fetchAddress($postcode, $number);
			}
			catch (Exception $e)
			{
				//do nothing
			}

			if (isset($address) && is_object($address))
			{
				$returnData = [
					"city"     => $address->getCity(),
					"street"   => $address->getStreet(),
					"province" => $address->getProvince(),
					"lat"      => $address->getLatitude(),
					"lon"      => $address->getLongitude()
				];

				header('Content-type:application/json;charset=utf-8');
			}
			else
			{
				$returnData = [
					'error' => 'De combinatie van postcode en huisnummer kan niet worden gevonden'
				];
			}
		}
		else
		{
			$returnData = [
				'error' => 'Er zijn geen postcode of huisnummer bij ons binnen gekomen'
			];
		}

		return $returnData;
	}
}
