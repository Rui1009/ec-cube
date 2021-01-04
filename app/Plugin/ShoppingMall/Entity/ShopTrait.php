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
 * @Eccube\EntityExtension("Plugin\ShoppingMall\Entity\Shop")
 */
trait ShopTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="order_email", type="string", length=255, nullable=true)
     */
    private $order_email;

    /**
     * @var string/null
     *
     * @ORM\Column(name="category", type="string", nullable=true)
     */
    private $category;

    /**
     * @var string|null
     *
     * @ORM\Column(name="memo", type="string", length=4000, nullable=true)
     */
    private $memo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=4000, nullable=true)
     */
    private $description;

    /**
     * @var \Eccube\Entity\Master\SaleType|null
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\SaleType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sale_type_id", referencedColumnName="id")
     * })
     */
    private $SaleType;

    /**
     * @return string|null
     */
    public function getOrderEmail()
    {
        return $this->order_email;
    }

    /**
     * @param string|null $orderEmail
     *
     * @return $this
     */
    public function setOrderEmail($orderEmail)
    {
        $this->order_email = $orderEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * @param string|null $memo
     *
     * @return $this
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param \Eccube\Entity\Master\SaleType|null $SaleType
     *
     * @return $this
     */
    public function setSaleType(\Eccube\Entity\Master\SaleType $SaleType)
    {
        $this->SaleType = $SaleType;

        return $this;
    }

    /**
     * @return \Eccube\Entity\Master\SaleType|null
     */
    public function getSaleType()
    {
        return $this->SaleType;
    }

    /**
     * @return bool
     */
    public function isSaleType()
    {
        return !is_null($this->SaleType);
    }
}
