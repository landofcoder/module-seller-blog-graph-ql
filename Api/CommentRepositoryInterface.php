<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\BlogGraphQl\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface CommentRepositoryInterface
 * @package Lofmp\BlogGraphQl\Api
 */
interface CommentRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getListComment(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * @param int $post_id
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getPostComments(
        int $post_id,
        SearchCriteriaInterface $searchCriteria
    );

}
