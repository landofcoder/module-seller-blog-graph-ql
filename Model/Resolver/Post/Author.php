<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lofmp\BlogGraphQl\Model\Resolver\Post;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lofmp\Blog\Model\AuthorFactory;

class Author implements ResolverInterface
{

    /**
     * @var AuthorFactory
     */
    protected $authorFactory;

    /**
     * @param AuthorFactory $authorFactory
     */
    public function __construct(
        AuthorFactory $authorFactory
    )
    {
        $this->authorFactory = $authorFactory;
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
        if (!isset($value['model']) || empty($value['model'])) {
            throw new GraphQlInputException(__('model value must be not empty.'));
        }

        /** @var \Lofmp\Blog\Model\Post */
        $post = $value['model'];
        if (!$post->getUserId()) {
            return [];
        }

        $author = $this->getPostAuthor($post->getUserId());

        return $author ? $author->getData() : [];
    }

    /**
     * get post author
     *
     * @param int $userId
     * @return \Lofmp\Blog\Model\Author|bool
     */
    public function getPostAuthor($userId)
    {
        $author = $this->authorFactory->loadByUserId($userId);
        if($author->getIsView()) {
            return $author;
        }
        return false;
    }
}
