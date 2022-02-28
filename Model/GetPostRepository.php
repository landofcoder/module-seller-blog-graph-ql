<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\BlogGraphQl\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Lofmp\BlogGraphQl\Api\GetPostRepositoryInterface;
use Lofmp\Blog\Helper\Data;
use Lofmp\Blog\Model\PostFactory;

/**
 * get Post repository model
 */
class GetPostRepository implements GetPostRepositoryInterface
{

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Initialize dependencies.
     *
     * @param Data $data
     * @param StoreManagerInterface $storeManager
     * @param PostFactory $postFactory
     */
    public function __construct(
        Data $data,
        StoreManagerInterface $storeManager,
        PostFactory $postFactory
    ) {
        $this->helper = $data;
        $this->storeManager = $storeManager;
        $this->postFactory = $postFactory;
    }

    /**
     * @inheritdoc
     */
    public function get($id, $storeId = null)
    {
        try {
            $item = $this->postFactory->create();
            $item->load($id);

            if (!$item->getId()) {
                return false;
            }

            if ($storeId !== null && !$item->isVisibleOnStore($storeId)) {
                return false;
            }

            $item->setImage($item->getImageUrl());
            $item->setThumbnail($item->getThumbnailUrl());
            return $item;
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Blog Post with id "%1" does not exist.', $id));
        }
    }
}
