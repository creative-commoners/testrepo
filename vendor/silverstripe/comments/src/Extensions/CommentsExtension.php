<?php

namespace SilverStripe\Comments\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Comments\Admin\CommentsGridField;
use SilverStripe\Comments\Admin\CommentsGridFieldConfig;
use SilverStripe\Comments\Controllers\CommentingController;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

/**
 * Extension to {@link DataObject} to enable tracking comments.
 *
 * @package comments
 */
class CommentsExtension extends DataExtension
{
    /**
     * Default configuration values
     *
     * enabled:                     Allows commenting to be disabled even if the extension is present
     * enabled_cms:                 Allows commenting to be enabled or disabled via the CMS
     * require_login:               Boolean, whether a user needs to login (required for required_permission)
     * require_login_cms:           Allows require_login to be set via the CMS
     * required_permission:         Permission (or array of permissions) required to comment
     * include_js:                  Enhance operation by ajax behaviour on moderation links (required for use_preview)
     * use_gravatar:                Set to true to show gravatar icons
     * gravatar_default:            Theme for 'not found' gravatar {@see http://gravatar.com/site/implement/images}
     * gravatar_rating:             Gravatar rating (same as the standard default)
     * show_comments_when_disabled: Show older comments when commenting has been disabled.
     * order_comments_by:           Default sort order.
     * order_replies_by:            Sort order for replies.
     * comments_holder_id:          ID for the comments holder
     * comment_permalink_prefix:    ID prefix for each comment
     * require_moderation:          Require moderation for all comments
     * require_moderation_cms:      Ignore other comment moderation config settings and set via CMS
     * frontend_moderation:         Display unmoderated comments in the frontend, if the user can moderate them.
     * frontend_spam:               Display spam comments in the frontend, if the user can moderate them.
     * html_allowed:                Allow for sanitized HTML in comments
     * use_preview:                 Preview formatted comment (when allowing HTML)
     * nested_comments:             Enable nested comments
     * nested_depth:                Max depth of nested comments in levels (where root is 1 depth) 0 means no limit.
     *
     * @var array
     *
     * @config
     */
    private static $comments = [
        'enabled' => true,
        'enabled_cms' => false,
        'require_login' => false,
        'require_login_cms' => false,
        'required_permission' => false,
        'include_js' => true,
        'use_gravatar' => false,
        'gravatar_size' => 80,
        'gravatar_default' => 'identicon',
        'gravatar_rating' => 'g',
        'show_comments_when_disabled' => false,
        'order_comments_by' => '"Created" DESC',
        'order_replies_by' => false,
        'comments_per_page' => 10,
        'comments_holder_id' => 'comments-holder',
        'comment_permalink_prefix' => 'comment-',
        'require_moderation' => false,
        'require_moderation_nonmembers' => false,
        'require_moderation_cms' => false,
        'frontend_moderation' => false,
        'frontend_spam' => false,
        'html_allowed' => false,
        'html_allowed_elements' => ['a', 'img', 'i', 'b'],
        'use_preview' => false,
        'nested_comments' => false,
        'nested_depth' => 2,
    ];

    /**
     * @var array
     */
    private static $db = [
        'ProvideComments' => 'Boolean',
        'ModerationRequired' => 'Enum(\'None,Required,NonMembersOnly\',\'None\')',
        'CommentsRequireLogin' => 'Boolean',
    ];

    /**
     * {@inheritDoc}
     */
    private static $has_many = [
        'Commments' => Comment::class . '.Parent'
    ];

    /**
     * CMS configurable options should default to the config values, but respect
     * default values specified by the object
     */
    public function populateDefaults()
    {
        $defaults = $this->owner->config()->get('defaults');

        // Set if comments should be enabled by default
        if (isset($defaults['ProvideComments'])) {
            $this->owner->ProvideComments = $defaults['ProvideComments'];
        } else {
            $this->owner->ProvideComments = $this->owner->getCommentsOption('enabled') ? 1 : 0;
        }

        // If moderation options should be configurable via the CMS then
        if (isset($defaults['ModerationRequired'])) {
            $this->owner->ModerationRequired = $defaults['ModerationRequired'];
        } elseif ($this->owner->getCommentsOption('require_moderation')) {
            $this->owner->ModerationRequired = 'Required';
        } elseif ($this->owner->getCommentsOption('require_moderation_nonmembers')) {
            $this->owner->ModerationRequired = 'NonMembersOnly';
        } else {
            $this->owner->ModerationRequired = 'None';
        }

        // Set login required
        if (isset($defaults['CommentsRequireLogin'])) {
            $this->owner->CommentsRequireLogin = $defaults['CommentsRequireLogin'];
        } else {
            $this->owner->CommentsRequireLogin = $this->owner->getCommentsOption('require_login') ? 1 : 0;
        }
    }


    /**
     * If this extension is applied to a {@link SiteTree} record then
     * append a Provide Comments checkbox to allow authors to trigger
     * whether or not to display comments
     *
     * @todo Allow customization of other {@link Commenting} configuration
     *
     * @param FieldList $fields
     */
    public function updateSettingsFields(FieldList $fields)
    {
        $options = FieldGroup::create()->setTitle(_t(__CLASS__ . '.COMMENTOPTIONS', 'Comments'));

        // Check if enabled setting should be cms configurable
        if ($this->owner->getCommentsOption('enabled_cms')) {
            $options->push(CheckboxField::create('ProvideComments', _t(
                'SilverStripe\\Comments\\Model\\Comment.ALLOWCOMMENTS',
                'Allow comments'
            )));
        }

        // Check if we should require users to login to comment
        if ($this->owner->getCommentsOption('require_login_cms')) {
            $options->push(
                CheckboxField::create(
                    'CommentsRequireLogin',
                    _t('Comments.COMMENTSREQUIRELOGIN', 'Require login to comment')
                )
            );
        }

        if ($options->FieldList()->count()) {
            if ($fields->hasTabSet()) {
                $fields->addFieldsToTab('Root.Settings', $options);
            } else {
                $fields->push($options);
            }
        }

        // Check if moderation should be enabled via cms configurable
        if ($this->owner->getCommentsOption('require_moderation_cms')) {
            $moderationField = DropdownField::create(
                'ModerationRequired',
                _t(
                    __CLASS__ . '.COMMENTMODERATION',
                    'Comment Moderation'
                ),
                [
                    'None' => _t(__CLASS__ . '.MODERATIONREQUIRED_NONE', 'No moderation required'),
                    'Required' => _t(__CLASS__ . '.MODERATIONREQUIRED_REQUIRED', 'Moderate all comments'),
                    'NonMembersOnly' => _t(
                        __CLASS__ . '.MODERATIONREQUIRED_NONMEMBERSONLY',
                        'Only moderate non-members'
                    ),
                ]
            );
            if ($fields->hasTabSet()) {
                $fields->addFieldToTab('Root.Settings', $moderationField);
            } else {
                $fields->push($moderationField);
            }
        }
    }

    /**
     * Get comment moderation rules for this parent
     *
     * None:           No moderation required
     * Required:       All comments
     * NonMembersOnly: Only anonymous users
     *
     * @return string
     */
    public function getModerationRequired()
    {
        if ($this->owner->getCommentsOption('require_moderation_cms')) {
            return $this->owner->getField('ModerationRequired');
        }

        if ($this->owner->getCommentsOption('require_moderation')) {
            return 'Required';
        }

        if ($this->owner->getCommentsOption('require_moderation_nonmembers')) {
            return 'NonMembersOnly';
        }

        return 'None';
    }

    /**
     * Determine if users must be logged in to post comments
     *
     * @return boolean
     */
    public function getCommentsRequireLogin()
    {
        if ($this->owner->getCommentsOption('require_login_cms')) {
            return (bool) $this->owner->getField('CommentsRequireLogin');
        }
        return (bool) $this->owner->getCommentsOption('require_login');
    }

    /**
     * Returns the RelationList of all comments against this object. Can be used as a data source
     * for a gridfield with write access.
     *
     * @return DataList
     */
    public function AllComments()
    {
        $order = $this->owner->getCommentsOption('order_comments_by');
        $comments = Comment::get()
            ->filter([
                'ParentID' => $this->owner->ID,
                'ParentClass' => $this->owner->ClassName,
            ])
            ->sort($order);
        $this->owner->extend('updateAllComments', $comments);
        return $comments;
    }

    /**
     * Returns all comments against this object, with with spam and unmoderated items excluded, for use in the frontend
     *
     * @return DataList
     */
    public function AllVisibleComments()
    {
        $list = $this->AllComments();

        // Filter spam comments for non-administrators if configured
        $showSpam = $this->owner->getCommentsOption('frontend_spam') && $this->owner->canModerateComments();

        if (!$showSpam) {
            $list = $list->filter('IsSpam', 0);
        }

        // Filter un-moderated comments for non-administrators if moderation is enabled
        $showUnmoderated = ($this->owner->ModerationRequired === 'None')
            || ($this->owner->getCommentsOption('frontend_moderation') && $this->owner->canModerateComments());
        if (!$showUnmoderated) {
            $list = $list->filter('Moderated', 1);
        }

        $this->owner->extend('updateAllVisibleComments', $list);
        return $list;
    }

    /**
     * Returns the root level comments, with spam and unmoderated items excluded, for use in the frontend
     *
     * @return DataList
     */
    public function Comments()
    {
        $list = $this->AllVisibleComments();

        // If nesting comments, only show root level
        if ($this->owner->getCommentsOption('nested_comments')) {
            $list = $list->filter('ParentCommentID', 0);
        }

        $this->owner->extend('updateComments', $list);
        return $list;
    }

    /**
     * Returns a paged list of the root level comments, with spam and unmoderated items excluded,
     * for use in the frontend
     *
     * @return PaginatedList
     */
    public function PagedComments()
    {
        $list = $this->Comments();

        // Add pagination
        $list = PaginatedList::create($list, Controller::curr()->getRequest());
        $list->setPaginationGetVar('commentsstart' . $this->owner->ID);
        $list->setPageLength($this->owner->getCommentsOption('comments_per_page'));

        $this->owner->extend('updatePagedComments', $list);
        return $list;
    }

    /**
     * Determine if comments are enabled for this instance
     *
     * @return boolean
     */
    public function getCommentsEnabled()
    {
        // Don't display comments form for pseudo-pages (such as the login form)
        if (!$this->owner->exists()) {
            return false;
        }

        // Determine which flag should be used to determine if this is enabled
        if ($this->owner->getCommentsOption('enabled_cms')) {
            return (bool) $this->owner->ProvideComments;
        }

        return (bool) $this->owner->getCommentsOption('enabled');
    }

    /**
     * Get the HTML ID for the comment holder in the template
     *
     * @return string
     */
    public function getCommentHolderID()
    {
        return $this->owner->getCommentsOption('comments_holder_id');
    }

    /**
     * Permission codes required in order to post (or empty if none required)
     *
     * @return string|array Permission or list of permissions, if required
     */
    public function getPostingRequiredPermission()
    {
        return $this->owner->getCommentsOption('required_permission');
    }

    /**
     * Determine if a user can post comments on this item
     *
     * @param Member $member Member to check
     *
     * @return boolean
     */
    public function canPostComment($member = null)
    {
        // Deny if not enabled for this object
        if (!$this->owner->CommentsEnabled) {
            return false;
        }

        if (!$this->owner->canView($member)) {
            // deny if current user cannot view the underlying record.
            return false;
        }

        // Check if member is required
        $requireLogin = $this->owner->CommentsRequireLogin;
        if (!$requireLogin) {
            return true;
        }

        // Check member is logged in
        $member = $member ?: Security::getCurrentUser();
        if (!$member) {
            return false;
        }

        // If member required check permissions
        $requiredPermission = $this->owner->PostingRequiredPermission;
        if ($requiredPermission && !Permission::checkMember($member, $requiredPermission)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if this member can moderate comments in the CMS
     *
     * @param Member $member
     *
     * @return boolean
     */
    public function canModerateComments($member = null)
    {
        // Deny if not enabled for this object
        if (!$this->owner->CommentsEnabled) {
            return false;
        }

        // Fallback to can-edit
        return $this->owner->canEdit($member);
    }

    /**
     * Gets the RSS link to all comments
     *
     * @return string
     */
    public function getCommentRSSLink()
    {
        return Director::absoluteURL('comments/rss');
    }

    /**
     * Get the RSS link to all comments on this page
     *
     * @return string
     */
    public function getCommentRSSLinkPage()
    {
        return Controller::join_links(
            $this->getCommentRSSLink(),
            str_replace('\\', '-', get_class($this->owner)),
            $this->owner->ID
        );
    }

    /**
     * Comments interface for the front end. Includes the CommentAddForm and the composition
     * of the comments display.
     *
     * To customize the html see templates/CommentInterface.ss or extend this function with
     * your own extension.
     *
     * @todo Cleanup the passing of all this configuration based functionality
     *
     * @see  docs/en/Extending
     */
    public function CommentsForm()
    {
        // Check if enabled
        $enabled = $this->getCommentsEnabled();
        if ($enabled && $this->owner->getCommentsOption('include_js')) {
            Requirements::javascript('//code.jquery.com/jquery-3.3.1.min.js');
            Requirements::javascript('silverstripe/comments:thirdparty/jquery-validate/jquery.validate.min.js');
            Requirements::javascript('silverstripe/admin:client/dist/js/i18n.js');
            Requirements::add_i18n_javascript('silverstripe/comments:client/lang');
            Requirements::javascript('silverstripe/comments:client/dist/js/CommentsInterface.js');
        }

        $controller = CommentingController::create();
        $controller->setOwnerRecord($this->owner);
        $controller->setParentClass($this->owner->getClassName());
        $controller->setOwnerController(Controller::curr());

        $session = Controller::curr()->getRequest()->getSession();
        $moderatedSubmitted = $session->get('CommentsModerated');
        $session->clear('CommentsModerated');

        $form = ($enabled) ? $controller->CommentsForm() : false;

        // a little bit all over the show but to ensure a slightly easier upgrade for users
        // return back the same variables as previously done in comments
        return $this
            ->owner
            ->customise([
                'AddCommentForm' => $form,
                'ModeratedSubmitted' => $moderatedSubmitted,
            ])
            ->renderWith('CommentsInterface');
    }

    /**
     * Returns whether this extension instance is attached to a {@link SiteTree} object
     *
     * @return bool
     */
    public function attachedToSiteTree()
    {
        $class = $this->owner->baseClass();

        return (is_subclass_of($class, SiteTree::class)) || ($class == SiteTree::class);
    }

    /**
     * Get the commenting option for this object.
     *
     * This can be overridden in any instance or extension to customise the
     * option available.
     *
     * @param string $key
     *
     * @return mixed Result if the setting is available, or null otherwise
     */
    public function getCommentsOption($key)
    {
        $settings = $this->getCommentsOptions();
        $value = null;

        if (isset($settings[$key])) {
            $value = $settings[$key];
        }

        // To allow other extensions to customise this option
        if ($this->owner) {
            $this->owner->extend('updateCommentsOption', $key, $value);
        }

        return $value;
    }

    /**
     * @return array
     */
    public function getCommentsOptions()
    {
        if ($this->owner) {
            $settings = $this->owner->config()->get('comments');
        } else {
            $settings = Config::inst()->get(__CLASS__, 'comments');
        }

        return $settings;
    }

    /**
     * Add moderation functions to the current fieldlist
     *
     * @param FieldList $fields
     */
    protected function updateModerationFields(FieldList $fields)
    {
        Requirements::css('silverstripe/comments:client/dist/styles/cms.css');

        $newComments = $this->owner->AllComments()->filter('Moderated', 0);

        $newGrid = CommentsGridField::create(
            'NewComments',
            _t('CommentsAdmin.NewComments', 'New'),
            $newComments,
            CommentsGridFieldConfig::create()
        );

        $approvedComments = $this->owner->AllComments()->filter('Moderated', 1)->filter('IsSpam', 0);

        $approvedGrid = new CommentsGridField(
            'ApprovedComments',
            _t('CommentsAdmin.Comments', 'Approved'),
            $approvedComments,
            CommentsGridFieldConfig::create()
        );

        $spamComments = $this->owner->AllComments()->filter('Moderated', 1)->filter('IsSpam', 1);

        $spamGrid = CommentsGridField::create(
            'SpamComments',
            _t('CommentsAdmin.SpamComments', 'Spam'),
            $spamComments,
            CommentsGridFieldConfig::create()
        );

        $newCount = '(' . count($newComments) . ')';
        $approvedCount = '(' . count($approvedComments) . ')';
        $spamCount = '(' . count($spamComments) . ')';

        if ($fields->hasTabSet()) {
            $tabs = TabSet::create(
                'Comments',
                Tab::create(
                    'CommentsNewCommentsTab',
                    _t('SilverStripe\\Comments\\Admin\\CommentAdmin.NewComments', 'New') . ' ' . $newCount,
                    $newGrid
                ),
                Tab::create(
                    'CommentsCommentsTab',
                    _t('SilverStripe\\Comments\\Admin\\CommentAdmin.Comments', 'Approved') . ' ' . $approvedCount,
                    $approvedGrid
                ),
                Tab::create(
                    'CommentsSpamCommentsTab',
                    _t('SilverStripe\\Comments\\Admin\\CommentAdmin.SpamComments', 'Spam') . ' ' . $spamCount,
                    $spamGrid
                )
            );
            $tabs->setTitle(_t(__CLASS__ . '.COMMENTSTABSET', 'Comments'));

            $fields->addFieldToTab('Root', $tabs);
        } else {
            $fields->push($newGrid);
            $fields->push($approvedGrid);
            $fields->push($spamGrid);
        }
    }

    public function updateCMSFields(FieldList $fields)
    {
        // Disable moderation if not permitted
        if ($this->owner->canModerateComments()) {
            $this->updateModerationFields($fields);
        }

        // If this isn't a page we should merge the settings into the CMS fields
        if (!$this->attachedToSiteTree()) {
            $this->updateSettingsFields($fields);
        }
    }
}
