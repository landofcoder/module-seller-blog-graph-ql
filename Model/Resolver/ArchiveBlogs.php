<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\BlogGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lofmp\Blog\Model\ResourceModel\Post\Collection;

/**
 * Class ArchiveBlogs
 * @package Lofmp\BlogGraphQl\Model\Resolver
 */
class ArchiveBlogs implements ResolverInterface
{

    /**
     * @var Collection
     */
    private $_postCollection;

    /**
     * ArchiveBlogs constructor.
     * @param Collection $collection
     */
    public function __construct(
        Collection $collection
    )
    {
        $this->_postCollection = $collection;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $times = [];
        $months = [];
        foreach ($this->_postCollection as $post) {
            $time = strtotime($post->getData('creation_time'));
            if (isset($months[date('Y-m', $time)]['count'])) {
                $months[date('Y-m', $time)]['count'] =  (int)$months[date('Y-m', $time)]['count']+1;
            } else {
                $months[date('Y-m', $time)]['count'] = 1;
            }
            $months[date('Y-m', $time)]['time'] = $time;
        }
        foreach ($months as $key => $item) {
            $times[$key]['time'] = $key;
            $times[$key]['count'] = $item['count'];
        }
        return $times;
    }
}
