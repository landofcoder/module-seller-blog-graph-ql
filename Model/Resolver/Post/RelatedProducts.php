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
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class RelatedProducts implements ResolverInterface
{

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
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

        if (!$post->getId() || !$post->getRelatedProducts()) {
            return [];
        }

        $relatedProducts = $post->getRelatedProducts();

        $store = $context->getExtensionAttributes()->getStore();

        $collection = $this->collectionFactory->create()
                                ->setStore($store)
                                ->addFieldToFilter('entity_id', ['in' => $relatedProducts]);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        return [
            'total_count' => $collection->getSize(),
            'items'       => $items
        ];
    }
}
