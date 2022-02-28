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
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Lofmp\BlogGraphQl\Api\CommentRepositoryInterface;

class Comments implements ResolverInterface
{

    /**
     * @var CommentRepositoryInterface
     */
    protected $repository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param CommentRepositoryInterface $repository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CommentRepositoryInterface $repository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->repository = $repository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        if (!isset($value['model']) || empty($value['model'])) {
            throw new GraphQlInputException(__('model value must be not empty.'));
        }

        /** @var \Lofmp\Blog\Model\Post */
        $post = $value['model'];

        if (!$post->getId()) {
            return [];
        }

        $searchCriteria = $this->searchCriteriaBuilder->build( 'lofmp_blog_comment', $args );
        $searchCriteria->setCurrentPage( $args['currentPage'] );
        $searchCriteria->setPageSize( $args['pageSize'] );

        $searchResult = $this->repository->getPostComments((int)$post->getId(), $searchCriteria );

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $searchResult->getItems(),
        ];
    }
}
