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
use Lofmp\BlogGraphQl\Api\TagRepositoryInterface;
use Lofmp\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;

class Tags implements ResolverInterface
{

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var TagRepositoryInterface
     */
    private $repository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var TagRepositoryInterface $repository
     * @var SearchCriteriaBuilder $searchCriteriaBuilder
     * @var CollectionFactory $collectionFactory
     */
    public function __construct(
        TagRepositoryInterface $repository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $collectionFactory
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->repository = $repository;
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
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        $searchCriteria = $this->searchCriteriaBuilder->build( 'lofmp_blog_post_tag', $args );
        $searchCriteria->setCurrentPage( $args['currentPage'] );
        $searchCriteria->setPageSize( $args['pageSize'] );

        $searchResult = $this->repository->getList( $searchCriteria );
        $items = [];
        foreach ($searchResult->getItems() as $item) {
            $collection = $this->collectionFactory->create()->addFieldToFilter('alias' , $item->getAlias());
            $_item = [
                "name" => $item->getName(),
                "alias" => $item->getAlias(),
                "meta_robots" => $item->getMetaRobots(),
                "total_posts" => $collection->getSize()
            ];
            $items[] = $_item;
        }
        return [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $items
        ];
    }
}
