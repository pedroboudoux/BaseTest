<?php

namespace SONBase\Controller;

use Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator,
    Zend\Paginator\Adapter\ArrayAdapter;

abstract class CrudController extends AbstractActionController {

    protected $em;
    protected $service;
    protected $entity;
    protected $form;
    protected $formService = false;
    protected $route;
    protected $controller;
    protected $paginator_qtd_itens = 3;
    protected $paginator_page_range = 5;

    public function indexAction() {
        $list = $this->getEm()
                ->getRepository($this->entity)
                ->findAll();

        $page = $this->params()->fromRoute('page', 1);
        $paginator = new Paginator(new ArrayAdapter($list));
        $paginator->setCurrentPageNumber($page)->setDefaultItemCountPerPage($this->paginator_qtd_itens);
        $paginator->setPageRange($this->paginator_page_range);

        return new ViewModel(array('data' => $paginator, 'page' => $page));
    }

    public function newAction() {
        if ($this->formService === true) {
            $form = $this->getServiceLocator()->get($this->form);
        } else {
            $form = new $this->form();
        }
        
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->insert($request->getPost()->toArray());

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }

        return new ViewModel(array('form' => $form));
    }

    public function editAction() {
        if ($this->formService === true) {
            $form = $this->getServiceLocator()->get($this->form);
        } else {
            $form = new $this->form();
        }
        
        $request = $this->getRequest();

        $repo = $this->getEm()->getRepository($this->entity);
        $id = $this->params()->fromRoute('id', 0);

        if ($id != 0) {
            $entity = $repo->find($id);
            if ($entity !== null) {
                $form->setData($entity->toArray());
            }
        }

        if ($request->isPost()) {
            $form->setData($request->getPost()->toArray());

            if ($form->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->update($request->getPost()->toArray());

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }

        return new ViewModel(array('form' => $form));
    }

    public function deleteAction() {
        $service = $this->getServiceLocator()->get($this->service);

        if ($service->delete($this->params()->fromRoute('id', 0)))
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
    }

    /**
     * @return EntityManager
     */
    protected function getEm() {
        if (is_null($this->em)) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->em;
    }

}

