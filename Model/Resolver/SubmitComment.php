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
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Lofmp\Blog\Api\CommentRepositoryInterface;
use Lofmp\Blog\Api\PostRepositoryInterface;
use Lofmp\Blog\Api\Data\CommentInterfaceFactory;
use Lofmp\Blog\Api\Data\CommentInterface;

class SubmitComment implements ResolverInterface
{

    /**
     * @var CommentRepositoryInterface
     */
    private $repository;

    /**
     * @var CommentInterfaceFactory
     */
    private $dataCommentFactory;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * construct
     *
     * @param CommentRepositoryInterface $repository
     * @param CommentInterfaceFactory $dataCommentFactory
     * @param PostRepositoryInterface $postRepository
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        CommentInterfaceFactory $repository,
        CommentInterfaceFactory $dataCommentFactory,
        PostRepositoryInterface $postRepository,
        GetCustomer $getCustomer
    )
    {
        $this->repository = $repository;
        $this->dataCommentFactory = $dataCommentFactory;
        $this->getCustomer = $getCustomer;
        $this->postRepository = $postRepository;
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
        /** @phpstan-ignore-next-line */

        if (empty($args['input']) || empty($args["input"]["post_id"]) || empty($args["input"]["content"])) {
            throw new GraphQlInputException(__('"input" with post_id value should be specified'));
        }

        $post = $this->postRepository->get((int)$args["input"]["post_id"]);

        if (!$post->getPostId() || !$post->getIsActive()) {
            throw new GraphQlInputException(__('the post "%1" is not exists or not published', $args["input"]["post_id"]));
        }

        $submitCommentData = $this->formatSubmitCommentData($args['input']);
        if (false !== $context->getExtensionAttributes()->getIsCustomer()) {
            $customer = $this->getCustomer->execute($context);
            $submitCommentData->setUserName($customer->getFirstname().' '.$customer->getLastname());
            $submitCommentData->setUserEmail($customer->getEmail);
        }

        return $this->repository->save($submitCommentData);
    }

    /**
     * format submit comment data
     *
     * @param mixed|array $input
     * @return CommentInterface
     */
    protected function formatSubmitCommentData($input = []): CommentInterface
    {
        $content = trim($input["content"]);
        $content = strip_tags($content);
        $submitData = $this->dataCommentFactory->create();
        $submitData->setPostId((int)$input["post_id"]);
        $submitData->setContent($content);
        $submitData->setParentId(isset($input["parent_id"]) ? (int)$input["parent_id"] : 0);
        return $submitData;
    }
}
