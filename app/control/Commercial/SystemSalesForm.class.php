<?php
/**
 * System_groupForm Registration
 * @author  LinkERP
 */
class SystemSalesForm extends TPage
{
    private $form_item;
    private $form_customer;
    private $datagrid;  // listing
    private $total;
    private $cartgrid;
    private $loaded;

    /**
     * Class constructor
     * Creates the page
     */
    public function __construct()
    {
        parent::__construct();
        new TSession;

        // creates the items form and add a table inside
        $this->form_item = new TForm('form_pos');
        $this->form_item->class = 'tform';
        $table_item = new TTable;
        $table_item-> width = '100%';
        $this->form_item->add($table_item);

        // create the form fields
        $product_id           = new TDBSeekButton('product_id', 'permission', 'form_pos', 'SystemStock', 'product', 'product_id', 'product');
        $product_description = new TEntry('product_description');
        $sale_price           = new TEntry('sale_price');
        $amount               = new TEntry('amount');
        $total                = new TEntry('total');

        // add validators
        $product_id->addValidation('Product', new TRequiredValidator);
        $amount->addValidation('Amount', new TRequiredValidator);

        // define the exit actions
        $product_id->setExitAction(new TAction(array($this, 'onExitProduct')));
        $amount->setExitAction(new TAction(array($this, 'onUpdateTotal')));

        // define some properties
        $product_id->setSize(50);
        $product_description->setEditable(FALSE);
        $sale_price->setEditable(FALSE);
        $total->setEditable(FALSE);
        $sale_price->setNumericMask(2, '.', ',');
        $total->setNumericMask(2, '.', ',');
        $sale_price->setSize(100);
        $amount->setSize(100);
        $total->setSize(100);

        // add a row for the form title
        $row  = $table_item->addRow();
        $row->class = 'tformtitle'; // CSS class
        $cell = $row->addCell( new TLabel('Point of Sales'));
        $cell->colspan = 4;

        // create the field labels
        $lab_pro = new TLabel('ID');
        $lab_des = new TLabel('Prodcut');
        $lab_pri = new TLabel('Price');
        $lab_amo = new TLabel('Amount');
        $lab_dis = new TLabel('Discount');
        $lab_tot = new TLabel('Total');

        // creates the action button
        $button1 = new TButton('add');
        $button1->setAction(new TAction(array($this, 'onAddItem')), 'Add item');
        $button1->setImage('ico_add.png');

        // add the form fields
        $table_item->addRowSet($lab_pro, $product_id,  $lab_des, $product_description);
        $table_item->addRowSet($lab_pri, $sale_price,  $lab_amo, $amount);

        // define the form fields
        $this->form_item->setFields(array($product_id, $product_description, $sale_price, $amount, $total, $button1));



        // creates the customer form and add a table inside it
        $this->form_customer = new TForm('form_customer');
        $this->form_customer->class = 'tform';
        $table_customer = new TTable;
        $table_customer-> width = '100%';
        $this->form_customer->add($table_customer);

        // add a row for the form title
        $row  = $table_customer->addRow();
        $row->class = 'tformtitle'; // CSS class
        $cell = $row->addCell( new TLabel('Client'));
        $cell->colspan = 5;

        // create the form fields
        $customer_id          = new TDBSeekButton('customer_id', 'permission', 'form_customer', 'SystemClients', 'name', 'customer_id', 'customer_name');
        $customer_name        = new TEntry('customer_name');

        // define validation and other properties
        $customer_id->addValidation('Client', new TRequiredValidator);

        $customer_id->setSize(50);
        $customer_name->setEditable(FALSE);

        // create tha form labels
        $lab_cus = new TLabel('Client');
        $lab_nam = new TLabel('Name');

        // action button
        $button2 = new TButton('save');
        $button2->setAction(new TAction(array($this, 'onSave')), 'Save and finish');
        $button2->setImage('ico_save.png');

        // add the form fields inside the table
        $table_customer->addRowSet($lab_cus, $customer_id, $lab_nam, $customer_name, $button2);

        // define the form fields
        $this->form_customer->setFields(array($customer_id, $customer_name, $button2));

        // creates the grid for items
        $this->cartgrid = new TQuickGrid;
        $this->cartgrid->class = 'tdatagrid_table customized-table';
        $this->cartgrid->makeScrollable();
        $this->cartgrid->setHeight( 150 );

        $this->cartgrid->addQuickColumn('ID', 'product_id', 'right', 25);
        $this->cartgrid->addQuickColumn('Description', 'product_description', 'left', 230);
        $this->cartgrid->addQuickColumn('Price', 'sale_price', 'right', 80);
        $this->cartgrid->addQuickColumn('Amount', 'amount', 'right', 70);
        $this->cartgrid->addQuickColumn('Discount', 'discount', 'right', 70);
        $this->cartgrid->addQuickColumn('Total', 'total', 'right', 100);

        $this->cartgrid->addQuickAction('Delete', new TDataGridAction(array($this, 'onDelete')), 'product_id', 'ico_delete.png');
        $this->cartgrid->createModel();



        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'SystemSalesList'));
        $vbox->add($this->form_item);
        $vbox->add(new TLabel('&nbsp;'));
        $vbox->add($this->cartgrid);
        $vbox->add(new TLabel('&nbsp;'));
        $vbox->add($this->form_customer);
        parent::add($vbox);
    }

    /**
     * Add a product into the cart
     */
    public function onAddItem()
    {
        try
        {
            $this->form_item->validate(); // validate form data

            $items = TSession::getValue('items'); // get items from session
            $item = $this->form_item->getData('SaleItem');
            $items[ $item->product_id ] = $item; // add the item
            TSession::setValue('items', $items); // store back tthe session
            $this->form_item->clear(); // clear form
            $this->onReload(); // reload data
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
        }
    }

    /**
     * Saves the cart
     */
    public function onSave()
    {
        try
        {
            $this->form_customer->validate(); // validate form data

            $data = $this->form_customer->getData();

            TTransaction::open('permission');
            $items = TSession::getValue('items'); // get items
            if ($items)
            {
                $sale = new Sale; // create a new Sale
                $sale->customer_id = $data->customer_id;
                $sale->date = date('Y-m-d');
                $total = 0;
                foreach ($items as $item)
                {
                    $item->sale_price = str_replace(',', '', $item->sale_price);
                    $item->total      = str_replace(',', '', $item->total);
                    $total += str_replace(',', '', $item->total);

                    $sale->addSaleItem($item); // add the item to the Sale
                }
                $sale->total = $total;
                $sale->store(); // store the Sale

                // clear items
                TSession::setValue('items', NULL);
                $this->form_customer->clear(); // clear form
                new TMessage('info', 'Record saved successfully');
            }
            TTransaction::close();
            $this->onReload();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Exit action for the field product
     * Fill some form fields (sale_price, amount, discount, total)
     */
    public static function onExitProduct($param)
    {
        $product_id = $param['product_id']; // get the product code
        try
        {
            TTransaction::open('permission');
            //$product = new Product($product_id); // reads the product
            $conn = TTransaction::get(); // get PDO connection
            // run query
            $result = $conn->query('SELECT product, quantity, price from system_stock where id = '.$product_id.' order by id LIMIT 1');
            print_r($result);

            $obj = new StdClass;
            $obj->sale_price  = number_format($result['price'], 2, '.', ',');
            $obj->amount = 1;
            $obj->total       = number_format($result['quantity'], 2, '.', ',');
            TTransaction::close();
            TForm::sendData('form_pos', $obj);
        }
        catch (Exception $e)
        {
            // does nothing
        }
    }

    /**
     * Update the total based on the sale price, amount and discount
     */
    public static function onUpdateTotal($param)
    {
        $sale_price = (double) str_replace(',', '', $param['sale_price']);
        $amount     = (double) str_replace(',', '', $param['amount']);

        $obj = new StdClass;
        $obj->total       = number_format( ($sale_price * $amount), 2, '.', ',');
        TForm::sendData('form_pos', $obj);
    }

    /**
     * Remove a product from the cart
     */
    public function onDelete($param)
    {
        // get the cart objects from session
        $items = TSession::getValue('items');
        unset($items[ $param['key'] ]); // remove the product from the array
        TSession::setValue('items', $items); // put the array back to the session

        // reload datagrid
        $this->onReload( func_get_arg(0) );
    }

    /**
     * Reload the datagrid with the objects from the session
     */
    function onReload($param = NULL)
    {
        try
        {
            $this->cartgrid->clear(); // clear datagrid
            $items = TSession::getValue('items');
            if ($items)
            {
                foreach ($items as $object)
                {
                    // add the item inside the datagrid
                    $this->cartgrid->addItem($object);
                }
            }
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
        }
    }

    /**
     * Show the page
     */
    public function show()
    {
        if (!$this->loaded)
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
