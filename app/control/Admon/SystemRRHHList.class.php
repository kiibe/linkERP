<?php
/**
 * System_groupList Listing
 * @author  LinkERP
 */
class SystemRRHHList extends TPage
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
        $this->form = new TForm('form_search_System_RRHH');
        $this->form->class = 'tform';

        // creates a table
        $table = new TTable;
        $table->style = 'width:100%';

        $table->addRowSet( new TLabel('RRHH'), '' )->class = 'tformtitle';

        // add the table inside the form
        $this->form->add($table);

        // create the form fields
        $id = new TEntry('id');
        $id->setValue(TSession::getValue('s_id'));

        $dni = new TEntry('dni');
        $dni->setValue(TSession::getValue('s_dni'));

        $name = new TEntry('name');
        $name->setValue(TSession::getValue('s_name'));

        $address = new TEntry('address');
        $address->setValue(TSession::getValue('s_address'));

        $email = new TEntry('email');
        $email->setValue(TSession::getValue('s_email'));

        $phone = new TEntry('phone');
        $phone->setValue(TSession::getValue('s_phone'));

        // add a row for the filter field
        $row=$table->addRow();
        $row->addCell(new TLabel('ID:'));
        $row->addCell($id);

        $row=$table->addRow();
        $row->addCell(new TLabel('DNI:'));
        $row->addCell($dni);

        $row=$table->addRow();
        $row->addCell(new TLabel('Name: '));
        $row->addCell($name);

        $row=$table->addRow();
        $row->addCell(new TLabel('Address: '));
        $row->addCell($address);

        $row=$table->addRow();
        $row->addCell(new TLabel('Email: '));
        $row->addCell($email);

        $row=$table->addRow();
        $row->addCell(new TLabel('Phone: '));
        $row->addCell($phone);

        // create two action buttons to the form
        $find_button = new TButton('find');
        $new_button  = new TButton('new');
        // define the button actions
        $find_button->setAction(new TAction(array($this, 'onSearch')), _t('Find'));
        $find_button->setImage('fa:search');

        $new_button->setAction(new TAction(array('SystemRRHHForm', 'onEdit')), _t('New'));
        $new_button->setImage('fa:plus-square green');

        $container = new THBox;
        $container->add($find_button);
        $container->add($new_button);

        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $container );
        $cell->colspan = 2;

        // define wich are the form fields
        $this->form->setFields(array($id, $dni, $name, $address, $email, $phone, $find_button, $new_button));

        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        // creates the datagrid columns
        $id   = new TDataGridColumn('id', 'ID', 'center');
        $dni   = new TDataGridColumn('dni', 'DNI', 'center');
        $name = new TDataGridColumn('name', 'Name', 'center');
        $address = new TDataGridColumn('address', 'Address', 'center');
        $email = new TDataGridColumn('email', 'Email', 'center');
        $phone = new TDataGridColumn('phone', 'Phone', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($dni);
        $this->datagrid->addColumn($name);
        $this->datagrid->addColumn($address);
        $this->datagrid->addColumn($email);
        $this->datagrid->addColumn($phone);

        // creates the datagrid column actions
        $order_id= new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $id->setAction($order_id);

        $order_dni= new TAction(array($this, 'onReload'));
        $order_dni->setParameter('order', 'dni');
        $dni->setAction($order_dni);

        $order_name= new TAction(array($this, 'onReload'));
        $order_name->setParameter('order', 'name');
        $name->setAction($order_name);

        $order_address= new TAction(array($this, 'onReload'));
        $order_address->setParameter('order', 'address');
        $address->setAction($order_address);

        $order_email= new TAction(array($this, 'onReload'));
        $order_email->setParameter('order', 'email');
        $email->setAction($order_email);

        $order_phone= new TAction(array($this, 'onReload'));
        $order_phone->setParameter('order', 'phone');
        $phone->setAction($order_phone);


        // inline editing
        $dni_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $dni_edit->setField('id');
        $dni->setEditAction($dni_edit);

        $name_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $name_edit->setField('id');
        $name->setEditAction($name_edit);

        $address_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $address_edit->setField('id');
        $address->setEditAction($address_edit);

        $email_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $email_edit->setField('id');
        $email->setEditAction($email_edit);

        $phone_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $phone_edit->setField('id');
        $phone->setEditAction($phone_edit);


        // creates two datagrid actions
        $action1 = new TDataGridAction(array('SystemRRHHForm', 'onEdit'));
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
            $object = new SystemGroup($key);
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
        TSession::setValue('s_dni_filter',   NULL);
        TSession::setValue('s_name_filter', NULL);
        TSession::setValue('s_address_filter', NULL);
        TSession::setValue('s_email_filter', NULL);
        TSession::setValue('s_phone_filter', NULL);

        TSession::setValue('s_id', '');
        TSession::setValue('s_dni', '');
        TSession::setValue('s_name', '');
        TSession::setValue('s_address', '');
        TSession::setValue('s_email', '');
        TSession::setValue('s_phone', '');

        // check if the user has filled the form
        if ( $data->id )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('id', '=', "{$data->id}");

            // stores the filter in the session
            TSession::setValue('s_id_filter',   $filter);
            TSession::setValue('s_id', $data->id);
        }
        if ( $data->dni )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('dni', '=', "{$data->dni}");

            // stores the filter in the session
            TSession::setValue('s_dni_filter',   $filter);
            TSession::setValue('s_dni', $data->dni);
        }
        if ( $data->name )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('name', 'like', "%{$data->name}%");

            TSession::setValue('s_name_filter', $filter);
            TSession::setValue('s_name', $data->name);
        }
        if ( $data->address )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('address', 'like', "%{$data->address}%");

            TSession::setValue('s_address_filter', $filter);
            TSession::setValue('s_address', $data->address);
        }
        if ( $data->email )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('email', 'like', "%{$data->email}%");

            TSession::setValue('s_email_filter', $filter);
            TSession::setValue('s_email', $data->email);
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
            $repository = new TRepository('SystemGroup');
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
            if (TSession::getValue('s_dni_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_dni_filter'));
            }
            if (TSession::getValue('s_name_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_name_filter'));
            }
            if (TSession::getValue('s_address_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_address_filter'));
            }
            if (TSession::getValue('s_email_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_email_filter'));
            }
            if (TSession::getValue('s_phone_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_phone_filter'));
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
            $object = new SystemGroup($key);

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