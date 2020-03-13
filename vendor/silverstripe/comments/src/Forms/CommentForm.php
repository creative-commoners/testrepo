<?php

namespace SilverStripe\Comments\Forms;

use SilverStripe\Comments\Controllers\CommentingController;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Security\Security;

class CommentForm extends Form
{
    /**
     * @param string $name
     * @param CommentingController $controller
     */
    public function __construct($name, CommentingController $controller)
    {
        $usePreview = $controller->getOption('use_preview');
        $nameRequired = _t('CommentInterface.YOURNAME_MESSAGE_REQUIRED', 'Please enter your name');
        $emailRequired = _t('CommentInterface.EMAILADDRESS_MESSAGE_REQUIRED', 'Please enter your email address');
        $emailInvalid = _t('CommentInterface.EMAILADDRESS_MESSAGE_EMAIL', 'Please enter a valid email address');
        $urlInvalid = _t('CommentInterface.COMMENT_MESSAGE_URL', 'Please enter a valid URL');
        $commentRequired = _t('CommentInterface.COMMENT_MESSAGE_REQUIRED', 'Please enter your comment');

        $fields = FieldList::create(
            $dataFields = CompositeField::create(
                // Name
                $a = TextField::create('Name', _t('CommentInterface.YOURNAME', 'Your name'))
                    ->setCustomValidationMessage($nameRequired)
                    ->setAttribute('data-msg-required', $nameRequired),
                // Email
                EmailField::create(
                    'Email',
                    _t(
                        'SilverStripe\\Comments\\Controllers\\CommentingController.EMAILADDRESS',
                        'Your email address (will not be published)'
                    )
                )
                    ->setCustomValidationMessage($emailRequired)
                    ->setAttribute('data-msg-required', $emailRequired)
                    ->setAttribute('data-msg-email', $emailInvalid)
                    ->setAttribute('data-rule-email', true),
                // Url
                TextField::create('URL', _t(
                    'SilverStripe\\Comments\\Controllers\\CommentingController.WEBSITEURL',
                    'Your website URL'
                ))
                    ->setAttribute('data-msg-url', $urlInvalid)
                    ->setAttribute('data-rule-url', true),
                // Comment
                TextareaField::create('Comment', _t(
                    'SilverStripe\\Comments\\Controllers\\CommentingController.COMMENTS',
                    'Comments'
                ))
                    ->setCustomValidationMessage($commentRequired)
                    ->setAttribute('data-msg-required', $commentRequired)
            ),
            HiddenField::create('ParentID'),
            HiddenField::create('ParentClassName'),
            HiddenField::create('ReturnURL'),
            HiddenField::create('ParentCommentID')
        );

        // Preview formatted comment. Makes most sense when shortcodes or
        // limited HTML is allowed. Populated by JS/Ajax.
        if ($usePreview) {
            $fields->insertAfter(
                ReadonlyField::create('PreviewComment', _t('CommentInterface.PREVIEWLABEL', 'Preview'))
                    ->setAttribute('style', 'display: none'), // enable through JS
                'Comment'
            );
        }

        $dataFields->addExtraClass('data-fields');

        // save actions
        $actions = FieldList::create(
            $postAction = new FormAction('doPostComment', _t('CommentInterface.POST', 'Post'))
        );

        if ($usePreview) {
            $actions->push(
                FormAction::create('doPreviewComment', _t('CommentInterface.PREVIEW', 'Preview'))
                    ->addExtraClass('action-minor')
                    ->setAttribute('style', 'display: none') // enable through JS
            );
        }

        $required = RequiredFields::create(
            $controller->config()->required_fields
        );

        parent::__construct($controller, $name, $fields, $actions, $required);


        // if the record exists load the extra required data
        if ($record = $controller->getOwnerRecord()) {
            // Load member data
            $member = Security::getCurrentUser();
            if (($record->CommentsRequireLogin || $record->PostingRequiredPermission) && $member) {
                $fields = $this->Fields();

                $fields->removeByName('Name');
                $fields->removeByName('Email');
                $fields->insertBefore(
                    ReadonlyField::create(
                        'NameView',
                        _t('CommentInterface.YOURNAME', 'Your name'),
                        $member->getName()
                    ),
                    'URL'
                );
                $fields->push(HiddenField::create('Name', '', $member->getName()));
                $fields->push(HiddenField::create('Email', '', $member->Email));
            }

            // we do not want to read a new URL when the form has already been submitted
            // which in here, it hasn't been.
            $this->loadDataFrom([
                'ParentID'        => $record->ID,
                'ReturnURL'       => $controller->getRequest()->getURL(),
                'ParentClassName' => $controller->getParentClass()
            ]);
        }

        // Set it so the user gets redirected back down to the form upon form fail
        $this->setRedirectToFormOnValidationError(true);

        // load any data from the session
        $data = $this->getSessionData();
        if (!is_array($data)) {
            return;
        }

        // load user data from previous form request back into form.
        if (array_key_exists('UserData', $data)) {
            $formData = json_decode($data['UserData'], true);

            $this->loadDataFrom([
                'Name' => isset($formData['Name']) ? $formData['Name'] : '',
                'URL' => isset($formData['URL']) ? $formData['URL'] : '',
                'Email' => isset($formData['Email']) ? $formData['Email'] : ''
            ]);
        }

        // allow previous value to fill if comment
        if (array_key_exists('Comment', $data)) {
            $prevComment = $data['Comment'];

            if ($prevComment && $prevComment != '') {
                $this->loadDataFrom(['Comment' => $prevComment]);
            }
        }
    }

    /**
     * @param  array $data
     * @param  Form $form
     * @return HTTPResponse
     */
    public function doPreviewComment($data, $form)
    {
        $data['IsPreview'] = 1;

        return $this->doPostComment($data, $form);
    }

    /**
     * Process which creates a {@link Comment} once a user submits a comment from this form.
     *
     * @param  array $data
     * @param  Form $form
     * @return HTTPResponse
     */
    public function doPostComment($data, $form)
    {
        // Load class and parent from data
        if (isset($data['ParentClassName'])) {
            $this->controller->setParentClass($data['ParentClassName']);
        }
        if (isset($data['ParentID']) && ($class = $this->controller->getParentClass())) {
            $this->controller->setOwnerRecord($class::get()->byID($data['ParentID']));
        }
        if (!$this->controller->getOwnerRecord()) {
            return $this->getRequestHandler()->httpError(404);
        }

        // cache users data
        $form->setSessionData([
            'UserData' => json_encode($data),
            'Comment' =>  $data['Comment']
        ]);

        // extend hook to allow extensions. Also see onAfterPostComment
        $this->controller->extend('onBeforePostComment', $form);

        // If commenting can only be done by logged in users, make sure the user is logged in
        if (!$this->controller->getOwnerRecord()->canPostComment()) {
            return Security::permissionFailure(
                $this->controller,
                _t(
                    'SilverStripe\\Comments\\Controllers\\CommentingController.PERMISSIONFAILURE',
                    "You're not able to post comments to this page. Please ensure you are logged in and have an "
                    . 'appropriate permission level.'
                )
            );
        }

        if ($member = Security::getCurrentUser()) {
            $form->Fields()->push(HiddenField::create('AuthorID', 'Author ID', $member->ID));
        }

        // What kind of moderation is required?
        switch ($this->controller->getOwnerRecord()->ModerationRequired) {
            case 'Required':
                $requireModeration = true;
                break;
            case 'NonMembersOnly':
                $requireModeration = empty($member);
                break;
            case 'None':
            default:
                $requireModeration = false;
                break;
        }

        $comment = Comment::create();
        $form->saveInto($comment);

        $comment->ParentID = $data['ParentID'];
        $comment->ParentClass = $data['ParentClassName'];

        $comment->AllowHtml = $this->controller->getOption('html_allowed');
        $comment->Moderated = !$requireModeration;

        // Save into DB, or call pre-save hooks to give accurate preview
        $usePreview = $this->controller->getOption('use_preview');
        $isPreview = $usePreview && !empty($data['IsPreview']);
        if ($isPreview) {
            $comment->extend('onBeforeWrite');
        } else {
            $comment->write();

            // extend hook to allow extensions. Also see onBeforePostComment
            $this->controller->extend('onAfterPostComment', $comment);
        }

        // we want to show a notification if comments are moderated
        if ($requireModeration && !$comment->IsSpam) {
            $this->getRequest()->getSession()->set('CommentsModerated', 1);
        }

        // clear the users comment since the comment was successful.
        if ($comment->exists()) {
            // Remove the comment data as it's been saved already.
            unset($data['Comment']);
        }

        // cache users data (name, email, etc to prepopulate on other forms).
        $form->setSessionData([
            'UserData' => json_encode($data),
        ]);

        // Find parent link
        if (!empty($data['ReturnURL'])) {
            $url = $data['ReturnURL'];
        } elseif ($parent = $comment->Parent()) {
            $url = $parent->Link();
        } else {
            return $this->controller->redirectBack();
        }

        // Given a redirect page exists, attempt to link to the correct anchor
        if ($comment->IsSpam) {
            // Link to the form with the error message contained
            $hash = $form->FormName();
        } elseif (!$comment->Moderated) {
            // Display the "awaiting moderation" text
            $hash = 'moderated';
        } else {
            // Link to the moderated, non-spam comment
            $hash = $comment->Permalink();
        }

        return $this->controller->redirect(Controller::join_links($url, "#{$hash}"));
    }
}
