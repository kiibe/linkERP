<?php
/**
* CalendarView
*
* @version    1.0
* @package    samples
* @subpackage tutor
* @author     Pablo Dall'Oglio
* @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
* @license    http://www.adianti.com.br/framework-license
*/
class CalendarView extends TPage
{
   private $form;
   private $calendar;
   private $back_action;
   private $next_action;

   /**
    * Class constructor
    * Creates the page
    */
   function __construct()
   {
       parent::__construct();

       // create the calendar
       $this->calendar = new TCalendar;
       // Gett actual data

       $this->calendar->setMonth(date('m'));
       $this->calendar->setYear(date('Y'));

       $this->calendar->selectDays(date("j"));
       $this->calendar->setSize(900,650);

       $this->calendar->setAction( new TAction(array($this, 'onSelect')) );

       // creates a simple form
       $this->form = new TQuickForm('calendar_helper');

       // creates the notebook around the form
       $notebook = new TNotebook(300, 180);
       $notebook->appendPage('Calendar Helper', $this->form);

       // creates the form fields
       $year  = new TEntry('year');
       $month = new TEntry('month');
       $day   = new TEntry('day');
       $year->setValue( $this->calendar->getYear() );
       $month->setValue( $this->calendar->getMonth() );
       $day->setValue( $this->calendar->getSelectedDays() );

       $this->form->addQuickField('Year',  $year,  100);
       $this->form->addQuickField('Month', $month, 100);
       //$this->form->addQuickField('Day',   $day,   100);

       // creates a table to wrap the treeview and the form
       $table = new TTable;
       $this->form->addQuickAction('Back', new TAction(array($this, 'onBack')), 'ico_back.png');
       $this->form->addQuickAction('Next', new TAction(array($this, 'onNext')), 'ico_next.png');
       $row = $table->addRow();
       $cell=$row->addCell($this->calendar)->valign='top';
       $cell=$row->addCell($notebook)->valign='top';

       // wrap the page content using vertical box
       $vbox = new TVBox;
       $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
       $vbox->add($table);
       parent::add($vbox);
   }

   /**
    * Next month
    */
   public function onNext($param)
   {
       $data = $this->form->getData();
       $data->month ++;
       if ($data->month ==13)
       {
           $data->month = 1;
           $data->year ++;
       }
       $this->form->setData( $data );
       $this->calendar->setMonth($data->month);
       $this->calendar->setYear($data->year);
   }

   /**
    * Previous month
    */
   public function onBack($param)
   {
       $data = $this->form->getData();
       $data->month --;
       if ($data->month == 0)
       {
           $data->month = 12;
           $data->year --;
       }
       $this->form->setData( $data );
       $this->calendar->setMonth($data->month);
       $this->calendar->setYear($data->year);
   }

   /**
    * Executed when the user clicks at a tree node
    * @param $param URL parameters containing key and value
    */
   public function onSelect($param)
   {
      $table = new TTable;
        $table->style = 'width:100%';

        $table->addRowSet( new TLabel(_t('Notes')), '' )->class = 'tformtitle';

        // add the table inside the form
        $this->form->add($table);

        // create the form fields
        $hour = new TEntry('Hour');
        $hour->setValue(TSession::getValue('s_hour'));

        $place = new TEntry('Place');
        $place->setValue(TSession::getValue('s_place'));

        $description = new TEntry('Description');
        $description->setValue(TSession::getValue('s_description'));

        // add a row for the filter field
        $row=$table->addRow();
        $row->addCell(new TLabel(_t('Hour') . ': '));
        $row->addCell($hour);

        $row=$table->addRow();
        $row->addCell(new TLabel(_t('Place') . ': '));
        $row->addCell($place);

        $row=$table->addRow();
        $row->addCell(new TLabel(_t('Description') . ': '));
        $row->addCell($description);

        // create two action buttons to the form
        $find_button = new TButton('find');
        $new_button  = new TButton('new');

        // define the button actions
        $find_button->setAction(new TAction(array($this, 'onSearch')), _t('Find'));
        $find_button->setImage('fa:search');

        $new_button->setAction(new TAction(array('SystemNoteForm', 'onEdit')), _t('New'));
        $new_button->setImage('fa:plus-square green');

        $container = new THBox;
        $container->add($find_button);
        $container->add($new_button);

        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $container );
        $cell->colspan = 2;

        // define wich are the form fields
        $this->form->setFields(array($hour, $place, $description, $find_button, $new_button));

        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        // creates the datagrid columns
        $hour   = new TDataGridColumn('hour', _t('Hour'), 'center');
        $place = new TDataGridColumn('Place', _t('Place'), 'center');
        $description = new TDataGridColumn('Description', _t('Description'), 'center');

        // add the columns to the DataGrid
        $this->datagrid->addColumn($hour);
        $this->datagrid->addColumn($place);
        $this->datagrid->addColumn($description);

        // creates the datagrid column actions
        $order_hour= new TAction(array($this, 'onReload'));
        $order_hour->setParameter('order', 'hour');
        $hour->setAction($order_hour);

        $order_place= new TAction(array($this, 'onReload'));
        $order_place->setParameter('order', 'place');
        $place->setAction($order_place);

        $order_description= new TAction(array($this, 'onReload'));
        $order_description->setParameter('order', 'description');
        $description->setAction($order_description);

        // inline editing
        $place_edit = new TDataGridAction(array($this, 'onInlineEdit'));
        $place_edit->setField('hour');
        $place->setEditAction($place_edit);

        // creates two datagrid actions
        $action1 = new TDataGridAction(array('SystemNoteForm', 'onEdit'));
        $action1->setLabel(_t('Edit'));
        $action1->setImage('fa:pencil-square-o blue fa-lg');
        $action1->setField('hour');

        $action2 = new TDataGridAction(array($this, 'onDelete'));
        $action2->setLabel(_t('Delete'));
        $action2->setImage('fa:trash-o grey fa-lg');
        $action2->setField('hour');

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
       /* $container = new TTable;
        $container->style = 'width: 80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->addRow()->addCell($this->form);
        $container->addRow()->addCell($this->datagrid);
        $container->addRow()->addCell($this->pageNavigation);*/

        // add the container inside the page
        parent::add($container);
        new TMessage('info',  $table);

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
    }//end onInlineEdit

    /**
     * method onSearch()
     * Register the filter in the session when the user performs a search
     */

    function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();

        TSession::setValue('s_hour_filter',   NULL);
        TSession::setValue('s_place_filter', NULL);
        TSession::setValue('s_description_filter', NULL);

        TSession::setValue('s_hour', '');
        TSession::setValue('s_place', '');
        TSession::setValue('s_description', '');

        // check if the user has filled the form
        if ( $data->hour )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('hour', '=', "{$data->hour}");

            // stores the filter in the session
            TSession::setValue('s_hour_filter',   $filter);
            TSession::setValue('s_hour', $data->hour);
        }
        if ( $data->place )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('place', 'like', "%{$data->place}%");

            TSession::setValue('s_place_filter', $filter);
            TSession::setValue('s_place', $data->place);
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
                $param['order'] = 'hour';
                $param['direction'] = 'asc';
            }

            // creates a repository for System_group
            $repository = new TRepository('SystemGroup');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);

            if (TSession::getValue('s_hour_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_hour_filter'));
            }
            if (TSession::getValue('s_place_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('s_place_filter'));
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
