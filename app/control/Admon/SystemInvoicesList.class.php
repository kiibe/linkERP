<?php
/**
 * System_groupList Listing
 * @author  LinkERP
 */
class SystemInvoicesList extends TPage
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
        $this->form = new TForm('form_search_System_Receipt');
        $this->form->class = 'tform';

        // creates a table
        $table = new TTable;
        $table->style = 'width:100%';

        $row1 = $table->addRow();
        $row1->class = 'tformtitle';
        $cell1 = $row1-> addCell(new TLabel('Receipt'), '' );
        $cell1->colspan = 2 ;

        // add the table inside the form
        $this->form->add($table);

        // create the form fields
        $id = new TEntry('id');
        $id->setValue(TSession::getValue('s_id'));

        $client = new TEntry('client');
        $client->setValue(TSession::getValue('s_client'));

        $amount = new TEntry('account_money');
        $amount->setValue(TSession::getValue('s_amount'));

        $date = new TDate('date');
        $date->setValue(TSession::getValue('s_date'));

        $id->setSize(100);
        $client->setSize(300);
        $amount->setSize(300);
        $date->setSize(300);

        // add a row for the filter field
        $row=$table->addRow();
        $row->addCell(new TLabel('ID:'));
        $row->addCell($id);

        $row=$table->addRow();
        $row->addCell(new TLabel('Client:'));
        $row->addCell($client);

        $row=$table->addRow();
        $row->addCell(new TLabel('Account money: '));
        $row->addCell($amount);

        $row=$table->addRow();
        $row->addCell(new TLabel('Date: '));
        $row->addCell($date);


        // create two action buttons to the form
        $find_button = new TButton('find');
        $new_button  = new TButton('new');
        // define the button actions
        $find_button->setAction(new TAction(array($this, 'onSearch')), _t('Find'));
        $find_button->setImage('fa:search');

        $new_button->setAction(new TAction(array('SystemInvoicesForm', 'onEdit')), _t('New'));
        $new_button->setImage('fa:plus-square green');

        $container = new THBox;
        $container->add($find_button);
        $container->add($new_button);

        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $container );
        $cell->colspan = 2;

        // define wich are the form fields
        $this->form->setFields(array($id, $client, $amount, $date, $find_button, $new_button));

        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        // creates the datagrid columns
        $id   = new TDataGridColumn('id', 'ID', 'center');
        $client   = new TDataGridColumn('client', 'Client', 'center');
        $amount = new TDataGridColumn('account_money', 'Account money', 'center');
        $date = new TDataGridColumn('date', 'Date', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($client);
        $this->datagrid->addColumn($amount);
        $this->datagrid->addColumn($date);

        // creates the datagrid column actions
        $order_id= new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $id->setAction($order_id);

        $order_client= new TAction(array($this, 'onReload'));
        $order_client->setParameter('order', 'client');
        $client->setAction($order_client);

        $order_amount= new TAction(array($this, 'onReload'));
        $order_amount->setParameter('order', 'account_money');
        $amount->setAction($order_amount);

        $order_date= new TAction(array($this, 'onReload'));
        $order_date->setParameter('order', 'date');
        $date->setAction($order_date);


        // inline editing
        $client_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $client_edit->setField('id');
        $client->setEditAction($client_edit);

        $amount_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $amount_edit->setField('id');
        $amount->setEditAction($amount_edit);

        $date_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $date_edit->setField('id');
        $date->setEditAction($date_edit);



        // creates two datagrid actions
        $action1 = new TDataGridAction(array('SystemInvoicesForm', 'onEdit'));
        $action1->setLabel(_t('Edit'));
        $action1->setImage('fa:pencil-square-o blue fa-lg');
        $action1->setField('id');

        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setLabel(_t('Delete'));
        $action2->setImage('fa:trash-o grey fa-lg');
        $action2->setField('id');

        $action3 = new TDataGridAction(array($this, 'onPDF'));
        $action3->setLabel('Generate PDF');
        $action3->setImage('fa:file-pdf-o red');
        $action3->setField('id');

        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        $this->datagrid->addAction($action3);
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
            $object = new SystemReceipt($key);
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
        TSession::setValue('s_client_filter',   NULL);
        TSession::setValue('s_amount_filter', NULL);
        TSession::setValue('s_date_filter', NULL);

        TSession::setValue('s_id', '');
        TSession::setValue('s_client', '');
        TSession::setValue('s_amount', '');
        TSession::setValue('s_date', '');

        // check if the user has filled the form
        if ( $data->id )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('id', '=', "{$data->id}");

            // stores the filter in the session
            TSession::setValue('s_id_filter',   $filter);
            TSession::setValue('s_id', $data->id);
        }
        if ( $data->client )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('client', '=', "{$data->client}");

            // stores the filter in the session
            TSession::setValue('s_client_filter',   $filter);
            TSession::setValue('s_client', $data->client);
        }
        if ( $data->account_money )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('account_money', 'like', "%{$data->account_money}%");

            TSession::setValue('s_amount_filter', $filter);
            TSession::setValue('s_amount', $data->account_money);
        }
        if ( $data->date )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('date', 'like', "%{$data->date}%");

            TSession::setValue('s_date_filter', $filter);
            TSession::setValue('s_date', $data->date);
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
            $repository = new TRepository('SystemReceipt');
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
            if (TSession::getValue('s_client_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_client_filter'));
            }
            if (TSession::getValue('s_amount_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_amount_filter'));
            }
            if (TSession::getValue('s_date_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_date_filter'));
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
            $object = new SystemReceipt($key);

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

    /**
     * method onPDF()
     * executed whenever the user clicks at the pdf button
     */
    function onPDF($param)
    {

      $pdf = new pdf_invoice();

      if (!file_exists("app/output/invoice_".date("Ymd").".pdf") OR is_writable("app/output/invoice_".date("Ymd").".pdf"))
      {
          $pdf->Output('app/output/invoice_'.date('Ymd').'.pdf');
      }
      else
      {
          throw new Exception(_t('Permission denied') . ': ' . "app/output/invoice_".date("Ymd").".pdf");
      }

      parent::openFile("app/output/invoice_".date("Ymd").".pdf");

      // shows the success message
      new TMessage('info', 'Report generated. Please, enable popups in the browser (just in the web).');

    }
}
?>
