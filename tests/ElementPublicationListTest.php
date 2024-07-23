<?php

namespace NSWDPC\Elemental\Models\Publications\Tests;

use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Elemental\Models\ElementContent;
use DNADesign\Elemental\Models\ElementalArea;
use NSWDPC\Elemental\Models\Publications\ElementPublicationList;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Dev\TestAssetStore;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * Test publication list
 */
class ElementPublicationListTest extends SapphireTest {


    protected $usesDatabase = true;

    protected static $fixture_file = 'ElementPublicationListTest.yml';

    protected function setUp(): void
    {
        parent::setUp();
        $this->logInWithPermission('ADMIN');
        Versioned::set_stage(Versioned::DRAFT);

        TestAssetStore::activate('ElementPublicationListTest');

        // Create a test files for each of the fixture references
        $fileIDs = $this->allFixtureIDs(File::class);
        foreach ($fileIDs as $fileID) {
            /** @var File $file */
            $file = DataObject::get_by_id(File::class, $fileID);
            $file->setFromString(str_repeat('x', 1000000), $file->getFilename());
        }
    }

    public function testItemListing() {
        $element = $this->objFromFixture(ElementPublicationList::class, 'list1');
        $element->SortType = 'Title';
        $element->SortDir = 'ASC';
        $element->write();
        $items = $element->getItems();
        $this->assertEquals(5, $items->count());// per fixture

        $byTitleAsc = $items->column('Title');
        $expected = [
            'FileTest1',
            'FileTest2',
            'Link 1',
            'Link 2',
            'Link 3'
        ];
        $this->assertEquals($expected, $byTitleAsc);

        $element->SortType = 'Title';
        $element->SortDir = 'DESC';
        $element->write();
        arsort($expected);
        $byTitleDesc = $items->column('Title');
        $this->assertEquals($expected, $byTitleDesc);
    }

}
