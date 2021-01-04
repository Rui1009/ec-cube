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

namespace Plugin\ShoppingMall\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

/**
 * @Eccube\EntityExtension("Eccube\Entity\Member")
 */
trait MemberTrait
{
    /**
     * @var Shop|null
     *
     * @ORM\ManyToOne(targetEntity="Plugin\ShoppingMall\Entity\Shop")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     * })
     */
    private $Shop;

    /**
     * @return Shop|null
     */
    public function getShop()
    {
        return $this->Shop;
    }

    /**
     * @return $this
     */
    public function setShop(Shop $Shop = null)
    {
        $this->Shop = $Shop;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShop()
    {
        return !is_null($this->Shop);
    }
}
