<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ShoppingMall;

use Eccube\Common\EccubeNav;

class ShoppingMallNav implements EccubeNav
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getNav()
    {
        return [
            'product' => [
                'children' => [
                    'shopping_mall.csv_import' => [
                        'name' => 'shopping_mall.admin.product.csv.title',
                        'url' => 'shopping_mall_admin_product_csv_import',
                    ],
                ],
            ],
            'setting' => [
                'children' => [
                    'shopping_mall.shop' => [
                        'name' => 'shopping_mall.admin.shop.title',
                        'url' => 'shopping_mall_admin_shop_index',
                    ],
                ],
            ],
        ];
    }
}
