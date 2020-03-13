<?php

namespace SilverStripe\AssetAdmin\GraphQL;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\EnumType;
use SilverStripe\AssetAdmin\Controller\AssetAdminFile;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\GraphQL\TypeCreator;
use SilverStripe\Assets\File;
use SilverStripe\ORM\Filterable;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Assets\Folder;
use SilverStripe\Forms\DateField;

class FileFilterInputTypeCreator extends TypeCreator
{

    protected $inputObject = true;

    public function attributes()
    {
        return [
            'name' => 'FileFilterInput',
            'description' => 'Input type for a File type',
        ];
    }

    public function fields()
    {
        $categoryValues = array_map(function ($category) {
            return ['value' => $category];
        }, File::config()->app_categories);

        // Sanitise GraphQL Enum aliases (some contain slashes)
        foreach ($categoryValues as $key => $v) {
            unset($categoryValues[$key]);
            $newKey = strtoupper(preg_replace('/[^[[:alnum:]]]*/', '', $key));
            $categoryValues[$newKey] = $v;
        }

        return [
            'id' => [
                'type' => Type::id(),
            ],
            'anyChildId' => [
                'type' => Type::id(),
                'description' => 'Identiies this file by the id of any immediate child'
            ],
            'parentId' => [
                'type' => Type::id(),
            ],
            'name' => [
                'type' => Type::string(),
                'description' => 'Searches both name and title fields with a partial match'
            ],
            'lastEditedFrom' => [
                'type' => Type::string(),
                'description' => 'Date in ISO format (YYYY-mm-dd)'
            ],
            'lastEditedTo' => [
                'type' => Type::string(),
                'description' => 'Date in ISO format (YYYY-mm-dd)'
            ],
            'appCategory' => [
                'type' => new EnumType([
                    'name' => 'AppCategory',
                    'values' => $categoryValues
                ]),
                'description' => 'Date in ISO format (YYYY-mm-dd)'
            ],
            'recursive' => [
                'type' => Type::boolean(),
                'description' => 'Find all descendants of "parentId" (not only direct children). '
                    . 'Caution: Only works with parentId=0 at the moment.'
            ]
        ];
    }

    /**
     * Caution: Does NOT enforce canView permissions
     *
     * @param Filterable $list
     * @param array $filter
     * @return Filterable
     */
    public function filterList(Filterable $list, $filter)
    {
        // ID filtering
        if (isset($filter['id']) && (int)$filter['id'] > 0) {
            $list = $list->filter('ID', $filter['id']);
            
            if ($list->count() === 0) {
                throw new HTTPResponse_Exception(_t(
                    __CLASS__ . '.FileNotFound',
                    'File or Folder could not be found'
                ));
            }
        } elseif (isset($filter['id']) && (int)$filter['id'] === 0) {
            // Special case for root folder
            $list = new ArrayList([new Folder([
                'ID' => 0,
            ])]);
        }

        // track if search is being applied
        $search = false;

        // Optionally limit search to a folder, supporting recursion
        if (isset($filter['parentId'])) {
            $recursive = !empty($filter['recursive']);

            if (!$recursive) {
                $list = $list->filter('ParentID', $filter['parentId']);
            } elseif ($filter['parentId']) {
                // Note: Simplify parentID = 0 && recursive to no filter at all
                $parents = AssetAdminFile::nestedFolderIDs($filter['parentId']);
                $list = $list->filter('ParentID', $parents);
            }
            $search = true;
        }

        if (!empty($filter['name'])) {
            $list = $list->filterAny(array(
                'Name:PartialMatch' => $filter['name'],
                'Title:PartialMatch' => $filter['name']
            ));
            $search = true;
        }

        // Date filtering last edited
        if (!empty($filter['lastEditedFrom'])) {
            $fromDate = new DateField(null, null, $filter['lastEditedFrom']);
            $list = $list->filter("LastEdited:GreaterThanOrEqual", $fromDate->dataValue().' 00:00:00');
            $search = true;
        }
        if (!empty($filter['lastEditedTo'])) {
            $toDate = new DateField(null, null, $filter['lastEditedTo']);
            $list = $list->filter("LastEdited:LessThanOrEqual", $toDate->dataValue().' 23:59:59');
            $search = true;
        }

        // Date filtering created
        if (!empty($filter['createdFrom'])) {
            $fromDate = new DateField(null, null, $filter['createdFrom']);
            $list = $list->filter("Created:GreaterThanOrEqual", $fromDate->dataValue().' 00:00:00');
            $search = true;
        }
        if (!empty($filter['createdTo'])) {
            $toDate = new DateField(null, null, $filter['createdTo']);
            $list = $list->filter("Created:LessThanOrEqual", $toDate->dataValue().' 23:59:59');
            $search = true;
        }

        // Categories (mapped to extensions through the enum type automatically)
        if (!empty($filter['appCategory'])) {
            $list = $list->filter('Name:EndsWith', $filter['appCategory']);
            $search = true;
        }

        // Filter unknown id by known child if search is not applied
        if (!$search && isset($filter['anyChildId'])) {
            /** @var File $child */
            $child = File::get()->byID($filter['anyChildId']);
            $id = $child ? ($child->ParentID ?: 0) : 0;
            if ($id) {
                $list = $list->filter('ID', $id);
            } else {
                // Special case for root folder, since filter by ID = 0 will return an empty list
                $list = new ArrayList([new Folder([
                    'ID' => 0,
                ])]);
            }
        }

        return $list;
    }
}
