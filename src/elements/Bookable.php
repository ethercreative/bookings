<?php
/**
 * Bookings plugin for Craft CMS 3.x
 *
 * An advanced booking plugin for Craft CMS and Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\bookings\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\models\FieldLayout;
use ether\bookings\elements\db\BookableQuery;
use ether\bookings\records\Bookable as BookableRecord;
use yii\base\InvalidConfigException;

/**
 * Bookable Element
 *
 * @property FieldLayout|null      $fieldLayout
 * @property array                 $htmlAttributes
 * @property int[]                 $supportedSiteIds
 * @property string|null           $uriFormat
 * @property string|null           $url
 * @property \Twig_Markup|null     $link
 * @property string|null           $ref
 * @property string                $indexHtml
 * @property bool                  $isEditable
 * @property string|null           $cpEditUrl
 * @property string|null           $thumbUrl
 * @property string|null           $iconUrl
 * @property string|null           $status
 * @property Element               $next
 * @property Element               $prev
 * @property Element               $parent
 * @property mixed                 $route
 * @property int|null              $structureId
 * @property ElementQueryInterface $ancestors
 * @property ElementQueryInterface $descendants
 * @property ElementQueryInterface $children
 * @property ElementQueryInterface $siblings
 * @property Element               $prevSibling
 * @property Element               $nextSibling
 * @property bool                  $hasDescendants
 * @property int                   $totalDescendants
 * @property string                $title
 * @property string|null           $serializedFieldValues
 * @property array                 $fieldParamNamespace
 * @property string                $contentTable
 * @property string                $fieldColumnPrefix
 * @property string                $fieldContext
 * @property int                   $groupId
 *
 * @author    Ether Creative
 * @package   ether\bookings\elements
 * @since     1.0.0-alpha.1
 */
class Bookable extends Element
{

	// Constants
	// =========================================================================

	// Statuses
	// -------------------------------------------------------------------------

	const STATUS_LIVE = 'live';
	const STATUS_PENDING = 'pending';
	const STATUS_FULLY_BOOKED = 'fully_booked';

	// Static Methods
	// =========================================================================

	/**
	 * Returns the display name of this class.
	 *
	 * @return string The display name of this class.
	 */
	public static function displayName (): string
	{
		return Craft::t('bookings', 'Bookable');
	}

	/**
	 * Returns whether elements of this type will be storing any data in the
	 * `content` table (tiles or custom fields).
	 *
	 * @return bool Whether elements of this type will be storing any data in
	 *              the `content` table.
	 */
	public static function hasContent (): bool
	{
		return true;
	}

	/**
	 * Returns whether elements of this type have traditional titles.
	 *
	 * @return bool Whether elements of this type have traditional titles.
	 */
	public static function hasTitles (): bool
	{
		return true;
	}

	/**
	 * Returns whether elements of this type have statuses.
	 *
	 * If this returns `true`, the element index template will show a Status
	 * menu by default, and your elements will get status indicator icons next
	 * to them.
	 *
	 * Use [[statuses()]] to customize which statuses the elements might have.
	 *
	 * @return bool Whether elements of this type have statuses.
	 * @see statuses()
	 */
	public static function isLocalized (): bool
	{
		return true;
	}

	/**
	 * Creates an [[ElementQueryInterface]] instance for query purpose.
	 *
	 * @return ElementQueryInterface The newly created
	 *                               [[ElementQueryInterface]] instance.
	 */
	public static function find (): ElementQueryInterface
	{
		return new BookableQuery(self::class);
	}

	/**
	 * Defines the sources that elements of this type may belong to.
	 *
	 * @param string|null $context The context ('index' or 'modal').
	 *
	 * @return array The sources.
	 * @see sources()
	 */
	protected static function defineSources (string $context = null): array
	{
		$sources = [];

		return $sources;
	}

	// Public Methods
	// =========================================================================

	/**
	 * Returns the validation rules for attributes.
	 *
	 * Validation rules are used by [[validate()]] to check if attribute values
	 * are valid. Child classes may override this method to declare different
	 * validation rules.
	 *
	 * More info:
	 * http://www.yiiframework.com/doc-2.0/guide-input-validation.html
	 *
	 * @return array
	 */
	public function rules ()
	{
		return [];
	}

	/**
	 * Returns whether the current user can edit the element.
	 *
	 * @return bool
	 */
	public function getIsEditable (): bool
	{
		return true;
	}

	/**
	 * Returns the field layout used by this element.
	 *
	 * @return FieldLayout|null
	 * @throws InvalidConfigException
	 */
	public function getFieldLayout ()
	{
		$tagGroup = $this->getGroup();

		if ($tagGroup)
		{
			return $tagGroup->getFieldLayout();
		}

		return null;
	}

	/**
	 * Returns this elements tag group
	 *
	 * @return \craft\models\TagGroup|null
	 * @throws InvalidConfigException
	 */
	public function getGroup ()
	{
		if ($this->groupId === null)
		{
			throw new InvalidConfigException('Tag is missing its group ID');
		}

		if (($group = Craft::$app->getTags()->getTagGroupById($this->groupId)) === null)
		{
			throw new InvalidConfigException('Invalid tag group ID: ' . $this->groupId);
		}

		return $group;
	}

	// Indexes, etc.
	// -------------------------------------------------------------------------

	/**
	 * Returns the HTML for the element’s editor HUD.
	 *
	 * @return string The HTML for the editor HUD
	 * @throws \yii\base\Exception
	 */
	public function getEditorHtml (): string
	{
		$html = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
			[
				'label'     => Craft::t('app', 'Title'),
				'siteId'    => $this->siteId,
				'id'        => 'title',
				'name'      => 'title',
				'value'     => $this->title,
				'errors'    => $this->getErrors('title'),
				'first'     => true,
				'autofocus' => true,
				'required'  => true,
			],
		]);

		$html .= parent::getEditorHtml();

		return $html;
	}

	// Events
	// -------------------------------------------------------------------------

	/**
	 * Performs actions before an element is saved.
	 *
	 * @param bool $isNew Whether the element is brand new
	 *
	 * @return bool Whether the element should be saved
	 */
	public function beforeSave (bool $isNew): bool
	{
		return true;
	}

	/**
	 * Performs actions after an element is saved.
	 *
	 * @param bool $isNew Whether the element is brand new
	 *
	 * @return void
	 * @throws \yii\db\Exception
	 */
	public function afterSave (bool $isNew)
	{
		$craft = \Craft::$app;

		if ($isNew)
		{
			$craft->db->createCommand()
				->insert(BookableRecord::TABLE_NAME, [
					'id' => $this->id,
					// TODO: Add necessary fields
				])
				->execute();
		}
		else
		{
			$craft->db->createCommand()
				->update(BookableRecord::TABLE_NAME, [
					// TODO: Add necessary fields
				], ['id' => $this->id])
				->execute();
		}

		parent::afterSave($isNew);
	}

	/**
	 * Performs actions before an element is deleted.
	 *
	 * @return bool Whether the element should be deleted
	 */
	public function beforeDelete (): bool
	{
		return true;
	}

	/**
	 * Performs actions after an element is deleted.
	 *
	 * @return void
	 */
	public function afterDelete ()
	{
	}

	/**
	 * Performs actions before an element is moved within a structure.
	 *
	 * @param int $structureId The structure ID
	 *
	 * @return bool Whether the element should be moved within the structure
	 */
	public function beforeMoveInStructure (int $structureId): bool
	{
		return true;
	}

	/**
	 * Performs actions after an element is moved within a structure.
	 *
	 * @param int $structureId The structure ID
	 *
	 * @return void
	 */
	public function afterMoveInStructure (int $structureId)
	{
	}

}
