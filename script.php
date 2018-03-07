<?php
/**
 * @package RSform!Pro
 * @copyright (C) 2014 www.rsjoomla.com
 * @license GPL, http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;

class plgSystemRSFPPostcodeapiInstallerScript
{
	public function preflight($type, $parent) {
		if ($type == 'uninstall') {
			return true;
		}

		$app = Factory::getApplication();

		try {
			if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/rsform.php')) {
				throw new Exception('Please install the RSForm! Pro component before continuing.');
			}

			if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/assets.php')) {
				throw new Exception('Please update RSForm! Pro to at least version 1.51.0 before continuing!');
			}

			require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/version.php';
			$version = new RSFormProVersion;

			if (version_compare((string) $version, '1.52.5', '<')) {
				throw new Exception('Please update RSForm! Pro to at least version 1.52.5 before continuing!');
			}
		} catch (Exception $e) {
			$app->enqueueMessage($e->getMessage(), 'error');
			return false;
		}

		return true;
	}

	
	public function update($parent) {
		$this->copyFiles($parent);
		$this->runSQL($parent, 'install');
	}
	
	public function install($parent) {
		$this->copyFiles($parent);
		$this->runSQL($parent, 'install');
	}
	
	protected function copyFiles($parent) {
		jimport('joomla.filesystem.folder');
		
		// Copy /admin files
		$src		= $parent->getParent()->getPath('source').'/admin';
		$dest 		= JPATH_ADMINISTRATOR.'/components/com_rsform';
		if (!JFolder::copy($src, $dest, '', true)) {
			throw new Exception('Could not copy to '.str_replace(JPATH_ADMINISTRATOR, '', $dest).', please make sure destination is writable!');
		}
	}
	
	protected function runSQL($parent, $file) {
		$db 	= JFactory::getDbo();
		$driver = strtolower($db->name);
		$src    = $parent->getParent()->getPath('source');
		if (strpos($driver, 'mysql') !== false) {
			$driver = 'mysql';
		}
		
		$sqlfile = $src.'/sql/'.$driver.'/'.$file.'.sql';
		
		if (file_exists($sqlfile)) {
			$buffer = file_get_contents($sqlfile);
			if ($buffer !== false) {
				$queries = InstallerHelper::splitSql($buffer);
				foreach ($queries as $query) {
					$query = trim($query);
					if ($query != '' && $query{0} != '#') {
						$db->setQuery($query);
						if (!$db->execute()) {
							throw new Exception(Text::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
						}
					}
				}
			}
		}
	}
}