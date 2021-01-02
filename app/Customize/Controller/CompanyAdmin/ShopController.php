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

namespace Customize\Controller\CompanyAdmin;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Eccube\Form\Type\Admin\ShopDetailType;
use Plugin\ShoppingMall\Repository\ShopRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    /**
     * @var ShopRepository
     */
    protected $shopRepository;

    /**
     * ConfigController constructor.
     */
    public function __construct(ShopRepository $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/setting/shop/detail", name="admin_setting_shop_detail")
     * @Template("@admin/Company/setting.twig")
     */
    public function index(Application $app, Request $request)
    {
        $Shop = $this->getUser()['Shop'];
        $builder = $this->formFactory->createBuilder(ShopDetailType::class, ['description' => $Shop['name']]);
        $form = $builder->getForm();
        $form['description']->setData($Shop['description']);
        $form->handleRequest($request);

        if ('GET' === $request->getMethod()) {
            return [
                'shop' => $Shop,
                'form' => $form->createView(),
            ];
        } elseif ('POST' === $request->getMethod()) {
            if ($form->isSubmitted() && $form->isValid()) {
                $Shop = $this->shopRepository->findOneBy(['id' => $Shop['id']]);
                $Shop->setDescription($form->get('description')->getData());

                // ショップ登録処理
                $this->entityManager->persist($Shop);

                // 登録実行
                $this->entityManager->flush();

                $this->addSuccess('admin.common.save_complete', 'admin');

                return  $this->redirectToRoute('admin_setting_shop_detail');
            }
        }
    }
}
