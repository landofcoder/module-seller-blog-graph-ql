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
use Lofmp\BlogGraphQl\Api\TagRepositoryInterface;

class Tags implements ResolverInterface
{

    /**
     * @var TagRepositoryInterface
     */
    protected $repository;

    /**
     * @param TagRepositoryInterface $repository
     */
    public function __construct(
        TagRepositoryInterface $repository
    )
    {
        $this->repository = $repository;
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

        if (!$post->getPostId()) {
            return [];
        }
        $collection = $this->repository->getListByPost((int)$post->getPostId());

        return [
            'total_count' => $collection->getSize(),
            'items'       => $collection->getItems()
        ];
    }
}
