<?php
/**
 * System_groupList Listing
 * @author  LinkERP
 */
class SystemStockList extends TPage
{
    private $form;     // registration form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;

    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();

        // creates the form
        $this->form = new TForm('form_search_System_Stock');
        $this->form->class = 'tform';

        // creates a table
        $table = new TTable;
        $table->style = 'width:100%';

        $row1 = $table->addRow();
        $row1->class = 'tformtitle';
        $cell1 = $row1-> addCell(new TLabel('Stock'), '' );
        $cell1->colspan = 2 ;

        // add the table inside the form
        $this->form->add($table);

        // create the form fields
        $id = new TEntry('id');
        $id->setValue(TSession::getValue('s_id'));

        $product = new TEntry('product');
        $product->setValue(TSession::getValue('s_product'));

        $quantity = new TEntry('quantity');
        $quantity->setValue(TSession::getValue('s_quantity'));

        $price = new TEntry('price');
        $price->setValue(TSession::getValue('s_price'));

        $id->setSize(100);
        $product->setSize(300);
        $quantity->setSize(300);
        $price->setSize(300);

        // add a row for the filter field
        $row=$table->addRow();
        $row->addCell(new TLabel('ID:'));
        $row->addCell($id);

        $row=$table->addRow();
        $row->addCell(new TLabel('Product: '));
        $row->addCell($product);

        $row=$table->addRow();
        $row->addCell(new TLabel('Quantity: '));
        $row->addCell($quantity);

        $row=$table->addRow();
        $row->addCell(new TLabel('Price: '));
        $row->addCell($price);

        // create two action buttons to the form
        $find_button = new TButton('find');
        $new_button  = new TButton('new');
        // define the button actions
        $find_button->setAction(new TAction(array($this, 'onSearch')), _t('Find'));
        $find_button->setImage('fa:search');

        $new_button->setAction(new TAction(array('SystemStockForm', 'onEdit')), _t('New'));
        $new_button->setImage('fa:plus-square green');

        $container = new THBox;
        $container->add($find_button);
        $container->add($new_button);

        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $container );
        $cell->colspan = 2;

        // define wich are the form fields
        $this->form->setFields(array($id, $product, $quantity, $price, $find_button, $new_button));

        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        // creates the datagrid columns
        $id   = new TDataGridColumn('id', 'ID', 'center');
        $product = new TDataGridColumn('product', 'Product', 'center');
        $quantity = new TDataGridColumn('quantity', 'Quantity', 'center');
        $price = new TDataGridColumn('price', 'Price', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($product);
        $this->datagrid->addColumn($quantity);
        $this->datagrid->addColumn($price);

        // creates the datagrid column actions
        $order_id= new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $id->setAction($order_id);

        $order_product= new TAction(array($this, 'onReload'));
        $order_product->setParameter('order', 'product');
        $product->setAction($order_product);

        $order_quantity= new TAction(array($this, 'onReload'));
        $order_quantity->setParameter('order', 'quantity');
        $quantity->setAction($order_quantity);

        $order_price= new TAction(array($this, 'onReload'));
        $order_price->setParameter('order', 'price');
        $price->setAction($order_price);


        // inline editing
        $product_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $product_edit->setField('id');
        $product->setEditAction($product_edit);

        $quantity_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $quantity_edit->setField('id');
        $quantity->setEditAction($quantity_edit);

        $price_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $price_edit->setField('id');
        $price->setEditAction($price_edit);


        // creates two datagrid actions
        $action1 = new TDataGridAction(array('SystemStockForm', 'onEdit'));
        $action1->setLabel(_t('Edit'));
        $action1->setImage('fa:pencil-square-o blue fa-lg');
        $action1->setField('id');

        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setLabel(_t('Delete'));
        $action2->setImage('fa:trash-o grey fa-lg');
        $action2->setField('id');

        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);

        // create the datagrid model
        $this->datagrid->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        // creates the page structure using a table
        $container = new TTable;
        $container->style = 'width: 80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->addRow()->addCell($this->form);
        $container->addRow()->addCell($this->datagrid);
        $container->addRow()->addCell($this->pageNavigation);

        // add the container inside the page
        parent::add($container);
    }

    /**
     * method onInlineEdit()
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content
     */
    function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];

            // open a transaction with database 'permission'
            TTransaction::open('permission');

            // instantiates object System_group
            $object = new SystemStock($key);
            // deletes the object from the database
            $object->{$field} = $value;
            $object->store();

            // close the transaction
            TTransaction::close();

            // reload the listing
            $this->onReload($param);
            // shows the success message
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * method onSearch()
     * Register the filter in the session when the user performs a search
     */
    function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();

        TSession::setValue('s_id_filter',   NULL);
        TSession::setValue('s_product_filter', NULL);
        TSession::setValue('s_quantity_filter', NULL);
        TSession::setValue('s_price_filter', NULL);

        TSession::setValue('s_id', '');
        TSession::setValue('s_product', '');
        TSession::setValue('s_quantity', '');
        TSession::setValue('s_price', '');

        // check if the user has filled the form
        if ( $data->id )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('id', '=', "{$data->id}");

            // stores the filter in the session
            TSession::setValue('s_id_filter',   $filter);
            TSession::setValue('s_id', $data->id);
        }
        if ( $data->product )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('product', 'like', "%{$data->product}%");

            TSession::setValue('s_product_filter', $filter);
            TSession::setValue('s_product', $data->product);
        }
        if ( $data->quantity )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('quantity', 'like', "%{$data->quantity}%");

            TSession::setValue('s_quantity_filter', $filter);
            TSession::setValue('s_quantity', $data->quantity);
        }
        if ( $data->price )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('price', 'like', "%{$data->price}%");

            TSession::setValue('s_price_filter', $filter);
            TSession::setValue('s_price', $data->price);
        }



        // fill the form with data again
        $this->form->setData($data);

        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }

    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'permission'
            TTransaction::open('permission');

            if( ! isset($param['order']) )
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }

            // creates a repository for System_group
            $repository = new TRepository('SystemStock');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);

            if (TSession::getValue('s_id_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_id_filter'));
            }
            if (TSession::getValue('s_product_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_product_filter'));
            }
            if (TSession::getValue('s_quantity_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_quantity_filter'));
            }
            if (TSession::getValue('s_price_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_price_filter'));
            }


            // load the objects according to criteria
            $objects = $repository->load($criteria);

            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }

            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);

            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit

            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * method onDelete()
     * executed whenever the user clicks at the delete button
     * Ask if the user really wants to delete the record
     */
    function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead

        // shows a dialog to the user
        new TQuestion(TAdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }

    /**
     * method Delete()
     * Delete a record
     */
    function Delete($param)
    {
        try
        {
            // get the parameter $key
            $key=$param['key'];
            // open a transaction with database 'permission'
            TTransaction::open('permission');

            // instantiates object System_group
            $object = new SystemStock($key);

            // deletes the object from the database
            $object->delete();

            // close the transaction
            TTransaction::close();

            // reload the listing
            $this->onReload( $param );
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted'));
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * method show()
     * Shows the page
     */
    function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded)
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
?>
