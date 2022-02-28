<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\BlogGraphQl\Model;

use Lofmp\BlogGraphQl\Api\CommentRepositoryInterface;
use Lofmp\Blog\Api\Data\CommentSearchResultsInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Lofmp\Blog\Helper\Data;
use Lofmp\Blog\Model\Post;
use Lofmp\Blog\Model\ResourceModel\Comment as ResourceComment;
use Lofmp\Blog\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;
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
 * Class CommentRepository
 * @package Lofmp\BlogGraphQl\Model
 */
class CommentRepository implements CommentRepositoryInterface
{

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

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
     * @var ResourceComment
     */
    protected $resource;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;
    /**
     * @var CommentSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ResourceConnection
     */
    private $_resourceConnection;

    /**
     * @var CommentCollectionFactory
     */
    private $commentCollectionFactory;
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
     * @var Post
     */
    private $post;

    /**
     * BlogRepository constructor.
     * @param ResourceComment $resource
     * @param CommentCollectionFactory $commentCollectionFactory
     * @param CommentSearchResultsInterfaceFactory $searchResultsFactory
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
     * @param Post $post
     */
    public function __construct(
        ResourceComment $resource,
        CommentCollectionFactory $commentCollectionFactory,
        CommentSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Data $data,
        ResourceConnection $resourceConnection,
        CollectionFactory $productCollection,
        ProductRepositoryInterface $productRepository,
        Post $post
    ) {
        $this->resource = $resource;
        $this->commentCollectionFactory = $commentCollectionFactory;
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
        $this->post = $post;
    }

    /**
     * {@inheritdoc}
     */
    public function getListComment(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getCommentsCollection($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostComments(
        int $post_id,
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        return $this->getCommentsCollection($criteria, $post_id);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param int $post_id
     * @return mixed
     */
    public function getCommentsCollection($criteria, int $post_id = 0)
    {
        $collection = $this->commentCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $collection->addFieldToFilter("is_active", 1);

        if ($post_id) {
            $collection->addFieldToFilter("post_id", (int)$post_id);
        }

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection->getItems() as $key => $model) {
            $model->load($model->getCommentId());
            $post = $this->getPost($model->getPostId());
            $items[$key] = $model->getData();
            $items[$key]['post_title'] = $post->getTitle();
            $items[$key]['post_identifier'] = $post->getIdentifier();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param $postId
     * @return Post
     */
    public function getPost($postId) {
        return $this->post->load($postId);
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
