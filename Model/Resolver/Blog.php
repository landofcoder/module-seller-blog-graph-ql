<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\BlogGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lofmp\BlogGraphQl\Api\GetPostRepositoryInterface;

class Blog implements ResolverInterface
{
    /**
     * @var GetPostRepositoryInterface
     */
    private $repository;

    /**
     * @var GetPostRepositoryInterface $repository
     */
    public function __construct(
        GetPostRepositoryInterface $repository
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
        if (empty($args['post_id'])) {
            throw new GraphQlInputException(__('Post Id is required.'));
        }
        $store = $context->getExtensionAttributes()->getStore();
        $post = $this->repository->get($args['post_id'], $store->getId());

        if (!$post || !$post->getIsActive()) {
            throw new GraphQlNoSuchEntityException(__('Post Id does not match any record.'));
        }

        $data = $post->getData();
        $data["model"] = $post;

        return $data;
    }
}
