# CWP 2.0 test environment

## Summary

This environment contains a CWP 2.x-dev recipe.

## Documentation

For general SilverStripe CMS user help, see https://userhelp.silverstripe.org.

For general CWP developer documentation see https://www.cwp.govt.nz/developer-docs/, and for general CWP user help
see https://www.cwp.govt.nz/working-with-cwp/content-management-system-guides/. 

## High level tests

### CMS

* Create a page, enter or edit some content, save and publish it
* Create child pages underneath any other page
* Ensure that images entered into an UploadField on a page (use BlogPost as an example for this) are automatically
  published and visible on the frontend when the page is published
* While editing a page, check that you can:
  * Modify the URL segment
  * Link to external pages
  * Link to pages on the site
  * Link to an anchor on another page in the site
  * Link to an email address
  * Link to a file in the "Files" section of the admin area
  * Embed media from a URL
  * Embed files from the "Files" section of the admin area, and;
  * Upload and embed new files
  * Add some metadata and it gets used in the rendered HTML on the frontend
  * Change the page type
  * Change permission levels for who can view and edit the page, at an individual page level
* Check that you can drag and drop to reorder pages, and change nesting levels for pages
* Check that when restructuring the site tree, modified pages are marked as modified (orange dot icon indicator)
* Check that you can view older history for a page (note that this will not work for content blocks pages at present),
  compare differences between versions and revert back to older versions
* Ensure you can unpublish a page, and that it restores the frontend version to the draft state (note: if you are
  logged into the CMS you'll still see draft content)
* Ensure you can archive a page
* Duplicate a page that has children and check that the child pages are also duplicated

### Files

* From the "Files" section, check that you can navigate the folder and file structure and view details for the files
* Check that you can upload new images or documents, and that you can upload images in bulk
* Edit the name or other details for an image and check that saving it creates new draft/modified version
* Publishing the image will result in the draft changes propagating to any frontend pages that use the image/file
* Toggle between list and grid views, and change sort orders  
* (Grid view only) Dragging and selecting multiple files presents an actions toolbar from which you can perform various
  actions on the selected files
* Changing the permission levels for "who can view this file?" and "who can edit this file?" allows you to control who
  can view or edit the file (ensure you publish it after modifying the settings)
* Searching for files returns the results you expect

### Campaigns

* You can create a new campaign from the "Campaigns" admin area
* You can add modified pages and files to a campaign
* Publishing the campaign will also publish all resources that are assigned to it at the same time

### Security

* Create a user
* Create a user group
* Assign various permission levels to the user group
* Add or remove users from a user group
* Log into the CMS as one of these users and check that the permissions assigned are respected, e.g. if a user group
  only has access to one area of the CMS then that is all they can actually access
* Click on your CMS username in the top left corner to navigate to your profile, change your password to something new
* Ensure that you can't re-use a password that you've already used (see note on next line)
* Ensure that password complexity criteria are met (in this demo it requires a minimum of 8 characters, it checks 6
  historical passwords for re-use and it must contain 3 out of 4 of lowercase, uppercase, digits and punctuation)
* Check that you can view the permission levels that your user account has associated to it via roles or groups in the
  "Permissions" tab of your profile

### Blog

* Create a blog page at any level in the site hierarchy
* Check that you can't create a blog post unless it's a child of a blog page type
* Assign tags and categories to a blog or blog post
* Add blog widgets to the parent blog page (note: uncheck "inherit sidebar from parent" for them to display) and check
  it works on the frontend
* Post comments on a blog or blog post (frontend)
* In the CMS, check that you can approve, spam or delete comments that have been posted on a page
* Upload a featured image onto a blog post, publish the post and ensure that the image has also been published
* Assign a specific author access to a blog, and check that the user can only access that blog

### Content blocks

* Content blocks CMS transformations are only applied to page types that are defined (by default: Blocks Page)
* Normal CMS pages are still managed in the regular manner
* Check that you can add a Content Block, a Banner Block and a File Block
* Check that any files attached to a File or Banner Block are published when the block is published, and are visible
  on the frontend

### Fluent/translations

* Create some new fluent locales
* Translate existing pages in various locales
* Check that content is only visible on the frontend in a given locale if it has been published in that locale from
  the CMS

### Subsites

* Create some new subsites
* Switch between subsites via the top left subsite dropdown selector
* Check that the CMS site tree is filtered by subsite
* In "Files" if you set a subsite in a file or folder's detail screen and save, check that it doesn't show up in other
  subsites

### Queued jobs

* Check that various user groups that have permission can access the "Jobs" section of the admin area
* Create a new job, e.g. a "dummy job"
* Execute the job
* Delete a job
* If possible, pause a job, or resume execution of a stalled job

### Site configuration

* Change the site name and tagline via "Settings" in the admin area
* Set "who can view pages on this site?" to a setting other than "Anyone" and check that it is honoured
* Set "Who can edit pages on this site?" to a specific user group, and check that it is honoured
* Set "who can create pages in the root of the site?" to a specific user group and check that it is honoured
* (CWP only) Set a Facebook and Twitter profile username and check that it is rendered on the frontend in the site
  footer

### Content review

* In the "Settings" section of the admin area, add your username to the list of users that are responsible for reviews
* For a page, set the Content Review schedule to yesterday (this may require a couple of saves, since it's not "happy
  path" behaviour) and make a page edit. Check that the alert bell icon in the CMS actions toolbar (at the bottom) shows
  and that clicking on it opens a content review modal window.
* Check that the above dialog works, and that the review you entered then shows up in the list of reviews for the page
* Check that following the above process creates a Content Review Notification Job in the "Jobs" section of the admin
  area
* Check that manually executing this job will trigger a notification email sent to your email address 

### Sharing draft content

* When viewing a page in the CMS that has not been published yet, click on "Share draft" in the bottom left to get a
  link. Check that this link can render the latest draft version of the page for anyone (unauthenticated) who has it.
* Other than the page that has been shared, ensure that you cannot see any other draft content from the site in this
  token based preview state

### Taxonomy

_Note: The "Taxonomy" admin area section is an example of a ModelAdmin that manages unversioned DataObjects._

* Create some taxonomy terms and types
* Create children for them
* Assign them to pages via the "Tags" tab (CWP only)

### Reports

* View any of the reports in the CMS
* Filter/search report data
* Export to CSV and/or render the "print" view

### User defined forms

* Create a User Defined Form page and add some fields to it
* Add multiple pages to the form fields and check that the frontend renders them correctly into each page, and that
  you can navigate between the pages
* Ensure required fields are validated for input, minimum and maximum length etc, and that custom error messages
  defined on form fields are shown when a validation error occurs on the field
* Assign yourself as an email recipient for submissions in a user defined form and check that you get an email when a
  submission is made
* Duplicate a user defined form page and check that all form fields and form field options (for multiple select fields)
  are also duplicated and associated to the new page

### Frontend search

_Note: Only if search is enabled, for example on the CWP platform._

* Check that searching for a term returns pages that contain the term in a title, content field, etc, or that a page
  with content blocks that match the term are returned
* Search for a misspelt term and check that the Solr search engine suggests the correct spelling for it

### General test variations

The above tests can be performed with the following variations:

* A different subsite (silverstripe/subsites)
* A different locale (tractorcow/silverstripe-fluent)
* A different CMS user (silverstripe/framework)
* With or without a workflow (symbiote/silverstripe-advancedworkflow) applied to the page

## Components

This contains the following PHP packages, libraries and SilverStripe
modules:

* asyncphp/doorman
* colymba/gridfield-bulk-editing-tools
* composer/ca-bundle
* composer/installers
* cwp/cwp
* cwp/cwp-core
* cwp/cwp-pdfexport
* cwp/cwp-recipe-cms
* cwp/cwp-recipe-core
* cwp/cwp-recipe-search
* cwp/cwp-search
* cwp/starter-theme
* dnadesign/silverstripe-elemental
* doctrine/instantiator
* embed/embed
* guzzlehttp/psr7
* intervention/image
* ivopetkov/html5-dom-document-php
* jeremeamia/SuperClosure
* league/flysystem
* m1/env
* marcj/topsort
* monolog/monolog
* nikic/php-parser
* paragonie/random_compat
* phpdocumentor/reflection-common
* phpdocumentor/reflection-docblock
* phpdocumentor/type-resolver
* phpspec/prophecy
* psr/cache
* psr/container
* psr/http-message
* psr/log
* psr/simple-cache
* ptcinc/solr-php-client
* sebastian/comparator
* sebastian/recursion-context
* silverstripe/admin
* silverstripe/akismet
* silverstripe/asset-admin
* silverstripe/assets
* silverstripe/auditor
* silverstripe/blog
* silverstripe/campaign-admin
* silverstripe/cms
* silverstripe/comment-notifications
* silverstripe/comments
* silverstripe/config
* silverstripe/content-widget
* silverstripe/contentreview
* silverstripe/documentconverter
* silverstripe/elemental-blocks
* silverstripe/environmentcheck
* silverstripe/errorpage
* silverstripe/externallinks
* silverstripe/framework
* silverstripe/fulltextsearch
* silverstripe/graphql
* silverstripe/html5
* silverstripe/hybridsessions
* silverstripe/iframe
* silverstripe/lumberjack
* silverstripe/mimevalidator
* silverstripe/recipe-authoring-tools
* silverstripe/recipe-blog
* silverstripe/recipe-cms
* silverstripe/recipe-collaboration
* silverstripe/recipe-content-blocks
* silverstripe/recipe-core
* silverstripe/recipe-form-building
* silverstripe/recipe-plugin
* silverstripe/recipe-reporting-tools
* silverstripe/recipe-services
* silverstripe/registry
* silverstripe/reports
* silverstripe/restfulserver
* silverstripe/securityreport
* silverstripe/segment-field
* silverstripe/sharedraftcontent
* silverstripe/siteconfig
* silverstripe/sitewidecontent-report
* silverstripe/spamprotection
* silverstripe/spellcheck
* silverstripe/subsites
* silverstripe/tagfield
* silverstripe/taxonomy
* silverstripe/userforms
* silverstripe/vendor-plugin
* silverstripe/versioned
* silverstripe/versionfeed
* silverstripe/widgets
* swiftmailer/swiftmailer
* symbiote/silverstripe-advancedworkflow
* symbiote/silverstripe-gridfieldextensions
* symbiote/silverstripe-queuedjobs
* symfony/cache
* symfony/config
* symfony/filesystem
* symfony/finder
* symfony/polyfill-apcu
* symfony/polyfill-mbstring
* symfony/polyfill-php56
* symfony/polyfill-util
* symfony/process
* symfony/translation
* symfony/yaml
* tijsverkoyen/akismet
* tractorcow/classproxy
* tractorcow/silverstripe-fluent
* tractorcow/silverstripe-proxy-db

For information on any of these see [Packagist.org](https://packagist.org).
