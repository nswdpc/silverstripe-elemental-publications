<?php

namespace NSWDPC\Elemental\Models\Publications;

use DNADesign\Elemental\Models\ElementContent;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\ArrayList;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\DropdownField;
use gorriecoe\Link\Models\Link;
use gorriecoe\LinkField\LinkField;

/**
 * @author mark.taylor@dpc.nsw.gov.au
 */
class ElementPublicationList extends ElementContent
{

    private static $inline_editable = false;

    private static $table_name = 'ElementPublicationList';

    private static $singular_name = 'Publication Listing';

    private static $plural_name = 'Publication Listings';

    private static $description = 'Create a list of publications';

    private static $db = [
        'SortType' => 'Varchar(36)',
        'SortDir'  => 'Varchar(36)'
    ];

    private static $many_many = [
        'Files' => File::class,
        'Links' => Link::class
    ];

    private static $many_many_extraFields = [
        'Links' => [
            'Sort' => 'Int'
        ]
    ];

    private static $owns = ['Files'];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        if (!$this->ID) {

            $fields->addFieldToTab('Root.Main', LiteralField::create('NewPubsMessage', '<span class="message validation">Set a title and save this block before images.</span>'), 'HTML');

        } else {

            $sortType = DropdownField::create(
                'SortType',
                'Sort listing by',
                [
                    'Title'      => 'Title',
                    'Name'       => 'File name',
                    'Created'    => 'Created',
                    'LastEdited' => 'Updated'
                ]
            )->setEmptyString('Choose a type');

            $sortDir = DropdownField::create(
                'SortDir',
                'Sort direction',
                [
                    'ASC'  => 'Ascending',
                    'DESC' => 'Descending'
                ]
            )->setEmptyString('Choose a direction');

            $files = UploadField::create('Files', 'Files');
            $files->setAllowedFileCategories('document');
            $files->setFolderName("Uploads/publications/{$this->Title}-{$this->ID}");

            $links = LinkField::create('Links', 'Links', $this);

            $fields->addFieldToTab('Root.Main', $sortType);
            $fields->addFieldToTab('Root.Main', $sortDir);
            $fields->addFieldToTab('Root.Files', $files);
            $fields->addFieldToTab('Root.Links', $links);

        }

        return $fields;
    }

    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'Publication Listing');
    }

    public function getItems()
    {
        $files = $this->Files();
        $links = $this->Links();

        $type      = $this->SortType ? $this->SortType : 'Title';
        $direction = $this->SortDir ? $this->SortDir : 'ASC';

        $merge = array_merge($files->toArray(), $links->toArray());

        return ArrayList::create($merge)->sort($type, $direction);
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
