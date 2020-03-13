<?php

namespace CWP\Search\Extensions;

use CWP\Search\CwpSearchEngine;
use Page;
use SilverStripe\CMS\Search\SearchForm;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\ViewableData_Customised;

/**
 * Adds a search form and handling method to a {@link Controller} instance, using the configured CWP search engine
 */
class SearchControllerExtension extends Extension
{
    use Configurable;

    private static $allowed_actions = [
        'SearchForm',
        'results',
    ];

    /**
     * How many search results should be shown per-page?
     *
     * @config
     * @var int
     */
    private static $results_per_page = 10;

    /**
     * If spelling suggestions for searches are given, enable
     * suggested searches to be followed immediately
     *
     * @config
     * @var bool
     */
    private static $search_follow_suggestions = true;

    /**
     * Which classes should be queried when searching?
     *
     * @config
     * @var array
     */
    private static $classes_to_search = [
        Page::class => [
            'class' => Page::class,
            'includeSubclasses' => true,
        ],
    ];

    /**
     * Site search form
     */
    public function SearchForm()
    {
        $searchText = $this->owner->getRequest()->getVar('Search');

        $fields = FieldList::create(
            TextField::create('Search', false, $searchText)
        );
        $actions = FieldList::create(
            FormAction::create('results', _t('SilverStripe\\CMS\\Search\\SearchForm.GO', 'Go'))
        );

        $form = SearchForm::create($this->owner, SearchForm::class, $fields, $actions);
        $form->setFormAction('search/SearchForm');

        return $form;
    }

    /**
     * Get search form with _header suffix
     *
     * @return SearchForm
     */
    public function HeaderSearchForm()
    {
        return $this->SearchForm()->setTemplate('SearchForm_header');
    }

    /**
     * Process and render search results.
     *
     * @param array $data The raw request data submitted by user
     * @param SearchForm $form The form instance that was submitted
     * @param HTTPRequest $request Request generated for this action
     * @return DBHTMLText
     */
    public function results($data, $form, $request)
    {
        // Check parameters for terms, pagination, and if we should follow suggestions
        $keywords = empty($data['Search']) ? '' : $data['Search'];
        $start = isset($data['start']) ? $data['start'] : 0;
        $suggestions = isset($data['suggestions'])
            ? $data['suggestions']
            : $this->config()->get('search_follow_suggestions');

        $results = CwpSearchEngine::create()
            ->search(
                $keywords,
                $this->config()->get('classes_to_search'),
                Injector::inst()->get(CwpSearchEngine::class . '.search_index'),
                $this->config()->get('results_per_page'),
                $start,
                $suggestions
            );

        // Customise content with these results
        $properties = [
            'MetaTitle' => _t(__CLASS__ . '.MetaTitle', 'Search {keywords}', ['keywords' => $keywords]),
            'NoSearchResults' => _t(__CLASS__ . '.NoResult', 'Sorry, your search query did not return any results.'),
            'EmptySearch' => _t(__CLASS__ . '.EmptySearch', 'Search field empty, please enter your search query.'),
            'PdfLink' => '',
            'Title' => _t('SilverStripe\\CMS\\Search\\SearchForm.SearchResults', 'Search Results'),
        ];
        $this->owner->extend('updateSearchResults', $results, $properties);

        // Customise page
        /** @var ViewableData_Customised $response */
        $response = $this->owner->customise($properties);
        if ($results) {
            $response = $response
                ->customise($results)
                ->customise(['Results' => $results->getResults()]);
        }

        // Render
        $templates = $this->getResultsTemplate($request);
        return $response->renderWith($templates);
    }

    /**
     * Select the template to render search results with
     *
     * @param HTTPRequest $request
     * @return array
     */
    protected function getResultsTemplate($request)
    {
        $templates = [
            Page::class . '_results',
            Page::class
        ];

        if ($request->getVar('format') == 'rss') {
            array_unshift($templates, Page::class . '_results_rss');
        }

        if ($request->getVar('format') == 'atom') {
            array_unshift($templates, Page::class . '_results_atom');
        }

        return $templates;
    }
}
