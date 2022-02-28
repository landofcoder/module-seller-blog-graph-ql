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
use Lofmp\BlogGraphQl\Api\BlogRepositoryInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;

class Blogs implements ResolverInterface
{

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var BlogRepositoryInterface
     */
    private $repository;

    /**
     * @param BlogRepositoryInterface $repository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        BlogRepositoryInterface $repository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        $searchCriteria = $this->searchCriteriaBuilder->build( 'lofmp_blog_post', $args );
        $searchCriteria->setCurrentPage( $args['currentPage'] );
        $searchCriteria->setPageSize( $args['pageSize'] );

        $searchResult = $this->repository->getListPost( $searchCriteria );

        $items = [];
        foreach ($searchResult->getItems() as $_item) {
            $_item["model"] = $_item;
            $items[] = $_item;
        }
        return [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $items
        ];
    }
}
