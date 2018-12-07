<?php

use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;

class FormController extends Controller
{
    /**
     * An array of actions that can be accessed via a request. Each array element should be an action name, and the
     * permissions or conditions required to allow the user to access it.
     *
     * <code>
     * [
     *     'action', // anyone can access this action
     *     'action' => true, // same as above
     *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
     *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
     * ];
     * </code>
     *
     * @var array
     */
    private static $allowed_actions = [
        'index',
        'Form'
    ];

    private static $url_rule = '/$Action/$ID/$OtherID';

    private static $url_segment = 'testform';

    protected function init()
    {
        parent::init();
        // You can include any CSS or JS required by your project here.
        // See: https://docs.silverstripe.org/en/developer_guides/templates/requirements/
    }

    public function index()
    {
        return $this->renderWith('Page', ['Title' => 'Test Form']);
    }

    public function Form()
    {
        $fields = FieldList::create(
            TextField::create('Title'),
            TextField::create('Description'),
            TextField::create('Category'),
            TextField::create('BirthYear')
        );
        $actions = FieldList::create(FormAction::create('doSubmit', 'Submit'));
        return new Form($this, 'Form', $fields, $actions);
    }

    public function doSubmit(array $data, Form $form)
    {
        $foobar = new FooBarObject($data);
        $foobar->write();

        $foobar = FooBarObject::get()->byID($foobar->ID);

        $response = $this->renderWith('Page', ['Title' => 'Test Form', 'Content' => $foobar, 'Form' => '']);
        $foobar->delete();

        return $response;
    }


}

