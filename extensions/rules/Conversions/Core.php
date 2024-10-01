<?php
/**
 * @brief        Rules conversions: Core
 * @package        Rules for IPS Social Suite
 * @since        20 Mar 2015
 * @version        SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\rules\Conversions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * @brief    Rules conversions extension: Core
 */
class _Core
{

    /**
     * Global Arguments
     *
     * Let rules know about any global arguments that can be used
     *
     * @return    array        Array of global argument definitions
     */
    public function globalArguments()
    {
        return [
            'site_settings' => [
                'token' => 'site',
                'argtype' => 'object',
                'class' => '\IPS\Settings',
                'getArg' => function () {
                    return \IPS\Settings::i();
                },
            ],
            'logged_in_member' => [
                'token' => 'user',
                'description' => 'the currently logged in user',
                'argtype' => 'object',
                'class' => '\IPS\Member',
                'nullable' => true,
                'getArg' => function () {
                    return \IPS\Member::loggedIn();
                },
            ],
            'current_time' => [
                'token' => 'time',
                'description' => 'the current time',
                'argtype' => 'object',
                'class' => '\IPS\DateTime',
                'getArg' => function () {
                    return \IPS\DateTime::ts(time());
                },
            ],
            'request_url' => [
                'token' => 'url',
                'description' => 'the request url',
                'argtype' => 'object',
                'class' => '\IPS\Http\Url',
                'getArg' => function () {
                    return \IPS\Request::i()->url();
                },
            ],
        ];
    }

    /**
     * Conversion Map
     *
     * Let's rules know how to convert objects into different types of arguments
     *
     * @return    array        Array of conversion definitions
     */
    public function conversionMap()
    {
        $map = [
            '\IPS\Member' => [
                'Name' => [
                    'token' => 'name',
                    'description' => 'User name',
                    'argtype' => 'string',
                    'converter' => function ($member) {
                        $name = (string)$member->name;
                        \IPS\Lang::load(\IPS\Lang::defaultLanguage())->parseOutputForDisplay($name);
                        return $name;
                    },
                ],
                'Name Link' => [
                    'token' => 'namelink',
                    'description' => 'User name linked to profile',
                    'argtype' => 'string',
                    'converter' => function ($member) {
                        return "<a href='" . $member->url() . "' data-ipsHover data-ipsHover-target='" . $member->url(
                            )->setQueryString(['do' => 'hovercard']) . "'>{$member->name}</a>";
                    },
                ],
                'Email' => [
                    'token' => 'email',
                    'description' => 'User email address',
                    'argtype' => 'string',
                    'converter' => function ($member) {
                        return $member->email;
                    },
                ],
                'Member Title' => [
                    'token' => 'title',
                    'description' => 'User title',
                    'argtype' => 'string',
                    'converter' => function ($member) {
                        return (string)$member->member_title;
                    },
                ],
                'Content Count' => [
                    'token' => 'posts',
                    'description' => 'Posts count',
                    'argtype' => 'int',
                    'converter' => function ($member) {
                        return (int)$member->real_member_posts;
                    },
                ],
                'Leaderboard Days Won' => [
                    'token' => 'leaderboards_won',
                    'description' => 'Number of days won on leaderboard',
                    'argtype' => 'int',
                    'converter' => function ($member) {
                        return (int)$member->getReputationDaysWonCount();
                    },
                ],
                'Reputation' => [
                    'token' => 'reputation',
                    'description' => 'Reputation points',
                    'argtype' => 'int',
                    'converter' => function ($member) {
                        return (int)$member->pp_reputation_points;
                    },
                ],
                'Warn Level' => [
                    'token' => 'warnlevel',
                    'description' => 'Warning level',
                    'argtype' => 'int',
                    'converter' => function ($member) {
                        return (int)$member->warn_level;
                    },
                ],
                'Joined Date' => [
                    'token' => 'joined',
                    'description' => 'Joined date',
                    'argtype' => 'object',
                    'class' => '\IPS\DateTime',
                    'converter' => function ($member) {
                        return $member->joined;
                    },
                    'tokenValue' => function ($date) {
                        return (string)$date;
                    },
                ],
                'Birthday' => [
                    'token' => 'birthday',
                    'description' => 'Birthday',
                    'argtype' => 'object',
                    'class' => '\IPS\DateTime',
                    'nullable' => true,
                    'converter' => function ($member) {
                        return $member->birthday;
                    },
                    'tokenValue' => function ($date) {
                        return (string)$date;
                    },
                ],
                'Age' => [
                    'token' => 'age',
                    'argtype' => 'int',
                    'description' => 'Age',
                    'nullable' => true,
                    'converter' => function ($member) {
                        return $member->age();
                    },
                ],
                'Member ID' => [
                    'token' => 'id',
                    'description' => 'The member id',
                    'argtype' => 'int',
                    'converter' => function ($member) {
                        return $member->member_id;
                    },
                ],
                'Url' => [
                    'token' => 'url',
                    'description' => 'The url',
                    'tokenValue' => function ($url) {
                        return (string)$url;
                    },
                    'argtype' => 'object',
                    'class' => '\IPS\Http\Url',
                    'converter' => function ($member) {
                        return $member->url();
                    },
                ],
                'Followers' => [
                    'argtype' => 'array',
                    'class' => '\IPS\Member',
                    'converter' => function ($member) {
                        $members = [];
                        foreach ($member->followers(3, ['immediate', 'daily', 'weekly'], null) as $follower) {
                            try {
                                $members[] = \IPS\Member::load($follower['follow_member_id']);
                            } catch (\OutOfRangeException $e) {
                            }
                        }
                        return $members;
                    },
                ],
                'Last Activity' => [
                    'token' => 'lastactivity',
                    'description' => 'Last activity',
                    'argtype' => 'object',
                    'class' => '\IPS\DateTime',
                    'converter' => function ($member) {
                        return \IPS\DateTime::ts($member->last_activity);
                    },
                    'tokenValue' => function ($date) {
                        return (string)$date;
                    },
                ],
            ],
            '\IPS\Content' => [
                'Title' => [
                    'token' => 'title',
                    'description' => 'The content title',
                    'argtype' => 'string',
                    'nullable' => true,
                    'converter' => function ($content) {
                        if ($content instanceof \IPS\Content\Comment) {
                            return $content->item()->mapped('title');
                        }

                        return $content->mapped('title');
                    },
                ],
                'Content' => [
                    'token' => 'content',
                    'description' => 'The content body',
                    'argtype' => 'string',
                    'nullable' => true,
                    'converter' => function ($content) {
                        return $content->content();
                    },
                ],
                'Created Date' => [
                    'token' => 'created',
                    'argtype' => 'object',
                    'class' => '\IPS\DateTime',
                    'converter' => function ($content) {
                        return \IPS\DateTime::ts($content->mapped('date'));
                    },
                ],
                'Updated Date' => [
                    'token' => 'updated',
                    'argtype' => 'object',
                    'class' => '\IPS\DateTime',
                    'converter' => function ($content) {
                        return \IPS\DateTime::ts($content->mapped('updated'));
                    },
                ],
                'Tags' => [
                    'argtype' => 'array',
                    'converter' => function ($content) {
                        return (array)$content->tags();
                    },
                ],
                'Content ID' => [
                    'token' => 'id',
                    'description' => 'The content ID',
                    'argtype' => 'int',
                    'converter' => function ($content) {
                        $idField = $content::$databaseColumnId;
                        return $content->$idField;
                    },
                ],
                'Author' => [
                    'argtype' => 'object',
                    'class' => '\IPS\Member',
                    'converter' => function ($content) {
                        return $content->author();
                    },
                ],
                'Author Name' => [
                    'token' => 'author:name',
                    'description' => 'The author name',
                    'argtype' => 'string',
                    'converter' => function ($content) {
                        return $content->author()->name;
                    },
                ],
                'Author ID' => [
                    'token' => 'author:id',
                    'description' => 'The author ID',
                    'argtype' => 'int',
                    'converter' => function ($content) {
                        return $content->author()->member_id;
                    },
                ],
                'Url' => [
                    'token' => 'url',
                    'tokenValue' => function ($url) {
                        return (string)$url;
                    },
                    'description' => 'The url',
                    'argtype' => 'object',
                    'class' => '\IPS\Http\Url',
                    'converter' => function ($content) {
                        return $content->url();
                    },
                ],
            ],
            '\IPS\Content\Item' => [
                'Container' => [
                    'argtype' => 'object',
                    'class' => '\IPS\Node\Model',
                    'nullable' => true,
                    'converter' => function ($item) {
                        return $item->containerWrapper();
                    },
                ],
                'Comment Count' => [
                    'token' => 'comments',
                    'description' => 'Item comment count',
                    'argtype' => 'int',
                    'converter' => function ($item) {
                        return (int)$item->mapped('num_comments');
                    },
                ],
                'Last Post Time' => [
                    'token' => 'lastpost',
                    'argtype' => 'object',
                    'class' => '\IPS\DateTime',
                    'converter' => function ($item) {
                        return \IPS\DateTime::ts(max($item->mapped('last_comment'), $item->mapped('last_review')));
                    },
                ],
                'Views' => [
                    'token' => 'views',
                    'description' => 'Item views count',
                    'argtype' => 'int',
                    'converter' => function ($item) {
                        return (int)$item->mapped('views');
                    },
                ],
                'Followers' => [
                    'argtype' => 'array',
                    'class' => '\IPS\Member',
                    'converter' => function ($item) {
                        try {
                            $members = [];
                            foreach (
                                $item->followers(
                                    3,
                                    ['none', 'immediate', 'daily', 'weekly'],
                                    null
                                ) as $follower
                            ) {
                                try {
                                    $members[] = \IPS\Member::load($follower['follow_member_id']);
                                } catch (\OutOfRangeException $e) {
                                }
                            }
                            return $members;
                        } catch (\BadMethodCallException $e) {
                            return [];
                        }
                    },
                ],
                'Author Followers' => [
                    'argtype' => 'array',
                    'class' => '\IPS\Member',
                    'converter' => function ($item) {
                        $members = [];
                        foreach (
                            $item->author()->followers(
                                3,
                                ['immediate', 'daily', 'weekly'],
                                null
                            ) as $follower
                        ) {
                            try {
                                $members[] = \IPS\Member::load($follower['follow_member_id']);
                            } catch (\OutOfRangeException $e) {
                            }
                        }
                        return $members;
                    },
                ],
                'First Comment' => [
                    'argtype' => 'object',
                    'class' => '\IPS\Content\Comment',
                    'converter' => function ($item) {
                        return $this->comments(1, 0, 'date', 'asc', null, false, null, null, true);
                    },
                ],
                'Last Comment' => [
                    'argtype' => 'object',
                    'class' => '\IPS\Content\Comment',
                    'converter' => function ($item) {
                        return $this->comments(1, 0, 'date', 'desc', null, false, null, null, true);
                    },
                ],
            ],
            '\IPS\Node\Model' => [
                'Parent' => [
                    'argtype' => 'object',
                    'class' => '\IPS\Node\Model',
                    'nullable' => true,
                    'converter' => function ($node) {
                        return $node->parent();
                    },
                ],
                'Root Parent' => [
                    'argtype' => 'object',
                    'class' => '\IPS\Node\Model',
                    'nullable' => true,
                    'converter' => function ($node) {
                        while ($parent = $node->parent()) {
                            ;
                        }
                        return $parent;
                    },
                ],
                'Title' => [
                    'token' => 'title',
                    'description' => 'The node title',
                    'argtype' => 'string',
                    'converter' => function ($node) {
                        $title = $node->_title;
                        \IPS\Lang::load(\IPS\Lang::defaultLanguage())->parseOutputForDisplay($title);
                        return $title;
                    },
                ],
                'Content Count' => [
                    'token' => 'items',
                    'description' => 'Total items count',
                    'argtype' => 'int',
                    'converter' => function ($node) {
                        return (int)$node->_items;
                    },
                ],
                'Node ID' => [
                    'token' => 'id',
                    'description' => 'The node ID',
                    'argtype' => 'int',
                    'converter' => function ($node) {
                        return $node->_id;
                    },
                ],
                'Url' => [
                    'token' => 'url',
                    'tokenValue' => function ($url) {
                        return (string)$url;
                    },
                    'description' => 'The url',
                    'argtype' => 'object',
                    'class' => '\IPS\Http\Url',
                    'converter' => function ($node) {
                        return $node->url();
                    },
                ],
            ],
            '\IPS\DateTime' => [
                'Date/Time' => [
                    'token' => 'datetime',
                    'description' => 'The formatted date/time',
                    'argtype' => 'string',
                    'converter' => function ($date) {
                        return (string)$date;
                    },
                ],
                'Timestamp' => [
                    'token' => 'timestamp',
                    'description' => 'The unix timestamp',
                    'argtype' => 'int',
                    'converter' => function ($date) {
                        return $date->getTimestamp();
                    },
                ],
                'Year' => [
                    'token' => 'year',
                    'description' => 'The full year',
                    'argtype' => 'int',
                    'converter' => function ($date) {
                        return $date->format('Y');
                    },
                ],
                'Month' => [
                    'token' => 'month',
                    'description' => 'The month number',
                    'argtype' => 'int',
                    'converter' => function ($date) {
                        return $date->format('n');
                    },
                ],
                'Day' => [
                    'token' => 'day',
                    'description' => 'The day of the month',
                    'argtype' => 'int',
                    'converter' => function ($date) {
                        return $date->format('j');
                    },
                ],
                'Hour' => [
                    'token' => 'hour',
                    'description' => 'The hour of day',
                    'argtype' => 'int',
                    'converter' => function ($date) {
                        return $date->format('G');
                    },
                ],
                'Minute' => [
                    'token' => 'minute',
                    'description' => 'The minute of hour',
                    'argtype' => 'int',
                    'converter' => function ($date) {
                        return $date->format('i');
                    },
                ],
            ],
            '\IPS\Http\Url' => [
                'Url' => [
                    'token' => 'url',
                    'description' => 'The url address',
                    'argtype' => 'string',
                    'converter' => function ($url) {
                        return (string)$url;
                    },
                ],
                'Params' => [
                    'argtype' => 'array',
                    'converter' => function ($url) {
                        return $url->queryString;
                    },
                ],
            ],
            '\IPS\Settings' => [
                'Site Name' => [
                    'token' => 'name',
                    'description' => 'The site name',
                    'argtype' => 'string',
                    'converter' => function ($settings) {
                        return $settings->board_name;
                    },
                ],
                'Site Url' => [
                    'token' => 'url',
                    'description' => 'The site url',
                    'argtype' => 'object',
                    'class' => '\IPS\Http\Url',
                    'converter' => function ($settings) {
                        return new \IPS\Http\Url($settings->base_url);
                    },
                    'tokenValue' => function ($url) {
                        return (string)$url;
                    },
                ],
            ],
        ];

        $lang = \IPS\Member::loggedIn()->language();

        /**
         * Content Item Conversions
         */
        foreach (\IPS\Application::allExtensions('core', 'ContentRouter') as $router) {
            foreach ($router->classes as $contentItemClass) {
                if (is_subclass_of($contentItemClass, '\IPS\Content\Item')) {
                    $contentTitle = ucwords(
                        ($lang->checkKeyExists($contentItemClass::$title) ? $lang->get(
                            $contentItemClass::$title
                        ) : $contentItemClass::$title)
                    );

                    /**
                     * Add Converters For Content Item
                     */
                    if (isset ($contentItemClass::$containerNodeClass)) {
                        $nodeClass = $contentItemClass::$containerNodeClass;
                        $nodeTitle = ucwords(
                            ($lang->checkKeyExists($nodeClass::$nodeTitle) ? $lang->get(
                                $nodeClass::$nodeTitle
                            ) : $nodeClass::$nodeTitle)
                        );
                        $node_type = rtrim($nodeTitle, "s");
                        $map['\\' . ltrim($contentItemClass, '\\')][$node_type] = [
                            'argtype' => 'object',
                            'class' => '\\' . ltrim($nodeClass, '\\'),
                            'converter' => function ($item) {
                                return $item->container();
                            },
                        ];
                    }

                    /**
                     * Add Converters For Comments
                     */
                    if (isset ($contentItemClass::$commentClass)) {
                        $commentClass = $contentItemClass::$commentClass;
                        $map['\\' . ltrim($commentClass, '\\')][$contentTitle] = [
                            'argtype' => 'object',
                            'class' => '\\' . ltrim($contentItemClass, '\\'),
                            'converter' => function ($comment) {
                                return $comment->item();
                            },
                        ];
                    }

                    /**
                     * Add Converters For Reviews
                     */
                    if (isset ($contentItemClass::$reviewClass)) {
                        $reviewClass = $contentItemClass::$reviewClass;
                        $map['\\' . ltrim($reviewClass, '\\')][$contentTitle] = [
                            'argtype' => 'object',
                            'class' => '\\' . ltrim($contentItemClass, '\\'),
                            'converter' => function ($review) {
                                return $review->item();
                            },
                        ];
                    }
                }
            }
        }

        /**
         * Custom Member Profile Fields
         */

        /* Cheap way to make sure profile fields are in the data store */
        \IPS\core\ProfileFields\Field::fieldsForContentView();

        foreach (\IPS\Data\Store::i()->profileFields['fields'] as $group_id => $fields) {
            foreach ($fields as $field_id => $fieldrow) {
                $field = \IPS\core\ProfileFields\Field::constructFromData($fieldrow);

                /* resets */
                $argtype = 'mixed';
                $argclass = null;
                $token = 'field_' . $field_id;
                $tokenValue = null;
                $converter = function ($member) use ($field_id) {
                    return $member->rulesProfileData($field_id);
                };

                switch ($field->type) {
                    case 'Text':
                    case 'Password':
                    case 'Email':
                    case 'Codemirror':
                    case 'Tel':
                    case 'TextArea':
                    case 'Color':
                    case 'Editor':
                    case 'Radio':

                        $argtype = 'string';
                        break;

                    case 'Checkbox':
                    case 'YesNo':

                        $argtype = 'bool';
                        $converter = function ($member) use ($field_id) {
                            if (($fieldData = $member->rulesProfileData($field_id)) !== null) {
                                return (bool)$fieldData;
                            }
                        };
                        $tokenValue = function ($bool) {
                            return $bool ? "Yes" : "No";
                        };
                        break;

                    case 'Date':

                        $argtype = 'object';
                        $argclass = '\IPS\DateTime';
                        $converter = function ($member) use ($field_id) {
                            if ($fieldData = $member->rulesProfileData($field_id)) {
                                return \IPS\DateTime::ts($fieldData);
                            }
                        };
                        break;

                    case 'Number':
                    case 'Rating':

                        $argtype = 'float';
                        break;

                    case 'Address':

                        $token = null;
                        $argtype = 'object';
                        $argclass = '\IPS\GeoLocation';
                        $converter = function ($member) use ($field_id) {
                            if ($fieldData = $member->rulesProfileData($field_id)) {
                                return \IPS\GeoLocation::buildFromJson($fieldData);
                            }
                        };
                        break;

                    case 'Member':

                        $argtype = 'object';
                        $argclass = '\IPS\Member';
                        $converter = function ($member) use ($field_id) {
                            if ($fieldData = $member->rulesProfileData($field_id)) {
                                try {
                                    return \IPS\Member::load($fieldData);
                                } catch (\OutOfRangeException $e) {
                                }
                            }
                        };
                        $tokenValue = function ($member) {
                            return $member->name;
                        };
                        break;

                    case 'Url':

                        $argtype = 'object';
                        $argclass = '\IPS\Http\Url';
                        $converter = function ($member) use ($field_id) {
                            if ($fieldData = $member->rulesProfileData($field_id)) {
                                return new \IPS\Http\Url($fieldData);
                            }
                        };
                        break;

                    case 'Select':

                        if ($field->multiple) {
                            $argtype = 'array';
                            $converter = function ($member) use ($field_id) {
                                if ($fieldData = $member->rulesProfileData($field_id)) {
                                    $values = explode(',', $fieldData);
                                    return $values;
                                }
                            };
                            $tokenValue = function ($array) {
                                return implode(', ', $array);
                            };
                        } else {
                            $argtype = 'string';
                            $converter = function ($member) use ($field_id) {
                                return $member->rulesProfileData($field_id);
                            };
                        }
                        break;

                    case 'CheckboxSet':

                        $options = json_decode($field->content, true);
                        if ($field->multiple) {
                            $argtype = 'array';
                            $converter = function ($member) use ($field_id, $options) {
                                if ($fieldData = $member->rulesProfileData($field_id)) {
                                    $values = [];
                                    $_selections = explode(',', $fieldData);
                                    foreach ($_selections as $_selection) {
                                        $values[$_selection] = $options[$_selection];
                                    }

                                    return $values;
                                }
                            };
                            $tokenValue = function ($array) {
                                return implode(', ', $array);
                            };
                        } else {
                            $argtype = 'string';
                            $converter = function ($member) use ($field_id, $options) {
                                if ($fieldData = $member->rulesProfileData($field_id)) {
                                    return $options[$fieldData];
                                }
                            };
                        }
                        break;
                }

                $map['\IPS\Member']['core_pfield_' . $field->id] = [
                    'token' => $token,
                    'tokenValue' => $tokenValue,
                    'argtype' => $argtype,
                    'class' => $argclass,
                    'description' => $field->_title . ' (profile field)',
                    'converter' => $converter,
                    'nullable' => true,
                ];
            }
        }

        return $map;
    }

}