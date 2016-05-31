<?php
/**
 * System_groupForm Registration
 * @author  LinkERP
 */
class SystemSalesForm extends TPage
{
    protected $form; // form

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();

        // creates the table container
        $table = new TTable;
        $table->style = 'width:100%';

        $frame_programs = new TFrame;

        // creates the form
        $this->form = new TForm('form_System_sales');
        $this->form->class = 'tform';


        // add the notebook inside the form
        $this->form->add($table);
        $row1 = $table->addRow();
        $row1->class = 'tformtitle';
        $cell1 = $row1-> addCell(new TLabel('Add new Sale'), '' );
        $cell1->colspan = 2 ;

        // create the form fields
        $id              = new TEntry('id');
        $date            = new TDate('date');
        $client          = new TEntry('client');
        $amount          = new TEntry('amount');
        $id->setEditable(false);

        // define the sizes
        $id->setSize(100);
        $date->setSize(300);
        $client->setSize(300);
        $amount->setSize(300);

        // validations
        $date->addValidation('date', new TRequiredValidator);
        $client->addValidation('client', new TRequiredValidator);
        $amount->addValidation('amount', new TRequiredValidator);

        // add a row for the field id
        $table->addRowSet(new TLabel('ID:'), $id);
        $table->addRowSet(new TLabel('Date: '), $date);
        $table->addRowSet(new TLabel('Client: '), $client);
        $table->addRowSet(new TLabel('Amount: '), $amount);


        // create an action button (save)
        $save_button=new TButton('save');
        $save_button->setAction(new TAction(array($this, 'onSave')), _t('Save'));
        $save_button->setImage('fa:floppy-o');

        // create an new button (edit with no parameters)
        $new_button=new TButton('new');
        $new_button->setAction(new TAction(array($this, 'onEdit')), _t('New'));
        $new_button->setImage('fa:plus-square green');

        $list_button=new TButton('list');
        $list_button->setAction(new TAction(array('SystemSalesList','onReload')), _t('Back to the listing'));
        $list_button->setImage('fa:table blue');

        // define the form fields
        $this->form->setFields(array($id,$date,$client,$amount,$save_button,$new_button,$list_button));

        $buttons = new THBox;
        $buttons->add($save_button);
        $buttons->add($new_button);
        $buttons->add($list_button);

        $container = new TTable;
        $container->width = '80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', 'SystemSalesList'));
        $container->addRow()->addCell($this->form);

        $row=$table->addRow();
        $row->class = 'tformaction';
        $cell = $row->addCell( $buttons );
        $cell->colspan = 2;

        // add the form to the page
        parent::add($container);
    }

    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    function onSave()
    {
        try
        {
            // open a transaction with database 'permission'
            TTransaction::open('permission');

            // get the form data into an active record System_group
            $object = $this->form->getData('SystemGroup');

            $this->form->validate(); // form validation
            $object->store(); // stores the object
            $object->clearParts();
            if( $object->programs )
            {
                foreach( $object->programs as $program )
                {
                    $object->addSystemProgram( $program );
                }
            }

            $this->form->setData($object); // fill the form with the active record data

            TTransaction::close(); // close the transaction
            new TMessage('info', _t('Record saved')); // shows the success message
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
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key=$param['key'];

                // open a transaction with database 'permission'
                TTransaction::open('permission');

                // instantiates object System_group
                $object = new SystemGroup($key);

                $object->programs = $object->getSystemPrograms();

                // fill the form with the active record data
                $this->form->setData($object);

                // close the transaction
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }
}
