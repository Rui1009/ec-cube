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

namespace Eccube\Controller\Admin\Setting\Shop;

use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Admin\CategoryCompanyType;
use Eccube\Repository\CategoryCompanyRepository;
use Eccube\Service\CsvExportService;
use Eccube\Util\CacheUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CategoryCompanyController extends AbstractController
{
    /**
     * @var CsvExportService
     */
    protected $csvExportService;

    /**
     * @var CategoryCompanyRepository
     */
    protected $categoryCompanyRepository;

    /**
     * CategoryCompanyController constructor.
     */
    public function __construct(
        CsvExportService $csvExportService,
        CategoryCompanyRepository $categoryCompanyRepository
    ) {
        $this->csvExportService = $csvExportService;
        $this->categoryCompanyRepository = $categoryCompanyRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/company/category_company", name="admin_company_category_company")
     * @Route("/%eccube_admin_route%/company/category_company/{parent_id}", requirements={"parent_id" = "\d+"}, name="admin_company_category_company_show")
     * @Route("/%eccube_admin_route%/company/category_company/{id}/edit", requirements={"id" = "\d+"}, name="admin_company_category_company_edit")
     * @Template("@admin/Setting/Shop/category_company.twig")
     */
    public function index(Request $request, $parent_id = null, $id = null, CacheUtil $cacheUtil)
    {
        if ($parent_id) {
            /** @var CategoryCompany $Parent */
            $Parent = $this->categoryCompanyRepository->find($parent_id);
            if (!$Parent) {
                throw new NotFoundHttpException();
            }
        } else {
            $Parent = null;
        }
        if ($id) {
            $TargetCategory = $this->categoryCompanyRepository->find($id);
            if (!$TargetCategory) {
                throw new NotFoundHttpException();
            }
            $Parent = $TargetCategory->getParent();
        } else {
            $TargetCategory = new \Eccube\Entity\CategoryCompany();
            $TargetCategory->setParent($Parent);
            if ($Parent) {
                $TargetCategory->setHierarchy($Parent->getHierarchy() + 1);
            } else {
                $TargetCategory->setHierarchy(1);
            }
        }

        $Categories = $this->categoryCompanyRepository->getList($Parent);

        // ツリー表示のため、ルートからのカテゴリを取得
        $TopCategories = $this->categoryCompanyRepository->getList(null);

        $builder = $this->formFactory
            ->createBuilder(CategoryCompanyType::class, $TargetCategory);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Parent' => $Parent,
                'TargetCategory' => $TargetCategory,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_COMPANY_CATEGORY_COMPANY_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();

        $forms = [];
        foreach ($Categories as $Category) {
            $forms[$Category->getId()] = $this->formFactory
                ->createNamed('category_'.$Category->getId(), CategoryCompanyType::class, $Category);
        }

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($this->eccubeConfig['eccube_category_nest_level'] < $TargetCategory->getHierarchy()) {
                    throw new BadRequestHttpException();
                }
                log_info('カテゴリ登録開始', [$id]);

                $this->categoryCompanyRepository->save($TargetCategory);

                log_info('カテゴリ登録完了', [$id]);

                // $formが保存されたフォーム
                // 下の編集用フォームの場合とイベント名が共通のため
                // このイベントのリスナーではsubmitされているフォームを判定する必要がある
                $event = new EventArgs(
                    [
                        'form' => $form,
                        'Parent' => $Parent,
                        'TargetCategory' => $TargetCategory,
                    ],
                    $request
                );
                $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_COMPANY_CATEGORY_COMPANY_INDEX_COMPLETE, $event);

                $this->addSuccess('admin.common.save_complete', 'admin');

                $cacheUtil->clearDoctrineCache();

                if ($Parent) {
                    return $this->redirectToRoute('admin_company_category_company_show', ['parent_id' => $Parent->getId()]);
                } else {
                    return $this->redirectToRoute('admin_company_category_company');
                }
            }

            foreach ($forms as $editForm) {
                $editForm->handleRequest($request);
                if ($editForm->isSubmitted() && $editForm->isValid()) {
                    $this->categoryCompanyRepository->save($editForm->getData());

                    // $editFormが保存されたフォーム
                    // 上の新規登録用フォームの場合とイベント名が共通のため
                    // このイベントのリスナーではsubmitされているフォームを判定する必要がある
                    $event = new EventArgs(
                        [
                            'form' => $form,
                            'editForm' => $editForm,
                            'Parent' => $Parent,
                            'TargetCategory' => $editForm->getData(),
                        ],
                        $request
                    );

                    $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_COMPANY_CATEGORY_COMPANY_INDEX_COMPLETE, $event);

                    $this->addSuccess('admin.common.save_complete', 'admin');

                    $cacheUtil->clearDoctrineCache();

                    if ($Parent) {
                        return $this->redirectToRoute('admin_company_category_company_show', ['parent_id' => $Parent->getId()]);
                    } else {
                        return $this->redirectToRoute('admin_company_category_company');
                    }
                }
            }
        }

        $formViews = [];
        foreach ($forms as $key => $value) {
            $formViews[$key] = $value->createView();
        }

        $Ids = [];
        if ($Parent && $Parent->getParents()) {
            foreach ($Parent->getParents() as $item) {
                $Ids[] = $item['id'];
            }
        }
        $Ids[] = intval($parent_id);

        return [
            'form' => $form->createView(),
            'Parent' => $Parent,
            'Ids' => $Ids,
            'Categories' => $Categories,
            'TopCategories' => $TopCategories,
            'TargetCategory' => $TargetCategory,
            'forms' => $formViews,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/company/category_company/{id}/delete", requirements={"id" = "\d+"}, name="admin_company_category_company_delete", methods={"DELETE"})
     */
    public function delete(Request $request, $id, CacheUtil $cacheUtil)
    {
        $this->isTokenValid();

        $TargetCategory = $this->categoryCompanyRepository->find($id);
        if (!$TargetCategory) {
            $this->deleteMessage();

            return $this->redirectToRoute('admin_company_category_company');
        }
        $Parent = $TargetCategory->getParent();

        log_info('カテゴリ削除開始', [$id]);

        try {
            $this->categoryCompanyRepository->delete($TargetCategory);

            $event = new EventArgs(
                [
                    'Parent' => $Parent,
                    'TargetCategory' => $TargetCategory,
                ], $request
            );
            $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_COMPANY_CATEGORY_COMPANY_DELETE_COMPLETE, $event);

            $this->addSuccess('admin.common.delete_complete', 'admin');

            log_info('カテゴリ削除完了', [$id]);

            $cacheUtil->clearDoctrineCache();
        } catch (\Exception $e) {
            log_info('カテゴリ削除エラー', [$id, $e]);

            $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $TargetCategory->getName()]);
            $this->addError($message, 'admin');
        }

        if ($Parent) {
            return $this->redirectToRoute('admin_company_category_company_show', ['parent_id' => $Parent->getId()]);
        } else {
            return $this->redirectToRoute('admin_company_category_company');
        }
    }

    /**
     * @Route("/%eccube_admin_route%/company/category_company/sort_no/move", name="admin_company_category_company_sort_no_move", methods={"POST"})
     */
    public function moveSortNo(Request $request, CacheUtil $cacheUtil)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        if ($this->isTokenValid()) {
            $sortNos = $request->request->all();
            foreach ($sortNos as $categoryId => $sortNo) {
                /* @var $CategoryCompany \Eccube\Entity\CategoryCompany */
                $Category = $this->categoryCompanyRepository
                    ->find($categoryId);
                $Category->setSortNo($sortNo);
                $this->entityManager->persist($Category);
            }
            $this->entityManager->flush();

            $cacheUtil->clearDoctrineCache();

            return new Response('Successful');
        }
    }
}
