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

namespace Plugin\ShoppingMall\Form\Extension;

use Eccube\Form\Type\Admin\MemberType;
use Plugin\ShoppingMall\Entity\Shop;
use Plugin\ShoppingMall\PluginManager;
use Plugin\ShoppingMall\Repository\ShopRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class MemberTypeExtension extends AbstractTypeExtension
{
    /**
     * @var ShopRepository
     */
    protected $shopRepository;

    /**
     * MemberTypeExtension constructor.
     */
    public function __construct(ShopRepository $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $authority = PluginManager::getShopAuthority();

        $builder
            ->add('Shop', EntityType::class, [
                'label' => 'shopping_mall.admin.member_shop.shop',
                'class' => Shop::class,
                'choice_label' => 'name',
                'choices' => $this->shopRepository->findBy([], ['sort_no' => 'DESC']),
                'required' => false,
                'eccube_form_options' => [
                    'auto_render' => true,
                ],
            ])
            ->add('shop_authority_id', HiddenType::class, [
                'data' => is_null($authority) ? null : $authority->getId(),
                'mapped' => false,
                'eccube_form_options' => [
                    'auto_render' => true,
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return MemberType::class;
    }
}
