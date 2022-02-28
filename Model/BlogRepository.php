<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\BlogGraphQl\Model;

use Lofmp\BlogGraphQl\Api\BlogRepositoryInterface;
use Lofmp\Blog\Api\Data\PostSearchResultsInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Lofmp\Blog\Helper\Data;
use Lofmp\Blog\Model\ResourceModel\Post as ResourcePost;
use Lofmp\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
/**
 * Class BlogRepository
 * @package Lofmp\BlogGraphQl\Model
 */
class BlogRepository implements BlogRepositoryInterface
{

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var
     */
    protected $brandFactory;

    /**
     * @var
     */
    protected $dataBrandFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var ResourcePost
     */
    protected $resource;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;
    /**
     * @var PostSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var
     */
    protected $brandCollectionFactory;
    /**
     * @var ResourceConnection
     */
    private $_resourceConnection;


    /**
     * @var PostCollectionFactory
     */
    private $postCollectionFactory;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var CollectionFactory
     */
    private $productCollection;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * BlogRepository constructor.
     * @param ResourcePost $resource
     * @param PostCollectionFactory $postCollectionFactory
     * @param PostSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Data $data
     * @param ResourceConnection $resourceConnection
     * @param CollectionFactory $productCollection
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ResourcePost $resource,
        PostCollectionFactory $postCollectionFactory,
        PostSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Data $data,
        ResourceConnection $resourceConnection,
        CollectionFactory $productCollection,
        ProductRepositoryInterface $productRepository
    ) {
        $this->resource = $resource;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->helper = $data;
        $this->_resourceConnection = $resourceConnection;
        $this->productCollection = $productCollection;
        $this->productRepository = $productRepository;

    }

    /**
     * {@inheritdoc}
     */
    public function getListPost(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getFilterCollection($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getListPostByTag(
        string $tag,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getFilterCollection($criteria, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function getListPostByUser(
        int $userId,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getFilterCollection($criteria, null, $userId);
    }

    /**
     * {@inheritdoc}
     */
    public function getListPostByCategory(
        int $categoryId,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getFilterCollection($criteria, null, null, $categoryId);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterCollection(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria,
        string $tag = null,
        int $userId = null,
        int $categoryId = null
    ) {
        $collection = $this->postCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Lofmp\Blog\Api\Data\PostInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        if ($tag) {
            $collection = $this->addFilterTag($tag, $collection);
        }

        if ($userId) {
            $collection->addFieldToFilter("user_id", (int)$userId);
        }

        if ($categoryId) {
            $collection->addCategoryFilter("category_id", (int)$categoryId);
        }

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $key => $model) {
            $_item = $model->getDataModel();
            $_item->setImage($model->getImageUrl());
            $_item->setThumbnail($model->getThumbnailUrl());
            $items[] = $_item;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * join and filter tag
     *
     * @param string $tag
     * @param \Lofmp\Blog\Model\ResourceModel\Post\Collection
     * @return \Lofmp\Blog\Model\ResourceModel\Post\Collection
     */
    public function addFilterTag(string $tag, $collection)
    {
        $collection->getSelect()
                    ->join(
                        ['post_tag_table' => $collection->getResource()->getTable("lofmp_blog_post_tag")],
                        'main_table.post_id = post_tag_table.post_id',
                        []
                    )
                    ->where("post_tag_table.alias = ?", $tag)
                    ->group(
                        'main_table.post_id'
                    );
        return $collection;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
}
