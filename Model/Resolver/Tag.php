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
use Lofmp\Blog\Api\PostRepositoryInterface;
use Lofmp\Blog\Api\TagRepositoryInterface;
use Lofmp\Blog\Model\ResourceModel\Tag\CollectionFactory;

/**
 * Class Tag
 * @package Lofmp\BlogGraphQl\Model\Resolver
 */
class Tag implements ResolverInterface
{
    /**
     * @var TagRepositoryInterface
     */
    private $tagManagement;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepositoryInterface;

    /**
     * Tag constructor.
     * @param TagRepositoryInterface $tagManagement
     * @param CollectionFactory $collectionFactory
     * @param PostRepositoryInterface $postRepositoryInterface
     */
    public function __construct(
        TagRepositoryInterface $tagManagement,
        CollectionFactory $collectionFactory,
        PostRepositoryInterface $postRepositoryInterface
    )
    {
        $this->tagManagement = $tagManagement;
        $this->collectionFactory = $collectionFactory;
        $this->postRepositoryInterface = $postRepositoryInterface;

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
        if (!isset($args['alias']) || empty($args['alias'])) {
            throw new GraphQlInputException(__('"Alias" can\'t be empty.'));
        }
        $collection = $this->collectionFactory->create()
                        ->addFieldToFilter('alias', $args['alias']);
        if (!$collection->getSize()) {
            throw new GraphQlInputException(__('This Tag does not exist.'));
        }
        $tag = $collection->getFirstItem();
        return [
            "name" => $tag->getName(),
            "alias" => $tag->getAlias(),
            "meta_robots" => $tag->getMetaRobots(),
            "total_posts" => $collection->getSize(),
            "model" => $collection
        ];
    }
}
