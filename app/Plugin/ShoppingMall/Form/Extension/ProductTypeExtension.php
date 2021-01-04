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

use Eccube\Form\Type\Admin\ProductType;
use Eccube\Request\Context;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ProductTypeExtension extends AbstractTypeExtension
{
    /**
     * @var Context
     */
    protected $requestContext;

    /**
     * ProductTypeExtension constructor.
     */
    public function __construct(Context $requestContext)
    {
        $this->requestContext = $requestContext;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('external_sales_url', TextType::class, [
                'label' => 'shopping_mall.admin.product.external_sales_url',
                'required' => false,
                'attr' => [
                    'maxlength' => 1024,
                    'placeholder' => 'shopping_mall.admin.product.external_sales_url.placeholder',
                ],
                'eccube_form_options' => [
                    'auto_render' => false,
                ],
                'constraints' => [
                    new Assert\Url(),
                    new Assert\Length(['max' => 1024]),
                ],
            ])
            ->add('should_show_price', CheckboxType::class, [
                'required' => false,
                'label' => 'shopping_mall.admin.product.should_show_price',
                'value' => '1',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductType::class;
    }
}
