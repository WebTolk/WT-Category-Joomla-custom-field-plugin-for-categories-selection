<?php
/**
 * @package    Fields - WT Category
 * @version       1.0.1
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2024 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

namespace Joomla\Plugin\Fields\Wtcategory\Fields;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class WtcategoryField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    public $type = 'Wtcategory';

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		if (\is_string($value) && strpos($value, ',') !== false) {
			$value = explode(',', $value);
		}

		return parent::setup($element, $value, $group);
	}


    /**
     * Method to get the field options for category
     * Use the extension attribute in a form to specify the.specific extension for
     * which categories should be displayed.
     * Use the show_root attribute to specify whether to show the global category root in the list.
     *
     * @return  object[]    The field option objects.
     *
     * @since   1.6
     */
    protected function getOptions()
    {
        $options   = [];
        $extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $this->element['scope'];
        $published = (string) $this->element['published'];
        $language  = (string) $this->element['language'];

        // Load the category options for a given extension.
        if (!empty($extension)) {
            // Filter over published state or not depending upon if it is present.
            $filters = [];

            if ($published) {
                $filters['filter.published'] = explode(',', $published);
            }

            // Filter over language depending upon if it is present.
            if ($language) {
                $filters['filter.language'] = explode(',', $language);
            }

            if ($filters === []) {
                $options = HTMLHelper::_('category.options', $extension);
            } else {
                $options = HTMLHelper::_('category.options', $extension, $filters);
            }

            // Verify permissions.  If the action attribute is set, then we scan the options.
            if ((string) $this->element['action']) {
                // Get the current user object.
                $user = $this->getCurrentUser();

                foreach ($options as $i => $option) {
                    /*
                     * To take save or create in a category you need to have create rights for that category
                     * unless the item is already in that category.
                     * Unset the option if the user isn't authorised for it. In this field assets are always categories.
                     */
                    if ($user->authorise('core.create', $extension . '.category.' . $option->value) === false) {
                        unset($options[$i]);
                    }
                }
            }

            if (isset($this->element['show_root'])) {
                array_unshift($options, HTMLHelper::_('select.option', '0', Text::_('JGLOBAL_ROOT')));
            }
        } else {
            Log::add(Text::_('JLIB_FORM_ERROR_FIELDS_CATEGORY_ERROR_EXTENSION_EMPTY'), Log::WARNING, 'jerror');
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
