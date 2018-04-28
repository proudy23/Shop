<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class OrderdetailController extends ControllerBase
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->persistent->parameters = null;
    }

    /**
     * Searches for orderDetail
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Orderdetail', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "id";

        $orderDetail = Orderdetail::find($parameters);
        if (count($orderDetail) == 0) {
            $this->flash->notice("The search did not find any orderDetail");

            $this->dispatcher->forward([
                "controller" => "orderDetail",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $orderDetail,
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
     * Edits a orderDetail
     *
     * @param string $id
     */
    public function editAction($id)
    {
        if (!$this->request->isPost()) {

            $orderDetail = Orderdetail::findFirstByid($id);
            if (!$orderDetail) {
                $this->flash->error("orderDetail was not found");

                $this->dispatcher->forward([
                    'controller' => "orderDetail",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $orderDetail->getId();

            $this->tag->setDefault("id", $orderDetail->getId());
            $this->tag->setDefault("productid", $orderDetail->getProductid());
            $this->tag->setDefault("orderid", $orderDetail->getOrderid());
            $this->tag->setDefault("quantity", $orderDetail->getQuantity());
            $this->tag->setDefault("subtotal", $orderDetail->getSubtotal());
            
        }
    }

    /**
     * Creates a new orderDetail
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "orderDetail",
                'action' => 'index'
            ]);

            return;
        }

        $orderDetail = new Orderdetail();
        $orderDetail->setproductid($this->request->getPost("productid"));
        $orderDetail->setorderid($this->request->getPost("orderid"));
        $orderDetail->setquantity($this->request->getPost("quantity"));
        $orderDetail->setsubtotal($this->request->getPost("subtotal"));
        

        if (!$orderDetail->save()) {
            foreach ($orderDetail->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "orderDetail",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("orderDetail was created successfully");

        $this->dispatcher->forward([
            'controller' => "orderDetail",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a orderDetail edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "orderDetail",
                'action' => 'index'
            ]);

            return;
        }

        $id = $this->request->getPost("id");
        $orderDetail = Orderdetail::findFirstByid($id);

        if (!$orderDetail) {
            $this->flash->error("orderDetail does not exist " . $id);

            $this->dispatcher->forward([
                'controller' => "orderDetail",
                'action' => 'index'
            ]);

            return;
        }

        $orderDetail->setproductid($this->request->getPost("productid"));
        $orderDetail->setorderid($this->request->getPost("orderid"));
        $orderDetail->setquantity($this->request->getPost("quantity"));
        $orderDetail->setsubtotal($this->request->getPost("subtotal"));
        

        if (!$orderDetail->save()) {

            foreach ($orderDetail->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "orderDetail",
                'action' => 'edit',
                'params' => [$orderDetail->getId()]
            ]);

            return;
        }

        $this->flash->success("orderDetail was updated successfully");

        $this->dispatcher->forward([
            'controller' => "orderDetail",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a orderDetail
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $orderDetail = Orderdetail::findFirstByid($id);
        if (!$orderDetail) {
            $this->flash->error("orderDetail was not found");

            $this->dispatcher->forward([
                'controller' => "orderDetail",
                'action' => 'index'
            ]);

            return;
        }

        if (!$orderDetail->delete()) {

            foreach ($orderDetail->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "orderDetail",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("orderDetail was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "orderDetail",
            'action' => "index"
        ]);
    }

}
