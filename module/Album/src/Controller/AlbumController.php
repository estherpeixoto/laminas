<?php

namespace Album\Controller;

use Album\Form\AlbumForm;
use Album\Model\Album;
use Album\Model\AlbumTable;
use Exception;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class AlbumController extends AbstractActionController
{
    private $table;

    public function __construct(AlbumTable $table)
    {
        $this->table = $table;
    }

    public function indexAction()
    {
        return new ViewModel([
            'albums' => $this->table->fetchAll(),
        ]);
    }

    public function addAction()
    {
        $form = new AlbumForm();

        // Alterar o texto do botão de submit
        $form->get('submit')->setValue('Add');

        // Verificar se a request é POST
        $request = $this->getRequest();
        if (!$request->isPost()) {
            // Reenvia os dados do formulário
            return ['form' => $form];
        }

        $album = new Album();
        $form->setInputFilter($album->getInputFilter());
        $form->setData($request->getPost());

        // Valida os dados preenchidos pelo usuário
        if (!$form->isValid()) {
            // Reenvia os dados do formulário
            return ['form' => $form];
        }

        // Se tudo for válido, salva no banco e redireciona para a lista
        $album->exchangeArray($form->getData());
        $this->table->saveAlbum($album);
        return $this->redirect()->toRoute('album');
    }

    public function editAction()
    {
        // Busca parâmetro da rota
        $id = (int) $this->params()->fromRoute('id', 0);

        // Verifica se ele foi realmente existe na rota
        if ($id === 0) {
            return $this->redirect()->toRoute('album', ['action' => 'add']);
        }

        // Busca album existente no banco
        try {
            $album = $this->table->getAlbum($id);
        } catch (Exception $e) {
            return $this->redirect()->toRoute('album', ['action' => 'index']);
        }

        $form = new AlbumForm();

        // Preenche os inputs
        $form->bind($album);

        // Alterar o texto do botão de submit
        $form->get('submit')->setValue('Edit');

        // Verificar se a request é POST
        $request = $this->getRequest();
        if (!$request->isPost()) {
            // Reenvia os dados do formulário
            return ['id' => $id, 'form' => $form];
        }

        $form->setInputFilter($album->getInputFilter());
        $form->setData($request->getPost());

        // Valida os dados preenchidos pelo usuário
        if (!$form->isValid()) {
            // Reenvia os dados do formulário
            return ['id' => $id, 'form' => $form];
        }

        // Se tudo for válido, salva no banco e redireciona para a lista
        $this->table->saveAlbum($album);
        return $this->redirect()->toRoute('album', ['action' => 'index']);
    }

    public function deleteAction()
    {
        // Busca parâmetro da rota
        $id = (int) $this->params()->fromRoute('id', 0);

        // Verifica se ele foi realmente existe na rota
        if (!$id) {
            return $this->redirect()->toRoute('album');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del === 'Yes') {
                $id = (int) $request->getPost('id');
                $this->table->deleteAlbum($id);
            }

            return $this->redirect()->toRoute('album');
        }

        return ['id' => $id, 'album' => $this->table->getAlbum($id)];
    }
}
