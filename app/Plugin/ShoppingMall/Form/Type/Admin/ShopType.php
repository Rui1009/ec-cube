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

namespace Plugin\ShoppingMall\Form\Type\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\CategoryCompany;
use Eccube\Form\Validator\Email;
use Eccube\Repository\CategoryCompanyRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ShopType extends AbstractType
{
    /**
     * @var CategoryCompanyRepository
     */
    protected $categoryCompanyRepository;
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ShopType constructor.
     */
    public function __construct(CategoryCompanyRepository $categoryCompanyRepository, EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->categoryCompanyRepository = $categoryCompanyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'required' => true,
            'attr' => [
                'maxlength' => 255,
            ],
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 255]),
            ],
        ])->add('order_email', TextType::class, [
            'required' => false,
            'attr' => [
                'maxlength' => 255,
            ],
            'constraints' => [
                new Length(['max' => 255]),
                new Email(['strict' => $this->eccubeConfig['eccube_rfc_email_check']]),
            ],
        ])->add('memo', TextareaType::class, [
            'required' => false,
            'attr' => [
                'maxlength' => 4000,
            ],
            'constraints' => [
                new Length(['max' => 4000]),
            ],
        ])->add('CompanyCategories', ChoiceType::class, [
            'choice_label' => 'Name',
            'multiple' => true,
            'mapped' => false,
            'expanded' => true,
            'choices' => $this->categoryCompanyRepository->getList(null, true),
            'choice_value' => function (CategoryCompany $Category = null) {
                return $Category ? $Category->getId() : null;
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_shop';
    }
}
