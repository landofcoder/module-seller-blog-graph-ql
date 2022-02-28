<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\BlogGraphQl\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface BlogRepositoryInterface
 * @package Lofmp\BlogGraphQl\Api
 */
interface BlogRepositoryInterface
{

    /**
     * get list posts
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Lofmp\Blog\Api\Data\PostSearchResultsInterface
     */
    public function getListPost(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * get list posts by tags
     *
     * @param string $tag
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Lofmp\Blog\Api\Data\PostSearchResultsInterface
     */
    public function getListPostByTag(
        string $tag,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * get list posts by user
     *
     * @param int $userId
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Lofmp\Blog\Api\Data\PostSearchResultsInterface
     */
    public function getListPostByUser(
        int $userId,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * get list posts by category
     *
     * @param int $categoryId
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Lofmp\Blog\Api\Data\PostSearchResultsInterface
     */
    public function getListPostByCategory(
        int $categoryId,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );
}
