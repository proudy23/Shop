<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;


class ProductController extends ControllerBase
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->persistent->parameters = null;
    }

    /**
     * Searches for product
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Product', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "id";

        $product = Product::find($parameters);
        if (count($product) == 0) {
            $this->flash->notice("The search did not find any product");

            $this->dispatcher->forward([
                "controller" => "product",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $product,
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
     * Edits a product
     *
     * @param string $id
     */
    public function editAction($id)
    {
        if (!$this->request->isPost()) {

            $product = Product::findFirstByid($id);
            if (!$product) {
                $this->flash->error("product was not found");

                $this->dispatcher->forward([
                    'controller' => "product",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $product->getId();

            $this->tag->setDefault("id", $product->getId());
            $this->tag->setDefault("name", $product->getName());
            $this->tag->setDefault("description", $product->getDescription());
            $this->tag->setDefault("colour", $product->getColour());
            $this->tag->setDefault("price", $product->getPrice());
            $this->tag->setDefault("image", $product->getImage());
            
        }
    }

    /**
     * Creates a new product
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "product",
                'action' => 'index'
            ]);

            return;
        }

        $product = new Product();
        $product->setname($this->request->getPost("name"));
        $product->setdescription($this->request->getPost("description"));
        $product->setcolour($this->request->getPost("colour"));
        $product->setprice($this->request->getPost("price"));
        $product->setimage($this->request->getPost("image"));
        

        if (!$product->save()) {
            foreach ($product->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "product",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("product was created successfully");

        $this->dispatcher->forward([
            'controller' => "product",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a product edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "product",
                'action' => 'index'
            ]);

            return;
        }

        $id = $this->request->getPost("id");
        $product = Product::findFirstByid($id);

        if (!$product) {
            $this->flash->error("product does not exist " . $id);

            $this->dispatcher->forward([
                'controller' => "product",
                'action' => 'index'
            ]);

            return;
        }

        $product->setname($this->request->getPost("name"));
        $product->setdescription($this->request->getPost("description"));
        $product->setcolour($this->request->getPost("colour"));
        $product->setprice($this->request->getPost("price"));
        $product->setimage($this->request->getPost("image"));
        

        if (!$product->save()) {

            foreach ($product->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "product",
                'action' => 'edit',
                'params' => [$product->getId()]
            ]);

            return;
        }

        $this->flash->success("product was updated successfully");

        $this->dispatcher->forward([
            'controller' => "product",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a product
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $product = Product::findFirstByid($id);
        if (!$product) {
            $this->flash->error("product was not found");

            $this->dispatcher->forward([
                'controller' => "product",
                'action' => 'index'
            ]);

            return;
        }

        if (!$product->delete()) {

            foreach ($product->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "product",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("product was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "product",
            'action' => "index"
        ]);
    }
	public function displayGridAction()
	{
		if ($this->session->has('cart')) {
				$cart = $this->session->get('cart');
				$totalQty=0;
				foreach ($cart as $product => $qty) {
						 $totalQty = $totalQty + $qty;
				}
				$this->view->totalItems=$totalQty;
		}
		else {
				$this->view->totalItems=0;
		}
		$this->view->products = Product::find();
}
	
	public function addItemAction()
	{
		$productid = $this->request->getPost('productid');
		if ($this->session->has('cart')) {
			$cart = $this->session->get('cart');
			if (isset($cart[$productid])) {
				$cart[$productid]=$cart[$productid]+1; //add one to product in cart
			}
			else {
					$cart[$productid]=1; //new product in cart
			}
	}
	else {
			$cart[$productid]=1; //new cart
	}
	$this->session->set('cart',$cart); // make the cart a session variable
}
	public function emptyCartAction()
	{
		$this->session->remove('cart');
	}
	public function checkOutAction()
	{
		if ($this->session->has('cart')) {
			$cart = $this->session->get('cart');
			$lineitems = array();
			foreach ($cart as $productid => $qty) {
					$lineitem['product'] = Product::findFirstByid($productid);
					$lineitem['qty'] = $qty;
					$lineitems[] = $lineitem;
		}
		$this->view->lineitems = $lineitems;
	}
	else {
		$this->flash->error("There are no items in your cart");
		$this->dispatcher->forward(['controller' => "product",'action' => 'displayGrid']);
	}
}
		public function placeOrderAction()
	{
		$thisOrder = new Scorder();
		$thisOrder->setOrderDate((new DateTime())->format("Y-m-d H:i:s"));
		$thisOrder->save();
		$orderID = $thisOrder->getId();
	
		$productids = $this->request->getPost("productid");
		$quantities = $this->request->getPost("quantity");
		for($i=0;$i<sizeof($productids);$i++) {
				$thisOrderDetail = new OrderDetail();
				$thisOrderDetail->setOrderid($orderID);
				$thisOrderDetail->setProductid($productids[$i]);
				$thisOrderDetail->setQuantity($quantities[$i]);
				$thisOrderDetail->save();
		}
		$this->session->remove('cart');
		$this->flash->success("The Order has been placed");
		$this->dispatcher->forward(["controller" => "product", "action" => "displayGrid"]);
	}
}