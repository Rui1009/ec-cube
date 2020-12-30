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
use Knp\Component\Pager\Paginator;
use Plugin\ShoppingMall\Repository\ShopRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CompaniesController extends AbstractController
{
    /**
     * @var ShopRepository
     */
    protected $shopRepository;

    /**
     * CompaniesController constructor.
     */
    public function __construct(ShopRepository $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    /**
     * 会社一覧画面
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

        // $ShopsRes = $this->shopRepository->findCompanies($ids);

        $orderByForm = $builder->getForm();

        $orderByForm->handleRequest($request);

        return [
            'pagination' => $pagination,
            'search_form' => $searchForm->createView(),
            'order_by_form' => $orderByForm->createView(),
        ];
    }
}
