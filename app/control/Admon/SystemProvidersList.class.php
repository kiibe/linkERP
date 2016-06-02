<?php
/**
 * System_groupList Listing
 * @author  LinkERP
 */
class SystemProvidersList extends TPage
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
        $this->form = new TForm('form_search_System_Provider');
        $this->form->class = 'tform';

        // creates a table
        $table = new TTable;
        $table->style = 'width:100%';

        $row1 = $table->addRow();
        $row1->class = 'tformtitle';
        $cell1 = $row1-> addCell(new TLabel('Provider'), '' );
        $cell1->colspan = 2 ;
        // add the table inside the form
        $this->form->add($table);

        // create the form fields
        $id = new TEntry('id');
        $id->setValue(TSession::getValue('s_id'));

        $nif = new TEntry('nif');
        $nif->setValue(TSession::getValue('s_nif'));

        $name = new TEntry('name');
        $name->setValue(TSession::getValue('s_name'));

        $id->setSize(100);
        $nif->setSize(300);
        $name->setSize(300);


        // add a row for the filter field
        $row=$table->addRow();
        $row->addCell(new TLabel('ID: '));
        $row->addCell($id);

        $row=$table->addRow();
        $row->addCell(new TLabel('NIF: '));
        $row->addCell($nif);

        $row=$table->addRow();
        $row->addCell(new TLabel('Name: '));
        $row->addCell($name);

        // create two action buttons to the form
        $find_button = new TButton('find');
        $new_button  = new TButton('new');
        $pdf_button = new TButton('pdf');
        // define the button actions
        $find_button->setAction(new TAction(array($this, 'onSearch')), _t('Find'));
        $find_button->setImage('fa:search');

        $new_button->setAction(new TAction(array('SystemProvidersForm', 'onEdit')), _t('New'));
        $new_button->setImage('fa:plus-square green');

        $pdf_button->setAction(new TAction(array($this, 'onPDF')), 'PDF');
        $pdf_button->setImage('fa:file-pdf-o red');

        $container = new THBox;
        $container->add($find_button);
        $container->add($new_button);
        $container->add($pdf_button);

        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $container );
        $cell->colspan = 2;

        // define wich are the form fields
        $this->form->setFields(array($id, $nif, $name, $find_button, $new_button, $pdf_button));

        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        // creates the datagrid columns
        $id   = new TDataGridColumn('id', 'ID', 'center');
        $nif = new TDataGridColumn('nif', 'NIF', 'center');
        $name = new TDataGridColumn('name', 'Name', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($nif);
        $this->datagrid->addColumn($name);

        // creates the datagrid column actions
        $order_id= new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $id->setAction($order_id);

        $order_nif= new TAction(array($this, 'onReload'));
        $order_nif->setParameter('order', 'nif');
        $nif->setAction($order_nif);

        $order_name= new TAction(array($this, 'onReload'));
        $order_name->setParameter('order', 'name');
        $name->setAction($order_name);


        // inline editing
        $nif_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $nif_edit->setField('id');
        $nif->setEditAction($nif_edit);

        $name_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $name_edit->setField('id');
        $name->setEditAction($name_edit);



        // creates two datagrid actions
        $action1 = new TDataGridAction(array('SystemProvidersForm', 'onEdit'));
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
            $object = new SystemProviders($key);
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
        TSession::setValue('s_nif_filter', NULL);
        TSession::setValue('s_name_filter', NULL);

        TSession::setValue('s_id', '');
        TSession::setValue('s_nif', '');
        TSession::setValue('s_name', '');

        // check if the user has filled the form
        if ( $data->id )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('id', '=', "{$data->id}");

            // stores the filter in the session
            TSession::setValue('s_id_filter',   $filter);
            TSession::setValue('s_id', $data->id);
        }
        if ( $data->nif )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('nif', 'like', "%{$data->nif}%");

            TSession::setValue('s_nif_filter', $filter);
            TSession::setValue('s_nif', $data->nif);
        }
        if ( $data->name )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('name', 'like', "%{$data->name}%");

            TSession::setValue('s_name_filter', $filter);
            TSession::setValue('s_name', $data->name);
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
            $repository = new TRepository('SystemProviders');
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
            if (TSession::getValue('s_nif_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_nif_filter'));
            }
            if (TSession::getValue('s_name_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_name_filter'));
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
            $object = new SystemProviders($key);

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
        try
        {
          TTransaction::open('permission'); // open transaction
          $conn = TTransaction::get(); // get PDO connection

          // run query
          $result = $conn->query('SELECT nif, name from system_providers order by id');

          // get the form data into an active record Customer -> tocar las columnas
          $widths = array(270, 270);
          $tr = new TTableWriterPDF($widths);

          // create the document styles
          $tr->addStyle('title', 'Arial', '12', 'B',  '#000000', '#A0B2BC');
          $tr->addStyle('par', 'Arial', '10', '',    '#000000', '#ECE9E9');
          $tr->addStyle('impar', 'Arial', '10', '',    '#000000', '#FFFFFF');
          $tr->addStyle('header', 'Times', '16', 'B', '#FFFFFF', '#4C5976');
          $tr->addStyle('footer', 'Times', '12', 'B', '#000000', '#88BACF');

          // add a header row
          $tr->addRow();
          $tr->addCell('Providers', 'center', 'header', 2);

          // add titles row
          $tr->addRow();
          $tr->addCell('NIF','center', 'title');
          $tr->addCell('Name','center', 'title');

          // controls the background filling
          $colour= FALSE;
          foreach ($result as $row)
          {
              $style = $colour ? 'par' : 'impar';
              $tr->addRow();
              $tr->addCell($row['nif'], 'left', $style);
              $tr->addCell($row['name'], 'left', $style);
              $colour = !$colour;
          }

          // footer row
          $tr->addRow();
          $tr->addCell(date('l jS \of F Y h:i:s A'), 'center', 'footer', 2);
          $tr->Footer('This document contains information about clients of the company.');

          if (!file_exists("app/output/providersList_".date("Ymd")."pdf") OR is_writable("app/output/providersList_".date("Ymd").".pdf"))
          {
              $tr->save("app/output/providersList_".date("Ymd").".pdf");
          }
          else
          {
              throw new Exception(_t('Permission denied') . ': ' . "app/output/providersList_".date("Ymd").".pdf");
          }

          parent::openFile("app/output/providersList_".date("Ymd").".pdf");

          // shows the success message
          new TMessage('info', 'Report generated. Please, enable popups in the browser (just in the web).');

          TTransaction::close(); // close transaction
        }
        catch (Exception $e) // in case of exception
        {
          // shows the exception error message
          new TMessage('error', '<b>Error</b> ' . $e->getMessage());
        }
    }
}
?>
