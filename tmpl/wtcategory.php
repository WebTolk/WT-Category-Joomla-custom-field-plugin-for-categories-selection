<?php
/**
 * @package       WT Category custom field
 * @version       1.0.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2024 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

/**
 * @var $field       object field object
 * @var $fieldParams object field params object. So, you can access via $field->fieldparams->get('option_name')
 * @var $item        object Article or category or contact (etc) object
 * @var $context     string rendering context
 */

/**
 * $field->id int
 * $field->title string "Field WT Category"
 * $field->name string "field-wt-category"
 * $field->checked_out int 822
 * $field->checked_out_time string "2024-08-27 12:20:40"
 * $field->note string
 * $field->state int 1 Published or not
 * $field->access int 1  Access group id
 * $field->created_time string "2024-08-27 12:06:05"
 * $field->created_user_id int 822
 * $field->ordering int 0
 * $field->language string "*" or "en_GB", "ru_RU"
 * $field->fieldparams Joomla\Registry\Registry field params for site: map_center, map_zoom, map_type, map_width, map_height
 * $field->params Joomla\Registry\Registry for admin panel hint, class, showlabel, showon
 * $field->type string "wtcategory"
 * $field->default_value string
 * $field->context string "com_content.article"
 * $field->group_id int 0
 * $field->label string "Field WT Category"
 * $field->description string ""
 * $field->required int 0
 * $field->only_use_in_subform int 0
 * $field->language_title string|null
 * $field->language_image string|null
 * $field->editor string "Sergey Tolkachyov"
 * $field->access_level "Public"
 * $field->author_name "Sergey Tolkachyov"
 * $field->group_title null
 * $field->group_access null
 * $field->group_state null
 * $field->group_note null
 * $field->value string  Field HTML
 * $field->rawvalue string
 */

$value = $field->value;

if (empty($value)) {
	return;
}

$value = (array) $value;

$is_core_extension = $fieldParams->get('is_core_extension', true);
$extension = $is_core_extension ? $fieldParams->get('component', 'com_content') : $fieldParams->get('customcomponent', '');

$categories = Factory::getApplication()->bootPlugin($field->type, 'fields')->getCategories($extension, $value);

$texts  = [];
foreach ($categories as $category) {

	$texts[] = \htmlentities(\trim($category->title));
}

echo implode(', ', $texts);
