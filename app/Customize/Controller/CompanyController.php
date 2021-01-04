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

namespace Customize\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\SearchCompanyType;
use Eccube\Repository\ProductRepository;
use Knp\Component\Pager\Paginator;
use Plugin\ShoppingMall\Repository\ShopRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CompanyController extends AbstractController
{
    /**
     * @var ShopRepository
     */
    protected $shopRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * CompanyController constructor.
     */
    public function __construct(ShopRepository $shopRepository, ProductRepository $productRepository)
    {
        $this->shopRepository = $shopRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * 　会社一覧画面
     *
     * @Route("/companies/list", name="company_list")
     * @Template("Company/list.twig")
     */
    public function index(Request $request, Paginator $paginator)
    {
        // handleRequestは空のqueryの場合は無視するため
        if ($request->getMethod() === 'GET') {
            $request->query->set('pageno', $request->query->get('pageno', ''));
        }

        // searchForm
        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $this->formFactory->createNamedBuilder('', SearchCompanyType::class);

        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_COMPANY_INDEX_INITIALIZE, $event);

        /* @var $searchForm \Symfony\Component\Form\FormInterface */
        $searchForm = $builder->getForm();

        $searchForm->handleRequest($request);

        // paginator
        $searchData = $searchForm->getData();
        $qb = $this->shopRepository->getQueryBuilderBySearchData($searchData);

        $event = new EventArgs(
            [
                'searchData' => $searchData,
                'qb' => $qb,
            ],
            $request
        );

        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_COMPANY_INDEX_SEARCH, $event);
        $searchData = $event->getArgument('searchData');

        $query = $qb->getQuery()
        ->useResultCache(true, $this->eccubeConfig['eccube_result_cache_lifetime_short']);

        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate(
            $query,
            !empty($searchData['pageno']) ? $searchData['pageno'] : 1
        );
        $ids = [];

        foreach ($pagination as $Shops) {
            $ids[] = $Shops->getId();
        }

        $orderByForm = $builder->getForm();

        $orderByForm->handleRequest($request);

        $category = $searchForm->get('category_id')->getData();

        return [
            'pagination' => $pagination,
            'search_form' => $searchForm->createView(),
            'order_by_form' => $orderByForm->createView(),
            'form' => $builder->getForm()->createView(),
            'Category' => $category,
        ];
    }

    /**
     * 会社詳細画面
     *
     * @Route("/companies/detail/{id}", name="company_detail", methods={"GET"})
     * @Template("Company/detail.twig")
     *
     * @return array
     */
    public function detail($id)
    {
        $Company = $this->shopRepository->find($id);
        $Product = $this->productRepository->findBy(['Shop' => ['id' => $id]]);

        // searchForm
        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $this->formFactory->createNamedBuilder('', SearchCompanyType::class);

        return [
            'Company' => $Company,
            'Products' => $Product,
            'form' => $builder->getForm()->createView(),
        ];
    }
}
