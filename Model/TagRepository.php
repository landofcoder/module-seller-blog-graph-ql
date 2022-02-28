<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\BlogGraphQl\Model;

use Lofmp\BlogGraphQl\Api\TagRepositoryInterface;
use Lofmp\Blog\Api\Data\TagInterfaceFactory;
use Lofmp\Blog\Api\Data\TagSearchResultsInterfaceFactory;
use Lofmp\Blog\Model\TagFactory;
use Lofmp\Blog\Model\ResourceModel\Tag as ResourceTag;
use Lofmp\Blog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TagRepository
 */
class TagRepository implements TagRepositoryInterface
{

    /**
     * @var TagCollectionFactory
     */
    protected $tagCollectionFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var ResourceTag
     */
    protected $resource;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var TagFactory
     */
    protected $tagFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TagInterfaceFactory
     */
    protected $dataTagFactory;

    /**
     * @var TagSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param ResourceTag $resource
     * @param TagFactory $tagFactory
     * @param TagInterfaceFactory $dataTagFactory
     * @param TagCollectionFactory $tagCollectionFactory
     * @param TagSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceTag $resource,
        TagFactory $tagFactory,
        TagInterfaceFactory $dataTagFactory,
        TagCollectionFactory $tagCollectionFactory,
        TagSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->tagFactory = $tagFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataTagFactory = $dataTagFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->tagCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Lofmp\Blog\Api\Data\TagInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $collection->getSelect()->where('alias != ""');
        $collection->getSelect()->group(
            'alias'
        );

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getListByPost(
        int $postId
    ) {
        $collection = $this->tagCollectionFactory->create();
        $collection->addFieldtoFilter("post_id", $postId);
        $collection->getSelect()->where('alias != ""');
        $collection->getSelect()->group(
            'alias'
        );

        return $collection;
    }

}

