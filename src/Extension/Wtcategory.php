<?php
/**
 * @package       WT Category custom field
 * @version       1.0.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2024 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

namespace Joomla\Plugin\Fields\Wtcategory\Extension;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields Text Plugin
 *
 * @since  3.7.0
 */
final class Wtcategory extends FieldsPlugin
{

	use DatabaseAwareTrait;
	use CurrentUserTrait;

	/**
	 * Categories data
	 *
	 * @var array $items
	 * @since 1.0.0
	 */
	private static $items;

	/**
	 * Transforms the field into a DOM XML element and appends it as a child on the given parent.
	 *
	 * @param   \stdClass    $field   The field.
	 * @param   \DOMElement  $parent  The field node parent.
	 * @param   Form         $form    The form.
	 *
	 * @return  \DOMElement
	 *
	 * @since   3.7.0
	 */
	public function onCustomFieldsPrepareDom($field, \DOMElement $parent, Form $form)
	{
		$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

		if ($fieldNode && $field->type == 'wtcategory')
		{
			$is_core_extension = $field->fieldparams->get('is_core_extension', true);
			$extension         = $is_core_extension ? $field->fieldparams->get('component', 'com_content') : $field->fieldparams->get('customcomponent', '');
			if (!empty($extension))
			{
				$fieldNode->setAttribute('extension', $extension);
				$label = $fieldNode->getAttribute('label');
				$label .= '<br/><code>'.$extension.'</code>';
				$fieldNode->setAttribute('label', $label);
			}

			$field_params = (new Registry($this->params))->merge($field->fieldparams);

			$multuple = $field_params->get('multiple', false);
			if ($multuple)
			{
				$fieldNode->setAttribute('multiple', 'true');
				$fieldNode->setAttribute('layout', 'joomla.form.field.list-fancy-select');
			}

			$published = $field_params->get('published', false);
			if ($published)
			{
				$fieldNode->setAttribute('published', '1');
			}

			$language = $field_params->get('language', '');
			if (!empty($language) && $language !== '*')
			{
				$fieldNode->setAttribute('language', $language);
			}

			FormHelper::addFieldPrefix('Joomla\Plugin\Fields\Wtcategory\Fields');
		}

		return $fieldNode;
	}

	/**
	 * Method to get categories by ids for specified extension
	 *
	 * @param $extension
	 * @param $categoryies_ids
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	public function getCategories($extension = 'com_content', $categoryies_ids = [])
	{

		$hash = md5($extension . '.' . serialize($categoryies_ids));

		if (!isset(static::$items[$hash]))
		{
			$config['filter.published'] = [1];
			$user                       = $this->getCurrentUser();
			$db                         = $this->getDatabase();
			$query                      = $db->getQuery(true)
				->select(
					[
						$db->quoteName('a.id'),
						$db->quoteName('a.title'),
						$db->quoteName('a.level'),
						$db->quoteName('a.parent_id'),
						$db->quoteName('a.language'),
					]
				)
				->from($db->quoteName('#__categories', 'a'))
				->where($db->quoteName('a.parent_id') . ' > 0');
			if (!empty($categoryies_ids))
			{
				$query->whereIn($db->quoteName('a.id'), $categoryies_ids);
			}


			// Filter on extension.
			$query->where($db->quoteName('extension') . ' = :extension')
				->bind(':extension', $extension);

			// Filter on user level.
			if (!$user->authorise('core.admin'))
			{
				$query->whereIn($db->quoteName('a.access'), $user->getAuthorisedViewLevels());
				$config['filter.published'] = [0, 1];
			}

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where($db->quoteName('a.published') . ' = :published')
						->bind(':published', $config['filter.published'], ParameterType::INTEGER);
				}
				elseif (\is_array($config['filter.published']))
				{
					$config['filter.published'] = ArrayHelper::toInteger($config['filter.published']);
					$query->whereIn($db->quoteName('a.published'), $config['filter.published']);
				}
			}

			$query->order($db->quoteName('a.lft'));

			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Assemble the list options.
			static::$items[$hash] = $items;

		}

		return static::$items[$hash];
	}
}
