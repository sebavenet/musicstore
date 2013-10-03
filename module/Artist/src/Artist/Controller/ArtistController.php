<?php
namespace Artist\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Artist\Model\Artist;
use Artist\Form\ArtistForm;

class ArtistController extends AbstractActionController
{
    protected $artistTable;
    
    public function indexAction()
    {
        return new ViewModel(array(
            'artists' => $this->getArtistTable()->fetchAll(),
        ));
    }
    
    public function addAction()
    {
        $form = new ArtistForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $artist = new Artist();
            $form->setInputFilter($artist->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $artist->exchangeArray($form->getData());
                $this->getArtistTable()->saveArtist($artist);

                // Redirect to list of artists
                return $this->redirect()->toRoute('artist');
            }
        }
        return array('form' => $form);
    }
    
    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('artist', array(
                'action' => 'add'
            ));
        }
        $artist = $this->getArtistTable()->getArtist($id);

        $form  = new ArtistForm();
        $form->bind($artist);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($artist->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getArtistTable()->saveArtist($form->getData());

                // Redirect to list of artists
                return $this->redirect()->toRoute('artist');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }
    
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('artist');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->getArtistTable()->deleteArtist($id);
            }

            // Redirect to list of artists
            return $this->redirect()->toRoute('artist');
        }

        return array(
            'id'    => $id,
            'artist' => $this->getArtistTable()->getArtist($id)
        );
    }
    
    public function getArtistTable()
    {
        if (!$this->artistTable) {
            $sm = $this->getServiceLocator();
            $this->artistTable = $sm->get('Artist\Model\ArtistTable');
        }
        return $this->artistTable;
    }
}