<?php
/**
 * @package       RSform!Pro
 * @copyright (C) 2018 extensions.perfectwebteam.com
 * @license       GPL, http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;

/**
 * RSForm! Pro system plugin
 */
class plgSystemRSFPPostcodeapi extends CMSPlugin
{
	public function __construct(&$subject, $config)
	{
		// Enable the language loading
		$this->autoloadLanguage = true;

		parent::__construct($subject, $config);
	}

	/**
	 * Update the conditions after form save.
	 *
	 * @param   object $form The form object that is being stored.
	 *
	 * @return  bool  True on success | False on failure
	 *
	 * @since   2.12
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 */
	public function rsfp_onFormSave($form)
	{
		//$post            = JRequest::get('post', JREQUEST_ALLOWRAW);
		$post            = Factory::getApplication()->input->get('postcodeapiParams', array(), 'array');
		$post['form_id'] = $form->FormId;

		$row = Table::getInstance('RSForm_Postcodeapi', 'Table');

		if (!$row)
		{
			return;
		}

		if (!$row->bind($post))
		{
			JError::raiseWarning(500, $row->getError());

			return false;
		}

		$db = Factory::getDBO();
		$db->setQuery("SELECT form_id FROM #__rsform_postcodeapi WHERE form_id='".(int) $post['form_id']."'");
		if (!$db->loadResult())
		{
			$db->setQuery("INSERT INTO #__rsform_postcodeapi SET form_id='".(int) $post['form_id']."'");
			$db->execute();
		}

		if ($row->store())
		{
			return true;
		}
		else
		{
			JError::raiseWarning(500, $row->getError());

			return false;
		}
	}

	public function rsfp_bk_onFormCopy($args)
	{
		$formId    = $args['formId'];
		$newFormId = $args['newFormId'];

		if ($row = Table::getInstance('RSForm_Postcodeapi', 'Table'))
		{
			if ($row->load($formId))
			{
				$vars = unserialize($row->published);

				if (isset($vars['form_id']))
				{
					$vars['form_id'] = $newFormId;
				}

				if (!$row->bind(array('form_id' => $newFormId, 'cc_merge_vars' => serialize($vars))))
				{
					JError::raiseWarning(500, $row->getError());

					return false;
				}

				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->select($db->qn('form_id'))
					->from($db->qn('#__rsform_postcodeapi'))
					->where($db->qn('form_id') . '=' . $db->q($newFormId));
				if (!$db->setQuery($query)->loadResult())
				{
					$query = $db->getQuery(true)
						->insert($db->qn('#__rsform_postcodeapi'))
						->set($db->qn('form_id') . '=' . $db->q($newFormId));
					$db->setQuery($query)->execute();
				}

				if ($row->store())
				{
					return true;
				}
				else
				{
					JError::raiseWarning(500, $row->getError());

					return false;
				}
			}
		}
	}

	/**
	 * Add the option under Extras when editing the form properties.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 */
	public function rsfp_bk_onAfterShowFormEditTabsTab()
	{
		$url  = 'javascript: void(0);';
		$text = '<span class="rsficon rsficon-envelope-o"></span><span class="inner-text">' . Text::_('PLG_RSFP_POSTCODEAPI_LABEL') . '</span>';

		echo '<li>' . HTMLHelper::_('link', $url, $text) . '</li>';
	}

	/**
	 * Add settings postcode api
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 */
	public function rsfp_bk_onAfterShowFormEditTabs()
	{
		$formId = Factory::getApplication()->input->getInt('formId');
		$row    = Table::getInstance('RSForm_Postcodeapi', 'Table');

		$postcodeapi_code = RSFormProHelper::getConfig('postcodeapi.code');

		if ($postcodeapi_code == '')
		{
			?>
            <div id="rsfpverticalresponsediv">
                <table class="admintable">
                    <tr>
                        <td valign="top" align="left" width="30%">
                            <table class="table table-bordered">
                                <div class="alert alert-warning"><?php echo JText::_('PLG_RSFP_POSATCODEAPI_NOTOKEN') ?></div>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
			<?php
			return;
		}

		if (!$row)
		{
			return;
		}

		$row->load($formId);

		// Get all RS Form! Fields
		$fields_array = $this->_getFields($formId);

		$fields = array();
		foreach ($fields_array as $field)
		{
			$fields[] = HTMLHelper::_('select.option', $field, $field);
		}

		// Radio for choosing wheter or not to use the integration
		$lists['published'] = HTMLHelper::_('select.booleanlist', 'published', 'class="inputbox" onclick="rsfp_changeCoActive();"', $row->published);

		$form = new Form('rsfppostcodeapi');
		$form->loadFile(__DIR__ . '/configuration.xml');
		$data = array('postcodeapiParams' => array('published' => $row->published));
		$form->bind($data);

		echo '<div id="postcodeapidiv">';
		echo $form->renderFieldset('formConfig');
		echo '</div>';
	}

	/**
	 * RSForm!Pro configuration options.
	 *
	 * @param   RSTabs $tabs The tabs class
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   2.2
	 *
	 * @throws  InvalidArgumentException
	 */
	public function rsfp_bk_onAfterShowConfigurationTabs($tabs)
	{
		// Collect the data and form
		$form = new Form('rsfppostcodeapi');
		$form->loadFile(__DIR__ . '/configuration.xml');
		$data = array('rsformConfig' => RSFormProHelper::getConfig());
		$form->bind($data);

		// Build the output form
		$output = '<div id="page-postcodeapi" class="form-horizontal">';
		$output .= $form->renderFieldset('postcodeapiConfig');
		$output .= '</div>';

		// Render the output
		$tabs->addTitle(Text::_('PLG_RSFP_POSTCODEAPI_LABEL'), 'form-postcodeapi');
		$tabs->addContent($output);
	}

	/**
	 * Load any files needed for the form display.
	 *
	 * @param   array  $details  An array of form details.
	 *
	 * @return  void.
	 *
	 * @since   4.3.0
	 */
	public function rsfp_bk_onBeforeCreateFrontComponentBody($details)
	{
		$code = RSFormProHelper::getConfig('postcodeapi.code');
		if (empty($code))
		{
			return;
		}

		if (!$details['formId'] > 0)
		{
			return;
		}

		// Load the Javascript file
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'plg_system_rsfppostcodeapi/rsfppostcodeapi.js', array('version' => 'auto', 'relative' => true));
	}

	protected function _getFields($formId)
	{
		$db = Factory::getDBO();

		$db->setQuery("SELECT p.PropertyValue FROM #__rsform_components c LEFT JOIN #__rsform_properties p ON (c.ComponentId=p.ComponentId) WHERE c.FormId='" . (int) $formId . "' AND p.PropertyName='NAME' ORDER BY c.Order");

		return $db->loadColumn();
	}

	/**
	 * Delete any form settings on form deletion.
	 *
	 * @param   int $formId The ID of the form to delete.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  RuntimeException
	 */
	public function rsfp_onFormDelete($formId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__rsform_postcodeapi'))
			->where($db->quoteName('form_id') . ' = ' . (int) $formId);
		$db->setQuery($query)->execute();
	}

	/**
	 * Backup the settings when the user does a form backup.
	 *
	 * @param   object             $form   The form being backed up.
	 * @param   RSFormProBackupXML $xml    The XML object.
	 * @param   object             $fields The form fields.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  RuntimeException
	 */
	public function rsfp_onFormBackup($form, $xml, $fields)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->qn('#__rsform_postcodeapi'))
			->where($db->qn('form_id') . '=' . $db->q($form->FormId));
		$db->setQuery($query);
		if ($cc = $db->loadObject())
		{
			// No need for a form_id
			unset($cc->form_id);

			$xml->add('postcodeapi');
			foreach ($cc as $property => $value)
			{
				$xml->add($property, $value);
			}
			$xml->add('/postcodeapi');
		}
	}

	/**
	 * Restore the settings when the user restores a form from backup.
	 *
	 * @param   object            $form   The form being backed up.
	 * @param   SimpleXMLIterator $xml    The XML object.
	 * @param   object            $fields The form fields.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   4.0
	 *
	 * @throws  RuntimeException
	 */
	public function rsfp_onFormRestore($form, $xml, $fields)
	{
		if (isset($xml->postcodeapi))
		{
			$data = array();

			foreach ($xml->postcodeapi->children() as $property => $value)
			{
				$data[$property] = (string) $value;
			}

			$row = Table::getInstance('RSForm_Postcodeapi', 'Table');

			if (!$row->load($form->FormId))
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->insert('#__rsform_postcodeapi')
					->set(array(
						$db->qn('form_id') . '=' . $db->q($form->FormId),
					));
				$db->setQuery($query)->execute();
			}

			$row->save($data);
		}

		return true;
	}

	/**
	 * Empty the table when all forms are deleted.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  RuntimeException
	 */
	public function rsfp_bk_onFormRestoreTruncate()
	{
		Factory::getDbo()->truncateTable('#__rsform_postcodeapi');
	}

}
