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
use Lofmp\Blog\Api\AuthorRepositoryInterface;

class Author implements ResolverInterface
{

    /**
     * @var AuthorRepositoryInterface
     */
    private $authorManagement;

    /**
     * @var AuthorRepositoryInterface $authorManagement
     */
    public function __construct(
        AuthorRepositoryInterface $authorManagement
    )
    {
        $this->authorManagement = $authorManagement;
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
        if (empty($args['author_id'])) {
            throw new GraphQlInputException(__('Author Id is required.'));
        }
        $author = $this->authorManagement->get((int)$args['author_id']);
        if (!$author) {
            throw new GraphQlInputException(__('Author Id does not match any Author.'));
        }
        return $author;
    }
}
