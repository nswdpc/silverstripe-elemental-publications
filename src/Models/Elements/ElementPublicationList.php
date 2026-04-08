<?php

namespace NSWDPC\Elemental\Models\Publications;

use DNADesign\Elemental\Models\ElementContent;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\DropdownField;
use gorriecoe\Link\Models\Link;
use gorriecoe\LinkField\LinkField;

/**
 * Elemental content block for publications listing
 * @property ?string $SortType
 * @property ?string $SortDir
 * @method \SilverStripe\ORM\ManyManyList<\SilverStripe\Assets\File> Files()
 * @method \SilverStripe\ORM\ManyManyList<\gorriecoe\Link\Models\Link> Links()
 */
class ElementPublicationList extends ElementContent
{
    /**
     * @inheritdoc
     */
    private static bool $inline_editable = false;

    /**
     * @inheritdoc
     */
    private static string $table_name = 'ElementPublicationList';

    /**
     * @inheritdoc
     */
    private static string $singular_name = 'Publication Listing';

    /**
     * @inheritdoc
     */
    private static string $plural_name = 'Publication Listings';

    /**
     * @inheritdoc
     */
    private static string $description = 'Create a list of publications';

    /**
     * @inheritdoc
     */
    private static array $db = [
        'SortType' => 'Varchar(36)',
        'SortDir'  => 'Varchar(36)'
    ];

    /**
     * @inheritdoc
     */
    private static array $defaults = [
        'SortType' => 'Title',
        'SortDir'  => 'ASC'
    ];

    /**
     * @inheritdoc
     */
    private static array $many_many = [
        'Files' => File::class,
        'Links' => Link::class
    ];

    /**
     * @inheritdoc
     */
    private static array $many_many_extraFields = [
        'Links' => [
            'Sort' => 'Int'
        ]
    ];

    /**
     * @inheritdoc
     */
    private static array $owns = ['Files'];

    /**
     * @var string
     */
    public const DEFAULT_SORT_TYPE = 'Title';

    /**
     * @var string
     */
    public const DEFAULT_SORT_DIR = 'ASC';

    /**
     * Return available sort options
     */
    public static function getSortOptions(): array
    {
        return [
            'Title'      => _t(self::class . '.SORT_LISTING_BY_TITLE', 'Title'),
            'Name'       => _t(self::class . '.SORT_LISTING_BY_FILENAME', 'File name'),
            'Created'    => _t(self::class . '.SORT_LISTING_BY_CREATED', 'Created'),
            'LastEdited' => _t(self::class . '.SORT_LISTING_BY_UPDATED', 'Updated')
        ];
    }

    /**
     * Return available sort directions
     */
    public static function getSortDirections(): array
    {
        return [
            'ASC'  => _t(self::class . '.SORT_DIRECTION_ASC', 'Ascending'),
            'DESC' => _t(self::class . '.SORT_DIRECTION_DESC', 'Descending')
        ];
    }

    /**
     * Return sort option label
     */
    public static function getSortLabel(string $sortOption): string
    {
        $options = static::getSortOptions();
        return $options[$sortOption] ?? '';
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        if (!$this->isInDB()) {
            $fields->addFieldToTab(
                'Root.Main',
                LiteralField::create(
                    'NewPubsMessage',
                    '<p class="message validation">'
                    . _t(self::class . '.SAVE_BEFORE_UPLOAD', 'Set a title and save this block before images.')
                    . '</p>'
                ),
                'HTML'
            );
        } else {

            $sortType = DropdownField::create(
                'SortType',
                _t(self::class . '.SORT_LISTING_BY', 'Sort listing by'),
                static::getSortOptions()
            )->setEmptyString(
                _t(self::class . '.SORT_LISTING_BY_CHOOSE', 'Choose')
            );

            $sortDir = DropdownField::create(
                'SortDir',
                _t(self::class . '.SORT_DIRECTION', 'Sort direction'),
                static::getSortDirections()
            )->setEmptyString(
                _t(self::class . '.SORT_DIRECTION_CHOOSE', 'Choose')
            );

            $files = UploadField::create(
                'Files',
                _t(self::class . '.FILES', 'Files')
            )->setAllowedFileCategories('document')
            ->setFolderName("Uploads/publications/{$this->Title}-{$this->ID}");

            $links = LinkField::create(
                'Links',
                _t(self::class . '.LINKS', 'Links'),
                $this
            );

            $fields->addFieldToTab('Root.Main', $sortType);
            $fields->addFieldToTab('Root.Main', $sortDir);
            $fields->addFieldToTab('Root.Files', $files);
            $fields->addFieldToTab('Root.Links', $links);
        }

        return $fields;
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function getType()
    {
        return _t(self::class . '.BlockType', 'Publication Listing');
    }

    /**
     * Return items
     */
    public function getItems(): ArrayList
    {
        $list = ArrayList::create();
        $files = $this->Files();
        $links = $this->Links();
        if ($files) {
            $list->merge($files->toArray());
        }

        if ($links) {
            $list->merge($links->toArray());
        }

        // check values
        $type = $this->SortType ?: static::DEFAULT_SORT_TYPE;
        $options = static::getSortOptions();
        if (!array_key_exists($type, $options)) {
            $type = static::DEFAULT_SORT_TYPE;
        }

        $direction = $this->SortDir ?: static::DEFAULT_SORT_DIR;
        $directions = static::getSortDirections();
        if (!array_key_exists($direction, $directions)) {
            $direction = static::DEFAULT_SORT_DIR;
        }

        return $list->sort($type, $direction);
    }

    /**
     * Check for any owned files, and unpublish
     */
    public function onBeforeUnpublish()
    {
        if ($files = $this->Files()) {
            foreach ($files as $file) {
                $file->doUnpublish();
            }
        }
    }

    /**
     * Check for any owned files, and archive
     */
    public function onBeforeArchive()
    {
        if ($files = $this->Files()) {
            foreach ($files as $file) {
                $file->doArchive();
            }
        }
    }
}
