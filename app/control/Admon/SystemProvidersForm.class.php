<?php
/**
 * System_groupForm Registration
 * @author  LinkERP
 */
class SystemProvidersForm extends TPage
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

        // creates the form
        $this->form = new TForm('form_System_Provider');
        $this->form->class = 'tform';


        // add the notebook inside the form
        $this->form->add($table);
        $row1 = $table->addRow();
        $row1->class = 'tformtitle';
        $cell1 = $row1-> addCell(new TLabel('Add new provider'), '' );
        $cell1->colspan = 2 ;

        // create the form fields
        $id            = new TEntry('id');
        $nif           = new TEntry('nif');
        $name          = new TEntry('name');
        $id->setEditable(false);

        // define the sizes
        $id->setSize(100);
        $nif->setSize(300);
        $name->setSize(300);

        // validations
        $nif->addValidation('nif', new TRequiredValidator);
        $name->addValidation('name', new TRequiredValidator);

        // add a row for the field id
        $table->addRowSet(new TLabel('ID:'), $id);
        $table->addRowSet(new TLabel('NIF: '), $nif);
        $table->addRowSet(new TLabel('Name: '), $name);


        // create an action button (save)
        $save_button=new TButton('save');
        $save_button->setAction(new TAction(array($this, 'onSave')), _t('Save'));
        $save_button->setImage('fa:floppy-o');

        // create an new button (edit with no parameters)
        $new_button=new TButton('new');
        $new_button->setAction(new TAction(array($this, 'onEdit')), _t('New'));
        $new_button->setImage('fa:plus-square green');

        $list_button=new TButton('list');
        $list_button->setAction(new TAction(array('SystemProvidersList','onReload')), _t('Back to the listing'));
        $list_button->setImage('fa:table blue');

        // define the form fields
        $this->form->setFields(array($id,$nif,$name,$save_button,$new_button,$list_button));

        $buttons = new THBox;
        $buttons->add($save_button);
        $buttons->add($new_button);
        $buttons->add($list_button);

        $container = new TTable;
        $container->width = '80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', 'SystemProvidersList'));
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
            $object = $this->form->getData('SystemProviders');

            $this->form->validate(); // form validation
            $object->store(); // stores the object

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
                $object = new SystemProviders($key);

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
