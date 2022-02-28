<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\BlogGraphQl\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface GetPostRepositoryInterface
{
    /**
     * Get item by id.
     *
     * @api
     * @param int $id
     * @param int|null $storeId
     * @return \Lofmp\Blog\Api\Data\PostInterface|\Lofmp\Blog\Model\Post|bool
     */
    public function get($id, $storeId = null);
}
