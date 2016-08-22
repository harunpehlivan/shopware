<?php

namespace Shopware\Tests\Bundle\SearchBundle\Condition;

use Shopware\Bundle\SearchBundle\Condition\VoteAverageCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Bundle\StoreFrontBundle\TestCase;

class VoteAverageConditionTest extends TestCase
{
    public function testVoteAverageCondition()
    {
        $condition = new VoteAverageCondition(3);
        $context = $this->getContext(1);

        $this->search(
            [
                'first'  =>   [ 1 => [1, 2]],
                'second' =>   [ 1 => [4, 5]],
                'third'  =>   [ 1 => [3, 5]],
                'fourth'  =>  [ 1 => [3, 3]],
                'first-2'  => [ 1 => [1, 2]],
                'second-2' => [ 1 => [4, 5]],
                'third-2'  => [ 1 => [3, 5]]
            ],
            ['second', 'third', 'fourth', 'second-2', 'third-2'],
            null,
            [$condition],
            [],
            [],
            $context,
            ['displayOnlySubShopVotes' => false]
        );
    }

    public function testSubShopVotes()
    {
        $condition = new VoteAverageCondition(3);
        $context = $this->getContext(2);

        $this->search(
            [
                'first' => [
                    1 => [1],   //shop = 1   1x vote with 1 point
                    2 => [3]    //shop = 2   1x vote with 3 point
                ],
                'second' => [
                    1 => [4],   //shop = 1   1x vote with 4 points
                    2 => [4]    //shop = 2   1x vote with 4 points
                ],
                'third' => [
                    1 => [4],   //shop = 1   1x vote with 4 points
                    2 => [2]    //shop = 2   1x vote with 2 points
                ]
            ],
            ['first', 'second'],
            $this->createCategory($context->getShop()),
            [$condition],
            [],
            [],
            $context,
            ['displayOnlySubShopVotes' => true]
        );
    }

    public function testMixedVotes()
    {
        $condition = new VoteAverageCondition(4.5);
        $context = $this->getContext(2);
        $this->search(
            [
                'first' => [
                    null => [5], //no assignment to shop
                    1 => [1],   //shop = 1   1x vote with 1 point
                    2 => [4]    //shop = 2   1x vote with 4 point
                ],
                'second' => [
                    null => [5],
                    1 => [4],   //shop = 1   1x vote with 4 points
                    2 => [3]    //shop = 2   1x vote with 2 points
                ],
                'third' => [
                    1 => [4],   //shop = 1   1x vote with 4 points
                    2 => [5]    //shop = 2   1x vote with 2 points
                ]
            ],
            ['first', 'third'],
            $this->createCategory($context->getShop()),
            [$condition],
            [],
            [],
            $context,
            ['displayOnlySubShopVotes' => true]
        );
    }

    public function testMixedVotesWithDisabledConfig()
    {
        $condition = new VoteAverageCondition(4);
        $context = $this->getContext(2);
        $this->search(
            [
                'first' => [
                    null => [5], //no assignment to shop
                    1 => [1],   //shop = 1   1x vote with 1 point
                    2 => [4]    //shop = 2   1x vote with 4 point
                ],
                'second' => [
                    null => [5],
                    1 => [4],   //shop = 1   1x vote with 4 points
                    2 => [3]    //shop = 2   1x vote with 2 points
                ],
                'third' => [
                    1 => [4],   //shop = 1   1x vote with 4 points
                    2 => [5]    //shop = 2   1x vote with 2 points
                ]
            ],
            ['second', 'third'],
            $this->createCategory($context->getShop()),
            [$condition],
            [],
            [],
            $context,
            ['displayOnlySubShopVotes' => false]
        );
    }

    /**
     * @param Shop $shop
     * @return Category
     */
    private function createCategory(Shop $shop)
    {
        $em = Shopware()->Container()->get('models');
        $category = $em->find(Category::class, $shop->getCategory()->getId());
        return $this->helper->createCategory(['parent' => $category]);
    }

    protected function createProduct(
        $number,
        ShopContext $context,
        Category $category,
        $additionally
    ) {
        $article = parent::createProduct(
            $number,
            $context,
            $category,
            $additionally
        );

        foreach ($additionally as $shopId => $votes) {
            if (empty($shopId)) {
                $shopId = null;
            }
            $this->helper->createVotes($article->getId(), $votes, $shopId);
        }

        return $article;
    }
}