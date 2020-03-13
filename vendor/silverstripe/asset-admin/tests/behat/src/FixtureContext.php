<?php

namespace SilverStripe\AssetAdmin\Tests\Behat\Context;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\NodeElement;
use Page;
use SilverStripe\Assets\Image;
use SilverStripe\BehatExtension\Context\FixtureContext as BaseFixtureContext;
use SilverStripe\BehatExtension\Utility\StepHelper;

/**
 * Context used to create fixtures in the SilverStripe ORM.
 */
class FixtureContext extends BaseFixtureContext
{
    use StepHelper;

    /**
     * Select a gallery item by type and name
     *
     * @Given /^I (?:(?:click on)|(?:select)) the (?:file|folder) named "([^"]+)" in the gallery$/
     * @param string $name
     */
    public function stepISelectGalleryItem($name)
    {
        $item = $this->getGalleryItem($name);
        assertNotNull($item, "File named $name could not be found");
        $item->click();
    }

    /**
     * Check the checkbox for a given gallery item
     * @Given /^I check the (?:file|folder) named "([^"]+)" in the gallery$/
     * @param string $name
     */
    public function stepICheckTheGalleryItem($name)
    {
        $item = $this->getGalleryItem($name);
        assertNotNull($item, "File named $name could not be found");
        $checkboxLabel = $item->find('css', 'label.gallery-item__checkbox-label.font-icon-tick');
        assertNotNull($checkboxLabel, "Could not find checkbox label for file named {$name}");
        $checkboxLabel->click();
    }

    /**
     * @Then /^I should see the file named "([^"]+)" in the gallery$/
     * @param string $name
     */
    public function iShouldSeeTheGalleryItem($name)
    {
        $item = $this->getGalleryItem($name);
        assertNotNull($item, "File named {$name} could not be found");
    }

    /**
     * @Then /^I should not see the file named "([^"]+)" in the gallery$/
     * @param string $name
     */
    public function iShouldNotSeeTheGalleryItem($name)
    {
        $item = $this->getGalleryItem($name, 0);
        assertNull($item, "File named {$name} was found when it should not be visible");
    }

    /**
     * @Then /^I should see the "([^"]*)" form$/
     * @param string $id HTML ID of form
     * @param integer $timeout
     */
    public function iShouldSeeTheForm($id, $timeout = 3)
    {
        /** @var DocumentElement $page */
        $page = $this->getMainContext()->getSession()->getPage();
        $form = $this->retryThrowable(function () use ($page, $id) {
            return $page->find('css', "form#{$id}");
        }, $timeout);
        assertNotNull($form, "form with id $id could not be found");
        assertTrue($form->isVisible(), "form with id $id is not visible");
    }

    /**
     * @Then /^I should see the file status flag$/
     */
    public function iShouldSeeTheFileStatusFlag()
    {
        // TODO This should wait for any XHRs and React rendering to finish
        $this->getMainContext()->getSession()->wait(
            1000,
            "window.jQuery && window.jQuery('.editor__status-flag').size() > 0"
        );

        $page = $this->getMainContext()->getSession()->getPage();
        $flag = $page->find('css', '.editor__status-flag');
        assertNotNull($flag, "File editor status flag could not be found");
        assertTrue($flag->isVisible(), "File status flag is not visible");
    }

    /**
     * @Then /^I should not see the file status flag$/
     */
    public function iShouldNotSeeTheFileStatusFlag()
    {
        // TODO Flakey assertion, since the status flag might not be loaded via XHR yet
        $page = $this->getMainContext()->getSession()->getPage();
        $flag = $page->find('css', '.editor__status-flag');
        assertNull($flag, "File editor status flag should not be present");
    }

    /**
     * @Given /^I click on the latest history item$/
     */
    public function iClickOnTheLatestHistoryItem()
    {
        $this->getMainContext()->getSession()->wait(
            5000,
            "window.jQuery && window.jQuery('.history-list__list li').size() > 0"
        );

        $page = $this->getMainContext()->getSession()->getPage();

        $elements = $page->find('css', '.history-list__list li');

        if (null === $elements) {
            throw new \InvalidArgumentException(sprintf('Could not find list item'));
        }

        $elements->click();
    }

    /**
     * @Given /^I attach the file "([^"]*)" to dropzone "([^"]*)"$/
     * @see MinkContext::attachFileToField()
     * @param string $path
     * @param string $name
     */
    public function iAttachTheFileToDropzone($path, $name)
    {
        // Get path
        $filesPath = $this->getFilesPath();
        if ($filesPath) {
            $fullPath = rtrim(realpath($filesPath), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$path;
            if (is_file($fullPath)) {
                $path = $fullPath;
            }
        }

        assertFileExists($path, "$path does not exist");
        // Find field
        $selector = "input[type=\"file\"].dz-hidden-input.dz-input-{$name}";

        /** @var DocumentElement $page */
        $page = $this->getMainContext()->getSession()->getPage();
        $input = $page->find('css', $selector);
        assertNotNull($input, "Could not find {$selector}");

        // Make visible temporarily while attaching
        $this->getMainContext()->getSession()->executeScript(
            <<<EOS
window.jQuery('.dz-hidden-input')
    .css('visibility', 'visible')
    .width(1)
    .height(1);
EOS
        );

        assert($input->isVisible());
        // Attach via html5
        $input->attachFile($path);
    }

    /**
     * @Then I should see an error message on the file :file
     * @param string $file
     */
    public function iShouldSeeAnErrorMessageOnTheFile($file)
    {
        $fileNode = $this->getGalleryItem($file);
        assertTrue($fileNode->getParent()->hasClass('gallery-item--error'));
    }

    /**
     * Checks that the message box contains specified text.
     *
     * @Then /^I should see "(?P<text>(?:[^"]|\\")*)" in the message box$/
     * @param string $text
     */
    public function assertMessageBoxContainsText($text)
    {
        /** @var FeatureContext $mainContext */
        $mainContext = $this->getMainContext();
        $mainContext
            ->assertSession()
            ->elementTextContains('css', '.message-box', str_replace('\\"', '"', $text));
    }

    /**
     * Helper for finding items in the visible gallery view
     *
     * @param string $name Title of item
     * @param int $timeout
     * @return NodeElement
     */
    protected function getGalleryItem($name, $timeout = 3)
    {
        /** @var DocumentElement $page */
        $page = $this->getMainContext()->getSession()->getPage();
        // Find by cell
        $cell = $page->find(
            'xpath',
            "//div[contains(@class, 'gallery-item')]//div[contains(text(), '{$name}')]"
        );
        if ($cell) {
            return $cell;
        }
        // Find by row
        $row = $page->find(
            'xpath',
            "//tr[contains(@class, 'gallery__table-row')]//div[contains(text(), '{$name}')]"
        );
        if ($row) {
            return $row;
        }
        return null;
    }

    /**
     * Helper for finding items in the visible gallery view by its order
     *
     * @param string $rank index of the item to get starting at 1
     * @param int $timeout
     * @return NodeElement
     */
    protected function getGalleryItemByRank($rank, $timeout = 3)
    {
        /** @var DocumentElement $page */
        $page = $this->getMainContext()->getSession()->getPage();
        // Find by cell
        $cell = $page->find(
            'xpath',
            "//div[contains(@class, 'gallery__files')]/div[$rank]"
        );
        if ($cell) {
            return $cell;
        }
        // Find by row
        $row = $page->find(
            'xpath',
            "//tr[contains(@class, 'gallery__table-row')][$rank]"
        );
        if ($row) {
            return $row;
        }
        return null;
    }

    /**
     * @Given /^a page "([^"]*)" containing an image "([^"]*)"$/
     * @param string $page
     * @param string $image
     */
    public function aPageContaining($page, $image)
    {
        // Find or create named image
        $fields = $this->prepareFixture(Image::class, $image);
        /** @var Image $image */
        $image = $this->fixtureFactory->createObject(Image::class, $image, $fields);

        // Create page
        $fields = $this->prepareFixture(Page::class, $page);
        $fields = array_merge($fields, [
            'Title' => $page,
            'Content' => sprintf(
                '<p>[image id="%d" width="%d" height="%d"]</p>',
                $image->ID,
                $image->getWidth(),
                $image->getHeight()
            ),
        ]);
        $this->fixtureFactory->createObject(Page::class, $page, $fields);
    }

    /**
     * @Then I should see a modal titled :title
     * @param string $title
     */
    public function iShouldSeeAModalTitled($title)
    {
        $page = $this->getMainContext()->getSession()->getPage();
        $modalTitle = $page->find('css', '[role=dialog] .modal-header > .modal-title');
        assertNotNull($modalTitle, 'No modal on the page');
        assertTrue($modalTitle->getText() == $title);
    }

    /**
     * @Then I press the :buttonName button inside the modal
     * @param string $buttonName
     */
    public function iPressButtonInModal($buttonName)
    {
        $page = $this->getMainContext()->getSession()->getPage();
        $modal = $page->find('css', '[role=dialog] .modal-dialog');
        assertNotNull($modal, 'No modal on the page');

        // Check if the popover is open for the block
        $button = $modal->find('xpath', "//button[contains(text(), '$buttonName')]");

        assertNotNull($button, sprintf('Could not find button labelled "%s"', $buttonName));

        $button->click();
    }

    /**
     * @Then /^I should see the gallery item "([^"]+)" in position "([^"]+)"$/
     * @param string $name
     * @param string $position
     */
    public function iShouldSeeTheGalleryItemInPosition($name, $position)
    {
        $itemByPosition = $this->getGalleryItemByRank($position);
        assertNotNull($itemByPosition, 'Should have found a fallery item at position ' . $position);
        $title = $itemByPosition->find(
            'xpath',
            "//div[contains(text(), '{$name}')]"
        );
        assertNotNull($title, sprintf('File at position %s should be named %s', $position, $name));
    }

    /**
     * @When /^I click the "([^"]+)" element$/
     * @param $selector
     */
    public function iClickTheElement($selector)
    {
        /** @var DocumentElement $page */
        $page = $this->getMainContext()->getSession()->getPage();
        $element = $page->find('css', $selector);

        assertNotNull($element, sprintf('Element %s not found', $selector));

        $element->click();
    }
}
