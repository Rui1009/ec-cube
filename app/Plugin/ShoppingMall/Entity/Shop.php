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
use Eccube\Entity\AbstractEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;

if (!class_exists('\Plugin\ShoppingMall\Entity\Shop')) {
    /**
     * Shop
     *
     * @ORM\Table(name="plg_shopping_mall_shop")
     * @ORM\Entity(repositoryClass="Plugin\ShoppingMall\Repository\ShopRepository")
     */
    class Shop extends AbstractEntity
    {
        /**
         * @var int
         *
         * @ORM\Column(name="id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;

        /**
         * @var string
         *
         * @ORM\Column(name="name", type="string", length=255)
         */
        private $name;

        /**
         * @var int
         *
         * @ORM\Column(name="sort_no", type="integer")
         */
        private $sort_no;

        /**
         * @var string|null
         *
         * @ORM\Column(name="search_word", type="string", length=4000, nullable=true)
         */
        private $search_word;

        /**
         * @var \Doctrine\Common\Collections\Collection
         *
         * @ORM\OneToMany(targetEntity="Eccube\Entity\CompanyCategory", mappedBy="Company", cascade={"persist","remove"})
         */
        private $CompanyCategories;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz")
         */
        private $create_date;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz")
         */
        private $update_date;

        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @param string $name
         *
         * @return $this
         */
        public function setName($name)
        {
            $this->name = $name;

            return $this;
        }

        /**
         * Set searchWord.
         *
         * @param string|null $searchWord
         *
         * @return Shop
         */
        public function setSearchWord($searchWord = null)
        {
            $this->search_word = $searchWord;

            return $this;
        }

        /**
         * Get searchWord.
         *
         * @return string|null
         */
        public function getSearchWord()
        {
            return $this->search_word;
        }

        /**
         * @return int
         */
        public function getSortNo()
        {
            return $this->sort_no;
        }

        /**
         * @param int $sortNo
         *
         * @return $this
         */
        public function setSortNo($sortNo)
        {
            $this->sort_no = $sortNo;

            return $this;
        }

        /**
         * Add CompanyCategory.
         *
         * @return Company
         */
        public function addCompanyCategory(\Eccube\Entity\CompanyCategory $companyCategory)
        {
            $this->CompanyCategories[] = $companyCategory;

            return $this;
        }

        /**
         * Remove companyCategory.
         *
         * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
         */
        public function removeCompanyCategory(\Eccube\Entity\CompanyCategory $companyCategory)
        {
            return $this->CompanyCategories->removeElement($companyCategory);
        }

        /**
         * Get companyCategories.
         *
         * @return \Doctrine\Common\Collections\Collection
         */
        public function getCompanyCategories()
        {
            return $this->CompanyCategories;
        }

        /**
         * @param \DateTime $createDate
         *
         * @return $this
         */
        public function setCreateDate($createDate)
        {
            $this->create_date = $createDate;

            return $this;
        }

        /**
         * @return \DateTime
         */
        public function getCreateDate()
        {
            return $this->create_date;
        }

        /**
         * @param \DateTime $updateDate
         *
         * @return $this
         */
        public function setUpdateDate($updateDate)
        {
            $this->update_date = $updateDate;

            return $this;
        }

        /**
         * @return \DateTime
         */
        public function getUpdateDate()
        {
            return $this->update_date;
        }

        /**
         * Unique check.
         */
        public static function loadValidatorMetadata(ClassMetadata $metadata)
        {
            $metadata->addConstraint(new UniqueEntity([
                'fields' => 'name',
            ]));
        }
    }
}
