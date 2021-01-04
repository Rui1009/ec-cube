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

namespace Plugin\ShoppingMall\Controller\Admin;

use Eccube\Controller\AbstractController;
use Eccube\Entity\CompanyCategory;
use Eccube\Entity\Master\SaleType;
use Eccube\Repository\CategoryCompanyRepository;
use Eccube\Repository\Master\SaleTypeRepository;
use Plugin\ShoppingMall\Entity\Shop;
use Plugin\ShoppingMall\Form\Type\Admin\ShopType;
use Plugin\ShoppingMall\Repository\ShopRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    /**
     * @var ShopRepository
     */
    protected $shopRepository;

    /**
     * @var CategoryCompanyRepository
     */
    protected $categoryCompanyRepository;

    /**
     * @var SaleTypeRepository
     */
    protected $saleTypeRepository;

    /**
     * ConfigController constructor.
     */
    public function __construct(ShopRepository $shopRepository, SaleTypeRepository $saleTypeRepository, CategoryCompanyRepository $categoryCompanyRepository)
    {
        $this->shopRepository = $shopRepository;
        $this->saleTypeRepository = $saleTypeRepository;
        $this->categoryCompanyRepository = $categoryCompanyRepository;
    }

    /**
     * List shop.
     *
     * @return array
     *
     * @Route("/%eccube_admin_route%/shopping_mall/shop", name="shopping_mall_admin_shop_index")
     * @Template("@ShoppingMall/admin/Shop/index.twig")
     */
    public function index(Request $request)
    {
        $Shops = $this->shopRepository
            ->findBy(
                [],
                ['sort_no' => 'DESC']
            );

        return [
            'Shops' => $Shops,
        ];
    }

    /**
     * Add/Edit shop.
     *
     * @param null $id
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @Route("/%eccube_admin_route%/shopping_mall/shop/new", name="shopping_mall_admin_shop_new")
     * @Route("/%eccube_admin_route%/shopping_mall/shop/{id}/edit", requirements={"id" = "\d+"}, name="shopping_mall_admin_shop_edit")
     * @Template("@ShoppingMall/admin/Shop/edit.twig")
     */
    public function edit(Request $request, $id = null)
    {
        //shopの初期化
        if (is_null($id)) {
            $Shop = new Shop();
            $sortNo = 1;
            if ($id) {
                $sortNo = $Shop->getSortNo() + 1;
            }
            $Shop = new Shop();
            $Shop
                ->setSortNo($sortNo);
        } else {
            $Shop = $this->shopRepository->find($id);
        }

        //formの初期化
        $builder = $this->formFactory->createBuilder(ShopType::class, $Shop);
        $form = $builder->getForm();
        $form->setData($Shop);
        $categories = [];
        $CompanyCategories = $Shop->getCompanyCategories();

        if (!is_null($id)) {
            foreach ($CompanyCategories as $CompanyCategory) {
                /* @var $CompanyCategory \Eccube\Entity\CompanyCategory */
                $categories[] = $CompanyCategory->getCategoryCompany();
            }
            $form['CompanyCategories']->setData($categories);
        }

        $form->handleRequest($request);

        if ('GET' === $request->getMethod()) {
            // ツリー表示のため、ルートからのカテゴリを取得
            $TopCategories = $this->categoryCompanyRepository->getList(null);
            $ChoicedCategoryCompanyIds = array_map(function ($CategoryCompany) {
                return $CategoryCompany->getId();
            }, is_null($form->get('CompanyCategories')->getData()) ? [] : $form->get('CompanyCategories')->getData());

            return [
                        'form' => $form->createView(),
                        'shop_id' => $Shop->getId(),
                        'TopCategories' => $TopCategories,
                        'ChoicedCategoryIds' => $ChoicedCategoryCompanyIds,
                    ];
        } elseif ('POST' === $request->getMethod()) {
            if ($form->isSubmitted() && $form->isValid()) {
                $Shop = $form->getData();
                if (is_null($Shop->getSaleType())) {
                    // 販売種別登録処理
                    $id = $this->saleTypeRepository->createQueryBuilder('st')
                        ->select('MAX(st.id)')
                        ->getQuery()
                        ->getSingleScalarResult();

                    if (!$id) {
                        $id = 0;
                    }

                    $sortNo = $this->saleTypeRepository->createQueryBuilder('st')
                        ->select('MAX(st.sort_no)')
                        ->getQuery()
                        ->getSingleScalarResult();

                    if (!$sortNo) {
                        $sortNo = 0;
                    }

                    $SaleType = new SaleType();
                    $SaleType->setId($id + 1);
                    $SaleType->setName($Shop->getName());
                    $SaleType->setSortNo($sortNo + 1);
                    $this->entityManager->persist($SaleType);
                    $Shop->setSaleType($SaleType);

                    $this->entityManager->persist($Shop);
                    $this->entityManager->flush();
                } else {
                    foreach ($Shop->getCompanyCategories() as $CompanyCategory) {
                        $Shop->removeCompanyCategory($CompanyCategory);
                        $this->entityManager->remove($CompanyCategory);
                    }
                    $this->entityManager->persist($Shop);
                    $this->entityManager->flush();
                }

                $count = 1;
                $Categories = $form->get('CompanyCategories')->getData();

                $categoriesIdList = [];

                foreach ($Categories as $Category) {
                    foreach ($Category->getPath() as $ParentCategory) {
                        if (!isset($categoriesIdList[$ParentCategory->getId()])) {
                            $CompanyCategory = $this->createCompanyCategory($Shop, $ParentCategory, $count);
                            $count++;

                            /* @var $Shop \Plugin\ShoppingMall\Entity\Shop */
                            $Shop->addCompanyCategory($CompanyCategory);
                            $categoriesIdList[$ParentCategory->getId()] = true;
                        }
                    }
                }

                /* @var $Shop \Plugin\ShoppingMall\Entity\Shop */

                log_info($Shop->toJSON());
                // ショップ登録処理

                $this->entityManager->persist($Shop);

                // 登録実行
                $this->entityManager->flush();

                $this->addSuccess('admin.common.save_complete', 'admin');

                return $this->redirectToRoute('shopping_mall_admin_shop_index', ['id' => $Shop->getId()]);
            }
        }
    }

    /**
     * Delete shop.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route(
     *     "/%eccube_admin_route%/shopping_mall/shop/{id}/delete",
     *     name="shopping_mall_admin_shop_delete", requirements={"id":"\d+"},
     *     methods={"DELETE"}
     * )
     */
    public function delete(Request $request, Shop $Shop)
    {
        $this->isTokenValid();

        try {
            $this->shopRepository->delete($Shop);

            $this->addSuccess('shopping_mall.admin.shop.delete.complete', 'admin');

            log_info('店舗削除完了', ['Shop id' => $Shop->getId()]);
        } catch (\Exception $e) {
            log_info('店舗削除エラー', ['Shop id' => $Shop->getId(), $e]);

            $message = trans('admin.delete.failed.foreign_key', ['%name%' => $Shop->getName()]); // TODO: ここlocaleに追加
            $this->addError($message, 'admin');
        }

        return $this->redirectToRoute('shopping_mall_admin_shop_index');
    }

    /**
     * Move sort no with ajax.
     *
     * @return Response
     *
     * @throws \Exception
     *
     * @Route(
     *     "/%eccube_admin_route%/shopping_mall/shop/move_sort_no",
     *     name="shopping_mall_admin_shop_move_sort_no",
     *     methods={"POST"}
     * )
     */
    public function moveSortNo(Request $request)
    {
        if ($request->isXmlHttpRequest() && $this->isTokenValid()) {
            $sortNos = $request->request->all();
            foreach ($sortNos as $shopId => $sortNo) {
                $Shop = $this->shopRepository->find($shopId);
                $Shop->setSortNo($sortNo);
                $this->entityManager->persist($Shop);
            }
            $this->entityManager->flush();
        }

        return new Response();
    }

    /**
     * CompanyCategory作成
     *
     * @param \Plugin\ShoppingMall\Entity\Shop $Shop
     * @param \Eccube\Entity\CategoryCompany $CategoryCompany
     * @param integer $count
     *
     * @return \Eccube\Entity\CompanyCategory
     */
    private function createCompanyCategory($Shop, $CategoryCompany, $count)
    {
        $CompanyCategory = new CompanyCategory();
        $CompanyCategory->setCompany($Shop);
        $CompanyCategory->setCompanyId($Shop->getId());
        $CompanyCategory->setCategoryCompany($CategoryCompany);
        $CompanyCategory->setCategoryCompanyId($CategoryCompany->getId());

        return $CompanyCategory;
    }
}
