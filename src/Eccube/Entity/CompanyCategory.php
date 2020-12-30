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

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!class_exists('\Eccube\Entity\CompanyCategory')) {
    /**
     * CompanyCategory
     *
     * @ORM\Table(name="dtb_company_category")
     * @ORM\InheritanceType("SINGLE_TABLE")
     * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
     * @ORM\HasLifecycleCallbacks()
     * @ORM\Entity(repositoryClass="Eccube\Repository\CompanyCategoryRepository")
     */
    class CompanyCategory extends \Eccube\Entity\AbstractEntity
    {
        /**
         * @var int
         *
         * @ORM\Column(name="company_id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="NONE")
         */
        private $company_id;

        /**
         * @var int
         *
         * @ORM\Column(name="category_id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="NONE")
         */
        private $category_id;

        /**
         * @var \Plugin\ShoppingMall\Entity\Shop
         *
         * @ORM\ManyToOne(targetEntity="\Plugin\ShoppingMall\Entity\Shop", inversedBy="CompanyCategories")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="company_id", referencedColumnName="id")
         * })
         */
        private $Company;

        /**
         * @var \Eccube\Entity\Category
         *
         * @ORM\ManyToOne(targetEntity="Eccube\Entity\Category", inversedBy="CompanyCategories")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
         * })
         */
        private $Category;

        /**
         * Set companyId.
         *
         * @param int $companyId
         *
         * @return CompanyCategory
         */
        public function setCompanyId($companyId)
        {
            $this->company_id = $companyId;

            return $this;
        }

        /**
         * Get companyId.
         *
         * @return int
         */
        public function getCompanyId()
        {
            return $this->company_id;
        }

        /**
         * Set categoryId.
         *
         * @param int $categoryId
         *
         * @return CompanyCategory
         */
        public function setCategoryId($categoryId)
        {
            $this->category_id = $categoryId;

            return $this;
        }

        /**
         * Get categoryId.
         *
         * @return int
         */
        public function getCategoryId()
        {
            return $this->category_id;
        }

        /**
         * Set company.
         *
         * @return CompanyCategory
         */
        public function setCompany(\Plugin\ShoppingMall\Entity\Shop $company = null)
        {
            $this->Company = $company;

            return $this;
        }

        /**
         * Get company.
         *
         * @return \Plugin\ShoppingMall\Entity\Shop|null
         */
        public function getCompany()
        {
            return $this->Company;
        }

        /**
         * Set category.
         *
         * @return CompanyCategory
         */
        public function setCategory(\Eccube\Entity\Category $category = null)
        {
            $this->Category = $category;

            return $this;
        }

        /**
         * Get category.
         *
         * @return \Eccube\Entity\Category|null
         */
        public function getCategory()
        {
            return $this->Category;
        }
    }
}
