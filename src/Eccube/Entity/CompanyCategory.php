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
         * @var \Eccube\Entity\CategoryCompany
         *
         * @ORM\ManyToOne(targetEntity="Eccube\Entity\CategoryCompany", inversedBy="CompanyCategories")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
         * })
         */
        private $CategoryCompany;

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
         * Set categoryCompanyId.
         *
         * @param int $categoryCompanyId
         *
         * @return CompanyCategory
         */
        public function setCategoryCompanyId($categoryCompanyId)
        {
            $this->category_id = $categoryCompanyId;

            return $this;
        }

        /**
         * Get categoryCompanyId.
         *
         * @return int
         */
        public function getCategoryCompanyId()
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
         * Set category_company.
         *
         * @return CompanyCategory
         */
        public function setCategoryCompany(\Eccube\Entity\CategoryCompany $categoryCompany = null)
        {
            $this->CategoryCompany = $categoryCompany;

            return $this;
        }

        /**
         * Get category_company.
         *
         * @return \Eccube\Entity\CategoryCompany|null
         */
        public function getCategoryCompany()
        {
            return $this->CategoryCompany;
        }
    }
}
