<?php
/**
* @package RSForm!Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;

class TableRSForm_Postcodeapi extends Table
{
	public $form_id 	= null;
	public $published 	= 0;
	
	public function __construct(& $db) {
		parent::__construct('#__rsform_postcodeapi', 'form_id', $db);
	}
}