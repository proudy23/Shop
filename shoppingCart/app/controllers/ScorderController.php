<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class ScorderController extends ControllerBase
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->persistent->parameters = null;
    }

    /**
     * Searches for scOrder
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Scorder', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "id";

        $scOrder = Scorder::find($parameters);
        if (count($scOrder) == 0) {
            $this->flash->notice("The search did not find any scOrder");

            $this->dispatcher->forward([
                "controller" => "scOrder",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $scOrder,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

    /**
     * Displays the creation form
     */
    public function newAction()
    {

    }

    /**
     * Edits a scOrder
     *
     * @param string $id
     */
    public function editAction($id)
    {
        if (!$this->request->isPost()) {

            $scOrder = Scorder::findFirstByid($id);
            if (!$scOrder) {
                $this->flash->error("scOrder was not found");

                $this->dispatcher->forward([
                    'controller' => "scOrder",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $scOrder->getId();

            $this->tag->setDefault("id", $scOrder->getId());
            $this->tag->setDefault("orderDate", $scOrder->getOrderdate());
            $this->tag->setDefault("deliveryStreet", $scOrder->getDeliverystreet());
            $this->tag->setDefault("deliveryCity", $scOrder->getDeliverycity());
            $this->tag->setDefault("deliveryCounty", $scOrder->getDeliverycounty());
            
        }
    }

    /**
     * Creates a new scOrder
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "scOrder",
                'action' => 'index'
            ]);

            return;
        }

        $scOrder = new Scorder();
        $scOrder->setorderDate($this->request->getPost("orderDate"));
        $scOrder->setdeliveryStreet($this->request->getPost("deliveryStreet"));
        $scOrder->setdeliveryCity($this->request->getPost("deliveryCity"));
        $scOrder->setdeliveryCounty($this->request->getPost("deliveryCounty"));
        

        if (!$scOrder->save()) {
            foreach ($scOrder->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "scOrder",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("scOrder was created successfully");

        $this->dispatcher->forward([
            'controller' => "scOrder",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a scOrder edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "scOrder",
                'action' => 'index'
            ]);

            return;
        }

        $id = $this->request->getPost("id");
        $scOrder = Scorder::findFirstByid($id);

        if (!$scOrder) {
            $this->flash->error("scOrder does not exist " . $id);

            $this->dispatcher->forward([
                'controller' => "scOrder",
                'action' => 'index'
            ]);

            return;
        }

        $scOrder->setorderDate($this->request->getPost("orderDate"));
        $scOrder->setdeliveryStreet($this->request->getPost("deliveryStreet"));
        $scOrder->setdeliveryCity($this->request->getPost("deliveryCity"));
        $scOrder->setdeliveryCounty($this->request->getPost("deliveryCounty"));
        

        if (!$scOrder->save()) {

            foreach ($scOrder->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "scOrder",
                'action' => 'edit',
                'params' => [$scOrder->getId()]
            ]);

            return;
        }

        $this->flash->success("scOrder was updated successfully");

        $this->dispatcher->forward([
            'controller' => "scOrder",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a scOrder
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $scOrder = Scorder::findFirstByid($id);
        if (!$scOrder) {
            $this->flash->error("scOrder was not found");

            $this->dispatcher->forward([
                'controller' => "scOrder",
                'action' => 'index'
            ]);

            return;
        }

        if (!$scOrder->delete()) {

            foreach ($scOrder->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "scOrder",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("scOrder was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "scOrder",
            'action' => "index"
        ]);
    }

}
