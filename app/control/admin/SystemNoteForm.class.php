<?php
/**
 * System_groupForm Registration
 * @author  linkERP
 */
class SystemNoteform extends TPage
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
        $frame_programs->setLegend(_t('Programs'));
        
        // creates the form
        $this->form = new TForm('form_System_group');
        $this->form->class = 'tform';
        
        
        // add the notebook inside the form
        $this->form->add($table);
        $table->addRowSet( new TLabel(_t('Notes')), '' )->class = 'tformtitle';

        // create the form fields
        $hour            = new TEntry('hour');
        $place           = new TEntry('place');
        $description     = new TEntry('description');
        $multifield      = new TMultiField('programs');
        $program_id      = new TDBSeekButton('program_hour', 'permission', 'form_System_group', 'SystemProgram', 'place','description', 'programs_hour', 'programs_place','programs_description');
        $program_place   = new TEntry('program_place');
        
        $frame_programs->add($multifield);    
        
        $multifield->setHeight(200);
        $multifield->setClass('SystemProgram');
        $multifield->addField('hour', ' Hour',  $program_hour, 80, true);
        $multifield->addField('place',_t('Place'), $program_place, 350);
        $multifield->addField('description',_t('Description'), $program_description, 350);
        $multifield->setOrientation('horizontal');
        
        // define the sizes
        $program_hour->setSize(70);
        $hour->setSize(100);
        $place->setSize(200);

        // validations
        $place->addValidation('place', new TRequiredValidator);
        
        // outras propriedades
        $hour->setEditable(false);
        $program_place->setEditable(false);

        // add a row for the field id
        $table->addRowSet(new TLabel('Hour:'), $hour);
        $table->addRowSet(new TLabel(_t('place') . ': '), $place);
        
        // add a row for the field name
        $row = $table->addRow();
        $cell = $row->addCell($frame_programs);
        $cell->colspan = 2;

        // create an action button (save)
        $save_button=new TButton('save');
        $save_button->setAction(new TAction(array($this, 'onSave')), _t('Save'));
        $save_button->setImage('fa:floppy-o');
        
        // create an new button (edit with no parameters)
        $new_button=new TButton('new');
        $new_button->setAction(new TAction(array($this, 'onEdit')), _t('New'));
        $new_button->setImage('fa:plus-square green');
        
        $list_button=new TButton('list');
        $list_button->setAction(new TAction(array('CalendarView','onReload')), _t('Back to the listing'));
        $list_button->setImage('fa:table blue');

        // define the form fields
        $this->form->setFields(array($id,$name,$multifield,$save_button,$new_button,$list_button));
        
        $buttons = new THBox;
        $buttons->add($save_button);
        $buttons->add($new_button);
        $buttons->add($list_button);
        
        $container = new TTable;
        $container->width = '80%';
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', 'CalendarView'));
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
