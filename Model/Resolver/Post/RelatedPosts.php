<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\BlogGraphQl\Model\Resolver\Post;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lofmp\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

class RelatedPosts implements ResolverInterface
{

    /**
     * @var PostCollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * @param PostCollectionFactory $postCollectionFactory
     */
    public function __construct(
        PostCollectionFactory $postCollectionFactory
    )
    {
        $this->postCollectionFactory = $postCollectionFactory;
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
        if (!isset($value['model']) || empty($value['model'])) {
            throw new GraphQlInputException(__('model value must be not empty.'));
        }

        /** @var \Lofmp\Blog\Model\Post */
        $post = $value['model'];

        if (!$post->getId() || !$post->getPostsRelated()) {
            return [];
        }

        $store = $context->getExtensionAttributes()->getStore();
        $collection = $this->postCollectionFactory->create();
        $collection->addFieldToFilter("is_active", 1);
        $collection->addStoreFilter($store);
        $collection->addFieldToFilter("post_id", ["in" => $value["posts_related"]]);

        return [
            'total_count' => $collection->getSize(),
            'items'       => $collection->getItems()
        ];
    }
}
