<?php
/**
 * System_group Active Record
 * @author  <your-name-here>
 */
class SystemNotes extends TRecord
{
    const TABLENAME = 'system_notes';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}


    private $system_programs = array();

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('date');
        parent::addAttribute('hour');
        parent::addAttribute('place');
        parent::addAttribute('description');
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_userSystem_user_group objects
        $id = isset($id) ? $id : $this->id;
        $repository = new TRepository('SystemNotes');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id', '=', $id));
        $repository->delete($criteria);

        // delete the object itself
        parent::delete($id);
    }

}
